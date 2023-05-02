<?php

class NewsletterBounce extends NewsletterAddon {

    static $instance;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('bounce', $version);
        $this->setup_options();
    }

    function init() {

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_settings', array($this, 'hook_newsletter_menu_settings'));
            }
        }
        if (!defined('DOING_CRON') || !DOING_CRON) {
            if (wp_get_schedule('newsletter_bounce_run') === false) {
                wp_schedule_event(time() + 60, 'newsletter', 'newsletter_bounce_run');
            }
        }
        add_action('newsletter_bounce_run', array($this, 'run'), 100);
    }

    function hook_newsletter_menu_settings($entries) {
        $entries[] = array('label' => '<i class="fas fa-exclamation-triangle"></i> Bounce', 'url' => '?page=newsletter_bounce_index', 'description' => 'Manage the bounces');
        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Bounce', '<span class="tnp-side-menu">Bounces</span>', 'exist', 'newsletter_bounce_index', array($this, 'menu_page_index'));
    }

    function menu_page_index() {
        global $wpdb;
        require dirname(__FILE__) . '/index.php';
    }

    /**
     * 
     * @global wpdb $wpdb
     * @param bool $test
     * @return bool
     */
    function run($test = false, $max_messages = false) {
        global $wpdb;
        $logger = $this->get_logger();

        if (!$test) {
            $this->save_last_run(time());
        }
        
        if (empty($this->options['host'])) {
            return;
        }

        $logger->info('Start');
        if ($test) {
            $logger->debug('Test mode');
        }

        @set_time_limit(0);

        $stats = array('total_messages' => 0, 'processed_messages' => 0, 'emails' => array(), 'error' => '');

        require_once(ABSPATH . WPINC . '/class-pop3.php');
        $pop3 = new POP3('', 20);
        $host = $this->options['host'];
        if ($this->options['secure']) {
            $host = $this->options['secure'] . '://' . $host;
        }

        if (!$pop3->connect($host, $this->options['port']) || !$pop3->user($this->options['login'])) {
            $logger->fatal($pop3->ERROR);
            return new WP_Error('1', $pop3->ERROR);
        }

        $count = $pop3->pass($this->options['password']);

        if (false === $count) {
            $logger->fatal($pop3->ERROR);
            return new WP_Error('2', $pop3->ERROR);
        }
        $logger->debug('Found ' . $count . ' messages');

        $stats['total_messages'] = $count;

        if ($max_messages && $count > $max_messages) {
            $count = $max_messages;
        }

        if ($count > 100) {
            $count = 100;
        }

        for ($i = 1; $i <= $count; $i++) {

            $message = $pop3->get($i);

            $bodysignal = false;
            $headers = array();
            $body = array();
            $message_id = '';
            foreach ($message as $line) {
                
                $logger->debug($line);

                // An empty line marks the body start
                if (strlen($line) < 3) {
                    $bodysignal = true;
                }

                if (!$bodysignal) {
                    $headers[] = $line;

                    // Extract a possible message id for debug purposes
                    if (stripos($line, 'message-id:') === 0) {
                        $message_id = trim(substr($line, 11));
                    }
                    continue;
                } else {
                    $body[] = $line;
                    // Consider only the first X lines
                    if (count($body) > 1500) {
                        break;
                    }
                }
            }

            // Here we have all the info we need to parse the message

            $dsn = $this->extract_dsn($body);
            
            $logger->debug($dsn);

            if (isset($dsn['email'])) {
                // When returned by spam it is not abounce even with error code 5.X.X
                if (stripos($dsn['diagnostic-code'], 'spam') === false) {
                    // TODO: Improve
                    if (!$test && ($dsn['type'] == 'permanent' || (!empty($this->options['transient']) && $dsn['type'] == 'transient'))) {
                        $res = @$wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "newsletter set status='B' where lower(email)=%s", $dsn['email']));
                        if (defined('NEWSLETTER_VERSION') && NEWSLETTER_VERSION > '4.9.0') {
                            $res = @$wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "newsletter set bounce_time=%d, bounce_type=%s where lower(email)=%s", time(), $dsn['type'], $dsn['email']));
                        }
                    }
                    $stats['emails'][] = $dsn['email'] . ' (' . $dsn['type'] . ')';
                }
            }

            $stats['processed_messages'] ++;

            if (!$test) {
                $delete_result = $pop3->delete($i);

                if (!$delete_result) {
                    $error = new WP_Error('3', $pop3->ERROR);
                    $logger->fatal('Quitting for error: ' . $pop3->ERROR);
                    $pop3->quit();
                    return $error;
                }
            }
        }

        $pop3->quit();
        $logger->debug('End');
        return $stats;
    }

    /**
     * Extracts the delivery status notification fields we are interested in to detect a
     * bounce.
     * 
     * @param array $body
     * @return array
     */
    function extract_dsn(array $body) {
        $dsn = array('type' => '');
        $found = false;
        foreach ($body as $line) {

            $line = trim($line);

            // Empty line after the block of DSN data means we aprsed everything
            if ($found && empty($line))
                break;

            if (strpos($line, ':') === false)
                continue;

            list($key, $value) = explode(':', $line, 2);
            $key = trim(strtolower($key));
            switch ($key) {
                case 'status':
                    $found = true;
                    $dsn['status'] = trim($value);
                    $major = substr($dsn['status'], 0, 1);
                    switch ($major) {
                        case '5':
                            $dsn['type'] = 'permanent';
                            break;
                        case 4:
                            $dsn['type'] = 'transient';
                            break;
                        default:
                            $dsn['type'] = '';
                    }
                    break;
                case 'final-recipient':
                    $found = true;
                    $dsn['final-recipient'] = $value;
                    list($type, $email) = explode(';', $value, 2);
                    $email = $this->extract_email($email);
                    $dsn['email'] = $email;
                    break;
                case 'diagnostic-code':
                    $found = true;
                    $dsn['diagnostic-code'] = $value;
                    break;
            }

//            if (isset($dsn['final-recipient']) && isset($dsn['status'])) {
//                break;
//            }
        }

        return $dsn;
    }

    /**
     * Extracts an email address from a DSN field, like the final-recipient
     * 
     * @param string $email
     * @return string
     */
    function extract_email($email) {
        $email = preg_replace('/[^a-zA-Z0-9@+.\\-_]/', '', $email);
        $email = strtolower(trim($email, "\n\r\t .:;\0\x0B"));
        if (!is_email($email)) {
            return false;
        }
        return $email;
    }

    function save_last_run($time) {
        update_option('newsletter_bounce_last_run', $time);
    }

    function get_last_run() {
        return get_option('newsletter_bounce_last_run', 0);
    }

}
