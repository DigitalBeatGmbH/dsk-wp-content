<?php
/* @var $this NewsletterImport */

defined('ABSPATH') || exit;

global $wpdb;

include_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

// If the import is active return to the import panel
if ($this->is_importing()) {
    $controls->js_redirect('?page=newsletter_import_csv');
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

    if ($can_import && $controls->is_action('import-from-clipboard')) {
        //Normalize pasted string
        $data = preg_replace('/\t/', ',', stripslashes($_POST['pasted_text']));
        $data = str_replace("\r\n", "\n", $data);

        //$data = utf8_encode( $data );

        $res = file_put_contents($this->get_filename(), $data);
        if ($res === false) {
            $controls->errors = 'Unable to write data to the temporary file ' . esc_html($this->get_filename()) . '.';
        } else {
            $controls->js_redirect('admin.php?page=newsletter_import_csv');
        }
    }
}
?>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">
        <h3>Advanced import</h3>
        <h2>1. Copy&Paste Import</h2>
        <?php $controls->panel_help('https://www.thenewsletterplugin.com/documentation/addons/extended-features/advanced-import/') ?>
    </div>

    <div id="tnp-body">
        <?php if ($can_import) { ?>

            <form method="post" action="">
                <?php $controls->init(); ?>

                <p>Note: the first line MUST contain field labels, like:</p>
                <p>
                    <code>Email;First Name; Last Name</code>
                </p>
                <p>the order is not important on next screen you can map your data columns to the subscriber fields.</p>


                <table class="form-table">
                    <tr>
                        <th>
                            <?php _e('Copy and Paste', 'newsletter') ?>
                        </th>
                        <td>
                            <textarea name="pasted_text" style="width: 100%; height: 200px; font-size: 11px; font-family: monospace"></textarea>
                            <?php $controls->button('import-from-clipboard', 'Next'); ?>
                        </td>
                    </tr>

                </table>


            </form>
        <?php } ?>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
