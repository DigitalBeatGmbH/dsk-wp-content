<?php
/* @var $this NewsletterBounce */

include_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

if (!$controls->is_action()) {
    $controls->data = $this->options;
} else {
    if ($controls->is_action('save')) {
        $this->save_options($controls->data);
        $controls->add_message_saved();
    }

    if ($controls->is_action('trigger')) {
        $res = $this->bounce();
        $controls->messages = 'Done. Found ' . $res . ' bounces.';
    }

    if ($controls->is_action('test')) {
        $this->save_options($controls->data);
        $res = $this->run(true, 10);
        if (is_wp_error($res)) {
            /* @var $res WP_Error */
            $controls->errors = $res->get_error_message() . ' <a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/bounce-extension/#test-errors" target="_blank">Read more</a>';
        } else {
            $controls->messages = 'Total message in the mailbox: ' . $res['total_messages'] . '<br>';
            $controls->messages .= 'Processed messages: ' . $res['processed_messages'] . '<br>';
            $controls->messages .= 'Bounced addresses: ' . implode(', ', $res['emails']) . '<br>';
            $controls->messages .= 'If you sent a test message and no addresses are reported but messages are found it means the delivery system is encoding the message. It is ok, it is working.';
        }
    }

    if ($controls->is_action('send')) {
        $newsletter = Newsletter::instance();
        if (empty($newsletter->options['return_path'])) {
            $controls->errors = 'The Return Path address is not set on main Newsletter settings.';
        } else {
            $message = new TNP_Mailer_Message();
            $message->body_text = file_get_contents(__DIR__ . '/dsn.txt');
            $message->to = $newsletter->options['return_path'];
            $message->subject = 'Delivery Error Notification (Test)';
            $message->encoding = '7bit';
            $res = $newsletter->deliver($message);

            $controls->messages = 'Fake delivery error message sent to ' . Newsletter::instance()->options['return_path'] . '.<br>';
            $controls->messages .= 'Try now to test the bounce detection. It should detect a bounce for the fake address bounced@email-address.com.';
        }
    }
}

$controls->warnings = 'NEVER USE YOUR MAILBOX FOR BOUNCE DETECTION, CREATE A NEW AND DEDICATED MAILBOX!<br>';
$controls->warnings .= 'Please read carefully the <a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/bounce-extension/" target="_blank">documentation</a>.';
?>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h2>Bounce Management</h2>
        <p>Read 
            <a href="https://www.thenewsletterplugin.com/documentation/newsletters/bounces/" target="_blank">what are bounces (the easy way)</a> and 
            <a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/bounce-extension/" target="_blank">how to correctly configure this addon</a>.
            <?php $controls->show(); ?>

    </div>

    <div id="tnp-body">

        <form action="" method="post">
            <?php $controls->init(); ?>

            <table class="form-table">
                <tr>
                    <th>Address where errors are reported</th>
                    <td>
                        <?php echo esc_html(Newsletter::instance()->options['return_path']) ?>
                        <div class="description">Can be changed on Newsletter main settings</div>
                    </td>
                </tr>

                <tr>
                    <th>POP3 host/port</th>
                    <td>
                        host: <?php $controls->text('host', 30); ?>
                        port: <?php $controls->text('port', 6); ?>
                        security: <?php $controls->select('secure', array('' => 'Standard', 'ssl' => 'SSL', 'tls' => 'TLS')); ?>
                    </td>
                </tr>
                <tr>
                    <th>Login</th>
                    <td>
                        <?php $controls->text('login', 70); ?>
                    </td>
                </tr>
                <tr>
                    <th>Password</th>
                    <td>
                        <?php $controls->text('password', 70); ?>
                    </td>
                </tr>
                <tr>
                    <th>Set transient errors as hard bounces?</th>
                    <td>
                        <?php $controls->yesno('transient'); ?>
                    </td>
                </tr>


            </table>

            <table class="form-table">
                <tr valign="top">
                    <th>Last time the bounces has been checked</th>
                    <td>
                        <?php echo NewsletterControls::print_date($this->get_last_run()) ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th>Next automated check on</th>
                    <td>
                        <?php echo NewsletterControls::print_date(wp_next_scheduled('newsletter_bounce_run')) ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <?php $controls->button_primary('save', 'Save'); ?>
                <?php $controls->button_primary('test', 'Test'); ?>
                <?php $controls->button_primary('send', 'Send a test delivery error'); ?>
            </p>


        </form>
    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>