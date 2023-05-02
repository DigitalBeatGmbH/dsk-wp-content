<?php
/* @var $this NewsletterAutomated */

global $wpdb;
global $controls;
require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
require_once NEWSLETTER_INCLUDES_DIR . '/paginator.php';

$controls = new NewsletterControls();


if (isset($_GET['id'])) {
    include __DIR__ . '/edit.php';
    return;
}

if ($controls->is_action('add')) {
    $data = array('name' => 'New channel', 'list' => 1, 'theme' => 'default', 'enabled' => 0, 'hour' => 6, 'max_posts' => 10, 'excerpt_length' => 30, 'track' => 1);
    $feed = array('data' => json_encode($data));
    $wpdb->insert($wpdb->prefix . "newsletter_automated", $feed);
    $id = $wpdb->insert_id;
    $controls->messages = 'New channel created.';

}

if ($controls->is_action('add_composer')) {
    $data = array('name' => 'New channel with composer', 'list' => '', 'enabled' => 0, 'hour' => 6, 'track' => 1);
    $feed = array('data' => json_encode($data), 'theme_type' => $this::THEME_TYPE_COMPOSER);
    $wpdb->insert($wpdb->prefix . "newsletter_automated", $feed);
    $id = $wpdb->insert_id;
    $controls->messages = 'New channel created.';
}

if ($controls->is_action('delete')) {
    /* @var $wpdb wpdb */
    $channel_id = (int) $_POST['btn'];
    $res = $wpdb->query($wpdb->prepare("delete from " . $wpdb->prefix . "newsletter_automated where id=%d limit 1", $channel_id));
    $emails = $this->get_emails($channel_id);
    $newsletter = Newsletter::instance();
    foreach ($emails as $email) {
        $newsletter->delete_email($email->id);
    }

    //Clear scheduled channel hook
    wp_clear_scheduled_hook('newsletter_automated', array($channel_id));

    if ($res === false) {
        $controls->errors = __('Unable to delete.', 'newsletter-automated');
    } else {
        $controls->messages .= __('Channel deleted.', 'newsletter-automated');
    }
}

if ($controls->is_action('copy')) {
    /* @var $wpdb wpdb */
    $res = $wpdb->get_row($wpdb->prepare("select * from " . $wpdb->prefix . "newsletter_automated where id=%d limit 1", $_POST['btn']), ARRAY_A);
    $data = json_decode($res['data'], true);
    $data['enabled'] = 0;
    $data['name'] .= ' (copy)';
    $res['data'] = json_encode($data);
    $newsletter = Newsletter::instance();
    $email = $newsletter->get_email($res['email_id']);
    if ($email) {
        $new_email = (array) $email;
        unset($new_email['id']);
        $new_email = $newsletter->save_email($new_email);
        $res['email_id'] = $new_email->id;
    }
    unset($res['id']);
    $r = $wpdb->insert($wpdb->prefix . "newsletter_automated", $res);
    if (!$r) {
        $controls->errors = 'Saving error: ' . esc_html($wpdb->last_error);
    } else {
        $controls->add_message_done();
    }
}


$pagination_controller = new TNP_Pagination_Controller(
        NEWSLETTER_AUTOMATED_TABLE,
        'id',
        [],
        20,
        [
    'id',
    'email_id',
    'data',
    'theme_type'
        ]);

$feeds = $pagination_controller->get_items();

$newsletters_sent_list = $wpdb->get_results("SELECT type, count(*) AS count"
        . " FROM " . NEWSLETTER_EMAILS_TABLE
        . " WHERE type LIKE 'automated_%' GROUP BY type", OBJECT_K);

foreach ($feeds as $feed) {
    $feed->data = json_decode($feed->data, true);
    $feed->newsletters_sent = isset($newsletters_sent_list["automated_$feed->id"]) ? (int) $newsletters_sent_list["automated_$feed->id"]->count : 0;
}
?>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">

        <h2>Automated Newsletters</h2>

        <p>
            Don't miss our <a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/automated-extension/" target="_blank">User and Video Guide</a>.
        </p>
        <p>
            Every channel is linked to a subscriber list. Subscribers can enter or exit a channel changing
            their profile.
        </p>

        <?php $controls->show(); ?>
    </div>
    <div id="tnp-body">
        <form method="post" action="">
            <?php $controls->init(); ?>

            <div class="tnp-buttons">
                <?php $controls->button('add_composer', 'New channel') ?>
            </div>

            <?php $pagination_controller->display_paginator(); ?>

            <table class="widefat">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Last</th>
                        <th>&nbsp;</th>
                        <th>Newsletters</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($feeds as $feed) { ?>
                    <?php $email = $this->get_last_email($feed->id) ?>
                        <tr>
                            <td>
                                <?php echo $feed->id ?>
                                <?php
                                if (NEWSLETTER_DEBUG) {
                                    echo '<br><code>email_id:&nbsp;', $feed->email_id, '</code>';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html($feed->data['name']) ?></td>
                            <td><?php echo !empty($feed->data['enabled']) ? 'Enabled' : 'Disabled' ?></td>

                            <td>
                                <?php if ($email) { ?>
                                    <?php echo $controls->print_date($email->send_on) ?>
                                <?php } ?>
                            </td>
                            <td>
                                
                                <?php if ($email) { ?>
                                    <?php echo $email->status == 'new' ? 'Aborted' : $email->status; ?>
                                    (<?php echo $email->sent; ?>/<?php echo $email->total; ?>)
                                <?php } ?>
                            </td>
                            
                            <td><?php echo $feed->newsletters_sent ?></td>
                            
                            <td style="white-space: nowrap">
                                <?php $controls->button_icon_configure('?page=newsletter_automated_index&id=' . $feed->id) ?>
                                <?php $controls->button_icon_newsletters('?page=newsletter_automated_newsletters&id=' . $feed->id) ?>
                                <?php if ($feed->theme_type == NewsletterAutomated::THEME_TYPE_COMPOSER) { ?>
                                    <?php $controls->button_icon_design('?page=newsletter_automated_template&id=' . $feed->id) ?>
                                <?php } ?>
                            </td>
                            
                            <td style="white-space: nowrap">
                                <?php $controls->button_icon_copy($feed->id); ?>
                                <?php $controls->button_icon_delete($feed->id); ?>
                            </td>
                            
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <div class="tnp-buttons">
                <?php $controls->button('add_composer', 'New channel') ?>
            </div>
            <hr>
            <div class="tnp-buttons">
                <?php $controls->button('add', 'New channel with obsolete themes') ?>
                <?php $controls->button_link('?page=newsletter_automated_config', 'Configuration') ?>
            </div>
        </form>
    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
