<?php
/* @var $this NewsletterElementor */

defined('ABSPATH') || exit;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

?>

<div class="wrap" id="tnp-wrap">
    <?php @include NEWSLETTER_DIR . '/tnp-header.php' ?>

    <div id="tnp-heading">
        <h2>Elementor Forms Integration</h2>
    </div>

    <div id="tnp-body">

        <p>
            There are no global settings, just create a form with Elementor and add to the 
            form actions "Newsletter". You can then configure the field mapping and subscription
            behaviors directly in the Elementor deisgner.
        </p>
        <p>
            <a href="https://www.thenewsletterplugin.com/documentation/addons/integrations/elementor-extension/" target="_blank">Read our guide to Elementor Forms configuration with Newsletter</a>.
        </p>
    </div>
    
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>

</div>