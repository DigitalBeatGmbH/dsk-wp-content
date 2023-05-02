<?php
/* @var $this NewsletterAutomated */

global $wpdb;
require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';

$newsletter_emails = NewsletterEmails::instance();
$controls = new NewsletterControls();

$channel_id = (int) $_GET['id'];
$channel = $this->get_channel($channel_id);
$email = Newsletter::instance()->get_email($channel->email_id);

if (!$controls->is_action()) {
    NewsletterEmails::instance()->regenerate($email, array('type' => 'automated', 'last_run' => 0));
    $email->subject = $channel->data['subject'];
    TNP_Composer::prepare_controls($controls, $email);
} else {

    if ($controls->is_action('save') || $controls->is_action('configure')) {

        TNP_Composer::update_email($email, $controls);
        $email = NewsletterEmails::instance()->save_email($email);
        
        $channel->data['subject'] = $email->subject;
        
        $wpdb->update($wpdb->prefix . "newsletter_automated", array('data' => json_encode($channel->data)), array('id' => $channel_id));

        // Old code kept for compatibility
        $wpdb->update($wpdb->prefix . "newsletter_automated", array('theme' => $email->message), array('id' => $channel_id));

        TNP_Composer::prepare_controls($controls, $email);
        $controls->add_message_saved();
    }

    if ($controls->is_action('configure')) {
        $controls->js_redirect('?page=newsletter_automated_index&id=' . $channel_id);
    }
}
?>

<div class="wrap" id="tnp-wrap">

    <?php $controls->show(); ?>

    <div class="tnp-automated-edit">

        <form method="post" id="tnpc-form" action="" onsubmit="tnpc_save(this); return true;">
            <?php $controls->init(); ?>

            <p>
                <?php $controls->button_icon_back('?page=newsletter_automated_index') ?>
                <?php $controls->button_save() ?>
                <?php $controls->button_icon('configure', 'fa-cog', 'Configure') ?>
            </p>
            <?php $controls->composer_fields_v2() ?>

        </form>
        <?php $controls->composer_load_v2(true, false, 'automated') ?>

    </div>

</div>
