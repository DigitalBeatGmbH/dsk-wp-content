<?php
defined('ABSPATH') || exit;

/* @var $this NewsletterImport */
/* @var $controls NewsletterControls */

//$this->hook_newsletter_import_run();

if ($controls->is_action('stop')) {
    $this->stop();
    $controls->js_redirect('admin.php?page=newsletter_import_index');
}

if ($controls->is_action('refresh')) {
    // just a reload
}
?>
<script>
    function importRun() {
        //debugger;
        jQuery.get(ajaxurl + '?action=newsletter_import', function (data) {
            if (!data.completed) {
                setTimeout(importRun, 3000);
                jQuery('#tnp-import-statistics').html(data.html);
            } else {
                location.href = '?page=newsletter_import_index';
            }
        });
    }
    importRun();
</script>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">
        <h3>Advanced import</h3>
        <h2>3. The import is running in background (keep this page open)</h2>
        <?php $controls->panel_help('https://www.thenewsletterplugin.com/documentation/addons/extended-features/advanced-import/', 'Read our guide for detailed instruction and import problems') ?>
    </div>

    <div id="tnp-body">

        <div id="tnp-import-statistics">
            <span style="color: white">Importing... (updated every 10 seconds)</span>
        </div>
        
        <form method="post" action="#">
            <?php $controls->init(); ?>

            <p>
                <?php $controls->button_confirm('stop', 'Stop'); ?>
                <?php $controls->button('refresh', 'Refresh'); ?>
            </p>

        </form>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
