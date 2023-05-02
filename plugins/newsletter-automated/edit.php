<?php
/* @var $this NewsletterAutomated */
/* @var $controls NewsletterControls */

$feed_id = (int) $_GET['id'];
$feed = $this->get_channel($feed_id);

$using_composer = $feed->theme_type == NewsletterAutomated::THEME_TYPE_COMPOSER;

$logger = $this->get_admin_logger();

if (!$controls->is_action()) {
    /* @var $wpdb wpdb */

    $controls->data = $feed->data;
    if ($controls->data === false) {
        $controls->data = [];
    }
    if (empty($controls->data['frequency'])) {
        $controls->data['frequency'] = 'weekly';
    }
    if (!isset($controls->data['enabled'])) {
        $controls->data['enabled'] = 0;
    }
} else {

    if ($controls->is_action('now')) {
        $logger->info('Forced newsletter generation for channel ' . $feed_id);
        if ($controls->data['enabled'] == 1) {
            update_option('newsletter_automated_' . $feed_id . '_wakeup', 0, false);
            $res = $this->hook_newsletter_automated($feed_id, true);
            if ($res) {
                $controls->messages = 'New newsletter generated and scheduled.';
            } else {
                $controls->errors = 'There is no new content to generate a new newsletter. You may change the last sent time to include already processed posts.';
            }
        } else {
            $controls->errors = 'This channel is not enabled.';
        }
    }

    if ($controls->is_action('save') || $controls->is_action('theme')) {

        $logger->info('Saved channel ' . $feed_id);
        // TODO: Remove when this patch is no more needed
        $controls->data['add_new'] = 0; //$controls->data['subscription'] == 2 ? 1 : 0;
        if (!isset($controls->data['max_posts']) || !is_numeric($controls->data['max_posts'])) {
            $controls->data['max_posts'] = 10;
        }

        if ($controls->is_action('save')) {
            wp_clear_scheduled_hook('newsletter_automated', array($feed_id));

            // Create the daily event at the specified time
            if ($controls->data['enabled'] == 1) {
                $hour = (int) $controls->data['hour'] - get_option('gmt_offset'); // to gmt
                // Set always to the next day to avoid to send the same day twice because delivery hour change!
                $day = gmdate("d");
                if (gmdate('G') > $hour) {
                    $day++;
                }
                $time = gmmktime($hour, 0, 0, gmdate("m"), $day, gmdate("Y"));
                wp_schedule_event($time, 'daily', 'newsletter_automated', array($feed_id));

                // Second time scheduled
                if (!empty($controls->data['hour2_enabled'])) {
                    $hour = (int) $controls->data['hour2'] - get_option('gmt_offset'); // to gmt
                    // Set always to the next day to avoid to send the same day twice because delivery hour change!
                    $day = gmdate("d");
                    if (gmdate('G') > $hour) {
                        $day++;
                    }
                    $time = gmmktime($hour, 0, 0, gmdate("m"), $day, gmdate("Y"));
                    wp_schedule_event($time, 'daily', 'newsletter_automated', array($feed_id));
                }
            }
            if (!$using_composer) {
                // Save the current theme option for future presets
                $theme_options = array();
                foreach ($controls->data as $key => &$value) {
                    if (substr($key, 0, 6) != 'theme_') {
                        continue;
                    }
                    $theme_options[$key] = $value;
                }

                update_option('newsletter_automated_theme_' . $controls->data['theme'], $theme_options, false);
            }
        }



        if ($controls->is_action('theme')) {

            foreach (array_keys($controls->data) as $key) {
                if (substr($key, 0, 6) == 'theme_') {
                    unset($controls->data[$key]);
                }
            }

            // Load the new theme
            $theme = $this->get_theme($controls->data['theme']);

            // Old or new?
            if (!empty($theme['type'])) {
                $controls->data['new_theme'] = 0;
            } else {
                $controls->data['new_theme'] = 1;
            }

            // Check for presets previously saved
            $theme_options = $this->get_theme_options($controls->data['theme']);

            $controls->data = array_merge($controls->data, $theme_options);
        }

        $wpdb->update($wpdb->prefix . "newsletter_automated", array('data' => json_encode($controls->data)), array('id' => $feed_id));

        $feed = $this->get_channel($feed_id);
    }

    if ($controls->is_action('test')) {
        $logger->info('Sending test for channel ' . $feed_id);
        $users = NewsletterUsers::instance()->get_test_users();
        if (empty($users)) {
            $controls->errors = 'No test subscribers found. Mark few subscribers as "test subscriber".';
        } else {

            $feed = $wpdb->get_row($wpdb->prepare("select * from " . $wpdb->prefix . "newsletter_automated where id=%d limit 1", $feed_id));
            $feed->data = $controls->data;

            $email = $this->create_email($feed, -1);
            //$controls->messages .= htmlspecialchars(print_r($email, true));
            $email['track'] = 0;
            // To avoid debug notices
            $email['id'] = 0;
            
            $email = (object)$email;

            Newsletter::instance()->send($email, $users, true);
            if (!empty($this->create_email_result)) {
                $controls->errors = $this->create_email_result;
            } else {
                $controls->messages = 'Test email sent to: ';
            }
            foreach ($users as $user) {
                $controls->messages .= $user->email . ' ';
            }
            $controls->messages .= '<br><br><strong>Channel changes have not been save. Remember to save if the new setting are ok.</strong>';
            $controls->messages .= '<br><strong>Tracking and online version are not active on test newsletters</strong>';
        }
    }

    if ($controls->is_action('reset_time')) {
        $logger->info('Time reset for channel ' . $feed_id);
        $this->set_last_run($feed_id, 0);
        $controls->messages = 'Reset. On next run all posts are considered as new';
        $feed = $this->get_channel($feed_id);
    }

    if ($controls->is_action('back_time')) {
        $logger->info('Back 1 day for channel ' . $feed_id);
        $this->add_to_last_run($feed_id, -3600 * 24);
        $controls->messages = 'Set.';
        $feed = $this->get_channel($feed_id);
    }

    if ($controls->is_action('forward_time')) {
        $logger->info('Forward 1 day for channel ' . $feed_id);
        $this->add_to_last_run($feed_id, 3600 * 24);
        $controls->messages = 'Set.';
        $feed = $this->get_channel($feed_id);
    }

    if ($controls->is_action('now_time')) {
        $logger->info('Time set to now for channel ' . $feed_id);
        $this->set_last_run($feed_id, time());
        $controls->messages = 'Set.';
        $feed = $this->get_channel($feed_id);
    }

    if ($controls->is_action('import')) {
        $list = (int) $controls->data['list'];
        if (empty($list)) {
            $controls->errors = 'Feed by mail subscriber import can be done only when a list is selected';
        } else {
            $res = $wpdb->query("update " . NEWSLETTER_USERS_TABLE . " set list_{$list}=1 where feed=1");
            $controls->messages = "Imported $res subscribers.";
        }
    }
}

