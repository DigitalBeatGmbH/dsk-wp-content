<?php

class TNP_Report {

    var $email_id;
    var $total = 0;
    var $open_count = 0;
    var $open_rate = 0;
    var $open_rank = 0;
    var $click_count = 0;
    var $click_rate = 0;
    var $click_rank = 0;
    var $reactivity = 0;
    var $reactivity_rank = 0;
    var $error_count = 0;
    var $error_rate = 0;
    var $unsub_count = 0;
    var $unsub_rate = 0;

    /**
     * Recomputes the rates using the absolute values already set.
     */
    function update($benchmark = null) {
        if ($this->total > 0) {
            $this->open_rate = round($this->open_count / $this->total * 100, 2);
            $this->click_rate = round($this->click_count / $this->total * 100, 2);
            $this->error_rate = round($this->error_count / $this->total * 100, 2);
            if ($this->open_count > 0) {
                $this->reactivity = round($this->click_count / $this->open_count * 100, 2);
            }
        } else {
            $this->open_rate = 0;
            $this->click_rate = 0;
            $this->error_rate = 0;
            $this->reactivity = 0;
        }

        if (!is_null($benchmark)) {
            $this->update_ranks($benchmark);
        }
    }

    /**
     * Updates the internal ranks variables.
     * @param TNP_Benchmark $benchmark Description
     */
    function update_ranks($benchmark) {

        // Opens
        $this->open_rank = 0;
        if ($this->open_rate > $benchmark->open_rate * 0.8) {
            $this->open_rank = 1;
        }
        if ($this->open_rate > $benchmark->open_rate * 1.2) {
            $this->open_rank = 2;
        }
        if ($this->open_rate > $benchmark->open_rate * 2) {
            $this->open_rank = 3;
        }

        // Clicks
        $this->click_rank = 0;
        if ($this->click_rate > $benchmark->click_rate * 0.8) {
            $this->click_rank = 1;
        }
        if ($this->click_rate > $benchmark->click_rate * 1.2) {
            $this->click_rank = 2;
        }
        if ($this->click_rate > $benchmark->click_rate * 2) {
            $this->click_rank = 3;
        }

        // Reactivity
        $this->reactivity_rank = 0;
        if ($this->reactivity > $benchmark->reactivity * 0.8) {
            $this->reactivity_rank = 1;
        }
        if ($this->reactivity > $benchmark->reactivity * 1.2) {
            $this->reactivity_rank = 2;
        }
        if ($this->reactivity > $benchmark->reactivity * 2) {
            $this->reactivity_rank = 3;
        }
    }

}

class TNP_Benchmark {

    var $open_rate = 15;
    var $click_rate = 2.5;
    var $reactivity = 2.5 / 15 * 100;

}

class NewsletterReports extends NewsletterAddon {

