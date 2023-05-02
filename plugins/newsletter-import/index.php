<?php
/* @var $this NewsletterImport */

include_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

// If the import is active return to the import panel
if ($this->is_importing()) {
    $controls->js_redirect('?page=newsletter_import_csv');
}

?>

<style>
    #tnp-body {
        padding: 25px;
    }
    #tnp-body a.widget {
        display: block;
        padding: 15px;
        float: left;
        margin: 0 20px 0 0;
        border: 1px solid #ddd;
        height: 150px;
        overflow: hidden;
        width: 200px;
        text-decoration: none;
    }

    #tnp-body a.widget:hover {
        background-color: #23282d;
    }

    #tnp-body a.widget h3 {
        padding: 0;
        margin: 0;
    }
</style>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">

        <h2>Advanced import</h2>
        <?php $controls->panel_help('https://www.thenewsletterplugin.com/documentation/addons/extended-features/advanced-import/') ?>
    </div>

    <div id="tnp-body">
        

        <a class="widget" href="?page=newsletter_import_csv">
            <h3>Import from CSV file</h3>
            <p>Upload, map and import a full CSV file.</p>
        </a>

        <a class="widget" href="?page=newsletter_import_clipboard">
            <h3>Import with copy and paste</h3>
            <p>Copy and paste a list of subscribers from Excel, Google Docs and other spreadsheets</p>
        </a>

        <a class="widget" href="?page=newsletter_import_bounce">
            <h3>Import bounced addresses</h3>
            <p>Copy and paste a list of email address to be marked as bounced.</p>
        </a>

        <div style="clear: both"></div>
        
        <?php include __DIR__ . '/last-import-statistics.php'; ?>
        
        <?php if (file_exists(NEWSLETTER_LOG_DIR . '/import-report.txt')) { ?>
        <h3>Last import report</h3>
        <pre style="padding: 15px; background-color: white; font-family: monospace; height: 300px; overflow: auto"><?php echo esc_html(file_get_contents(NEWSLETTER_LOG_DIR . '/import-report.txt'))?></pre>
        <?php } ?>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
