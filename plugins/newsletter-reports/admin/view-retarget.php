<?php

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$email_id = (int) $_GET['id'];
$module = NewsletterStatistics::instance();
$email = $module->get_email($email_id);

if ($controls->is_action('open')) {
    $r = $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set list_" . ((int) $controls->data['list']) . "=1 where id in (select distinct user_id from " . NEWSLETTER_STATS_TABLE . " where email_id=%d)", $email_id));
    $controls->messages = '<strong>Added ' . $r . ' subscribers to list ' . ((int) $controls->data['list']) . '</strong><br>(number could not match the total since subscribers could have been removed after the newsletter has been sent or alredy in that list)';
}

if ($controls->is_action('click')) {
    $r = $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set list_" . ((int) $controls->data['list']) . "=1 where id in (select distinct user_id from " . NEWSLETTER_STATS_TABLE . " where url<>'' and email_id=%d)", $email_id));
    $controls->messages = '<strong>Added ' . $r . ' subscribers to list ' . ((int) $controls->data['list']) . '</strong><br>(number could not match the total since subscribers could have been removed after the newsletter has been sent or alredy in that list)';
}

if ($controls->is_action('nothing')) {
    $r = $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set list_" . ((int) $controls->data['list']) . "=1 where id in (select distinct user_id from " . NEWSLETTER_SENT_TABLE . " where open=0 and email_id=%d)", $email_id));
    $controls->messages = '<strong>Added ' . $r . ' subscribers to list ' . ((int) $controls->data['list']) . '</strong><br>(number could not match the total since subscribers could have been removed after the newsletter has been sent or alredy in that list)';
}

if ($controls->is_action('error')) {
    $r = $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set list_" . ((int) $controls->data['list']) . "=1 where id in (select distinct user_id from " . NEWSLETTER_SENT_TABLE . " s where s.status>0 and s.email_id=%d)", $email_id));
    $controls->messages = '<strong>Added ' . $r . ' subscribers to list ' . ((int) $controls->data['list']) . '</strong><br>(number could not match the total since subscribers could have been removed after the newsletter has been sent or alredy in that list)';
}

if ($controls->is_action('set')) {
    $r = $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set list_" . ((int) $controls->data['list']) . "=1 where id in (select distinct user_id from " . NEWSLETTER_STATS_TABLE . " s where s.url=%s and s.email_id=%d)", $controls->data['url'], $email_id));
    $controls->messages = '<strong>Added ' . $r . ' subscribers to list ' . ((int) $controls->data['list']) . '</strong><br>(number could not match the total since subscribers could have been removed after the newsletter has been sent or alredy in that list)';
}

$urls = $wpdb->get_results($wpdb->prepare("select url, count(distinct user_id) as number from " . NEWSLETTER_STATS_TABLE . " where url<>'' and email_id=%d group by url order by number desc", $email->id));
?>
<style>
    .form-table .widefat td, .form-table .widefat th {
        padding: 4px!important;
        font-size: .9em;
    }
</style>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h2>Retargeting for "<?php echo htmlspecialchars($email->subject); ?>"</h2>

        <?php $controls->show(); ?>

    </div>

    <div id="tnp-body" style="min-width: 500px">

        <?php $controls->button_back('admin.php?page=newsletter_reports_view&id=' . $email->id) ?>

        <div class="tnp-retarget">
            <table class="form-table tnp-retarget-table">

                <tr>
                    <th>Subscribers who open the newsletter</th>
                    <td>
                        <form action="" method="post">
                            <?php $controls->init() ?>
                            <?php $controls->lists_select() ?>
                            <?php $controls->button_primary('open', 'Add to this list') ?>
                        </form>

                    </td>
                </tr>
                <tr>
                    <th>Subscribers who click the newsletter</th>
                    <td>
                        <form action="" method="post">
                            <?php $controls->init() ?>
                            <?php $controls->lists_select() ?>
                            <?php $controls->button_primary('click', 'Add to this list') ?>
                        </form>

                    </td>
                </tr>

                <tr>
                    <th>Subscribers who did nothing</th>
                    <td>
                        <form action="" method="post">
                            <?php $controls->init() ?>
                            <?php $controls->lists_select() ?>
                            <?php $controls->button_primary('nothing', 'Add to this list') ?>
                        </form>

                    </td>
                </tr>

                <tr>
                    <th>Subscribers with delivery error</th>
                    <td>
                        <form action="" method="post">
                            Total: <?php echo $wpdb->get_var($wpdb->prepare("select count(*) from " . NEWSLETTER_SENT_TABLE . " where status>0 and email_id=%d", $email_id)) ?><br>
                            <?php $controls->init() ?>
                            <?php $controls->lists_select() ?>
                            <?php $controls->button_primary('error', 'Add to this list') ?>
                        </form>

                    </td>
                </tr>

                <tr>
                    <th>By clicked link</th>
                    <td>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>Clicks</th>

                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < count($urls); $i++) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($urls[$i]->url) ?></td>
                                        <td><?php echo $urls[$i]->number ?></td>

                                        <td>
                                            <form action="" method="post">
                                                <?php $controls->init() ?>
                                                <?php $controls->data['url'] = $urls[$i]->url; ?>
                                                <?php $controls->hidden('url') ?>
                                                <?php $controls->lists_select() ?>
                                                <?php $controls->button_primary('set', 'Add to this list') ?>
                                            </form>
                                        </td>

                                    </tr>
                                <?php } ?>


                            </tbody>
                        </table>
                    </td>
                </tr>

            </table>
        </div>





    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
