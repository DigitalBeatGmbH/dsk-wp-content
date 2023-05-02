<?php
/* @var $this NewsletterAutoresponder */
/* @var $wpdb wpdb */
global $wpdb;

$autoresponder_id = (int) $_GET['id'];
$autoresponder = $this->get_autoresponder($autoresponder_id);
$logger = $this->get_admin_logger();
$statistics = NewsletterStatistics::instance();
$newsletter = Newsletter::instance();

$debug = isset($_GET['debug']) || NEWSLETTER_DEBUG;

if (isset($_GET['email_id'])) {
    if ($autoresponder->type == TNP_Autoresponder::TYPE_COMPOSER) {
        include __DIR__ . '/edit-email-composer.php';
    } else {
        include __DIR__ . '/edit-email.php';
    }
    return;
}

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';

$controls = new NewsletterControls();

if (!$controls->is_action()) {
    $controls->set_data($autoresponder);
} else {

    if ($controls->is_action('save')) {
        $controls->data['id'] = $autoresponder_id;
        $autoresponder = $this->save_autoresponder($controls->data);
        $controls->set_data($autoresponder);
        $controls->add_message_saved();
    }

    if ($controls->is_action('add')) {
        $email = array('type' => 'autoresponder_' . $autoresponder_id, 'subject' => '[no subject]', 'track' => 1, 'status' => 'sent', 'editor' => NewsletterEmails::EDITOR_COMPOSER, 'options' => ['delay' => 24]);
        $email = Newsletter::instance()->save_email($email);
        $autoresponder->emails[] = $email->id;
        $autoresponder = $this->save_autoresponder($autoresponder);
        $controls->set_data($autoresponder);
        $controls->js_redirect('?page=newsletter_autoresponder_index&id=' . $autoresponder->id . '&email_id=' . $email->id);
        die();
    }

    if ($controls->is_action('copy')) {
        $i = (int) $_POST['btn'];
        $email = $newsletter->get_email($autoresponder->emails[$i], ARRAY_A);
        unset($email['id']);
        $email['subject'] .= ' (copy)';
        $email = $newsletter->save_email($email);
        $autoresponder->emails[] = $email->id;
        $this->save_autoresponder($autoresponder);
    }

    //Move up email
    if ($controls->is_action('up')) {
        $i = (int) $_POST['btn'];
        $emails = $autoresponder->emails;

        $tmp = $emails[$i];
        $emails[$i] = $emails[$i - 1];
        $emails[$i - 1] = $tmp;

        $autoresponder->emails = $emails;
        $autoresponder = $this->save_autoresponder($autoresponder);
        $controls->data = (array) $autoresponder;
    }

    //Move down email
    if ($controls->is_action('down')) {
        $i = (int) $_POST['btn'];
        $emails = $autoresponder->emails;

        $tmp = $emails[$i + 1];
        $emails[$i + 1] = $emails[$i];
        $emails[$i] = $tmp;

        $autoresponder->emails = $emails;
        $autoresponder = $this->save_autoresponder($autoresponder);
        $controls->set_data($autoresponder);
    }

    if ($controls->is_action('delete')) {
        $i = (int) $_POST['btn'];
        $emails = $autoresponder->emails;
        Newsletter::instance()->delete_email($emails[$i]);
        unset($emails[$i]);
        $autoresponder->emails = $emails;
        $autoresponder = $this->save_autoresponder($autoresponder);
        $controls->set_data($autoresponder);
        $controls->add_message_deleted();
    }

    if ($controls->is_action('reset')) {
        $logger->info('Reset called for autoresponder ' . $autoresponder->id);

        $controls->data['id'] = $autoresponder_id;
        $autoresponder = $this->save_autoresponder($controls->data);
        $controls->set_data($autoresponder);

        if (!$autoresponder->list) {
            $controls->errors = 'No list assigned.';
        } else {
            if ($autoresponder->emails) {
                // Get the first email to compute the first delay
                $email = $newsletter->get_email($autoresponder->emails[0]);
                $send_at = time() + $email->options['delay'] * 3600;
            } else {
                $send_at = time();
            }
            $list = (int) $autoresponder->list;
            $this->query($wpdb->prepare("delete from {$wpdb->prefix}newsletter_autoresponder_steps where autoresponder_id=%d", $autoresponder->id));

            $wpdb->query("insert ignore into " . $wpdb->prefix . "newsletter_autoresponder_steps (autoresponder_id, user_id, send_at) (
                    select " . $autoresponder->id . ", u.id, " . $send_at . " from " . $wpdb->prefix . "newsletter u left join " . $wpdb->prefix . "newsletter_autoresponder_steps s on u.id=s.user_id and autoresponder_id=" .
                    $autoresponder->id . " where s.user_id is null and u.list_" . $list . "=1)");
            $controls->add_message_reset();
        }
    }

    if ($controls->is_action('run')) {
        $this->hook_newsletter(true, $autoresponder);
        $controls->messages .= 'Engine triggered';
    }

    if ($controls->is_action('continue_completed_subscriber')) {

        $this->move_subscribers_with_completed_status_to_new_step($autoresponder);
    }
}


