<?php

class NewsletterWoocommerce extends NewsletterAddon {

    /**
     * @var NewsletterWoocommerce
     */
    static $instance;
    
    var $is_checkout = false;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('woocommerce', $version);
        $this->setup_options();
        add_filter('newsletter_automated_themes', array($this, 'hook_newsletter_automated_themes'));
    }

    function init() {
        parent::init();

        add_action('woocommerce_order_status_completed', array($this, 'hook_woocommerce_order_status_completed'));
        add_action('woocommerce_order_status_processing', array($this, 'hook_woocommerce_order_status_completed'));
        //add_action('woocommerce_order_status_pending', array($this, 'hook_woocommerce_order_status_completed'));
        //add_action('woocommerce_order_status_on-hold', array($this, 'hook_woocommerce_order_status_completed'));
        add_action('woocommerce_account_dashboard', array($this, 'hook_woocommerce_account_dashboard'));
        add_filter('newsletter_blocks_dir', array($this, 'hook_newsletter_blocks_dir'));

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_subscription', array($this, 'hook_newsletter_menu_subscription'));
            }
            add_filter('newsletter_lists_notes', array($this, 'hook_newsletter_lists_notes'), 20, 2);
        } else {
            if (isset($this->options['enabled']) && $this->options['enabled']) {
                add_action('woocommerce_checkout_process', [$this, 'hook_woocommerce_checkout_process']);
                add_action(empty($this->options['location']) ? 'woocommerce_review_order_before_submit' : $this->options['location'], array($this, 'hook_woocommerce_checkbox'));
                add_action('woocommerce_after_checkout_validation', array($this, 'hook_woocommerce_after_checkout_validation'));

                // Customer registration
                add_action('woocommerce_register_form', array($this, 'hook_woocommerce_register_form'));
                add_action('woocommerce_created_customer', array($this, 'hook_woocommerce_created_customer'), 99);
            }
        }
    }
    
    function hook_woocommerce_checkout_process() {
        $this->is_checkout = true;
    }

    function hook_newsletter_automated_themes($themes) {
        $themes[] = __DIR__ . '/themes/automated/woocommerce';
        return $themes;
    }

    function hook_newsletter_blocks_dir($blocks_dir) {
        $blocks_dir[] = __DIR__ . '/blocks';
        return $blocks_dir;
    }

    function hook_newsletter_menu_subscription($entries) {
        $entries[] = array('label' => '<i class="fas fa-shopping-cart"></i> Woocommerce', 'url' => '?page=newsletter_woocommerce_index', 'description' => 'Woocommerce checkout integration');
        return $entries;
    }

    function hook_newsletter_lists_notes($notes, $list_id) {
        for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {

            if (empty($this->options['rule_product_' . $i . '_id'])) {
                continue;
            }

            if ($this->options['rule_product_' . $i . '_list'] == $list_id) {
                $notes[] = 'Linked to specific product purchase';
                break;
            }
        }

        for ($i = 1; $i <= 20; $i++) {
            if (empty($this->options['rule_category_' . $i . '_id'])) {
                continue;
            }

            if ($this->options['rule_category_' . $i . '_list'] == $list_id) {
                $notes[] = 'Linked to product purchase in specific category';
                break;
            }
        }
        return $notes;
    }

    /**
     * When an order is completed, the segmentation rules are applied.
     * 
     * @global wpdb $wpdb
     * @param int $order_id
     */
    function hook_woocommerce_order_status_completed($order_id) {
        global $wpdb;

        $logger = $this->get_logger();

        $logger->debug('Processing order completed id ' . $order_id);

        $order = new WC_Order($order_id);

        // TODO: Manage the possible associated user
        // Only for version 3
        if (!method_exists($order, 'get_billing_email')) {
            $logger->debug('get_billing_email() does not exists');
            return;
        }

        $items = $order->get_items();
        $logger->debug('The order contains ' . count($items) . ' items');

        // User data to be updated
        $data = array();

        /* @var $item WC_Order_Item */
        foreach ($items as $item) {

            // Very object oriented
            $product_id = $item['product_id'];

            $logger->debug('Checking the product id ' . $product_id);

            // Product rules
            for ($i = 1; $i <= 20; $i++) {
                $logger->debug('Analyzing product rule ' . $i);
                if (empty($this->options['rule_product_' . $i . '_id'])) {
                    $logger->debug('Products not specified');
                    continue;
                }
                if (empty($this->options['rule_product_' . $i . '_list'])) {
                    $logger->debug('List not assigned');
                    continue;
                }

                $id = $this->options['rule_product_' . $i . '_id'];
                $list = (int) $this->options['rule_product_' . $i . '_list'];
                $list_value = empty($this->options['rule_product_' . $i . '_action']) ? 1 : 0;

                $product_ids = array_map('intval', explode(',', $id));
                
                $logger->debug($product_ids);
                if (in_array($product_id, $product_ids)) {
                    $logger->debug('Match found');
                    $data['list_' . $list] = $list_value;
                } else {
                    $logger->debug('Match not found');
                }
            }

            // Category rules
            for ($i = 1; $i <= 20; $i++) {
                $logger->debug('Analyzing category rule ' . $i);
                if (empty($this->options['rule_category_' . $i . '_id'])) {
                    $logger->debug('Rule ' . $i . ' is empty');
                    continue;
                }
                if (empty($this->options['rule_category_' . $i . '_list'])) {
                    $logger->debug('List not assigned');
                    continue;
                }

                $id = $this->options['rule_category_' . $i . '_id'];
                $list = $this->options['rule_category_' . $i . '_list'];
                $list_value = empty($this->options['rule_category_' . $i . '_action']) ? 1 : 0;

                $logger->debug('Checking against the categories ' . print_r($id, true));

                if (has_term($id, 'product_cat', $product_id)) {
                    $logger->debug('Match found');
                    $data['list_' . $list] = $list_value;
                } else {
                    $logger->debug('Match not found');
                }
            }
        }

        $logger->debug('User data: ' . print_r($data, true));

        // Se l'è el caso, aggiorna la sottoscrizione
        if (!empty($data)) {
            // Trova l'email del cliente, crea la query per il set delle liste
            // DA SISTEMARE, usare il customer id? ad esempio anche sistemando l'aggancio per email tra
            // il customer ed un iscritto se hanno la stessa email e si sono disallineati
            $email = $order->get_billing_email();
            $r = $wpdb->update(NEWSLETTER_USERS_TABLE, $data, array('email' => $email));

            $logger->debug('Update result ' . print_r($r, true));
        }
    }

    /**
     * Adds a checkbox to the WooCommerce registration form.
     */
    function hook_woocommerce_register_form() {
        if (empty($this->options['registration_ask'])) {
            return;
        }

        $language = $this->get_current_language();
        echo '<input type="hidden" name="tnp-nlang" value="', esc_attr($language), '">';

        if ($this->options['registration_ask'] == '1') {
            $ask_text = $this->get_label('registration_ask_text');
            echo '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox">
            <input type="checkbox" class="input-checkbox" name="tnp-nl" ', ($this->options['registration_checked'] ? "checked" : ""), '> <span>', $ask_text, '</span></label></p>';
        }
    }

    /**
     * Intercept a customer registration (which is actually a WP user) with the
     * WC registration feature.
     */
    function hook_woocommerce_created_customer($wp_user_id) {
        global $wpdb;

        $logger = $this->get_logger();
        
        if ($this->is_checkout) {
            $logger->info('Registration during checkout, subscription managed by the checkout process.');
            return;
        }
        
        if (empty($this->options['registration_ask'])) {
            $logger->debug('Subscription on registration disabled');
            return;
        }

        if (!empty($_POST['tnp-nl']) || $this->options['registration_ask'] == '2') {
            $wp_user = $wpdb->get_row($wpdb->prepare("select * from $wpdb->users where id=%d limit 1", $wp_user_id));
            if (empty($wp_user)) {
                $logger->fatal('WP user not found?!');
                return;
            }

            // Yes, some registration procedures allow empty email
            if (!NewsletterModule::is_email($wp_user->user_email)) {
                $logger->fatal('WP user without a valid email?!');
                return;
            }

            // Check for existing subscriber
            $user = Newsletter::instance()->get_user($wp_user->user_email);
            if ($user) {
                // TODO: Evaluate a subscriber update
                $logger->info("Subscriber already registered");
                return;
            }

            $language = $this->get_current_language();
            if (isset($_POST['tnp-nlang'])) {
                $language = $_POST['tnp-nlang'];
            }

            $subscription_module = NewsletterSubscription::instance();

            $subscription = $subscription_module->get_default_subscription();
            if (!empty($this->options['registration_status'])) {
                $subscription->optin = $this->options['registration_status'];
            }
            if ($subscription->optin === 'single') {
                $subscription->send_emails = $this->options['registration_welcome_email'] == 1;
            }

            $subscription->data->email = $wp_user->user_email;
            $subscription->data->name = get_user_meta($wp_user_id, 'first_name', true);
            $subscription->data->surname = get_user_meta($wp_user_id, 'last_name', true);
            $subscription->data->referrer = 'woocommerce-registration';
            $this->add_addon_lists_preference_to($subscription->data->lists);

            $user = NewsletterSubscription::instance()->subscribe2($subscription);

            // Now we associate it with wp
            if (!( $user instanceof WP_Error )) {
                Newsletter::instance()->set_user_wp_user_id($user->id, $wp_user_id);
            }
        }
    }

    /**
     *
     * @param array $posted
     */
    function hook_woocommerce_after_checkout_validation($posted) {

        $logger = $this->get_logger();
        
        $logger->debug('Checkout');

        if (empty($posted) || !isset($posted['billing_email'])) {
            $logger->debug('Billing email not present. Stop.');
            $logger->debug($posted);
            return;
        }

        $email = NewsletterModule::normalize_email($posted['billing_email']);

        if (!$email) {
            $logger->error("Email not valid: " . $posted['billing_email'] . '. Stop.');
            return;
        }

        $is_forced = !$this->options['ask'];
        $is_checked = isset($_POST['tnp-nl']);
        $language = $this->get_current_language();
        if (isset($_POST['tnp-nlang'])) {
            $language = $_POST['tnp-nlang'];
        }

        // If we already have this subscriber (by email) skip the subscription process but update list
        if ($user = Newsletter::instance()->get_user($email)) {
            $logger->info("Subscriber already registered");

            $user_current_status = $user->status;

            // Update user info
            $user = array(
                'id' => $user->id,
                'name' => $posted['billing_first_name'],
                'surname' => $posted['billing_last_name']
            );

            $this->update_user_lists_from_addon_preferences($user);

            //If is checked or is forced then force subscription status
            if ($user_current_status != TNP_User::STATUS_CONFIRMED && ( $is_checked || $is_forced )) {
                if (!empty($this->options['status']) && $this->options['status'] === 'single') {
                    $user['status'] = TNP_User::STATUS_CONFIRMED;
                } else {
                    //TODO not handled case
                }
            }

            Newsletter::instance()->save_user($user);

            return;
        }

        // New subscriber
        if ($is_checked || $is_forced) {
            $logger->info("Subscribing: " . $email);

            $subscription_module = NewsletterSubscription::instance();

            $subscription = $subscription_module->get_default_subscription($language);
            if (!empty($this->options['status'])) {
                $subscription->optin = $this->options['status'];
            }

            if ($subscription->optin === 'single') {
                $subscription->send_emails = $this->options['confirm'] === '1';
            }
            $subscription->data->email = $email;
            $subscription->data->name = isset($posted['billing_first_name']) ? $posted['billing_first_name'] : null;
            $subscription->data->surname = isset($posted['billing_last_name']) ? $posted['billing_last_name'] : null;
            $subscription->data->referrer = 'woocommerce-checkout';
            $this->add_addon_lists_preference_to($subscription->data->lists);
            
            $logger->debug($subscription);

            $r = $subscription_module->subscribe2($subscription);
        }
    }

    private function add_addon_lists_preference_to(&$lists) {

        for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {
            if (!empty($this->options['preferences_' . $i])) {
                $lists[$i] = 1;
            }
        }
    }

    private function update_user_lists_from_addon_preferences(&$user) {
        // Update the lists set on woocommerce integration preferences
        for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {
            if (!empty($this->options['preferences_' . $i])) {
                $user['list_' . $i] = 1;
            }
        }
    }

    function hook_woocommerce_checkbox() {

        //Don't show checkbox if email is already subscribed and confirmed
        if ($this->is_billing_email_already_subscribed_and_confirmed()) {
            if (current_user_can('administrator') && ( $this->options['ask'] == 1 )) {
                echo "<p>The newsletter subscription checkbox is not visible because you are already subscribed. This message is visible only to administrators.</p>";
            }

            return;
        }

        $language = $this->get_current_language();
        echo '<input type="hidden" name="tnp-nlang" value="' . esc_attr($language) . '">';

        if ($this->options['ask'] == 1) {

            $ask_text = $this->get_label('ask_text');

            echo "<div class='tnp-nl-checkout form-row'>
                    <label for='tnp-nl-checkout-checkbox' class='tnp-nl-checkout-label checkbox'>
                        <input type='checkbox' name='tnp-nl' id='tnp-nl-checkout-checkbox' class='input-checkbox' " . ( $this->options['checked'] ? "checked" : "" ) . " />
                        <span>$ask_text</span>
                    </label>
                </div>";
        }
    }

    private function is_billing_email_already_subscribed_and_confirmed() {
        //Don't show checkbox if email is already subscribed and confirmed
        try {
            $customer = new WC_Customer(get_current_user_id());
            $subscriber = Newsletter::instance()->get_user($customer->get_billing_email());
            if ($subscriber && $subscriber->status == TNP_User::STATUS_CONFIRMED) {
                return true;
            }
        } catch (Exception $e) {
            
        }

        return false;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'WooCommerce', '<span class="tnp-side-menu">WooCommerce</span>', 'manage_options', 'newsletter_woocommerce_index', array($this, 'menu_page_index'));
        add_submenu_page(null, 'WooCommerce Import', 'WooCommerce Import', 'manage_options', 'newsletter_woocommerce_import', array($this, 'menu_page_import'));
    }

    function menu_page_index() {
        global $wpdb, $newsletter;
        require dirname(__FILE__) . '/index.php';
    }

    function menu_page_import() {
        global $wpdb, $newsletter;
        require dirname(__FILE__) . '/import.php';
    }

    /**
     * Returns a label using the configured values from the options panel, is not empty or the standard
     * values from the gettext files possibly translated by a multilanguage plugin.
     *
     * @param string $key
     * @return string
     */
    function get_label($key) {
        if (!empty($this->options[$key])) {
            return $this->options[$key];
        }

        switch ($key) {
            case 'profile_link_label': return __('Manage your newsletter preferences', 'newsletter-woocommerce');
            case 'ask_text': return __('Subscribe to our newsletter', 'newsletter-woocommerce');
        }
    }

    function hook_woocommerce_account_dashboard() {

        try {

            if (isset($this->options['hide_profile_link']) && $this->options['hide_profile_link']) {
                return;
            }

            $customer = new WC_Customer(get_current_user_id());

            //Get subscriber attached to the logged in customer
            $subscriber = NewsletterProfile::instance()->get_user_by_wp_user_id($customer->get_id());
            $profile_url = $this->get_confirmed_subscriber_profile_url($subscriber);

            $notice = 'Subscriber connected by WordPress user ID.';

            if (empty($profile_url)) {
                //Get subscriber attached to the logged in customer email
                $subscriber = NewsletterProfile::instance()->get_user($customer->get_email());
                $profile_url = $this->get_confirmed_subscriber_profile_url($subscriber);
                $notice = 'Subscriber connected by customer email.';
            }

            if (empty($profile_url)) {
                //Get subscriber attached to the billing customer email
                $subscriber = NewsletterProfile::instance()->get_user($customer->get_billing_email());
                $profile_url = $this->get_confirmed_subscriber_profile_url($subscriber);
                $notice = 'Subscriber connected by customer billing email.';
            }

            if (empty($profile_url)) {
                return;
            }

            echo "<div class='tnp-newsletter-profile-url'>";
            echo "<a href='$profile_url'>" . $this->get_label('profile_link_label') . "</a>";
            if (current_user_can('administrator')) {
                echo '<p style="background-color: #eee; color: #000; padding: 10px; margin: 10px 0">' . $notice . ' <strong>This notice is shown only to administrators to help understand connection to subscribers.</strong></p>';
            }
            echo "</div>";
        } catch (Exception $e) {
            //Catch Exception thrown if customer cannot be read/found and $data is set and do nothing.
        }
    }

    private function get_confirmed_subscriber_profile_url($subscriber) {
        $newsletter_profile_page = '';

        //Check if is a confirmed subscriber
        if ($subscriber && $subscriber->status === TNP_User::STATUS_CONFIRMED) {
            $newsletter_profile_page = NewsletterProfile::instance()->get_profile_url($subscriber);
        }

        return $newsletter_profile_page;
    }

}
