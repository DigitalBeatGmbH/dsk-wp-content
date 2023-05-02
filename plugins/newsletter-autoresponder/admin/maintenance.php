<?php
/* @var $this NewsletterAutoresponder */
/* @var $wpdb wpdb */

global $wpdb;

$autoresponder = $this->get_autoresponder($_GET['id']);
$logger = $this->get_admin_logger();
$newsletter = Newsletter::instance();

$debug = isset($_GET['debug']) || NEWSLETTER_DEBUG;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';

$controls = new NewsletterControls();

if (!$controls->is_action()) {
    $controls->set_data($autoresponder);
} else {

    if ($controls->is_action('test')) {
        $logger->info('Test mode set to ' . $controls->data['test'] . ' on autoresponder ' . $autoresponder->id);
        $wpdb->update("{$wpdb->prefix}newsletter_autoresponder", ['test' => (int) $controls->data['test']], ['id' => $autoresponder->id]);
        $controls->add_message_done();
        $autoresponder = $this->get_autoresponder($autoresponder->id);
        $controls->set_data($autoresponder);
    }

    if ($controls->is_action('reset')) {
        $logger->info('Reset called for autoresponder ' . $autoresponder->id);

        $controls->data['id'] = $autoresponder->id;
        $autoresponder = $this->save_autoresponder($controls->data);
        $controls->set_data($autoresponder);

        if (!$autoresponder->list) {
            $controls->errors = 'No list assigned.';
        } else {
            if ($autoresponder->emails) {
                // Get the first email to compute the first delay
                $email = $newsletter->get_email($autoresponder->emails[0]);
                $send_at = time() + $email->options['delay'] * 3600;
            } else {
                $send_at = time();
            }
            $list = (int) $autoresponder->list;
            $this->query($wpdb->prepare("delete from {$wpdb->prefix}newsletter_autoresponder_steps where autoresponder_id=%d", $autoresponder->id));

            $wpdb->query("insert ignore into " . $wpdb->prefix . "newsletter_autoresponder_steps (autoresponder_id, user_id, send_at) (
                    select " . $autoresponder->id . ", u.id, " . $send_at . " from " . $wpdb->prefix . "newsletter u left join " . $wpdb->prefix . "newsletter_autoresponder_steps s on u.id=s.user_id and autoresponder_id=" .
                    $autoresponder->id . " where s.user_id is null and u.list_" . $list . "=1)");
            $controls->add_message_reset();
        }
    }

    if ($controls->is_action('run')) {
        $this->hook_newsletter(true, $autoresponder);
        $controls->messages .= 'Engine triggered';
    }

    if ($controls->is_action('convert')) {
        $logger->info('Conversion triggered');
        foreach ($autoresponder->emails as $email_id) {
            $email = $newsletter->get_email($email_id);
            ob_start();
            NewsletterEmails::instance()->render_block('header', true);
            NewsletterEmails::instance()->render_block('text', true, ['html' => $email->message]);
            NewsletterEmails::instance()->render_block('footer', true);
            $body = ob_get_clean();
            $email->message = TNP_Composer::get_html_open($email) . TNP_Composer::get_main_wrapper_open($email) .
                    $body . TNP_Composer::get_main_wrapper_close($email) . TNP_Composer::get_html_close($email);
            $newsletter->save_email($email);
            $logger->info($email);
            $autoresponder->type = TNP_Autoresponder::TYPE_COMPOSER;
            $autoresponder->status = TNP_Autoresponder::STATUS_DISABLED;
            $this->save_autoresponder($autoresponder);
            //break;
        }
        $controls->add_message_done();
    }
}
?>

<style>
<?php include __DIR__ . '/admin.css'; ?>
</style>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>

    <div id="tnp-heading">
        <h3>Maintenance</h3>
        <h2><?php echo esc_html($autoresponder->name) ?></h2>
    </div>

    <div id="tnp-body">

        <form method="post" action="">
            <?php $controls->init(); ?>
            
            <div class="tnp-buttons">
                <?php $controls->button_icon_back('?page=newsletter_autoresponder_index&id=' . $autoresponder->id); ?>
            </div>

            <table class="form-table">
                <tr>
                    <th>Test mode</th>
                    <td>
                        <?php $controls->yesno('test') ?>
                        <?php $controls->button('test', 'Set'); ?>
                        <p class="description">
                            <strong>PLEASE USE WITH CARE!</strong><br>
                            In test mode messages are sent only if you force an engine run
                            (see below the ↻ button). Each run moves the subscribers a step forward sending them
                            the message.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>Trigger series processing</th>
                    <td>
                        <?php $controls->button_primary('run', 'Trigger') ?>
                        <p class="description">
                            Force the processing of pending emails in this series. <a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/autoresponder-extension/#trigger" target="_blank">Read more</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>Reset and restart</th>
                    <td>
                        <?php $controls->btn('reset', 'Go', ['confirm'=>true]); ?>
                        <p class="description">
                            Every subsceriber is <strong>removed</strong>, its status relative to this series <strong>cleaned up</strong> and the linked list
                            subscribers readded to the first step of the series.
                        </p>
                    </td>
                </tr>
                <?php if ($autoresponder->type == TNP_Autoresponder::TYPE_CLASSIC) { ?>
                    <tr>
                        <th>Convert to the new format</th>
                        <td>
                            <?php $controls->btn('convert', 'Convert to composer', ['confirm'=>true]); ?> 
                            <p class="description">
                                Converts this email series to the new version (editable with the composer).<br>
                                Remember to re-enable it after conversion.<br>
                                Only the body part is kept!<br>
                                PLEASE, save a copy of the original messages before proceed!
                            </p>
                        </td>
                    </tr> 
                <?php } ?>
            </table>

        </form>

    </div>
    
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