global $post;

$themes = $this->get_themes();
$theme_options = array();
foreach ($themes as $id => $data) {
    $theme_options[$id] = $data['name'];
}

$email = $this->get_last_email($feed_id);

if ($feed->last_run_status) {
    if ($feed->theme_type == self::THEME_TYPE_COMPOSER) {
        $controls->add_message('On last run there was no new contents and the newsletter has not been generated. If you think it is wrong, please check your template dynamic blocks.');
    } else {
        $controls->add_message('On last run there was no new contents and the newsletter has not been generated. If you think it is wrong, please check your Automated theme settings.');
    }
}

if ($email) {
    $controls->add_message('Last automated newsletter has been sent on ' . $controls->print_date($email->send_on));
}

if (!empty($controls->data['enabled'])) {
    if (!wp_next_scheduled('newsletter_automated', [$feed_id])) {
        $controls->errors .= 'Something happened in your WP, the Automated engine has been disabled. Please save this option to restore it';
    }
}
?>

<style>
    #preview-subject {
        font-size: 20px;
        border: 1px solid #ccc;
        margin-bottom: 16px;
        padding: 10px;
    }
    #preview-desktop, #preview-mobile {
        float: left;
        border: 1px dashed #aaa;
        height: 500px
    }
    #preview-desktop, #preview-subject {
        width: 800px;
    }
    #preview-mobile {
        width: 375px;
        margin-left: 20px;
    }
