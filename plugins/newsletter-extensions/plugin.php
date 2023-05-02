<?php


defined('ABSPATH') || exit;

class NewsletterExtensions extends NewsletterAddon
{

    /**
     * @var NewsletterExtensions
     */
    static $instance;
    var $prefix = 'newsletter_extensions';
    var $slug = 'newsletter-extensions';
    var $plugin = 'newsletter-extensions/extensions.php';
    var $id = 85;
    static $required_nl_version = "6.3.5";

    function __construct($version)
    {
        self::$instance = $this;
        parent::__construct('extensions', $version);
    }

    function init()
    {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'hook_admin_menu'), 50);
            add_filter('newsletter_menu_settings', array($this, 'hook_newsletter_menu_settings'));
            add_action('wp_ajax_tnp_addons_register', array($this, '_register'));
            add_action('wp_ajax_tnp_addons_license', array($this, 'license'));
        }
    }

    function _register()
    {
        $logger = new NewsletterLogger('extensions');

        check_ajax_referer('register');
        header('Content-Type: application/json');
        $email = $_POST['email'];
        if (!is_email($email)) {
            echo json_encode(array('message' => 'The email address is invalid.'));
            wp_die();
        }

        //$marketing = $_POST['marketing'];
        $marketing = '1';
        $response = wp_remote_post('https://www.thenewsletterplugin.com/wp-content/new-account.php', array(
            'body' => array('email' => $email, 'marketing'=>$marketing)
        ));

        if (is_wp_error($response)) {
            $logger->error($response);
            echo json_encode(array('message' => 'Unable to contact the registration service.'));
            // TODO: Logging
            wp_die();
        }

        if (wp_remote_retrieve_response_code($response) != '200') {
            echo json_encode(array('message' => 'Registration service error (code ' . wp_remote_retrieve_response_code($response) . ').'));
            // TODO: Logging
            wp_die();
        }

        $logger->debug(wp_remote_retrieve_body($response));
        // $response['body']
        $data = json_decode(wp_remote_retrieve_body($response));

        if (!is_object($data)) {
            $logger->debug($data);
            echo json_encode(array('message' => 'Invalid response from the registration service.'));
            // TODO: Logging
            die();
        }

        // That is a warning
        if (isset($data->message)) {
            echo json_encode(array('message' => $data->message));
            die();
        }

        // User registered
        $options = get_option('newsletter_main');
        $options['contract_key'] = $data->license_key;
        // Forces an update
        $this->get_license_data();
        update_option('newsletter_main', $options);

        // Setup the license key      
        echo json_encode(array('message' => 'Registration completed', 'reload' => true));
        wp_die();
    }

    /**
     * Return the license details and authorizations for the given license key.
     *
     * @param string $key
     * @return mixed
     */
    function get_license_data()
    {
        return Newsletter::instance()->get_license_data(true);
    }

    function check_license($license_key)
    {
        $response = wp_remote_post('https://www.thenewsletterplugin.com/wp-content/plugins/file-commerce-pro/license-check.php', array(
            'body' => array('k' => $license_key)
        ));
        if (is_wp_error($response))
            return $response;

        if (wp_remote_retrieve_response_code($response) != '200') {
            return new WP_Error(wp_remote_retrieve_response_code($response), 'License validation service error (code ' . wp_remote_retrieve_response_code($response) . ').');
        }
        $data = json_decode(wp_remote_retrieve_body($response));

        if (!is_object($data)) {
            return new WP_Error(1, 'Invalid response from the license validation service.');
        }

        // That is a warning
        if (isset($data->message)) {
            return new WP_Error(1, $data->message);
        }
        return $data;
    }

    function license()
    {
        check_ajax_referer('license');
        header('Content-Type: application/json');
        $license_key = trim($_POST['license_key']);

        $options = get_option('newsletter_main');
        $options['contract_key'] = $license_key;
        update_option('newsletter_main', $options);

        $data = $this->get_license_data();

        if (is_wp_error($data)) {
            echo json_encode(array('message' => $data->get_error_message()));
            die();
        }

        echo "{}";
        wp_die();
    }

    /**
     * @deprecated
     */
    function get_extension_version($extension_id)
    {
        $versions = get_option('newsletter_extension_versions');
        if (!is_array($versions)) {
            return null;
        }
        foreach ($versions as $data) {
            if ($data->id == $extension_id) {
                return $data->version;
            }
        }

        return null;
    }

    function get_package($extension_id, $licence_key = '')
    {
        return 'http://www.thenewsletterplugin.com/wp-content/plugins/file-commerce-pro/get.php?f=' . urlencode($extension_id) .
            '&d=' . urlencode(home_url()) . '&k=' . urlencode($licence_key);
    }

    function hook_newsletter_menu_settings($entries)
    {
        $entries[] = array('label' => '<i class="fas fa-pen-square"></i> Addons Manager', 'url' => '?page=newsletter_extensions_index', 'description' => 'Manager free and premium extensions for Newsletter');
        return $entries;
    }

    function hook_admin_menu()
    {
        add_submenu_page('newsletter_main_index', 'Addons Manager', '<span style="color:#27AE60; font-weight: bold;">Addons manager</span>', 'manage_options', 'newsletter_extensions_index', array($this, 'menu_page_index'));
    }

    function menu_page_index()
    {
        global $wpdb;
        require dirname(__FILE__) . '/index.php';
    }

    function register($extension)
    {
        if (empty($extension->plugin))
            return;
        $this->extensions[$extension->plugin] = $extension;
    }

    function get_extensions_catalog()
    {

        return Newsletter::instance()->getTnpExtensions();
    }

}

