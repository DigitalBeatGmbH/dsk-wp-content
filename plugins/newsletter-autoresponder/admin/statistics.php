<?php
/* @var $this NewsletterAutoresponder */
global $wpdb;
require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$autoresponder_id = (int) $_GET['id'];

$autoresponder = $this->get_autoresponder($autoresponder_id);
$statistics = NewsletterStatistics::instance();
$newsletter = Newsletter::instance();

$emails = [];
foreach ($autoresponder->emails as $email_id) {
    $emails[] = $newsletter->get_email($email_id);
}
?>
<style>
    .widefat {
        min-width: 500px;
    }
</style>

<div class="wrap" id="tnp-wrap">
    <?php @include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h3>Statistics</h3>
        <h2><?php echo esc_html($autoresponder->name) ?></h2>

        <?php $controls->show(); ?>

    </div>
    <div id="tnp-body">

        <form method="post" action="">
            <?php $controls->init(); ?>
            <div class="tnp-buttons">
                <?php $controls->button_icon_back('?page=newsletter_autoresponder_index') ?>
                <?php $controls->button_icon_configure('?page=newsletter_autoresponder_index&id=' . $autoresponder->id) ?>
                <?php $controls->button_icon_subscribers('?page=newsletter_autoresponder_users&id=' . $autoresponder->id) ?>
            </div>

            <h3>Messages</h3>
            <p>Counts are limited to active subscribers who have not abandoned the series (by list change, cancellation, ...).</p>

            <table class="widefat" style="width: auto">
                <thead>
                    <tr>
                        <th>Progress</th>
                        <th>Subscribers</th>
                        <th>Subject</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php for ($i = 0; $i < count($emails); $i++) { ?>
                        <?php
                        $email = $emails[$i];
                        $count = $wpdb->get_var($wpdb->prepare("select count(*) from " . $wpdb->prefix . "newsletter_autoresponder_steps where autoresponder_id=%d and step=%d and status=" . TNP_Autoresponder_Step::STATUS_RUNNING, $autoresponder->id, $i));
                        $total += $count;
                        ?>
                        <tr>
                            <td>Waiting to receive message <?php echo $i + 1 ?></td>

                            <td>
                                <?php echo $count ?>
                            </td>
                            <td>
                                <?php echo esc_html($email->subject) ?>
                            </td>
                            <td>
                                <?php $statistics->echo_statistics_button($autoresponder->emails[$i]) ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td><strong>Total queued</strong></td>
                        <td>
                            <strong>
                                <?php echo $total ?>
                            </strong>
                        </td>
                        <td>
                            &nbsp;
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                </tbody>
            </table>

            <h3>By status</h3>
            <p>Overview of subscriber on this message series.</p>
            <table class="widefat" style="width: auto">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Subscribers</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td>Completed</td>
                        <td>
                            <?php echo $wpdb->get_var($wpdb->prepare("select count(*) from " . $wpdb->prefix . "newsletter_autoresponder_steps where autoresponder_id=%d and status=1", $autoresponder->id))
                            ?>
                        </td>

                    </tr>
                    <tr>
                        <td>Active</td>
                        <td>
                            <?php echo $wpdb->get_var($wpdb->prepare("select count(*) from " . $wpdb->prefix . "newsletter_autoresponder_steps where autoresponder_id=%d and status=0", $autoresponder->id))
                            ?>
                        </td>

                    </tr>
                    <tr>
                        <td>Abandoned</td>
                        <td>
                            <?php echo $wpdb->get_var($wpdb->prepare("select count(*) from " . $wpdb->prefix . "newsletter_autoresponder_steps where autoresponder_id=%d and status=%d", $autoresponder->id, NewsletterAutoresponder::STEPS_STATUS_NOT_IN_LIST))
                            ?>
                        </td>

                    </tr>
                    <tr>
                        <td>
                            Other<br>
                            <small>Missing user, errors</small>
                        </td>
                        <td>

                            <?php echo $wpdb->get_var($wpdb->prepare("select count(*) from " . $wpdb->prefix . "newsletter_autoresponder_steps where autoresponder_id=%d and status not in (0, 1, " . NewsletterAutoresponder::STEPS_STATUS_NOT_IN_LIST . ")", $autoresponder->id))
                            ?>
                        </td>

                    </tr>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td>
                            <strong>
                                <?php echo $wpdb->get_var($wpdb->prepare("select count(*) from " . $wpdb->prefix . "newsletter_autoresponder_steps where autoresponder_id=%d", $autoresponder->id)) ?>
                            </strong>
                        </td>

                    </tr>
                </tbody>
            </table>

            <h3>Abandons</h3>
            <p>At which message subscribers abandoned the series (by subscription cancellation or list change)</p>
            <table class="widefat" style="width: auto">
                <thead>
                    <tr>
                        <th>Step</th>
                        <th>Subscribers</th>
                        <th>Subject</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Before receive first message</td>
                        <td>
                            <?php echo $wpdb->get_var($wpdb->prepare("select count(*) from " . $wpdb->prefix . "newsletter_autoresponder_steps where autoresponder_id=%d and step=0 and status=%d", $autoresponder->id, NewsletterAutoresponder::STEPS_STATUS_NOT_IN_LIST))
                            ?>
                        </td>

                    </tr>
                    <?php for ($i = 0; $i < count($emails); $i++) { ?>
                        <?php
                        $email = $emails[$i];
                        ?>
                        <tr>
                            <td>Just after message <?php echo $i + 1 ?></td>
                            <td>
                                <?php
                                echo $wpdb->get_var($wpdb->prepare("select count(*) from " . $wpdb->prefix . "newsletter_autoresponder_steps where autoresponder_id=%d and step=%d and (status=%d or status=%d)", $autoresponder->id, $i + 1, NewsletterAutoresponder::STEPS_STATUS_NOT_IN_LIST, TNP_Autoresponder_Step::STATUS_NOT_CONFIRMED))
                                ?>
                            </td>
                            <td>
                                <?php echo esc_html($email->subject) ?>
                            </td>
                            <td>
                                <?php $statistics->echo_statistics_button($autoresponder->emails[$i]) ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </form>

    </div>
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>