</style>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">

        <h2>Channel configuration</h2>

        <?php $controls->show(); ?>

        <?php if (empty($controls->data['new_theme']) && !$using_composer) { ?>
            <?php
            $posts = $this->get_posts($controls->data, $feed_id);
            list($new_posts, $old_posts) = $this->split_posts($posts, $feed->last_run);
            ?>
            <?php if (empty($new_posts)) { ?>
                <div class="tnpc-warning">
                    There are not new posts to generate a newsletter for this channel. Take time to publish something new before
                    the next scheduled delivery.
                </div>
            <?php } else { ?>
                <div class="tnpc-message">
                    There are <?php echo count($new_posts) ?> new posts ready for the next automated newsletter.
                </div>
            <?php } ?>
        <?php } ?>

    </div>

    <div id="tnp-body" class="tnp-automated-edit">

        <form method="post" action="">
            <?php $controls->init(); ?>

            <div class="tnp-buttons">
                <?php $controls->button_icon_back('?page=newsletter_automated_index'); ?>
                <?php $controls->button_save('save', 'Save'); ?>
                <?php //$controls->button('reset', 'Reset');   ?>
                <?php $controls->button_test(); ?>
                <?php if ($controls->data['enabled'] == 1) { ?>
                    <?php $controls->button_confirm('now', 'Generate and send now', 'Are you sure?'); ?>
                <?php } ?>
                <?php if ($using_composer) { ?>
                    <?php $controls->button_icon_design('?page=newsletter_automated_template&id=' . $feed_id) ?>
                <?php } ?>
            </div>

            <div id="tabs">

                <ul>
                    <li><a href="#tabs-configuration">Configuration</a></li>
                    <li><a href="#tabs-planning">Planning</a></li>
                    <?php if (!$using_composer) { ?>
                        <li><a href="#tabs-theme">Theme Options</a></li>
                        <li><a href="#tabs-preview">Theme Preview</a></li>
                    <?php } ?>1
                    <li><a href="#tabs-newsletter-preview">Newsletter Preview</a></li>
                    <li><a href="#tabs-status">Status</a></li>
                    <?php if (empty($controls->data['new_theme']) && !$using_composer) { ?>
                        <li><a href="#tabs-posts">New Posts</a></li>
                    <?php } ?>
                </ul>

                <div id="tabs-configuration">

                    <table class="form-table">
                        <tr valign="top">
                            <th>Enabled?</th>
                            <td>
                                <?php $controls->yesno('enabled'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>Channel name</th>
                            <td>
                                <?php $controls->text('name', 50); ?>
                                <p class="description">
                                    For internal use, be descriptive.
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th>Subject</th>
                            <td>
                                <?php $controls->text('subject', 50); ?>
                                <p class="description">
                                    <?php if ($using_composer) { ?>
                                        If empty the first block suggested subject is used. Use <code>{dynamic_subject}</code> to reference the first block suggested
                                        subject. Use <code>{date}</code> tag for the current date
                                        (<a href="https://www.thenewsletterplugin.com/documentation/newsletter-tags" target="_blank">see more options</a>).
                                    <?php } else { ?>
                                        If empty the last post title is used. Use the <code>{date}</code> tag for the current date or <code>{last_post_title}</code>.
                                        (<a href='http://www.thenewsletterplugin.com/plugins/newsletter/feed-by-mail-module#subject' target='_blank'>Read more about subject generation</a>.
                                    <?php } ?>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th>Send to list</th>
                            <td>
                                <?php $controls->lists_select('list', 'Everyone'); ?>
                                <p class="description">
                                    The subscriber list this channel is sent to. Subscribers can stop to receive this channel disabling the
                                    list on their profile.
                                </p>
                            </td>
                        </tr>
                        <?php if (Newsletter::instance()->is_multilanguage()) { ?>
                            <tr valign="top">
                                <th>Filter subscribers by language</th>
                                <td>
                                    <?php $controls->languages(); ?>
                                    <p class="description">
                                        If no language is selected, no filter is applied. This filter DOES NOT affect the newsletter content.
                                    </p>
                                </td>
                            </tr>
                        <?php } ?>






                        <tr valign="top">
                            <th>Track opens and clicks?</th>
                            <td>
                                <?php $controls->yesno('track'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>Sender name</th>
                            <td>
                                <?php $controls->text('sender_name', 50); ?>
                                <p class="description">
                                    Leave empty to use the default.
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th>Sender email name</th>
                            <td>
                                <?php $controls->text_email('sender_email', 50); ?>
                                <p class="description">
                                    Leave empty to use the default.
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h3>Google Analytics Tracking</h3>
                    <p>
                        Requires the <a href="?page=newsletter_main_extensions">Analytics Extension</a>. UTMs parameters are not added to links
                        on newsletter body but appended when the subscriber clicks them. Links on test newsletter are not processed.
                    </p>
                    <table class="form-table">
                        <tr>
                            <th>UTM Campaign</th>
                            <td>
                                <?php $controls->text('utm_campaign', 50); ?>
                                <p class="description">
                                    This is the campaign name, mandatory to track, could be your channel name
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th>UTM Source</th>
                            <td>
                                <?php $controls->text('utm_source', 50); ?>
                                <p class="description">
                                    Should set as "newsletter-{email_id}" and it's mandatory for Google. "{email_id}" is replaced with the
                                    newsletter unique id. Automated newsletter, autoresponders and other non standard newsletter use a different
                                    source like automated-{channel numer}-{email id}.
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

                <div id="tabs-planning">
                    <table class="form-table">
                        <tr valign="top">
                            <th>Planning</th>
                            <td>
                                <?php $controls->radio('frequency', 'weekly', 'Weekly on...'); ?><br>
                                Monday&nbsp;<?php $controls->yesno('day_1'); ?>
                                Tuesday&nbsp;<?php $controls->yesno('day_2'); ?>
                                Wednesday&nbsp;<?php $controls->yesno('day_3'); ?>
                                Thursday&nbsp;<?php $controls->yesno('day_4'); ?>
                                Friday&nbsp;<?php $controls->yesno('day_5'); ?>
                                Saturday&nbsp;<?php $controls->yesno('day_6'); ?>
                                Sunday&nbsp;<?php $controls->yesno('day_7'); ?>
                                <br><br>
                                <?php $controls->radio('frequency', 'monthly', 'Monthly on...'); ?><br>

                                <style>
                                    #tnp-monthly-plan {
                                        width: auto!important;
                                    }
                                    #tnp-monthly-plan th, #tnp-monthly-plan td {
                                        padding: 3px;
                                        text-align: center;
                                        width: 80px;
                                    }
                                    #tnp-monthly-plan th {
                                        font-weight: bold;
                                    }
                                </style>
                                <table id="tnp-monthly-plan">
                                    <tr>
                                        <th>Week</th>
                                        <th>Monday</th>
                                        <th>Tuesday</th>
                                        <th>Wednesday</th>
                                        <th>Thursday</th>
                                        <th>Friday</th>
                                        <th>Saturday</th>
                                        <th>Sunday</th>
                                    </tr>
                                    <?php for ($week = 1; $week <= 5; $week++) { ?>

                                        <tr>
                                            <td><?php echo $week; ?></td>
                                            <?php
                                            for ($i = 1; $i <= 7; $i++) {
                                                echo '<td>';
                                                $controls->checkbox_group('monthly_' . $week . '_days', $i);
                                                echo '</td>';
                                            }
                                            ?>
                                        </tr>
                                    <?php } ?>

                                </table>

                            </td>
                        </tr>

                        <tr valign="top">
                            <th>Delivery hour</th>
                            <td>
                                <?php $controls->hours('hour'); ?>
                                <p class="description">
                                    <a href="https://www.thenewsletterplugin.com/documentation/addons/extended-features/automated-extension/#hours">Read more about DST (Daylight Time Saving)</a>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th>Second optional delivery hour</th>
                            <td>
                                <?php $controls->enabled('hour2_enabled', ['bind_to' => 'hour2']); ?>

                                <?php $controls->hours('hour2'); ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if (!$using_composer) { ?>
                    <div id="tabs-theme">
                        <?php $controls->hidden('new_theme') ?>

                        <table class="form-table">
                            <tr valign="top">
                                <th>Theme</th>
                                <td>
                                    <?php $controls->select('theme', $theme_options); ?>
                                    <?php $controls->button('theme', 'Change'); ?>

                                    <p class="description">
                                        Update to load the new theme and update the previews. Custom themes MUST be added to the
                                        <code>wp-content/extensions/newsletter-automated/themes</code> folder.
                                        <a href='http://www.thenewsletterplugin.com/plugins/newsletter/newsletter-themes' target='_blank'>Read more on themes</a>.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <?php if (empty($controls->data['new_theme'])) { ?>
                            <h3>Posts selection</h3>
                            <table class="form-table">
                                <tr valign="top">
                                    <th>Max posts to extract</th>
                                    <td>
                                        <?php $controls->text('max_posts', 5); ?>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th>Excerpt length</th>
                                    <td>
                                        <?php $controls->text('excerpt_length', 5); ?> (words)
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th>Tags to include</th>
                                    <td>
                                        <?php $controls->text('tags'); ?>
                                        <p>Comma separated tag slugs</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th>Categories to include</th>
                                    <td>
                                        <?php $controls->categories_group('categories'); ?>
                                        <p>If none selected ALL categories will be included (useful for an all content channel).</p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th>Post types</th>
                                    <td>
                                        <?php $controls->post_types(); ?>

                                        <p class="description">
                                            Check the post types actually available on your blog that will be included in the periodic email. If none is checked
                                            the standard blog posts are used.
                                        </p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th>Generate even if there are no new posts</th>
                                    <td>
                                        <?php $controls->yesno('ignore_no_new_posts'); ?>
                                        <a href="https://www.thenewsletterplugin.com/documentation/automated-extension#ignore-no-new-posts" target="_blank"><i class="fa fa-question-circle"></i></a>
                                    </td>
                                </tr>
                            </table>
                        <?php } ?>

                        <?php
                        $file = $this->get_theme_file($controls->data['theme'], 'theme-options.php');
                        if (@is_file($file))
                            include $file;
                        ?>

                    </div>


                    <div id="tabs-preview">
                        <p>
                            This is only a preview to see how the theme will generate emails, it's not the actual email that will be sent
                            next time.
                        </p>

                        <iframe id="preview-desktop" src="<?php echo wp_nonce_url(Newsletter::add_qs(home_url('/'), 'na=automated-preview&id=' . $feed_id), 'preview'); ?>"></iframe>

                        <iframe id="preview-mobile" src="<?php echo wp_nonce_url(Newsletter::add_qs(home_url('/'), 'na=automated-preview&id=' . $feed_id), 'preview'); ?>"></iframe>

                        <div style="clear: both"></div>
                        <!--
                        <h3>Textual version</h3>
                        <iframe style="border: 1px dashed #aaa; width: 800px" src="<?php echo wp_nonce_url(plugins_url('newsletter-automated') . '/preview-text.php?id=' . $feed_id, 'preview'); ?>" height="500"></iframe>
                        -->
                    </div>
                <?php } ?>

                <div id="tabs-newsletter-preview">
                    <p>
                        Generated newsletter preview as if it would be sent right now. Different content could be included at
                        sending time. Dynamic subject can be different at delivery time.
                    </p>
                    <p>
                        <strong>If empty there is no new contents since the last delivery.</strong>
                    </p>
                    <script>
                        function setPreviewSubject(iframe) {
                            document.getElementById("preview-subject").innerHTML = iframe.contentDocument.title;
                        }
                    </script>

                    <div id="preview-subject"></div>
                    <iframe id="preview-desktop" src="<?php echo wp_nonce_url(Newsletter::add_qs(home_url('/'), 'real=1&na=automated-preview&id=' . $feed_id), 'preview'); ?>&real=1" onload="setPreviewSubject(this)"></iframe>

                    <iframe id="preview-mobile" src="<?php echo wp_nonce_url(Newsletter::add_qs(home_url('/'), 'real=1&na=automated-preview&id=' . $feed_id), 'preview'); ?>"></iframe>

                    <div style="clear: both"></div>
                </div>

                <div id="tabs-status">

                    <p>
                        Posts below are the one will be included on next email (sheduled future posts are not counted so
                        more posts could be included).
                    </p>

                    <table class="form-table">

                        <tr valign="top">
                            <th>Last sent time</th>
                            <td>
                                <?php echo NewsletterControls::print_date($feed->last_run); ?>
                                <?php $controls->button_confirm('reset_time', 'Reset as it never ran', 'Are you sure?'); ?>
                                <?php $controls->button('back_time', 'Back one day'); ?>
                                <?php $controls->button('forward_time', 'Forward one day'); ?>
                                <?php $controls->button('now_time', 'Set to now'); ?>
                                <p class="description">
                                    Last time a newsletter has been generated and sent.
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>Generator next run</th>
                            <td>
                                <?php echo NewsletterControls::print_date(wp_next_scheduled('newsletter_automated', array($feed_id)), false, true); ?>
                                <p class="description">
                                    When the newsletter generator runs next time in its daily cycle. Of course on not enabled days it will
                                    stop suddenly.
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>Subscribers on this channel</th>
                            <td>
                                <?php echo $this->get_subscriber_count($feed) ?>
                                <?php if (NEWSLETTER_DEBUG) { ?>
                                    <p class="description"><code><?php echo $this->generate_subscribers_list_query($feed) ?>
                                        <?php } ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>Import</th>
                            <td>
                                <?php $controls->button_confirm('import', 'Import feed by mail subscribers', 'Are you sure?'); ?>
                                <p class="description">
                                    The import associates the "old" feed by mail subscribed user to the Newsletter list associated to this
                                    channel.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if (empty($controls->data['new_theme']) && !$using_composer) { ?>
                    <div id="tabs-posts">
                        <table class="form-table">
                            <tr valign="top">
                                <th>New posts from last sending</th>
                                <td>
                                    <?php if (empty($new_posts)) { ?>
                                        There are no new posts to generate a newsletter. It's ok if the newsletter has just been sent, otherwise
                                        take time to publish something new.
                                    <?php } else { ?>
                                        <?php
                                        foreach ($new_posts as $post) {
                                            setup_postdata($post);
                                            ?>
                                            [<?php echo the_ID(); ?>] <?php echo NewsletterControls::print_date(NewsletterAutomated::m2t($post->post_date_gmt)); ?> <a target="_blank" href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a><br />
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php } ?>

            </div>

            <div class="tnp-buttons">
                <?php $controls->button('save', 'Save'); ?>
                <?php $controls->button('test', 'Test'); ?>
            </div>


        </form>

    </div>
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
