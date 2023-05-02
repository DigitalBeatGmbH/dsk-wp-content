<?php
/* @var $wpdb wpdb */
/* @var $this NewsletterWoocommerce */

defined('ABSPATH') || exit;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$options_profile = get_option('newsletter_profile');

$last_imported_id = (int) get_option('newsletter_woocommerce_last_imported_id', 0);

if (!$controls->is_action()) {

    $controls->data = get_option('newsletter_woocommerce_import', array());
} else if ($controls->is_action('import')) {

    update_option('newsletter_woocommerce_import', $controls->data);

    $mode = $controls->data['mode'];

    // TODO: to be removed, it's not safe
    @set_time_limit(0);

    $results = '';

    // Order extraction
    $list = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->postmeta} where meta_key='_billing_email' and post_id>%d order by post_id", $last_imported_id));


    // Set the selected preferences inside the
    if (empty($controls->data['lists'])) {
        $controls->data['lists'] = array();
    }


    $error_count = 0;
    $added_count = 0;
    $updated_count = 0;
    $skipped_count = 0;

    $newsletter = Newsletter::instance();

    $status = $controls->data['import_as'];
    $override_status = isset($controls->data['override_status']);

    foreach ($list as $item) {
        $last_imported_id = (int) $item->post_id;
        $email = Newsletter::normalize_email($item->meta_value);
        if (empty($email)) {
            $results .= '[INVALID EMAIL] On order ' . $last_imported_id . ": " . $item->meta_value . "\n";
            $error_count++;
            update_option('newsletter_woocommerce_last_imported_id', $last_imported_id, false);
            continue;
        }

        $first_name = $wpdb->get_var("select meta_value from {$wpdb->postmeta} where post_id={$last_imported_id} and meta_key='_billing_first_name' limit 1");
        $last_name = $wpdb->get_var("select meta_value from {$wpdb->postmeta} where post_id={$last_imported_id} and meta_key='_billing_last_name' limit 1");
        $subscriber = $newsletter->get_user($email, ARRAY_A);
        if ($subscriber == null) {
            $subscriber = array();
            $subscriber['email'] = $email;
            if ($first_name) {
                $subscriber['name'] = $first_name;
            }
            if ($last_name) {
                $subscriber['surname'] = $last_name;
            }
            $subscriber['status'] = $status;
            foreach ($controls->data['lists'] as $i) {
                $subscriber['list_' . $i] = 1;
            }
            $newsletter->save_user($subscriber);
            $results .= '[ADDED] ' . $email . "\n";
            $added_count++;
        } else {
            if ($mode == 'skip') {
                $results .= '[SKIPPED] ' . $email . "\n";
                $skipped_count++;
            } else if ($mode == 'update') {
                if ($first_name) {
                    $subscriber['name'] = $first_name;
                }
                if ($last_name) {
                    $subscriber['surname'] = $last_name;
                }

                if ($override_status) {
                    $subscriber['status'] = $status;
                }
                foreach ($controls->data['lists'] as $i) {
                    $subscriber['list_' . $i] = 1;
                }
                $newsletter->save_user($subscriber);

                $results .= '[UPDATED] ' . $email . "\n";
                $updated_count++;
            }
        }

        update_option('newsletter_woocommerce_last_imported_id', $last_imported_id, false);
    }

    // If here, the import completed, if not the script died.
    $last_imported_id = 0;
    update_option('newsletter_woocommerce_last_imported_id', 0, false);

    if ($error_count) {
        $controls->errors = "Import completed but with errors.";
    }
    $controls->messages = "Import completed: $error_count errors, $added_count added, $updated_count updated, $skipped_count skipped.";
}

// We didn0t complete the import
if ($last_imported_id) {
    $controls->warnings[] = 'Last import job didn\'t complete, please run the import again (import option has been preserved)';
}
?>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">
        <h3>WooCommerce Integration</h3>
        <h2><?php _e('Import from orders', 'newsletter') ?></h2>
        <?php $controls->page_help('https://www.thenewsletterplugin.com/documentation/woocommerce-extension') ?>

    </div>

    <div id="tnp-body" class="tnp-users tnp-users-import">

        <?php if (!empty($results)) { ?>

            <h3>Results</h3>

            <textarea wrap="off" style="width: 100%; height: 150px; font-size: 11px; font-family: monospace"><?php echo esc_html($results) ?></textarea>

        <?php } ?>

        <form method="post" enctype="multipart/form-data">

            <?php $controls->init(); ?>

            <table class="form-table">

                <tr>
                    <th><?php _e('Import Customers As', 'newsletter') ?></th>
                    <td>
                        <?php $controls->select('import_as', array('C' => __('Confirmed', 'newsletter'), 'S' => __('Not confirmed', 'newsletter'))); ?>
                        <?php $controls->checkbox('override_status', __('Override status of existing subscribers', 'newsletter')) ?>
                    </td>
                </tr>

                <tr>
                    <th><?php _e('Import mode', 'newsletter') ?></th>
                    <td>
                        <?php $controls->select('mode', array('update' => 'Update', 'skip' => 'Skip')); ?>
                        if email is already present
                        <p class="description">
                            <strong>Update</strong>: <?php _e('Subscriber data will be updated, existing preferences will be left untouched and new ones will be added.', 'newsletter') ?><br />
                            <strong>Skip</strong>: <?php _e('Subscriber data will be left untouched if already present.', 'newsletter') ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th><?php _e('Lists', 'newsletter') ?></th>
                    <td>
                        <?php $controls->lists_checkboxes('lists'); ?>
                        <div class="hints">
                            Every new imported or updated subscriber will be associate with selected preferences above.
                        </div>
                    </td>
                </tr>

            </table>
            <p><?php $controls->button_confirm('import', 'Import'); ?></p>

        </form>

    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
