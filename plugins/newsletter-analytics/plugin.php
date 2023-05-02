<?php

class NewsletterAnalytics extends NewsletterAddon {

    static $instance;
    
    function __construct($version) {
        self::$instance = $this;
        parent::__construct('analytics', $version);
        $this->setup_options();
    }
    
    public function upgrade($first_install = false) {
        $this->setup_options();
        if (empty($this->options)) {
            $this->save_options(array('utm_source' => '', 'utm_campaign' => '', 'utm_mediun' => '', 'utm_term' => '', 'utm_content' => ''));
        }
    }

    function init() {
        parent::init();

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_settings', array($this, 'hook_newsletter_menu_settings'));
            }
            
            add_action('newsletter_emails_edit_other', array($this, 'hook_newsletter_emails_edit_other'), 10, 2);
        }
        
        add_filter('newsletter_redirect_url', array($this, 'hook_newsletter_redirect_url'), 10, 3);
    }

    function hook_newsletter_menu_settings($entries) {
        $entries[] = array('label' => '<i class="fas fa-chart-bar"></i> Google Analytics', 'url' => '?page=newsletter_analytics_index', 'description' => 'Add Google Analytics tracking');
        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Analytics', '<span class="tnp-side-menu">Google Analytics</span>', 'exist', 'newsletter_analytics_index', array($this, 'menu_page_index'));
    }

    function menu_page_index() {
        global $wpdb;
        require dirname(__FILE__) . '/index.php';
    }

    /**
     * Fired on "other" panel while editing a newsletter.
     * 
     * @param type $email
     * @param NewsletterControls $controls
     */
    function hook_newsletter_emails_edit_other($email, $controls) {
        // Fill in with defaults
        if (!isset($controls->data['options_utm_source'])) {
            $controls->data['options_utm_source'] = $this->options['utm_source'];
            $controls->data['options_utm_medium'] = $this->options['utm_medium'];
            $controls->data['options_utm_campaign'] = $this->options['utm_campaign'];
            $controls->data['options_utm_term'] = $this->options['utm_term'];
            $controls->data['options_utm_content'] = $this->options['utm_content'];
        }
        include __DIR__ . '/email-options.php';
    }

    function hook_newsletter_redirect_url($url, $email, $user) {
        $logger = $this->get_logger();
        $logger->debug('Processing ' . $url);


        // Already tracked check
        if (strpos($url, 'utm_source') !== false) {
            $logger->debug('Already tracked with GA');
            return $url;
        }

        if (empty($email->options)) {
            $logger->debug('No analytics setting in this email');
            return $url;
        }

        if (!is_array($email->options)) {
            $email->options = maybe_unserialize($email->options);
        }

        if (empty($email->options['utm_source'])) {
            $logger->debug('Source not set');
            return $url;
        }
        
        // Do not track newsletter actions (this check should be improved) but required for wp rocket compatibility
        if (strpos($url, '?na=')) {
            $logger->debug('Newsletter action');
            return $url;
        }

        // External domain check: add tracking only if enabled
        if (empty($this->options['external'])) {

            // Track only our domain (?)
            // Remove host name from the domain
            $parts = explode('.', $_SERVER['HTTP_HOST']);
            $parts = array_reverse($parts);
            $domain = $parts[1] . '.' . $parts[0];
            $logger->debug('Domain: ' . $domain);

            if (strpos($url, $domain) === false) {
                $logger->debug('External domain');
                return $url;
            }
        }

        $query = 'utm_source=' . urlencode($this->replace($email->options['utm_source'], $email, $user));

        if (!empty($email->options['utm_medium'])) {
            $query .= '&utm_medium=' . urlencode($this->replace($email->options['utm_medium'], $email, $user));
        }

        if (!empty($email->options['utm_campaign'])) {
            $query .= '&utm_campaign=' . urlencode($this->replace($email->options['utm_campaign'], $email, $user));
        }

        if (!empty($email->options['utm_term'])) {
            $query .= '&utm_term=' . urlencode($this->replace($email->options['utm_term'], $email, $user));
        }

        if (!empty($email->options['utm_content'])) {
            $query .= '&utm_content=' . urlencode($this->replace($email->options['utm_content'], $email, $user));
        }

        if (strpos($url, '?') !== false) {
            return $url . '&' . $query;
        } else {
            return $url . '?' . $query;
        }
    }

    function replace($text, $email, $user) {
        $text = str_replace('{email_id}', $email->id, $text);
        $text = str_replace('{email_subject}', urlencode($email->subject), $text);
        return $text;
    }

}

