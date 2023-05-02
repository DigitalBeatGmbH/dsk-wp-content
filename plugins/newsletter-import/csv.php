<?php
/* @var $this NewsletterImport */

defined('ABSPATH') || exit;

global $wpdb;

include_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$return_page = 'newsletter_import_csv';

if ($this->is_importing()) {
    include __DIR__ . '/csv-importing.php';
    return;
}

if ($this->has_file()) {
    include __DIR__ . '/csv-map.php';
    return;
}

$can_import = true;

$r = $this->prepare_dir();
if (is_wp_error($r)) {
    $controls->errors .= $r->get_error_message();
    $can_import = false;
}

if (!$controls->is_action()) {
    $controls->data = $this->options;
} else {

    if ($can_import && $controls->is_action('import')) {
        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $this->stop();
            $r = move_uploaded_file($_FILES['file']['tmp_name'], $this->get_filename());
            if ($r === false) {
                $controls->errors = 'The file cannot be copied in the folder ' . esc_html($dir) . '/newsletter. Check if it exists and is writeable. You can also ask for support to your hosting provider.';
            } else {
                $controls->js_redirect('admin.php?page=newsletter_import_csv');
            }
        }
        return;
    }
}
?>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <?php $controls->show() ?>

    <div id="tnp-heading">
        <h3>Advanced import</h3>
        <h2>1. Upload your file</h2>
        <?php $controls->panel_help('https://www.thenewsletterplugin.com/documentation/addons/extended-features/advanced-import/', 'Read our guide for detailed instruction and import problems') ?>
    </div>

    <div id="tnp-body">
        <?php if ($can_import) { ?>
            <form method="post" action="" enctype="multipart/form-data">
                <?php $controls->init(); ?>
                <div id="tabs-file">
                    <table class="form-table">
                        <tr>
                            <th>
                                <?php _e('CSV file', 'newsletter') ?>
                            </th>
                            <td>
                                <input type="file" name="file" />

                                <?php $controls->button('import', 'Next'); ?>

                                <p class="description">
                                    The file <strong>must be UTF-8 encoded</strong>, be sure to export it in that format. For Excel, choose "File/Save as" to have the option to
                                    select that format.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        <?php } ?>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