$emails = $autoresponder->emails;

if ($autoresponder->test) {
    $controls->warnings[] = 'Running in test mode!';
}
?>

<style>
<?php include __DIR__ . '/admin.css'; ?>
</style>

<div class="wrap" id="tnp-wrap">
    <?php @include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h3>Settings</h3>
        <h2><?php echo esc_html($autoresponder->name) ?></h2>

        <?php $controls->show(); ?>

    </div>
    <div id="tnp-body">

        <form method="post" action="">
            <div class="tnp-buttons">
                <?php $controls->button_icon_back('?page=newsletter_autoresponder_index'); ?>
                <?php $controls->button_save(); ?>
                <?php $controls->button_icon_subscribers('?page=newsletter_autoresponder_users&id=' . $autoresponder->id) ?>
                <?php $controls->button_icon_statistics('?page=newsletter_autoresponder_statistics&id=' . $autoresponder->id) ?>
            </div>
            <?php $controls->init(); ?>

            <div id="tabs">

                <ul>
                    <li><a href="#tabs-general"><?php _e('General', 'newsletter') ?></a></li>
                    <li><a href="#tabs-advanced"><?php _e('Advanced', 'newsletter') ?></a></li>
                    <li><a href="#tabs-analytics"><?php _e('Google Analytics', 'newsletter') ?></a></li>
                </ul>

                <div id="tabs-general">
                    <table class="form-table">
                        <tr>
                            <th>Enabled</th>
                            <td><?php $controls->yesno('status') ?></td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td><?php $controls->text('name', 70) ?></td>
                        </tr>
                        <tr>
                            <th>List</th>
                            <td>
                                <?php $controls->lists_select_with_notes('list', 'Select...') ?>
                                <p class="description">
                                    This series is activated only to subscribers in the specified list. Subscribers are automatically
                                    captured when they enter the list and automatically released when they exit the list (usually within 5 minutes).
                                </p>
                            </td>
                        </tr>
                    </table>

                </div>
                <div id="tabs-advanced">
                    <table class="form-table">
                        <tr>
                            <th>Restart on re-subscription</th>
                            <td>
                                <?php $controls->yesno('restart') ?>
                                <p class="description">
                                    If a subscriber re-subscribes and the series is already completed, restart it.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th>Lists to add on completion</th>
                            <td>
                                <?php $controls->lists('new_lists') ?>
                                <p class="description">
                                    List to be set on a subscriber's profile when the series reaches its end.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="tabs-analytics">
                    
                    <p>
                        Google Analytics addon required.<br>
                        On UTM parameters <code>{email_id}</code> and <code>{email_subsject}</code> can be used to make them dynamic.<br>
                    </p>
                   
                    <table class="form-table">
                        <tr>
                            <th>UTM Campaign</th>
                            <td>
                                <?php $controls->text('utm_campaign', 50); ?>
                                <p class="description">
                                    
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th>UTM Source (mandatory)</th>
                            <td>
                                <?php $controls->text('utm_source', 50); ?>
                                <p class="description">
                                    Use the <code>{step}</code> tag to have the step number inserted (1, 2, 3, ...). The suggested value
                                    is <code>step-{step}</code>.
                                </p>
                            </td>
                        </tr>


                        <tr>
                            <th>UTM Medium</th>
                            <td>
                                <?php $controls->text('utm_medium', 50); ?>
                                <p class="description">
                                    Should be set to "email" since this is the only medium used.
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th>UTM Term</th>
                            <td>
                                <?php $controls->text('utm_term', 50); ?>
                                <p class="description">
                                    Usually empty can be used on specific newsletters but it is more related to keyword based advertising.
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th>UTM Content</th>
                            <td>
                                <?php $controls->text('utm_content', 50); ?>
                                <p class="description">
                                    Usually empty can be used on specific newsletters.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>


            <h3>Messages</h3>

            <table class="widefat" style="width: 100%">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <?php if ($debug) { ?>
                            <th>Email ID</th>
                        <?php } ?>
                        <th><?php _e('Subject', 'newsletter') ?></th>
                        <th>Delay <small>(from previous message)</small></th>
                        <th><?php _e('Subscribers waiting', 'newsletter') ?></th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>

                <tbody>
                    <?php for ($i = 0; $i < count($emails); $i++) { ?>
                        <?php
                        $email_id = $emails[$i];
                        $email = $newsletter->get_email($email_id);
                        ?>
                        <tr>
                            <td><?php echo $i + 1 ?></td>
                            <?php if ($debug) { ?>
                                <td><code><?php echo $email->id ?></code></td>
                            <?php } ?>
                            <td><?php echo esc_html($email->subject) ?></td>
                            <td><?php echo esc_html($this->format_delay($email->options['delay'])) ?></td>
                            <td>
                                <?php echo $this->get_subscribers_count_waiting_on_step($autoresponder->id, $i) ?>
                                <?php if ($debug) { ?>
                                    <code>(<?php echo $this->get_late_subscribers_count_waiting_on_step($autoresponder->id, $i) ?> late)</code>
                                <?php } ?>
                            </td>
                            <td>
                                <?php
                                if ($i > 0) {
                                    $controls->button_confirm('up', '↑', '', $i);
                                } else {
                                    echo '<span style="margin-left: 34px"></span>';
                                }
                                ?>
                                <?php
                                if ($i < ( count($emails) - 1 )) {
                                    $controls->button_confirm('down', '↓', '', $i);
                                }
                                ?>
                            </td>
                            <td style="white-space: nowrap">
                                <?php $controls->button_icon_edit('?page=newsletter_autoresponder_index&id=' . $autoresponder->id . '&email_id=' . $email->id) ?>
                                <?php $controls->button_icon_statistics($statistics->get_statistics_url($autoresponder->emails[$i])) ?>
                            </td>
                            <td style="white-space: nowrap">
                                <?php $controls->button_icon_copy($i); ?>
                                <?php $controls->button_icon_delete($i); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="tnp-buttons"><?php $controls->button('add', 'Add a new message'); ?></div>

            <?php if ($this->has_too_early_completed_subscribers($autoresponder)) { ?>
                <div class="tnp-message">
                    There are subscribers which completed this series BEFORE more steps have been added. Would you re-enable them?<br>
                    <a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/autoresponder-extension/#completed">Please read carefully about possible drawbacks.</a>
                    <br><br>
                    <?php $controls->button('continue_completed_subscriber', __('Re-enable them', 'newsletter-autoresponder')); ?>
                </div>
            <?php } ?>

            <p><br><a href="?page=newsletter_autoresponder_maintenance&id=<?php echo $autoresponder->id ?>">Maintenance panel</a></p>

        </form>

    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
