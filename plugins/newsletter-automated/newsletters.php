<?php
/* @var $wpdb wpdb */
/* @var $this NewsletterAutomated */

global $wpdb;
require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
require_once NEWSLETTER_INCLUDES_DIR . '/paginator.php';
$controls = new NewsletterControls();

$feed_id = (int) $_GET['id'];

if (!$controls->is_action()) {

} else {


    if ($controls->is_action('delete')) {
        Newsletter::instance()->delete_email($_POST['btn']);
        $controls->add_message_done();
    }

    if ($controls->is_action('abort')) {
        $wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "newsletter_emails set status='new' where id=%d", $_POST['btn']));
        $controls->messages = 'Newsletter definitively blocked';
    }
}

$pagination_controller = new TNP_Pagination_Controller(NEWSLETTER_EMAILS_TABLE, 'id', [ 'type' => 'automated_' . $feed_id ]);

$emails = $pagination_controller->get_items();

?>


<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">

        <h2>Generated newsletters</h2>

        <?php $controls->show(); ?>

    </div>

    <div id="tnp-body" class="tnp-automated-edit">

        <form method="post" action="">
            <?php $controls->init(); ?>


            <div class="tnp-buttons">
                <?php $controls->button_icon_back('admin.php?page=newsletter_automated_index')?>
            </div>

            <?php if (empty($emails)) { ?>
                <p>No newsletters have been generated since now for this channel.</p>
            <?php } else { ?>

                <?php $pagination_controller->display_paginator(); ?>

                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Statistics</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $count = 0; ?>
                        <?php foreach ($emails as $email) { ?>
                        <?php $count++; ?>
                            <tr>
                                <td><?php echo $email->id; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($email->subject); ?>
                                    <?php if (NEWSLETTER_DEBUG) {
                                        echo '<br>';
                                        echo '<code>', esc_html($email->query), '</code>';
                                     }
                                     ?>
                                </td>
                                <td><?php echo NewsletterControls::print_date($email->send_on); ?></td>
                                <td>
                                    <?php echo $email->status == 'new' ? 'Aborted' : $email->status; ?>
                                    (<?php echo $email->sent; ?>/<?php echo $email->total; ?>)
                                </td>
                                <td>
                                    <?php if ($count < 20) { ?>
                                    Sent: <?php echo NewsletterStatistics::instance()->get_total_count($email->id)?><br>
                                    Read: <?php echo NewsletterStatistics::instance()->get_open_count($email->id)?><br>
                                    Clicked: <?php echo NewsletterStatistics::instance()->get_click_count($email->id)?>
                                    <?php } else { ?>
                                    See the statistics panel
                                    <?php } ?>
                                </td>
                                <td style="white-space: nowrap">
                                    <?php $controls->button_icon_statistics(NewsletterStatistics::instance()->get_statistics_url($email->id))?>
                                    <?php $controls->button_icon_view(home_url('/') . '?na=v&id=' . $email->id)?>
                                    <?php $controls->button_icon_delete($email->id); ?>
                                    <?php $controls->button_icon('abort', 'fa-stop', 'Block this newsletter', $email->id, true); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>



        </form>

    </div>
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
