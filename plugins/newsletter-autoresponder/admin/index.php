<?php
/* @var $this NewsletterAutoresponder */
/* @var $wpdb wpdb */
global $wpdb;

if (isset($_GET['id'])) {
    include dirname(__FILE__) . '/edit.php';
    return;
}

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$logger = $this->get_admin_logger();

$debug = isset($_GET['debug']) || NEWSLETTER_DEBUG;

// Delete data of no more existent subscribers
$wpdb->query("delete s from {$wpdb->prefix}newsletter_autoresponder_steps s left join {$wpdb->prefix}newsletter u on u.id=s.user_id where u.id is null");

if ($controls->is_action('add')) {
    $data = array('name' => 'New autoresponder', 'status' => 0, 'emails' => array());
    $data = $this->save_autoresponder($data);
    $controls->js_redirect('?page=newsletter_autoresponder_index&id=' . $data->id);
}

if ($controls->is_action('add_composer')) {
    $data = array('name' => 'New autoresponder', 'status' => 0, 'type' => TNP_Autoresponder::TYPE_COMPOSER, 'emails' => array());
    $data = $this->save_autoresponder($data);
    $controls->js_redirect('?page=newsletter_autoresponder_index&id=' . $data->id);
    exit();
}

if ($controls->is_action('delete')) {

    $autoresponder_id = (int) $_POST['btn'];

    $autoresponder = $this->get_autoresponder($autoresponder_id);

    $logger->info('Deletion of series ' . $autoresponder->id . ' - ' . $autoresponder->name);

    $res = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}newsletter_autoresponder where id=%d limit 1", $autoresponder_id));
    if ($res) {
        $res = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}newsletter_autoresponder_steps where autoresponder_id=%d", $autoresponder_id));
        Newsletter::instance()->delete_email($autoresponder->emails);
    }
    if ($res === false) {
        $controls->errors = __('Unable to delete.', 'newsletter-autoresponder');
    } else {
        $controls->add_message_deleted();
    }
}

if ($controls->is_action('copy')) {
    $logger->info('Copy of series ' . $_POST['btn']);

    $this->copy_autoresponder($_POST['btn']);
    $controls->messages .= __('Series duplicated.', 'newsletter-autoresponder');
}

$autoresponders = $this->get_autoresponders();

$max_delay = 3600 * 8;
if ($debug)
    $max_delay = 3600;

$late_total = 0;
$late_min_send_at = time();

foreach ($autoresponders as $ar) {
    if ($ar->status != 1) {
        continue;
    }
    // Compute the queued subscriber which are late
    $r = $wpdb->get_row("select autoresponder_id, min(send_at) as min_send_at, count(*) as total from {$wpdb->prefix}newsletter_autoresponder_steps where status=0 and send_at<" . time() . " and autoresponder_id=" . $ar->id);
    if ($r && $r->total) {
        $late_total += $r->total;
        $late_min_send_at = min($late_min_send_at, $r->min_send_at);
        if ($late_min_send_at < time() - $max_delay) {
            $controls->warnings[] = 'Series ' . $ar->id . ' has late messages in queue. ' .
                    '<a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/autoresponder-extension/#late-messages" target="_blank">Read more</a>. ' .
                    'You can check the <a href="?page=newsletter_main_status">status page</a> for warnings as well.<br>' .
                    'Max delay: ' . $controls->delta_time(time() - $late_min_send_at);
        }
    }
}
?>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">

        <h2>Email series</h2>

        <p>
            Every email series is associated to a subscriber list. Subscribers can enter or exit an email series changing
            their profile.
            <?php $controls->page_help('https://www.thenewsletterplugin.com/documentation/addons/extended-features/autoresponder-extension/', 'Read more') ?>
        </p>

        <?php $controls->show(); ?>
    </div>
    <div id="tnp-body">
        <form method="post" action="">
            <?php $controls->init(); ?>

            <div class="tnp-buttons">
                <?php $controls->button('add_composer', 'Add new email series') ?>
            </div>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>List</th>
                        <th>Status</th>
                        <th>Steps</th>
                        <th>Subscribers</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($autoresponders as $autoresponder) { ?>
                        <tr>
                            <td><?php echo $autoresponder->id ?></td>
                            <td><?php echo esc_html($autoresponder->name) ?></td>
                            <td>
                                <?php
                                $list = Newsletter::instance()->get_list($autoresponder->list);
                                if ($list) {
                                    echo esc_html($list->name);
                                } else {
                                    echo 'No list associated';
                                }
                                ?>
                            </td>
                            <td><?php echo $autoresponder->status == 1 ? 'Enabled' : 'Disabled' ?></td>
                            <td><?php echo count($autoresponder->emails) ?></td>
                            <td>
                                <?php echo $this->get_user_count($autoresponder) ?>
                                <?php
                                if ($debug) {
                                    echo ' <code>(', $this->get_late_user_count($autoresponder) . ' late)</code>';
                                }
                                ?>
                            </td>
                            <td style="white-space: nowrap">
                                <?php $controls->button_icon_configure('?page=newsletter_autoresponder_index&id=' . $autoresponder->id) ?>
                                <?php $controls->button_icon_statistics('?page=newsletter_autoresponder_statistics&id=' . $autoresponder->id) ?>
                                <?php $controls->button_icon_subscribers('?page=newsletter_autoresponder_users&id=' . $autoresponder->id) ?>

                                <?php if ($autoresponder->type == TNP_Autoresponder::TYPE_CLASSIC) { ?>
                                    <?php $controls->button_icon_design('?page=newsletter_autoresponder_theme&id=' . $autoresponder->id) ?>
                                <?php } ?>
                            </td>
                            <td style="white-space: nowrap">
                                <?php $controls->button_icon_copy($autoresponder->id); ?>
                                <?php $controls->button_icon_delete($autoresponder->id); ?>
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>
            <p>
                <?php $controls->button_primary('add', 'Add new email series (classic theme)') ?>
            </p>

        </form>
        <p>
            <a href="?page=newsletter_autoresponder_index&debug=1" style="color: #999; text-decoration: none">Load this page showing debug information</a>
        </p>
    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
