<?php
/* @var $wpdb wpdb */
/* @var $this NewsletterWoocommerce */

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();
$logger = $this->get_admin_logger();

if (!$controls->is_action()) {

    $controls->data = $this->options;

    if (empty($controls->data['location'])) {
        $controls->data['location'] = 'woocommerce_review_order_before_submit';
    }

} else {
    // Product IDs from CSV to array

    $this->save_options($controls->data);
    for ($rule = 1; $rule <= 20; $rule++) {
        $product_ids = array_map('intval', explode(',', $controls->data['rule_product_' . $rule . '_id']));
        $controls->data['rule_product_' . $rule . '_id'] = $product_ids;
    }

    if ($controls->is_action('save')) {
        $controls->add_message_saved();
    }

    if ($controls->is_action('run-product')) {
        $rule = (int) $controls->button_data;
        // Could be the category or a product

        $list = (int) $controls->data['rule_product_' . $rule . '_list'];
        $product_ids_for_query = implode(',', $controls->data['rule_product_' . $rule . '_id']);

        $list_value = empty($controls->data['rule_product_' . $rule . '_action']) ? 1 : 0;

        // Get all the orders with items within the specified categories
        $items = $wpdb->get_results("select * from {$wpdb->prefix}woocommerce_order_items i 
                    join {$wpdb->prefix}woocommerce_order_itemmeta im on 
                    im.order_item_id=i.order_item_id and im.meta_key='_product_id' and im.meta_value in ({$product_ids_for_query})");

        $logger->debug('Found ' . count($items) . ' items');

        $count = 0;
        foreach ($items as $item) {
            $logger->debug('Processing order ' . $item->order_id);
            $order_id = $item->order_id;
            $order = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->posts} where id=%d and post_status IN ('wc-processing','wc-completed')", $order_id));
            if (!$order) {
                $logger->debug('Order not found or with wrong status');
                continue;
            }

            //$logger->debug($order);
            // Key "_customer_user" to have the WP user ID if associated
            $meta = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->postmeta} where post_id=%d and meta_key='_billing_email'", $order_id));

            if (!$meta) {
                $logger->debug('Order billing email not found');
                continue;
            }

            $email = Newsletter::normalize_email($meta->meta_value);

            $r = $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set list_{$list}={$list_value} where email=%s limit 1", $email));
            if ($r) {
                $count++;
            }
        }

        /*
          // Roba che spacca, chiedere a stefano
          // newsletter -> join con il billing address che è nei post meta dell'ordine -> che va in join con gli ordini per avere lo stato
          // completed, che va in join con gli item per avere il numero di ordine che va in join con gli item meta per avere il codice prodotto
          $r = $wpdb->query("

          update {$wpdb->prefix}newsletter n

          join {$wpdb->prefix}postmeta om
          on om.meta_value=n.email

          join {$wpdb->prefix}posts o on o.id=om.post_id and om.meta_key='_billing_email'

          join {$wpdb->prefix}woocommerce_order_items i
          on i.order_id=o.id and post_status='wc-completed'

          join {$wpdb->prefix}woocommerce_order_itemmeta im
          on im.order_item_id=i.order_item_id and im.meta_key='_product_id' and im.meta_value in ({$product_ids_for_query})

          set list_{$list}=1");
         */
        $controls->messages = "$count subscriber(s) changed";
    }

    if ($controls->is_action('run-category')) {
        $rule = (int) $controls->button_data;
        // Could be the category or a product
        $category_ids = $controls->data['rule_category_' . $rule . '_id'];
        $list = (int) $controls->data['rule_category_' . $rule . '_list'];

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => '10000',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                    'terms' => $category_ids,
                    'operator' => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
                )
            )
        );
        $products = get_posts($args);
        $product_ids = array();
        foreach ($products as $product) {
            $product_ids[] = "'" . $product->ID . "'";
        }

        $product_ids = implode(',', $product_ids);

        $list_value = empty($controls->data['rule_category_' . $rule . '_action']) ? 1 : 0;

        // Get all the orders with items within the specified categories
        $items = $wpdb->get_results("select * from {$wpdb->prefix}woocommerce_order_items i 
                    join {$wpdb->prefix}woocommerce_order_itemmeta im on 
                    im.order_item_id=i.order_item_id and im.meta_key='_product_id' and im.meta_value in ({$product_ids})");

        $count = 0;
        foreach ($items as $item) {
            $order_id = $item->order_id;
            $order = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->posts} where id=%d and post_status IN ('wc-processing','wc-completed')", $order_id));
            if (!$order)
                continue;

            $meta = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->postmeta} where post_id=%d and meta_key='_billing_email'", $order_id));

            if (!$meta)
                continue;

            $email = Newsletter::normalize_email($meta->meta_value);

            $r = $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set list_{$list}={$list_value} where email=%s limit 1", $email));
            if ($r)
                $count++;
        }

        /*
          $r = $wpdb->query("

          update {$wpdb->prefix}newsletter n

          join {$wpdb->prefix}postmeta om
          on om.meta_value=n.email

          join {$wpdb->prefix}posts o on o.id=om.post_id and om.meta_key='_billing_email'

          join {$wpdb->prefix}woocommerce_order_items i
          on i.order_id=o.id and post_status='wc-completed'

          join {$wpdb->prefix}woocommerce_order_itemmeta im
          on im.order_item_id=i.order_item_id and im.meta_key='_product_id' and im.meta_value in ({$product_ids})

          set list_{$list}={$list_value}");
         */


        $controls->messages = "$count subscriber(s) changed";
    }
}

