<?php
/* @var $this NewsletterFacebook */

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

if (!$controls->is_action()) {
    $controls->data = $this->options;
} else {
    if ($controls->is_action('save')) {
        $this->save_options($controls->data);
        $controls->add_message_saved();
    }
}
?>

<div class="wrap" id="tnp-wrap">
    <?php @include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h2>Newsletter Facebook Extension</h2>

        <p>
            This module add the Facebook Connect as a subscription method. Find more information
            on its <a href="http://www.thenewsletterplugin.com/plugins/newsletter/facebook-module" target="_blank">official page</a>.
        </p>

    </div>

    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>

            <div id="tabs">
                <ul>
                    <li><a href="#tabs-general">General</a></li>
                    <li><a href="#tabs-help">Help</a></li>
                </ul>

                <div id="tabs-general">


                    <table class="form-table">
                        <tr valign="top">
                            <th>Enabled?</th>
                            <td>
                                <?php $controls->yesno('enabled'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>App id</th>
                            <td>
                                <?php $controls->text('app_id', 70); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>App secret</th>
                            <td>
                                <?php $controls->text('app_secret', 70); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>Button label</th>
                            <td>
                                <?php $controls->text('button_label'); ?>
                                <p class="description">
                                    Used with the button tag {facebook_button}.
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>Send the Newsletter welcome email</th>
                            <td>
                                <?php $controls->yesno('welcome'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>Resend the user to the starting page</th>
                            <td>
                                <?php $controls->yesno('redirect'); ?>
                                <p class="description">
                                    Send back the user to the page from which he started the subscription
                                    instead of show the welcome message.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="tabs-help">
                    <p>
                        This module activates the {facebook_url} tag which generate a link that starts the
                        subscription via Facebook.
                    </p>
                    <p>
                        The link generated can be used directly and in your site it is:
                    </p>
                    <pre><?php echo plugins_url('newsletter-facebook') . '/login.php'; ?></pre>
                    <p>
                        so you can use it directly on posts and pages.
                    </p>

                </div>

            </div>

            <p>
                <?php $controls->button('save', 'Save'); ?>

            </p>
        </form>
    </div>
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>