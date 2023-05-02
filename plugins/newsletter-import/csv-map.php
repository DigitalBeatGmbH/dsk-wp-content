<?php
defined('ABSPATH') || exit;

/* @var $this NewsletterImport */
/* @var $controls NewsletterControls */

if (!$controls->is_action()) {
    $controls->data = $this->options;
    if (empty($controls->data['delimiter'])) {
        $controls->data['delimiter'] = ';';
    }
    $controls->data['import_as'] = '';
} else {
    if ($controls->is_action('delete')) {
        $this->stop();
        $controls->js_redirect("admin.php?page=newsletter_import_index");
        die();
    }

    if ($controls->is_action('import')) {
        //die('import');

        if (empty($controls->data['import_as'])) {
            //die('status missing');
            $controls->errors = 'Please select the status of imported subscribers';
        } else if (empty($controls->data['email'])) {
            $controls->errors = 'Please, map at least the email field on "Fields" tab';
        } else {
            $this->save_options($controls->data);
            // Patch for a bug in NewsletterAddon
            $this->options = $controls->data;
            $this->start();
            //$this->hook_newsletter_import_run(5);
            $controls->js_redirect("admin.php?page=newsletter_import_csv");
            die();
        }
    }
}

// Read the first 5 lines
$lines = [];
$handle = fopen($this->get_filename(), 'r');
if ($handle) {
    $count = 0;
    while (($line = fgets($handle)) !== false) {
        $lines[] = trim($line);
        if ($count++ > 10) {
            break;
        }
    }
    fclose($handle);
} else {
    $controls->errors = 'Imported file not found.';
}

$csv_fields = array('' => 'None');
$headers = str_getcsv($lines[0], $controls->data['delimiter'], '"');
for ($i = 0; $i < count($headers); $i++) {
    $csv_fields['' . $i + 1] = $headers[$i];
}
$data = [];
for ($i = 1; $i < count($lines); $i++) {
    $row = str_getcsv($lines[$i], $controls->data['delimiter'], '"');
    $data[] = $row;
}
?>
<style>
    table.parsed {
        border-collapse: collapse;
    }
    table.parsed td, table.parsed th {
        padding: 3px;
        border: 1px solid #ddd !important;
        font-size: 12px;
        text-align: left!important;
    }