    /**
     * @return NewsletterReports
     */
    static $instance;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('reports', $version);
    }

    function init() {

        if (is_admin()) {


            add_action('admin_menu', array($this, 'hook_admin_menu'), 100);

            add_filter('newsletter_statistics_view', function ($slug) {
                return 'newsletter_reports_view';
            });

            add_filter('newsletter_statistics_index', function ($slug) {
                return 'newsletter_reports_index';
            });

            add_action('newsletter_users_edit_newsletters', function ($user_id) {
                include __DIR__ . '/users/edit-newsletters.php';
            });

            add_action('newsletter_users_edit_general', function ($id, $controls) {
                include __DIR__ . '/users/edit-general.php';
            }, 10, 2);

            add_action('newsletter_users_statistics_countries', array($this, 'hook_newsletter_users_statistics_countries'));
            add_action('newsletter_users_statistics_time', array($this, 'hook_newsletter_users_statistics_time'));

            add_action('wp_ajax_newsletter_reports_export', array($this, 'hook_wp_ajax_newsletter_reports_export'));
            add_action('wp_ajax_newsletter_reports_urls', array($this, 'hook_wp_ajax_newsletter_reports_urls'));
            add_action('wp_ajax_newsletter_reports_user_count', array($this, 'hook_wp_ajax_newsletter_reports_user_count'));

            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', [$this, 'hook_admin_menu'], 100);
            }

            add_action('newsletter_emails_edit_target', array($this, 'hook_newsletter_emails_edit_target'), 10, 2);
            add_action('newsletter_emails_email_query', array($this, 'hook_newsletter_emails_email_query'), 10, 2);
        }
    }

    function hook_newsletter_emails_edit_target($email, $controls) {
        include __DIR__ . '/emails/edit.php';
    }

    function hook_newsletter_emails_email_query($query, $email) {
        if (!empty($email->options['date_year']) && !empty($email->options['date_month']) && !empty($email->options['date_day'])) {

            $year = (int) $email->options['date_year'];
            $month = (int) $email->options['date_month'];
            $day = (int) $email->options['date_day'];

            $query .= " and created>'{$year}-{$month}-{$day}'";
        }

        return $query;
    }

    function hook_wp_ajax_newsletter_reports_urls() {
        if (!NEWSLETTER_DEBUG && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
            if (!check_admin_referer('newsletter-reports-urls')) {
                die('Expired');
            }
        }

        include __DIR__ . '/admin/index-urls.php';
        die();
    }

    function hook_wp_ajax_newsletter_reports_user_count() {
        global $wpdb;
        if (!check_admin_referer('newsletter-reports-user-count')) {
            die('Expired');
        }
        $email_ids = array_map('intval', explode(',', $_GET['email_ids']));
        $res = $wpdb->get_var("select count(distinct user_id) from " . NEWSLETTER_SENT_TABLE . " where email_id in (" . implode(',', $email_ids) . ")");
        echo $res;
        die();
    }

    function hook_wp_ajax_newsletter_reports_export() {
        global $wpdb;

        $email_id = (int) $_GET['email_id'];

        header('Content-Type: application/octect-stream;charset=UTF-8');
        header('Content-Disposition: attachment; filename=newsletter-' . $email_id . '.csv');

        echo '"Subscriber ID";"Email";"Name";"Surname";"Sex";"Open";"URL"';
        echo "\n";

        $page = 0;
        while (true) {
            $users = $wpdb->get_results($wpdb->prepare("select distinct u.id, u.email, u.name, u.surname, u.sex, t.open as sent_open, s.url from " . NEWSLETTER_USERS_TABLE . " u
    join " . NEWSLETTER_SENT_TABLE . " t on t.user_id=u.id and t.email_id=%d
        left join " . NEWSLETTER_STATS_TABLE . " s on u.id=s.user_id and s.email_id=%d
        order by u.id limit " . $page * 500 . ",500", $email_id, $email_id));

            if (!empty($wpdb->last_error)) {
                die($wpdb->last_error);
            }

            for ($i = 0; $i < count($users); $i++) {
                echo '"' . $users[$i]->id;
                echo '";"';
                echo $users[$i]->email;
                echo '";"';
                echo $this->sanitize_csv($users[$i]->name);
                echo '";"';
                echo $this->sanitize_csv($users[$i]->surname);
                echo '";"';
                echo $users[$i]->sex;
                echo '";"';
                echo $users[$i]->sent_open == 0 ? '0' : '1';
                echo '";"';
                echo $this->sanitize_csv($users[$i]->url);
                echo '"';
                echo "\n";
                flush();
            }
            if (count($users) < 500) {
                break;
            }
            $page++;
        }
        die('');
    }

    function get_email_send_mode($type) {
        return apply_filters('newsletter_email_send_mode', 'standard', $type);
    }

    /**
     * Return an associative array with email type as in the database as key and the display type name as value
     * 
     */
    function get_email_types() {
        global $wpdb;
        $types = $wpdb->get_results("select distinct type from " . NEWSLETTER_EMAILS_TABLE);
        $options = array('message' => 'Standard Newsletter');
        foreach ($types as $type) {
            if ($type->type == 'followup') {
                continue;
            }

            if ($type->type == 'message') {
                continue;
            }

            if ($type->type == 'feed') {
                $options['feed'] = 'Feed by Mail (legacy)';
                continue;
            }

            $name = apply_filters('newsletter_email_type', $type->type, $type->type);

            if ($name !== $type->type) {
                $options[$type->type] = $name;
            } else if (strpos($type->type, 'automated') === 0) {
                list($a, $id) = explode('_', $type->type);
                $options[$type->type] = 'Automated Channel ' . $id;
            } else if (strpos($type->type, 'autoresponder') === 0) {
                list($a, $id) = explode('_', $type->type);
                $options[$type->type] = 'Autoresponder ' . $id;
            } else {
                $options[$type->type] = $type->type;
            }
        }

        return $options;
    }

    function sanitize_csv($text) {
        $text = str_replace('"', "'", $text);
        $text = str_replace("\n", ' ', $text);
        $text = str_replace("\r", ' ', $text);
        $text = str_replace(";", ' ', $text);
        return $text;
    }

    function hook_newsletter_users_statistics_countries() {
        global $wpdb;
        include __DIR__ . '/users/statistics-countries.php';
    }

    function hook_newsletter_users_statistics_time() {
        global $wpdb;
        include __DIR__ . '/users/statistics-time.php';
    }

    function hook_admin_menu() {
        $newsletter = Newsletter::instance();
        if ($newsletter->is_allowed()) {
            add_submenu_page(null, 'Report', 'Report', 'exist', 'newsletter_reports_view', array($this, 'hook_newsletter_reports_view'));
            add_submenu_page(null, 'Users', 'Users', 'exist', 'newsletter_reports_view_users', array($this, 'hook_newsletter_reports_view_users'));
            add_submenu_page(null, 'Retarget', 'Retarget', 'exist', 'newsletter_reports_view_retarget', array($this, 'hook_newsletter_reports_view_retarget'));
            add_submenu_page('newsletter_main_index', 'Reports', '<span class="tnp-side-menu">Reports</span>', 'exist', 'newsletter_reports_index', function () {
                include __DIR__ . '/admin/index.php';
            });
        }
    }

    function hook_newsletter_reports_view() {
        global $wpdb, $newsletter;
        require __DIR__ . '/admin/view.php';
    }

    function hook_newsletter_reports_view_users() {
        global $wpdb, $newsletter;
        require __DIR__ . '/admin/view-users.php';
    }

    function hook_newsletter_reports_view_retarget() {
        global $wpdb, $newsletter;
        require __DIR__ . '/admin/view-retarget.php';
    }

    /**
     * Returns an object with statistics values
     * 
     * @global type $wpdb
     * @param int $email_id
     * @return TNP_Report
     */
    function get_statistics($email) {
        global $wpdb;

        $report = new TNP_Report();
        $report->email_id = $email->id;

        if (!$email->stats_time) {

            $data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) as total, 
            count(case when status>0 then 1 else null end) as `errors`,
            count(case when open>0 then 1 else null end) as `opens`,
            count(case when open>1 then 1 else null end) as `clicks`
            from " . NEWSLETTER_SENT_TABLE . " where email_id=%d", $email->id));

            $unsub_count = $wpdb->get_var($wpdb->prepare("select count(*) from " . NEWSLETTER_USERS_TABLE . " where unsub_email_id=%d", $email->id));

            $report->total = $data->total;
            $report->unsub_count = $unsub_count;
            $report->open_count = $data->opens;
            $report->click_count = $data->clicks;
            $report->error_count = $data->errors;

            $wpdb->update(NEWSLETTER_EMAILS_TABLE, [
                'sent' => $report->total,
                'error_count' => $report->error_count,
                'unsub_count' => $report->unsub_count,
                'open_count' => $report->open_count,
                'click_count' => $report->click_count,
                'stats_time' => time()
                    ], ['id' => $email->id]);
        } else {
            $report->total = $email->sent;
            $report->open_count = $email->open_count;
            $report->click_count = $email->click_count;
            $report->error_count = $email->error_count;
            $report->unsub_count = $email->unsub_count;
        }

        $report->update($this->get_benchmark());

        return $report;
    }

    function get_benchmark($sector = false) {
        $benchmark = new TNP_Benchmark();
        return $benchmark;
    }

    function get_countries($email_type = '', $days = 180) {
        global $wpdb;
        // Denormalize the country
        //$last_time = (int) get_option('newsletter_reports_country_time', 0);
        //update_option('newsletter_reports_country_time', time(), false);
        //$wpdb->query("update " . NEWSLETTER_SENT_TABLE . " s join " . NEWSLETTER_USERS_TABLE . " u
        //    on s.user_id=u.id set s.country=coalesce(u.country, '') where s.time>$last_time");

        $start_time = time() - $days * 24 * 3600;
//        $data = $wpdb->get_results("select lower(u.country) as country, count(*) as total,
//            count(case when open>0 then 1 else null end) as `opens`,
//            count(case when open>1 then 1 else null end) as `clicks`
//            from " . NEWSLETTER_SENT_TABLE . " s join " . NEWSLETTER_USERS_TABLE . " u
//            on s.user_id=u.id
//            where u.country<>'' and u.country<>'XX' and time > $start_time
//            group by u.country");
        if (empty($email_type)) {
            $data = $wpdb->get_results("select lower(u.country) as country, count(*) as total,
            count(case when open>0 then 1 else null end) as `opens`,
            count(case when open>1 then 1 else null end) as `clicks`
            from " . NEWSLETTER_SENT_TABLE . " s join " . NEWSLETTER_USERS_TABLE . " u
            on s.user_id=u.id
            where u.country<>'' and u.country<>'XX' and time > $start_time
            group by u.country");
        } else {
            $data = $wpdb->get_results($wpdb->prepare("select lower(u.country) as country, count(*) as total,
            count(case when open>0 then 1 else null end) as `opens`,
            count(case when open>1 then 1 else null end) as `clicks`
            from " . NEWSLETTER_SENT_TABLE . " s join " . NEWSLETTER_USERS_TABLE . " u
            on s.user_id=u.id join " . NEWSLETTER_EMAILS_TABLE . " e 
            on s.email_id=e.id
            where u.country<>'' and u.country<>'XX' and s.time>%d and e.type=%s
            group by u.country", $start_time, $email_type));
        }

        foreach ($data as $item) {
            $item->open_rate = 0;
            $item->click_rate = 0;
            if ($item->total > 0) {
                $item->open_rate = round($item->opens / $item->total * 100, 2);
                $item->click_rate = round($item->clicks / $item->total * 100, 2);
            }
            $item->reactivity = 0;
            if ($item->opens > 0) {
                $item->reactivity = round($item->clicks / $item->opens * 100, 1);
            }
        }
        return $data;
    }

    function list_to_map($list, $key_field, $value_field) {
        $map = [];
        foreach ($list as $item) {
            $map[$item->$key_field] = $item->$value_field;
        }
        return $map;
    }

    /**
     * Returns a paginated list of subscribers' data (limited) with specific condition (status, clicked, opened, ...)
     * 
     * @global wpdb $wpdb
     * @param array $args
     * @return array
     */
    function get_subscribers($args = array()) {

        global $wpdb;

        $query = "SELECT u.id, u.email, u.name, u.surname, u.status, s.status as sent_status, s.open as sent_open, s.error, s.email_id FROM " . NEWSLETTER_SENT_TABLE . " s JOIN " .
                NEWSLETTER_USERS_TABLE . " u on s.user_id=u.id and s.email_id=%d";

        $query_args = [$args['email_id']];

        if (isset($args['status'])) {
            if ($args['status'] == 'error') {
                $query .= " AND s.status=1";
            } else if ($args['status'] == 'success') {
                $query .= " AND s.status=0";
            } else if ($args['status'] == 'open') {
                $query .= " AND s.open=1";
            } else if ($args['status'] == 'click') {
                $query .= " AND s.open=2";
            } else if ($args['status'] == 'unsubscribed') {
                $query .= " AND u.status='U' and u.unsub_email_id=%d";
                $query_args[] = $args['email_id'];
            }
        }

        if (isset($args['items_per_page'])) {
            $query .= " LIMIT " . ( $args['page'] * $args['items_per_page'] ) . "," . $args['items_per_page'];
        }

        $list = $wpdb->get_results($wpdb->prepare($query, $query_args));

        return $list;
    }

    /**
     * Computes the subscriber count for a specific newsletter with a specific condition (status, ...).
     * 
     * @global wpdb $wpdb
     * @param array $args
     * @return int
     */
    function get_subscriber_count($args = array()) {

        global $wpdb;

        $query = "SELECT COUNT(*) as count FROM " . NEWSLETTER_SENT_TABLE . " s JOIN " . NEWSLETTER_USERS_TABLE . " u on s.user_id=u.id AND s.email_id=%d";

        if (isset($args['status'])) {
            if ($args['status'] == 'error') {
                $query .= " AND s.status=1";
            } else if ($args['status'] == 'success') {
                $query .= " AND s.status=0";
            } else if ($args['status'] == 'open') {
                $query .= " AND s.open=1";
            } else if ($args['status'] == 'click') {
                $query .= " AND s.open=2";
            }
        }

        return (int) $wpdb->get_var($wpdb->prepare($query, $args['email_id']));
    }
    
    function get_open_events($email_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("select date(created) as event_day, count(distinct user_id) as events_count
            from " . NEWSLETTER_STATS_TABLE . 
            " where email_id=%d
            group by event_day
            order by event_day", $email_id));
    }

}
