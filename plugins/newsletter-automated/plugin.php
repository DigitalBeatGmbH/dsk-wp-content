<?php

class NewsletterAutomated extends NewsletterAddon {

    const THEME_TYPE_CLASSIC = 0;
    const THEME_TYPE_COMPOSER = 1;

    static $instance;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('automated', $version);
        $this->setup_options();

        // Here since the alternative wp cron runs tasks on init!
        add_action('newsletter_automated', array($this, 'hook_newsletter_automated'));
        add_action('newsletter_action', array($this, 'hook_newsletter_action'));
    }

    function upgrade($first_time = false) {
        global $wpdb, $charset_collate;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta("CREATE TABLE `" . $wpdb->prefix . "newsletter_automated` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `data` longtext,
            `theme` longtext,
            `theme_type` int default 0,
            `last_run` int default 0,
            `email_id` int default 0,
            `last_run_status` int default 0, 
            PRIMARY KEY (`id`)) $charset_collate;");
        maybe_convert_table_to_utf8mb4($wpdb->prefix . 'newsletter_automated');

        if ($this->version > '4.4.0') {
            $channels = $this->get_channels();
            foreach ($channels as $channel) {
                $last_run = (int) get_option('newsletter_automated_last_run_' . $channel->id);
                if ($last_run) {
                    $this->query($wpdb->prepare("update {$wpdb->prefix}newsletter_automated set last_run=%d where id=%d limit 1", $last_run, $channel->id));
                }
                delete_option('newsletter_automated_last_run_' . $channel->id);
            }
        }
    }

    function init() {

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_newsletters', array($this, 'hook_newsletter_menu_newsletters'));
            }
            add_action('admin_enqueue_scripts', array($this, 'hook_admin_enqueue_scripts'));
            add_filter('newsletter_lists_notes', array($this, 'hook_newsletter_lists_notes'), 10, 2);
        }
    }

    function hook_newsletter_action($action) {
        
        switch ($action) {
            case 'automated-preview':
                $newsletter = Newsletter::instance();
                if (!$newsletter->is_allowed()) {
                    die('Not enough privileges');
                }

                include __DIR__ . '/preview.php';

                die();
                break;
        }
    }

    function hook_newsletter_lists_notes($notes, $list_id) {
        $channels = $this->get_channels();
        foreach ($channels as $channel) {
            if ($channel->data['list'] == $list_id) {
                $notes[] = 'Linked to automated channel "' . $channel->data['name'] . '"';
            }
        }
        return $notes;
    }

    function hook_admin_enqueue_scripts() {
        wp_enqueue_script('jquery-ui-accordion');
    }

    function hook_newsletter_menu_newsletters($entries) {
        $entries[] = array('label' => '<i class="fas fa-calendar-alt"></i> Automated Newsletters', 'url' => '?page=newsletter_automated_index', 'description' => 'Automatically generated from your blog content');
        return $entries;
    }

    function is_scheduled_day($channel, $time) {
        // 1 - Monday, ..., 7 - Sunday
        $day_of_week = gmdate('N', $time + get_option('gmt_offset') * 3600);
        $day_of_month = gmdate('j', $time + get_option('gmt_offset') * 3600);
        $week_of_month = floor(($day_of_month - 1) / 7) + 1;

        $options = $channel->data;

        if ($options['frequency'] == 'monthly') {
            if ($day_of_month < 8) {
                if (!isset($options['monthly_1_days']) || !is_array($options['monthly_1_days'])) {
                    return false;
                }
                if (array_search($day_of_week, $options['monthly_1_days']) === false) {
                    return false;
                }
            } else if ($day_of_month < 15) {
                if (!isset($options['monthly_2_days']) || !is_array($options['monthly_2_days'])) {
                    return false;
                }
                if (array_search($day_of_week, $options['monthly_2_days']) === false) {
                    return false;
                }
            } else if ($day_of_month < 22) {
                if (!isset($options['monthly_3_days']) || !is_array($options['monthly_3_days'])) {
                    return false;
                }
                if (array_search($day_of_week, $options['monthly_3_days']) === false) {
                    return false;
                }
            } else if ($day_of_month < 29) {
                if (!isset($options['monthly_4_days']) || !is_array($options['monthly_4_days'])) {
                    return false;
                }
                if (array_search($day_of_week, $options['monthly_4_days']) === false) {
                    return false;
                }
            } else {
                if (!isset($options['monthly_5_days']) || !is_array($options['monthly_5_days'])) {
                    return false;
                }
                if (array_search($day_of_week, $options['monthly_5_days']) === false) {
                    return false;
                }
            }
        } else {
            if ($options['day_' . $day_of_week] == 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Called daily to generate the email (if needed). Return false is not generated
     * when called directly. Called daily for each channel at the channel delivery time.
     */
    function hook_newsletter_automated($id = null, $force = false) {
        global $wpdb;

        $newsletter = Newsletter::instance();

        $logger = $this->get_logger();

        $logger->info('Waking up channel ' . $id);
        
        $channel = $this->get_channel($id);
        
        if (!$channel) {
            $logger->fatal('Channel "' . $id . '" does not exist. Could be WP has an old schduled job with a wrong channel ID. That is not actually a real problem.');
            return false;
        }    
        
        $last_wakeup = get_option('newsletter_automated_' . $id . '_wakeup', 0);
        $logger->debug('Last wakeup: ' . $last_wakeup);
        if ($last_wakeup > time() - 900) {
            $logger->fatal('Channel ' . $id . ' waked up too early. It means the job has been executed twice. We managed it but there could be a problem on WP scheduler.');
            return false;  
        }
        update_option('newsletter_automated_' . $id . '_wakeup', time(), false);

        if (!isset($channel->data['enabled']) || $channel->data['enabled'] != 1) {
            $logger->info('Channel ' . $id . ' not enabled. It should have not been waked up.');
            return false;
        }
        
        if (!$force && !$this->is_scheduled_day($channel, time())) {
            $logger->info('Channel ' . $id . ' not scheduled for today.');
            return false;
        }

        if (!$force && !is_user_logged_in()) {
            if (!empty($this->options['user_id'])) {
                wp_set_current_user($this->options['user_id']);
                $logger->info('Current user set to ' . $this->options['user_id']);
            }
        }

        $email = $this->create_email($channel, $channel->last_run);

        if (!$force) {
            if (!empty($this->options['user_id'])) {
                wp_set_current_user(0);
            }
        }

        if (!$email) {
            $logger->info('Email not created: the template or the theme returned to skip this delivery.');
            $this->query($wpdb->prepare("update {$wpdb->prefix}newsletter_automated set last_run_status=1 where id=%d limit 1", $id));
            return false;
        }

        $logger->info('Email generated with subject: ' . $email['subject']);

        $email['type'] = 'automated_' . $id;
        $email['total'] = $this->get_subscriber_count($channel);
        $email['query'] = $this->generate_subscribers_list_query($channel);

        $newsletter->save_email($email);

        // Used as timestamp for the next posts extraction
        $this->set_last_run($id, time());
        $this->query($wpdb->prepare("update {$wpdb->prefix}newsletter_automated set last_run_status=0 where id=%d limit 1", $id));

        return true;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Automated Newsletter', '<span class="tnp-side-menu">Automated</span>', 'exist', 'newsletter_automated_index', array($this, 'menu_page_index'));
        add_submenu_page(null, 'Edit', 'Edit', 'exist', 'newsletter-automated/edit.php');
        add_submenu_page(null, 'Newsletters', 'Newsletters', 'exist', 'newsletter_automated_newsletters', array($this, 'menu_page_newsletters'));
        add_submenu_page(null, 'Config', 'Config', 'exist', 'newsletter_automated_config', function () {
            require __DIR__ . '/config.php';
        });
        add_submenu_page(null, 'Template', 'Template', 'exist', 'newsletter_automated_template', function () {
            require __DIR__ . '/template.php';
        });
    }

    function menu_page_index() {
        require __DIR__ . '/index.php';
    }

    function menu_page_newsletters() {
        require __DIR__ . '/newsletters.php';
    }

    function hook_strip_shortcodes_tagnames($tags) {
        $tags[] = 'vc_row';
        $tags[] = 'vc_column';
        $tags[] = 'vc_column_text';
        return $tags;
    }

    /**
     * Extract all post based on options (passed or the ones saved).
     * Sets some variables inside $newsletter, for compatibility with old themes.
     *
     * @param array $options
     */
    function get_posts($options = null) {
        global $newsletter;

        $cat = '';
        // Channels have positive category selection
        if (!empty($options['categories'])) {
            $cat = implode(',', $options['categories']);
        }

        if (!isset($options['max_posts']))
            $options['max_posts'] = 10;
        // Extract the max posts
        $max_posts = $options['max_posts'];
        if (!is_numeric($max_posts)) {
            $max_posts = 10;
        }

        // Build the filter
        $filters = array('showposts' => $max_posts, 'post_status' => 'publish');
        if ($cat != '') {
            $filters['cat'] = $cat;
        }

        if (!empty($options['tags'])) {
            $filters['tag'] = str_replace(' ', '', $options['tags']);
        }

        if (!empty($options['post_types'])) {
            $post_types = $options['post_types'];
            if (!empty($post_types)) {
                $filters['post_type'] = $post_types;
            }
        }

        if (!empty($options['language'])) {
            if (class_exists('Polylang')) {
                $filters['lang'] = $options['language'];
            }
            if (class_exists('SitePress')) {
                $filters['suppress_filters'] = false;
                do_action('wpml_switch_language', $options['language']);
            }
        }

        $posts = get_posts($filters);
        if (empty($options['excerpt_length'])) {
            $options['excerpt_length'] = 30;
        }

        foreach ($posts as $post) {
            if (empty($post->post_excerpt)) {
                add_filter('strip_shortcodes_tagnames', array($this, 'hook_strip_shortcodes_tagnames'), 99, 1);
                $post->excerpt = wp_strip_all_tags(strip_shortcodes($post->post_content));
                remove_filter('strip_shortcodes_tagnames', array($this, 'hook_strip_shortcodes_tagnames'));

                $post->excerpt = wp_trim_words($post->excerpt, $options['excerpt_length']);
            } else {
                $post->excerpt = wp_trim_words($post->post_excerpt, $options['excerpt_length']);
            }

            $post->title = $post->post_title;
            $post->content = do_shortcode($post->post_content);
            $post->content = wpautop($post->content);
            $post->link = get_permalink($post->ID);
            $post->images = array();
            $post->images['thumbnail'] = NewsletterModule::get_post_image($post->ID, 'thumbnail');
            $post->images['medium'] = NewsletterModule::get_post_image($post->ID, 'medium');
            $post->images['large'] = NewsletterModule::get_post_image($post->ID, 'large');
        }

        return $posts;
    }

    /**
     * Creates an email to be sent for real or for test. A set of options can be passed i place of the actual saved
     * module options.
     * 
     * $last_run can be passed as timestamp to simulate a previous newsletter generation at thattime.
     *
     * @global Newsletter $newsletter
     * @global wpdb $wpdb
     * @return array Created email
     */
    var $create_email_result;

    function create_email($channel, $last_run = null) {
        global $wpdb, $newsletter;
        $options = $channel->data;
        $id = $channel->id;
        $logger = $this->get_logger();

        //$logger->debug('Creating email for channel ' . $channel->id);

        if ($last_run === null) {
            $last_run = $channel->last_run;
        }

        $email = ['options' => []];

        if ($channel->theme_type == self::THEME_TYPE_COMPOSER) {
            $template = Newsletter::instance()->get_email($channel->email_id);
            $result = NewsletterEmails::instance()->regenerate($template, array('type' => 'automated', 'last_run' => $last_run));
            //echo esc_html($template->message);
            //die();
            if (!$result) {
                return false;
            }
            $email['message'] = $template->message;
            $email['message_text'] = "This email requires a modern e-mail reader but you can view the email online here:\n{email_url}.\nThank you, " . wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) .
                    "\nTo change your subscription follow: {profile_url}.";

            $email['subject'] = 'Latest news from {blog_title}';
            if (!empty($options['subject'])) {
                $email['subject'] = $options['subject'];
                $email['subject'] = str_replace('{dynamic_subject}', $template->subject, $email['subject']);
            } else if (!empty($template->subject)) {
                $email['subject'] = $template->subject;
            }
        } else {

            $posts = array();

            // Old themes rely on post extracted by Automated, new theme do themself
            if (empty($options['new_theme'])) {

                // Load posts using the filters specified in the channel configuration
                $posts = $this->get_posts($options, $id);

                if (!empty($posts)) {
                    if ($last_run >= $this->m2t($posts[0]->post_date_gmt)) {
                        $logger->debug('The latest extracted post is older than the last generation time. Post list will be emptied.');
                        $this->create_email_result = 'The most recent post is too old';
                        $posts = array();
                    }
                }

                // Here we have an array of posts, empty of there are no posts available after the last generation date
                // We can proceed if the configuration allow it
                if (empty($posts) && empty($options['ignore_no_new_posts'])) {
                    $logger->debug('The post list is empty, exit');
                    // Rather odd, it means the blog has not published posts...
                    $this->create_email_result = 'No posts found, has the blog published posts?';
                    return false;
                }

                if (empty($posts)) {
                    $new_posts = array();
                    $old_posts = array();
                } else {
                    list($new_posts, $old_posts) = $this->split_posts($posts, $last_run);
                }
            }



            // Channels have theme options inside the channel options 

            $theme = $this->get_theme($options['theme'], true);
            $theme_options = $options;

            $theme_defaults_file = $theme['dir'] . '/theme-defaults.php';
            if (file_exists($theme_defaults_file)) {
                @include $theme_defaults_file;
                if (isset($theme_defaults) && is_array($theme_defaults)) {
                    $theme_options = array_merge($theme_defaults, $theme_options);
                }
            }

            $main_options = Newsletter::instance()->options;
            foreach ($main_options as $key => $value) {
                $theme_options['main_' . $key] = $value;
            }

            $theme_url = $this->get_theme_url($options['theme']);
            $theme_subject = trim($options['subject']);

            // The subject can be changed by the theme
            ob_start();
            require $this->get_theme_file($options['theme'], 'theme.php');
            $email['message'] = ob_get_clean();

            if (empty($email['message'])) {
                $logger->info('Theme returned an empty message');
                $this->create_email_result = 'The theme returned an empty message';
                return false;
            }

            $email['message'] = $this->inline_css($email['message']);

            if (!empty($theme_subject)) {
                $email['subject'] = $theme_subject;
            } else {
                if (!empty($posts)) {
                    $email['subject'] = $posts[0]->post_title;
                } else {
                    $email['subject'] = 'Subject not set';
                }
            }

            if (!empty($posts)) {
                $email['subject'] = str_replace('{last_post_title}', $posts[0]->post_title, $email['subject']);
            } else {
                $email['subject'] = str_replace('{last_post_title}', '', $email['subject']);
            }

            $email['message_text'] = 'This message can be viewed only with a modern email client, sorry.';
            $file = $this->get_theme_file($options['theme'], 'theme-text.php');
            if (is_file($file)) {
                ob_start();
                include $file;
                $email['message_text'] = ob_get_clean();
            }

            // END CLASSIC THEME
        }

        // Truncates too long subjects (if there is not mb_strlen, use strlen even if it wrongly find the length of utf-8 strings)

        if (mb_strlen($email['subject']) > 250) {
            $x = strrpos($email['subject'], ' ', 250);
            $email['subject'] = substr($email['subject'], 0, $x);
        }

        $email['subject'] = $newsletter->replace_date($email['subject']);
        $email['message'] = $newsletter->replace_date($email['message']);

        $email['total'] = $this->get_subscriber_count($channel);
        $email['query'] = $this->generate_subscribers_list_query($channel);

        $email['type'] = 'automated_' . $id;
        $email['send_on'] = time();
        $email['status'] = 'sending';
        $email['track'] = $options['track'];

        $email['message'] = str_replace('{subject}', $email['subject'], $email['message']);
        $email['message'] = str_replace('{email_subject}', $email['subject'], $email['message']);

        $email['options'] = array();
        if (isset($options['utm_campaign'])) {
            $email['options']['utm_campaign'] = $options['utm_campaign'];
            $email['options']['utm_source'] = $options['utm_source'];
            $email['options']['utm_medium'] = $options['utm_medium'];
            $email['options']['utm_term'] = $options['utm_term'];
            $email['options']['utm_content'] = $options['utm_content'];
        }
        
        // Sender name and email
        if (!empty($options['sender_name'])) {
            $email['options']['sender_name'] = $options['sender_name'];
        }
        
        if (!empty($options['sender_email'])) {
            $email['options']['sender_email'] = $options['sender_email'];
        }        

        return $email;
    }

    static function get_last_email($id = null) {
        global $wpdb;
        $id = (int) $id;
        // Limit the fields returned to the minimum
        $email = $wpdb->get_row("select id, send_on, status, sent, total from " . NEWSLETTER_EMAILS_TABLE . " where type='automated_$id' order by id desc limit 1", OBJECT);
        if ($wpdb->last_error) {
            return false;
        }
        return $email;
    }

    static function get_emails($id = null, $max = false) {
        global $wpdb;
        $id = (int) $id;
        $query = "select * from " . NEWSLETTER_EMAILS_TABLE . " where type='automated_$id' order by id desc";
        if ($max)
            $query .= ' limit ' . $max;
        $list = $wpdb->get_results($query, OBJECT);
        if ($wpdb->last_error) {
            return false;
        }
        if (empty($list)) {
            return array();
        }
        return $list;
    }

    /**
     * Returns all the available themes. The list is a set of arrays with keys:
     * 
     * dir - the path to the theme
     * name - the theme name
     * 
     */
    function get_themes() {
        static $list = array();

        // Caching
        if (!empty($list))
            return $list;

        $logger = $this->get_logger();

        $dir = dirname(__FILE__) . '/themes';
        $handle = @opendir($dir);

        if ($handle !== false) {
            while ($file = readdir($handle)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                if (!@is_file($dir . '/' . $file . '/theme.php')) {
                    continue;
                }

                $list[$file] = array('dir' => $dir . '/' . $file, 'name' => $file, 'type' => 'old');
            }
            closedir($handle);
        }

        $dir = WP_CONTENT_DIR . '/extensions/newsletter-automated/themes';
        if (is_dir($dir)) {
            $handle = @opendir($dir);

            if ($handle !== false) {
                while ($file = readdir($handle)) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    // Theme already registered with that name
                    if (isset($list[$file])) {
                        $logger->error('Theme ' . $file . ' in extensions folder already registered');
                        continue;
                    }
                    if (!@is_file($dir . '/' . $file . '/theme.php')) {
                        continue;
                    }
                    $list[$file] = array('dir' => $dir . '/' . $file, 'name' => $file, 'type' => 'old');
                }
                closedir($handle);
            }
        }

        $extra = array();
        $extra = apply_filters('newsletter_automated_themes', $extra);

        // [TODO] On windows it may not work
        foreach ($extra as $dir) {
            $dir = wp_normalize_path($dir);
            if (!file_exists($dir . '/theme.php')) {
                continue;
            }

            if (isset($list[basename($dir)])) {
                $logger->error('Theme in ' . $dir . ' folder already registered');
                continue;
            }

            $data = get_file_data($dir . '/theme.php', array('name' => 'Name'));

            // Should never happen
            if (empty($data['name'])) {
                $data['name'] = basename($dir);
                $data['type'] = 'old';
            }
            $data['dir'] = $dir;

            $list[basename($dir)] = $data;
        }

        return $list;
    }

    function get_theme($id, $fallback = false) {
        $themes = $this->get_themes();
        if (isset($themes[$id])) {
            return $themes[$id];
        }

        if ($fallback) {
            return $themes['default'];
        }

        return null;
    }

    function get_theme_options($theme) {
        return get_option('newsletter_automated_theme_' . $theme, array());
    }

    function get_theme_url($id) {
        $theme = $this->get_theme($id);
        $path = substr($theme['dir'], strlen(WP_CONTENT_DIR));

        return content_url($path);
    }

    /**
     * Returns the full path to a theme file.
     * 
     * @param string $id
     * @param string $file
     * @return string
     */
    function get_theme_file($id, $file) {
        $theme = $this->get_theme($id);
        return $theme['dir'] . '/' . $file;
    }

    function set_last_run($id, $time) {
        global $wpdb;
        $this->query($wpdb->prepare("update {$wpdb->prefix}newsletter_automated set last_run=%d where id=%d limit 1", $time, $id));
    }

    function add_to_last_run($id, $delta) {
        global $wpdb;
        $delta = (int) $delta;
        $this->query($wpdb->prepare("update {$wpdb->prefix}newsletter_automated set last_run=last_run+({$delta}) where id=%d limit 1", $id));
    }

    static function split_posts(&$posts, $time = 0) {
        if ($time < 0) {
            if (count($posts) > 1) {
                return array_chunk($posts, ceil(count($posts) / 2));
            } else if (count($posts) == 0) {
                return array(array(), array());
            } else {
                return array($posts, array());
            }
        }

        $result = array(array(), array());
        foreach ($posts as &$post) {
            if (self::is_post_old($post, $time))
                $result[1][] = $post;
            else
                $result[0][] = $post;
        }
        return $result;
    }

    static function is_post_old(&$post, $time = 0) {
        return self::m2t($post->post_date_gmt) <= $time;
    }

    static function m2t($s) {

        // TODO: use the wordpress function I don't remeber the name
        $s = explode(' ', $s);
        $d = explode('-', $s[0]);
        $t = explode(':', $s[1]);
        return gmmktime((int) $t[0], (int) $t[1], (int) $t[2], (int) $d[1], (int) $d[2], (int) $d[0]);
    }

    function get_channels() {
        global $wpdb;

        static $channels = null;

        if (is_array($channels))
            return $channels;

        $channels = $wpdb->get_results("select * from " . $wpdb->prefix . "newsletter_automated order by id");
        foreach ($channels as $channel) {
            $channel->data = json_decode($channel->data, true);
        }
        return $channels;
    }

    function get_channel($id) {
        global $wpdb;
        $channel = $wpdb->get_row($wpdb->prepare("select * from " . $wpdb->prefix . "newsletter_automated where id=%d limit 1", $id));
        if (!$channel) {
            return null;
        }
        
        $channel->data = json_decode($channel->data, true);
        if (!isset($channel->data['list'])) {
            $channel->data['list'] = '';
        }

        if ($channel->theme_type == self::THEME_TYPE_COMPOSER && !$channel->email_id) {
            $email = [];
            $email['type'] = 'automated_template';
            $email['editor'] = NewsletterEmails::EDITOR_COMPOSER;
            $email['message'] = $channel->theme;
            $email = NewsletterEmails::instance()->save_email($email);
            $wpdb->update($wpdb->prefix . "newsletter_automated", array('email_id' => $email->id), array('id' => $channel->id));
            $channel->email_id = $email->id;
        }

        return $channel;
    }

    function inline_css($content, $strip_style_blocks = false) {
        return NewsletterEmails::instance()->inline_css($content, $strip_style_blocks);
    }

    /**
     * @param array $filter
     * @param boolean $get_count
     *
     * @return string
     */
    function generate_subscribers_list_query($channel, $get_count = false) {

        $default_filter = [
            'list' => null,
            'languages' => array(),
            'status' => TNP_User::STATUS_CONFIRMED,
        ];

        $filter = array(
            'list' => (int) $channel->data['list'],
            'languages' => isset($channel->data['languages']) ? $channel->data['languages'] : array()
        );

        $filter = array_merge($default_filter, $filter);
        $status = $filter['status'];

        if (!$get_count) {
            $query = "SELECT * FROM ";
        } else {
            $query = "SELECT COUNT(*) FROM ";
        }

        $query .= NEWSLETTER_USERS_TABLE . " WHERE status='$status'";

        if (!empty($filter['list'])) {
            $list = $filter['list'];
            $query .= " AND list_$list=1";
        }

        if (!empty($filter['languages'])) {
            $query .= $this->generate_languages_in_clause($filter['languages']);
        }

        return $query;
    }

    /**
     * @param array $filter
     *
     * @return string
     */
    function generate_subscribers_list_count_query($channel) {
        return $this->generate_subscribers_list_query($channel, true);
    }

    /**
     * @param array $languages_array
     *
     * @return string
     */
    function generate_languages_in_clause($languages_array = array()) {
        if (!empty($languages_array)) {
            $languages = array_map(function ($lang) {
                $lang = esc_sql($lang);

                return "'$lang'";
            }, $languages_array);
            $in_clause = '(' . implode(',', $languages) . ')';

            return " and language IN $in_clause";
        }

        return '';
    }

    /**
     * Returns the number of subscribers targeted by the provided channel.
     * @global wpdb $wpdb
     * @param stdClass $channel
     * @return int
     */
    function get_subscriber_count($channel) {
        global $wpdb;
        $query = $this->generate_subscribers_list_count_query($channel);
        return $wpdb->get_var($query);
    }

}
