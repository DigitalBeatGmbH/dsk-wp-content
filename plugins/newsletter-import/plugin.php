<?php

class NewsletterImport extends NewsletterAddon {

    /**
     * @var NewsletterImport
     */
    static $instance;

    public function __construct($version = '0.0.0') {
        self::$instance = $this;
        parent::__construct('import', $version);
        $this->setup_options();
        add_action('newsletter_import_run', array($this, 'hook_newsletter_import_run'));
    }
    
    function upgrade($first_install = false) {
        parent::upgrade($first_install);
        $this->stop();
    }

    function init() {
        parent::init();

        if ($this->is_allowed()) {
            add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
            add_filter('newsletter_menu_subscribers', array($this, 'hook_newsletter_menu_subscribers'));
        }

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    add_action('wp_ajax_newsletter_import', [$this, 'hook_wp_ajax_newsletter_import']);
                }
            }
        }
    }

    function hook_newsletter_menu_subscribers($entries) {
        $entries[] = array('label' => '<i class="fas fa-database"></i> Advanced Import', 'url' => '?page=newsletter_import_index', 'description' => 'Import subscribers');
        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Advanced Import', '<span class="tnp-side-menu">Advanced Import</span>', 'exist', 'newsletter_import_index', function () {
            require __DIR__ . '/index.php';
        });
        add_submenu_page(null, 'CSV import', 'CSV import', 'exist', 'newsletter_import_csv', function () {
            require __DIR__ . '/csv.php';
        });
        add_submenu_page(null, 'Copy&Paste import', 'Copy&Paste import', 'exist', 'newsletter_import_clipboard', function () {
            require __DIR__ . '/clipboard.php';
        });
        add_submenu_page(null, 'Bounce import', 'Bounce import', 'exist', 'newsletter_import_bounce', function () {
            require __DIR__ . '/bounce.php';
        });
    }

    function hook_wp_ajax_newsletter_import() {
        $this->hook_newsletter_import_run(10);
        $data = [];
        $data['completed'] = !$this->is_importing();
        ob_start();
        include __DIR__ . '/progress.php';
        $data['html'] = ob_get_clean();
        $data['progress'] = $this->get_progress();
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
    }

    function hook_newsletter_import_run($max_run_time = 30) {
        $logger = $this->get_logger();

        if (get_transient('newsletter_import')) {
            $logger->error('Import job already running');
            return;
        }

        @setlocale(LC_CTYPE, 'en_US.UTF-8');
        @set_time_limit(0);
        set_transient('newsletter_import', time(), $max_run_time + 10);
        //var_dump($logger);
        //die('Siamo qui!');
        $logger->info('Starting');

        // It could have been removed
        $position = $this->get_position();
        if ($position === -1) {
            $logger->info('Job stopped');
            $this->stop();
            return;
        }

        $position = (int) $position;

        $options = $this->options;

        $handle = fopen($this->get_filename(), 'r');
        if (!$handle) {
            $logger->fatal('File disappeared...');
            $this->stop();

            return;
        }

        $stats = get_option('newsletter_import_stats');

        $logger->info('Starting from ' . $position);
        fseek($handle, $position);

        // Skips the first line
        if ($position === 0) {
            $line = fgets($handle);
            $this->log('Line 1 - Column names: ' . trim($line));
        }

        $mode = $options['mode'];
        $logger->debug('Mode ' . $mode);

        $newsletter = Newsletter::instance();

        $email_idx = (int) $options['email'] - 1;
        $first_name_idx = (int) $options['first_name'] - 1;
        $last_name_idx = (int) $options['last_name'] - 1;
        $gender_idx = (int) $options['gender'] - 1;
        $country_idx = (int) $options['country'] - 1;
        $region_idx = (int) $options['region'] - 1;
        $ip_idx = (int) $options['ip'] - 1;
        $language_idx = (int) $options['language'] - 1;
        $city_idx = (int) $options['city'] - 1;

        $start = time();
        while (($line = fgets($handle)) !== false && time() < $start + $max_run_time) {
            $this->set_position(ftell($handle));

            $line = trim($line);

            $logger->debug('Processing ' . $line);

            $stats['total']++;

            if (empty($line)) {
                $stats['empty']++;
                update_option('newsletter_import_stats', $stats);
                continue;
            }

            if (!mb_check_encoding($line, "UTF-8")) {
                $this->log('Line ' . ($stats['total'] + 1) . ' - Wrong UTF-8 enconding, check if the file has been saved as UTF-8');
                $logger->fatal('Line ' . $line . ' is not UTF-8 encoded');
                $stats['errors']++;
                update_option('newsletter_import_stats', $stats);
                continue;
            }

            // For fatal logging...
            $text_line = $line;

            //$line = preg_split('/[,;\t]/', $line);
            //$line = array_map('trim', $line);

            $line = str_getcsv($line, $options['delimiter'], '"');
            $email = $newsletter->normalize_email($line[$email_idx]);
            $logger->info('Current email: ' . $email);
            if (!$email) {
                $this->log('Line ' . ($stats['total'] + 1) . ' - Invalid email address');
                $logger->fatal('Invalid email on line ' . $text_line);
                $stats['errors']++;
                update_option('newsletter_import_stats', $stats);
                continue;
            }

            // Get subscriber

            $subscriber = $newsletter->get_user($email, ARRAY_A);
            if ($subscriber == null) {
                $logger->info('New subscriber');
                $subscriber = array();
                $subscriber['email'] = $email;

                if ($first_name_idx > -1 && isset($line[$first_name_idx])) {
                    $subscriber['name'] = mb_strimwidth($newsletter->normalize_name($line[$first_name_idx]), 0, 100);
                }

                if ($last_name_idx > -1 && isset($line[$last_name_idx])) {
                    $subscriber['surname'] = mb_strimwidth($newsletter->normalize_name($line[$last_name_idx]), 0, 100);
                }

                if ($country_idx > -1 && isset($line[$country_idx])) {
                    $country = strtoupper(trim($line[$country_idx]));

                    if (strlen($country) > 2) {
                        $this->log('Line ' . ($stats['total'] + 1) . ' - Invalid country code 2 digit ISO format: ' . $country);
                        $logger->fatal('Country "' . $line[$country_idx] . '" is not 2 digit ISO format on line ' . $text_line);
                        $stats['errors']++;
                        update_option('newsletter_import_stats', $stats);
                        continue;
                    }
                    $subscriber['country'] = $country;
                }

                if ($region_idx > -1 && isset($line[$region_idx])) {
                    $subscriber['region'] = mb_strimwidth(trim($line[$region_idx]), 0, 100);
                }

                if ($ip_idx > -1 && isset($line[$ip_idx])) {
                    $subscriber['ip'] = trim($line[$ip_idx]);
                }

                if ($language_idx > -1 && isset($line[$language_idx])) {
                    $subscriber['language'] = mb_strimwidth(strtolower(trim($line[$language_idx])), 0, 2);
                }

                if ($gender_idx > -1 && isset($line[$gender_idx])) {
                    $gender = strtolower(trim($line[$gender_idx]));
                    if (!empty($gender)) {
                        $gender = substr($gender, 0, 1);
                        if ($gender != 'f' && $gender != 'm' && $gender != 'n') {
                            $this->log('Line ' . ($stats['total'] + 1) . ' - Invalid gender (not one of M, F, N): ' . $gender);
                            $logger->fatal('Gender is invalid on line ' . $text_line);
                            $stats['errors']++;
                            update_option('newsletter_import_stats', $stats);
                            continue;
                        }
                        $subscriber['sex'] = $gender;
                    }
                }

                if ($city_idx > -1 && isset($line[$city_idx])) {
                    $subscriber['city'] = trim($line[$city_idx]);
                }

                $subscriber['status'] = $options['import_as'];

                foreach ($options['lists'] as $i) {
                    $subscriber['list_' . $i] = '1';
                }

                $profiles = $newsletter->get_profiles();
                foreach ($profiles as $profile) {
                    $profile_idx = (int) $options['profile_' . $profile->id] - 1;
                    if ($profile_idx > -1 && isset($line[$profile_idx])) {
                        $subscriber['profile_' . $profile->id] = mb_strimwidth(trim($line[$profile_idx]), 0, 250);
                    }
                }

                $newsletter->save_user($subscriber);
                $stats['new']++;
                //$logger->debug($subscriber);
            } else {
                $logger->info('Matched subscriber ID ' . $subscriber['id']);
                if ($mode == 'skip') {
                    $logger->info('Skipped');
                    $stats['skipped']++;
                    update_option('newsletter_import_stats', $stats);
                    continue;
                }

                if ($mode == 'overwrite') {
                    // Clean up the subscriber
                    $subscriber['name'] = '';
                    $subscriber['surname'] = '';
                    $subscriber['sex'] = 'n';
                    for ($i = 1; $i <= NEWSLETTER_PROFILE_MAX; $i++) {
                        $subscriber['profile_' . $i] = '';
                    }
                    for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {
                        $subscriber['list_' . $i] = 0;
                    }
                    $subscriber['country'] = '';
                    $subscriber['region'] = '';
                    $subscriber['city'] = '';
                }

                if ($language_idx > -1 && isset($line[$language_idx])) {
                    $subscriber['language'] = strtolower(trim($line[$language_idx]));
                }

                if ($first_name_idx > -1) {
                    $subscriber['name'] = mb_strimwidth($newsletter->normalize_name($line[$first_name_idx]), 0, 100);
                }
                if ($last_name_idx > -1) {
                    $subscriber['surname'] = mb_strimwidth($newsletter->normalize_name($line[$last_name_idx]), 0, 100);
                }
                if ($gender_idx > -1) {
                    $gender = strtolower($line[$gender_idx]);
                    if (!empty($gender)) {
                        $gender = substr($gender, 0, 1);
                        if ($gender != 'f' && $gender != 'm' && $gender != 'n') {
                            $this->log('Line ' . ($stats['total'] + 1) . ' - Invalid genderr (not one of M, F, N): ' . $gender);
                            $logger->fatal('Gender is invalid on line ' . $text_line);
                            $stats['errors']++;
                            update_option('newsletter_import_stats', $stats);
                            continue;
                        }
                        $subscriber['sex'] = $gender;
                    }
                }
                if (isset($options['override_status'])) {
                    $subscriber['status'] = $options['import_as'];
                }
                if ($country_idx > -1 && isset($line[$country_idx])) {
                    $country = strtoupper(trim($line[$country_idx]));

                    if (strlen($country) > 2) {
                        $this->log('Line ' . ($stats['total'] + 1) . ' - Invalid country code 2 digit ISO format: ' . $country);
                        $logger->fatal('Country "' . $line[$country_idx] . '" is not 2 digit ISO format on line ' . $text_line);
                        $stats['errors']++;
                        update_option('newsletter_import_stats', $stats);
                        continue;
                    }
                    $subscriber['country'] = $country;
                }
                if ($region_idx > -1 && isset($line[$region_idx])) {
                    $subscriber['region'] = mb_strimwidth(trim($line[$region_idx]), 0, 100);
                }
                if ($city_idx > -1 && isset($line[$city_idx])) {
                    $subscriber['city'] = mb_strimwidth(trim($line[$city_idx]), 0, 100);
                }


                foreach ($options['lists'] as $i) {
                    $subscriber['list_' . $i] = 1;
                }

                $profiles = $newsletter->get_profiles();
                foreach ($profiles as $profile) {
                    $profile_idx = (int) $options['profile_' . $profile->id] - 1;
                    if ($profile_idx > -1 && isset($line[$profile_idx])) {
                        $subscriber['profile_' . $profile->id] = mb_strimwidth(trim($line[$profile_idx]), 0, 250);
                    }
                }

                //$logger->debug($subscriber);
                $newsletter->save_user($subscriber);
                $stats['updated']++;
            }
            update_option('newsletter_import_stats', $stats);

            // TODO: Remove is for test
            //break;
        }
        fclose($handle);
        if ($line === false) {
            $logger->info('Finished');
            $this->stop();
            return true;
        } else {
            $logger->info('To be continued');
            //wp_schedule_single_event(time()+5, 'newsletter_import_run');
        }
        delete_transient('newsletter_import');
        return false;
    }

    function stop() {
        $this->delete_file();
        delete_transient('newsletter_import');
        update_option('newsletter_import_position', -1, false);
        //$p = $this->get_position();
        //$this->get_logger()->debug('Position: ' . var_export($p, true));
    }

    function start() {
        $this->set_position(0);
        update_option('newsletter_import_stats', array('total' => 0, 'errors' => 0, 'new' => 0, 'updated' => 0, 'skipped' => 0, 'empty' => 0), false);
        @unlink(NEWSLETTER_LOG_DIR . '/import-report.txt');
    }

    function log($line) {
        file_put_contents(NEWSLETTER_LOG_DIR . '/import-report.txt', $line . "\n", FILE_APPEND);
    }

    function is_importing() {
        return $this->has_file() && $this->get_position() != -1;
    }

    function get_position() {
        return (int)get_option('newsletter_import_position', -1);
    }

    function set_position($position) {
        update_option('newsletter_import_position', $position, false);
    }

    function has_file() {
        return file_exists($this->get_filename());
    }

    function get_filename() {
        return $this->get_dir() . '/import.csv';
    }

    function get_dir() {
        $dir = wp_upload_dir();
        return $dir['basedir'] . '/newsletter/import';
    }

    /**
     * 
     * @return \WP_Error|boolean
     */
    function prepare_dir() {
        $dir = $this->get_dir();
        $r = wp_mkdir_p($dir);
        if (!$r || !is_writable($dir) || !is_readable($dir)) {
            return new WP_Error(1, 'The folder "' . esc_html($dir) . '" cannot be created or is not writable/readable. Ask for support to your provider.');
        }

        $file = $this->get_filename();

        if (file_exists($file)) {
            $r = unlink($this->get_filename());
            if (!$r) {
                return new WP_Error(2, 'The old import file "' . esc_html($file) . '" cannot be deleted. Ask for support to your provider.');
            }
        }

        file_put_contents($dir . '/.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder deny,allow\nDeny from all\n</IfModule>");
        //return new WP_Error(1, 'Test error condition');
        return true;
    }

    function get_progress() {
        $position = get_option('newsletter_import_position');
        $total = filesize($this->get_filename());
        if (!$total)
            return 100;
        return (int) ($position * 100 / $total);
    }

    function delete_file() {
        $fn = $this->get_filename();
        if (file_exists($fn)) {
            return unlink($fn);
        }
    }

}
