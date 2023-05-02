<?php

/* @var $this NewsletterAnalytics */

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
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h2>Newsletter Analytics</h2>
        <p>
            <a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/analytics-extension/"><i class="fas fa-book" aria-hidden="true"></i> Read our guide (with examples)</a>.
        </p>

        <?php $controls->show(); ?>

    </div>
    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>
            
            <table class="form-table">

                <tr>
                    <th>Add UTM tags on external domains link</th>
                    <td>
                        <?php $controls->yesno('external'); ?>
                        <p class="description">
                        </p>
                    </td>
                </tr>
            </table>

            <h3>Default values</h3>

            <table class="form-table">

                <tr>
                    <th>UTM Campaign</th>
                    <td>
                        <?php $controls->text('utm_campaign', 50); ?>
                        <p class="description">
                            This is the campaign name Newsletter-{email_id}
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>UTM Source</th>
                    <td>
                        <?php $controls->text('utm_source', 50); ?>
                        <p class="description">
                            Should set as "newsletter-{email_id}" and it's mandatory for Google. "{email_id}" is replaced with the
                            newsletter unique id. Automated newsletter, autoresponders and other non standard newsletter use a different
                            source like automated-{channel number}-{email id}.
                        </p>
                    </td>
                </tr>


                <tr>
                    <th>UTM Medium</th>
                    <td>
                        <?php $controls->text('utm_medium', 50); ?>
                        <p class="description">
                            Should be set to "email" since this is the only medium used.
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>UTM Term</th>
                    <td>
                        <?php $controls->text('utm_term', 50); ?>
                        <p class="description">
                            Usually empty can be used on specific newsletters but it is more related to keyword based advertising.
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>UTM Term</th>
                    <td>
                        <?php $controls->text('utm_content', 50); ?>
                        <p class="description">
                            Usually empty can be used on specific newsletters.
                        </p>
                    </td>
                </tr>

            </table>

            <p>
                <?php $controls->button('save', 'Save'); ?>
            </p>
        </form>
    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>