// Conversion of product IDs from array to CSV
for ($rule = 1; $rule <= 20; $rule++) {
    if (!isset($controls->data['rule_product_' . $rule . '_id']))
        continue;
    if (!is_array($controls->data['rule_product_' . $rule . '_id']))
        continue;

    $controls->data['rule_product_' . $rule . '_id'] = implode(',', $controls->data['rule_product_' . $rule . '_id']);
}

// recupero i prodotti per le select
//$all_products = array();
//$args = array(
//    'post_type' => 'product',
//    'posts_per_page' => -1
//);
//
//$products = get_posts($args);
//foreach ($products as $product) {
//    $all_products[$product->ID] = $product->post_title;
//}
// recupero le categorie per le select
$all_categories = array();
$product_categories = get_terms($args = array(
    'taxonomy' => "product_cat",
    'hide_empty' => false,
//        'parent'     => 0,
        ));

foreach ($product_categories as $cat) {
    $all_categories[$cat->term_id] = $cat->name;
}
?>

<div class="wrap" id="tnp-wrap">
    <?php @include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">

        <h3>WooCommerce Integration</h3>
        <h2>General Configuration</h2>

        <?php $controls->show(); ?>

        <?php echo $controls->page_help("https://www.thenewsletterplugin.com/documentation/woocommerce-extension"); ?>
    </div>


    <div id="tnp-body">

        <form action="" method="post">
            <?php $controls->init(); ?>

            <p>
                <?php $controls->button_save(); ?>
                <a href="admin.php?page=newsletter_woocommerce_import" class="button-primary"><i class="fas fa-arrow-circle-up"></i> <?php _e('Import customers from orders', 'newsletter') ?></a>
            </p>

            <div id="tabs">
                <ul>
                    <li><a href="#tabs-general"><?php _e('General', 'newsletter') ?></a></li>
                    <li><a href="#tabs-rules-product"><?php _e('Rules by product', 'newsletter') ?></a></li>
                    <li><a href="#tabs-rules-category"><?php _e('Rules by category', 'newsletter') ?></a></li>
                </ul>

                <div id="tabs-general">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th>Enabled</th>
                                <td>
                                    <?php $controls->yesno('enabled'); ?>
                                    <p class="description">
                                        When not enabled this addon stops interacting with WooCommerce checkout.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <h3>Subscription at the checkout</h3>
                    <p>
                        If a subscriber already exists with the email provided at checkout, it is only updated with the first name, last name and 
                        lists specified below.
                    </p>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th>Subscribe on checkout</th>
                                <td>
                                    <?php $controls->select('ask', array(0 => 'Force subscription', 1 => 'Show a checkbox')); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>
                                    <?php $controls->select('location', array(
                                        'woocommerce_before_checkout_billing_form' => 'Before billing form',
                                        'woocommerce_after_checkout_billing_form' => 'After billing form',
                                        'woocommerce_review_order_before_submit' => 'Before submit button',
                                        'woocommerce_review_order_after_submit' => 'After submit button'
                                    )); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Subscription checkbox</th>
                                <td>
                                    <?php $controls->text('ask_text', 50, __('Subscribe to our newsletter', 'newsletter-woocommerce')); ?>
                                    <?php $controls->select('checked', array("0" => "Unchecked", "1" => "Checked")); ?>
                                    <p class="description">
                                        <?php if (NewsletterWoocommerce::$instance->is_multilanguage()): ?>
                                            Leave  empty and use your multilanguage plugin to translate it.
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th><?php _e('Opt-in', 'newsletter') ?></th>
                                <td>
                                    <?php $controls->select('status', ['' => 'Default', 'double' => 'Double opt-in', 'single' => 'Single opt-in']); ?>
                                    <p class="description">
                                        Double opt-in requires confirmation with an activation email.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>Send welcome email?</th>
                                <td>
                                    <?php $controls->yesno('confirm'); ?>
                                    <p class="description">
                                        Only for single opt-in. With double opt-in the welcome email si controlled by the Newsletter subscription configiration.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>Add users to these lists</th>
                                <td><?php $controls->preferences() ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <h3>WooCommerce Registration Form</h3>
                    <p>
                        Not connected to the checkout: this is a registration form to signup the site by WooCommerce replacing the standard
                        WP registration form.
                    </p>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th>Subscribe on registration</th>
                                <td>
                                    <?php $controls->select('registration_ask', [0 => 'Disabled', 1 => 'Show a checkbox', 2 => 'Force the subscription']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Registration checkbox</th>
                                <td>
                                    <?php $controls->text('registration_ask_text', 50, __('Subscribe to our newsletter', 'newsletter-woocommerce')); ?>
                                    <?php $controls->select('registration_checked', ['0' => 'Unchecked', '1' => 'Checked']); ?>
                                    <p class="description">
                                        <?php if (NewsletterWoocommerce::$instance->is_multilanguage()): ?>
                                            Leave  empty and use your multilanguage plugin to translate it.
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th><?php _e('Opt-in', 'newsletter') ?></th>
                                <td>
                                    <?php $controls->select('registration_status', ['' => 'Default', 'double' => 'Double opt-in', 'single' => 'Single opt-in']); ?>
                                    <p class="description">
                                        Double opt-in requires confirmation with an activation email.
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th><?php _e('Send welcome email', 'newsletter') ?></th>
                                <td>
                                    <?php $controls->yesno('registration_welcome_email'); ?>
                                    <p class="description">
                                        Only for single opt-in. With double opt-in the welcome email si controlled by the Newsletter subscription configiration.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <h3>Customer personal page</h3>           
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th>Hide link to subscription management page on customer account</th>
                                <td>
                                    <?php $controls->yesno('hide_profile_link'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Subscription management link label</th>
                                <td>
                                    <?php $controls->text('profile_link_label', 50, __('Manage your newsletter preferences', 'newsletter-woocommerce')) ?>
                                    <p class="description">
                                        Shown on customer account page (if subscribed). 
                                        <?php if (NewsletterWoocommerce::$instance->is_multilanguage()): ?>
                                            <br>Leave empty and use your multilanguage plugin to translate it.
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="tabs-rules-product">

                    <h3>If a customer purchased a product...</h3>
                    <p>Multiple id can be specified comma separated. Only already subscribed customers will be processed.</p>
                    <table class="form-table">
                        <tbody>
                            <?php for ($i = 1; $i <= 20; $i++) { ?>
                                <tr>
                                    <th>Rule <?php echo $i ?></th>
                                    <td>
                                        <?php /* Products <?php $controls->select2('rule_product_' . $i . '_id', $all_products, null, true, "width: 500px") ?> add to list <?php $controls->preferences_select('rule_product_' . $i . '_list') ?> */ ?>
                                        Product IDs <?php $controls->text('rule_product_' . $i . '_id') ?>
                                        <?php $controls->select('rule_product_' . $i . '_action', array('' => 'add to list', 1 => 'remove from list')) ?>
                                        <?php $controls->preferences_select('rule_product_' . $i . '_list') ?>
                                        <?php $controls->button_confirm('run-product', 'Run now', 'Proceed?', $i) ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div id="tabs-rules-category">

                    <h3>If a customer purchased a product in category...</h3>
                    <p>Only already subscribed customers will be processed.</p>
                    <table class="form-table">
                        <tbody>
                            <?php for ($i = 1; $i <= 20; $i++) { ?>
                                <tr>
                                    <th>Rule <?php echo $i ?></th>
                                    <td>
                                        Categories <?php $controls->select2('rule_category_' . $i . '_id', $all_categories, null, true, "width: 500px") ?>
                                        <?php $controls->select('rule_category_' . $i . '_action', array('' => 'add to list', 1 => 'remove from list')) ?>
                                        <?php $controls->preferences_select('rule_category_' . $i . '_list') ?>
                                        <?php $controls->button_confirm('run-category', 'Run now', 'Proceed?', $i) ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <p>
                    <?php $controls->button_save(); ?>
                </p>

        </form>


    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