</style>
<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">
        <h3>Advanced import</h3>
        <h2>2. Import Mode and Fields Mapping</h2>
        <?php $controls->panel_help('https://www.thenewsletterplugin.com/documentation/addons/extended-features/advanced-import/', 'Read our guide for detailed instruction and import problems') ?>
    </div>

    <div id="tnp-body">

        <form method="post" action="" enctype="multipart/form-data">
            <?php $controls->init(); ?>

            <div id="tabs">
                <ul>
                    <li><a href="#tabs-settings">Settings</a></li>
                    <li><a href="#tabs-fields">Fields</a></li>
                    <li><a href="#tabs-lists">Lists</a></li>
                    <li><a href="#tabs-extra">Extra fields</a></li>
                </ul>

                <div id="tabs-settings">

                    <table class="form-table">
                        <tr>
                            <th>Sample lines from your file</th>
                            <td>
                                <textarea readonly style="background-color: #eee; font-family: monospace; font-size: 12px; width: 100%; height: 100px"><?php echo implode("\n", $lines) ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th>As parsed</th>
                            <td>
                                <table class="parsed">
                                    <tr>
                                        <?php
                                        foreach ($headers as $header) {
                                            echo '<th>', esc_html($header), '</th>';
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                    foreach ($data as $row) {
                                        echo '<tr>';
                                        foreach ($row as $cell) {
                                            echo '<td>', esc_html($cell), '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <th>Fields separator</th>
                            <td>
                                <?php $controls->select('delimiter', [';' => 'Semicolon (;)', ',' => 'Comma (,)']); ?>
                                <?php $controls->button('reload', 'Reload'); ?>

                                <p class="description">
                                    Excel (!) lets you to export in "CSV UTF-8 comma separated" but ACTUALLY it uses semicolons (;)
                                    as field separator. Check the file with a text editor like Notepad.
                                </p>

                            </td>
                        </tr>

                        <tr>
                            <th>When a subscriber is already present<br><small>Identified by it's email</small></th>
                            <td>

                                <?php $controls->select('mode', array('update' => 'Update', 'overwrite' => 'Overwrite', 'skip' => 'Skip')); ?>
                                <p class="description">
                                    <strong>Update</strong>: <?php _e('subscriber data will be updated, existing lists will be left untouched and new ones will be added.', 'newsletter') ?><br />
                                    <strong>Overwrite</strong>: <?php _e('subscriber data will be cleared and set again', 'newsletter') ?><br />
                                    <strong>Skip</strong>: <?php _e('subscriber won\'t be changed', 'newsletter') ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Import Subscribers As', 'newsletter') ?></th>
                            <td>
                                <?php
                                $controls->select('import_as', [
                                    'C' => __('Confirmed', 'newsletter'),
                                    'S' => __('Not confirmed', 'newsletter'),
                                    'U' => __('Unsubscribed', 'newsletter'),
                                    'B' => __('Bounced', 'newsletter')
                                        ], 'Select...');
                                ?>
                                <br>
                                <?php $controls->checkbox('override_status', __('Override status of existing users', 'newsletter')) ?>
                            </td>
                        </tr>
                    </table>

                </div>


                <div id="tabs-lists">
                    <p>
                        Lists cannot actually be assigned using CSV fields.
                    </p>
                    <table class="form-table">

                        <tr>
                            <th><?php _e('Lists', 'newsletter') ?></th>
                            <td>
                                <?php $controls->preferences_group('lists', true); ?>
                                <div class="hints">
                                    Every new imported or updated subscriber will be associate with selected lists.
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>


                <div id="tabs-fields">
                    <table class="widefat" style="width: auto">
                        <thead>
                            <tr>
                                <th>Subscriber field</th>
                                <th>CSV column</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Email</td>
                                <td><?php $controls->select('email', $csv_fields) ?></td>
                            </tr>
                            <tr>
                                <td>First name</td>
                                <td><?php $controls->select('first_name', $csv_fields) ?></td>
                            </tr>
                            <tr>
                                <td>Last name</td>
                                <td><?php $controls->select('last_name', $csv_fields) ?></td>
                            </tr>
                            <tr>
                                <td>Language</td>
                                <td>
                                    <?php $controls->select('language', $csv_fields) ?>
                                    <div class="description">
                                        It should be 2 lowecase characters code (<a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank">ISO 639-1</a>)
                                        or the 2 lowecase characters code used by your multilangiage plugin.
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Gender</td>
                                <td>
                                    <?php $controls->select('gender', $csv_fields) ?>
                                    <div class="description">
                                        It should be "f" or "m" or "n".
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>IP Address</td>
                                <td><?php $controls->select('ip', $csv_fields) ?></td>
                            </tr>
                            <tr>
                                <td>Country</td>
                                <td>
                                    <?php $controls->select('country', $csv_fields) ?>
                                    <p class="description">
                                        It should be the country <a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank">ISO 3166-1 alpha 2 code</a>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td>Region</td>
                                <td>
                                    <?php $controls->select('region', $csv_fields) ?>
                                    <p class="description">Can be a state, county, province and so on</p>
                                </td>
                            </tr>
                            <tr>
                                <td>City</td>
                                <td>
                                    <?php $controls->select('city', $csv_fields) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>


                <div id="tabs-extra">
                    <p>To import the extra profile fields, define them on the <a href="?page=newsletter_subscription_profile">subscription fields panel</a>.</p>
                    <?php
                    $profiles = Newsletter::instance()->get_profiles();
                    ?>

                    <?php if (empty($profiles)) { ?>
                        <p style="font-weight: strong">
                            There are not extra profile fields defined.
                        </p>
                    <?php } else { ?>
                        <table class="widefat" style="width: auto">
                            <thead>
                                <tr>
                                    <th>Subscriber field</th>
                                    <th>CSV column</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($profiles as $profile) { ?>
                                    <tr>
                                        <td><?php echo esc_html($profile->name) ?></td>
                                        <td><?php $controls->select('profile_' . $profile->id, $csv_fields) ?></td>
                                    <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>

                </div>
            </div>

            <p>
                <?php $controls->button_confirm('import', 'Import'); ?>
                <?php $controls->button_confirm('delete', 'Delete the file'); ?>
            </p>

        </form>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
