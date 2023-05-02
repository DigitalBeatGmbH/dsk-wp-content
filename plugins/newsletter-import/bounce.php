<?php
/* @var $wpdb wpdb */

global $wpdb;

defined('ABSPATH') || exit;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

if ($controls->is_action('import')) {

    @set_time_limit(0);

    $emails = NewsletterModule::to_array($controls->data['csv']);

    $updated = 0;
    $total = count($emails);
    $wrong = 0;

    foreach ($emails as &$email) {
        if (!is_email($email)) {
            $wrong++;
            continue;
        }
        $r = $wpdb->update(NEWSLETTER_USERS_TABLE, ['status' => TNP_User::STATUS_BOUNCED], ['email' => $email]);
        if ($r) {
            $updated++;
        }
    }

    $controls->messages = "$updated set as bounced ($total provided). Missing or already bounced emails are not counted.";
    if ($wrong) {
        $controls->messages .= "<br>$wrong wrong email(s).";
    }
}
?>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">

        <h2>Bounced addresses import</h2>

    </div>

    <div id="tnp-body" class="tnp-users tnp-users-import">

        <form method="post">

            <?php $controls->init(); ?>

            <?php $controls->button_back('?page=newsletter_import_index') ?>

            <table class="form-table">

                <tr>
                    <th>
                        Bounced addresses
                    </th>
                    <td>
                        <textarea name="options[csv]" wrap="off" style="width: 100%; height: 200px; font-size: 11px; font-family: monospace"><?php echo esc_html($controls->get_value('csv')); ?></textarea>
                        <p class="description">
                            One per line.
                        </p>
                    </td>
                </tr>
            </table>
            <div class="tnp-buttons">
                <?php $controls->button('import', 'Import'); ?>
            </div>


        </form>

    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
