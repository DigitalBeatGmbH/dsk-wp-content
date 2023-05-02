<?php

defined('ABSPATH') || exit;

class NewsletterGeo extends NewsletterAddon {

    static $instance;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('geo', $version);
    }

    //function upgrade($first_time = false) {
    //}

    function init() {

        if (is_admin()) {
            add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
            add_filter('newsletter_menu_subscribers', array($this, 'hook_newsletter_menu_subscribers'));
            add_action('newsletter_emails_edit_target', array($this, 'hook_newsletter_emails_edit_target'), 10, 2);
            add_action('newsletter_emails_email_query', array($this, 'hook_newsletter_emails_email_query'), 10, 2);
        }
        if (!defined('DOING_CRON') || !DOING_CRON) {
            if (wp_get_schedule('newsletter_geo_run') === false) {
                wp_schedule_event(time() + 60, 'newsletter', 'newsletter_geo_run');
            }
        }
        add_action('newsletter_geo_run', array($this, 'run'), 100);
    }

    function hook_newsletter_emails_edit_target($email, $controls) {
        global $wpdb;
        include __DIR__ . '/admin/email-options.php';
    }
    
    function hook_newsletter_menu_subscribers($entries) {
        $entries[] = ['label' => '<i class="fas fa-globe"></i> Geo Resolver', 'url' => '?page=newsletter_geo_index', 'description' => 'Geolocation of subscribers for a precise targeting'];
        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Geo Resolver', '<span class="tnp-side-menu">Geo Resolver</span>', 'manage_options', 'newsletter_geo_index', [$this, 'menu_page_index']);
    }

    function menu_page_index() {
        global $wpdb;
        $this->setup_options();
        require __DIR__ . '/admin/index.php';
    }    

    function hook_newsletter_emails_email_query($query, $email) {
        global $wpdb;
        if (!empty($email->options['countries'])) {
            $countries = array();
            foreach ($email->options['countries'] as $country) {
                $countries[] = "'" . esc_sql($country) . "'";
            }

            $countries = implode(',', $countries);

            $query .= " and country in (" . $countries . ")";
        }
        if (!empty($email->options['regions'])) {
            $regions = array();
            foreach ($email->options['regions'] as $region) {
                $regions[] = "'" . esc_sql($region) . "'";
            }

            $regions = implode(',', $regions);

            $query .= " and region in (" . $regions . ")";
        }
        if (!empty($email->options['cities'])) {
            $cities = array();
            foreach ($email->options['cities'] as $city) {
                $cities[] = "'" . esc_sql($city) . "'";
            }

            $cities = implode(',', $cities);

            $query .= " and city in (" . $cities . ")";
        }
        return $query;
    }

    var $country_result = '';

    function get_response_data($url) {
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return $response;
        } else if (wp_remote_retrieve_response_code($response) != 200) {
            return new WP_Error(wp_remote_retrieve_response_code($response), 'Error on connection to ' . $url . ' with error HTTP code ' . wp_remote_retrieve_response_code($response));
        }
        $data = json_decode(wp_remote_retrieve_body($response));
        if (!$data) {
            return new WP_Error(1, 'Unable to decode the JSON from ' . $url, $body);
        }
        return $data;
    }

    /**
     * 
     * @param string[] $ips
     * @return array
     */
    function resolve($ips) {
        if (empty($ips)) {
            return [];
        }
        $data = $this->get_response_data('http://geo.thenewsletterplugin.com/resolve.php?ip=' . urlencode(implode(',', $ips)));

        if (is_wp_error($data)) {
            return $data;
        } else {
            $result = [];
            foreach ($data as $ip => $values) {
                $result[$ip] = (array) $values;
            }
        }

        return $result;
    }

    /**
     * 
     * @global wpdb $wpdb
     * @param type $test
     * @return type
     */
    function run($test = false) {
        global $wpdb;
        $logger = $this->get_logger();

        $logger->info('Start');

        @set_time_limit(0);

        if (!$test) {
            $this->save_last_run(time());
        } else {
            $logger->debug('Test mode');
            $data = $this->resolve([$_SERVER['REMOTE_ADDR'], '8.8.8.8']);
            return $data;
        }

        $count = 0;
        for ($i = 0; $i < 5; $i++) {

            $list = $wpdb->get_results("select id, ip from " . NEWSLETTER_USERS_TABLE . " where geo=0 and ip<>'' limit 20");
            if (!empty($list)) {
                $this->country_result .= ' Processed ' . count($list) . ' subscribers.';
                $ips = [];
                foreach ($list as $r) {
                    $ips[] = $r->ip;
                }
                $result = $this->resolve($ips);

                if (is_wp_error($result)) {
                    $logger->error('Service unavailable');
                    $logger->error($result);
                    return $result;
                }

                //$logger->debug($result);

                foreach ($list as $r) {
                    //$logger->debug($r);
                    //$logger->debug('Resolving: ' . $r->ip);
                    $data = $result[$r->ip];

                    $count++;

                    if (is_wp_error($data)) {
                        $logger->fatal($data);
                        $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set geo=1, country='', region='', city='' where id=%d limit 1", $r->id));
                        //return $data;
                    } else {
                        if (!empty($data)) {
                            $rr = $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set geo=1, country=%s, region=%s, city=%s where id=%d limit 1", $data['country'], $data['region'], $data['city'], $r->id));
                            if (!empty($wpdb->last_error))
                                die($wpdb->last_error);
                        } else {
                            $wpdb->query($wpdb->prepare("update " . NEWSLETTER_USERS_TABLE . " set geo=1, country='XX', region='', city='' where id=%d limit 1", $r->id));
                            if (!empty($wpdb->last_error))
                                die($wpdb->last_error);
                        }
                    }
                }
                sleep(1);
            }
        }
        return $count;
    }

    function save_last_run($time) {
        update_option('newsletter_geo_last_run', $time);
    }

    function get_last_run() {
        return get_option('newsletter_geo_last_run', 0);
    }

}
