<?php

require_once __DIR__ . '/autoloader.php';

use TNP\API\V2\ExtraFields\ExtraFields_Controller;
use TNP\API\V2\Lists\Lists_Controller;
use TNP\API\V2\Newsletters\Newsletters_Controller;
use TNP\API\V2\Subscribers\Subscribers_Controller;
use TNP\API\V2\Subscriptions\Subscriptions_Controller;
use TNP\API\V2\TNP_REST_Authentication;
use TNP\API\V2\TNP_REST_Authentication_Repository;

class NewsletterApi extends NewsletterAddon {

    /**
     * @var NewsletterApi
     */
    static $instance;

    function __construct($version = '') {
        self::$instance = $this;
        parent::__construct('api', $version);
        $this->setup_options();

        TNP_REST_Authentication::getInstance()->init_authentication_method();
    }
   

    function upgrade($first_install = false) {
        parent::upgrade($first_install);
        require_once __DIR__ . '/V2/TNP_REST_Authentication_Repository.php';

        TNP_REST_Authentication_Repository::getInstance()->create_table();
    }

    function init() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Obsolete
//        if (empty($this->options)) {
//            $this->options['key'] = Newsletter::instance()->options['api_key'];
//            $this->save_options($this->options);
//        }

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_settings', array($this, 'hook_newsletter_menu_settings'));
            }
        }
    }

    function hook_newsletter_menu_settings($entries) {
        $entries[] = array(
            'label' => '<i class="fa fa-exchange-alt"></i> API',
            'url' => '?page=newsletter_api_index',
            'description' => ''
        );

        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'API', '<span class="tnp-side-menu">API</span>', 'manage_options', 'newsletter_api_index', function () {
            require dirname(__FILE__) . '/index.php';
        });
    }

    function register_rest_routes() {
        require_once NEWSLETTER_INCLUDES_DIR . '/module.php';

        require_once __DIR__ . '/TNP_REST_Controller.php';

        // V1 REST API registration
        new TNP_REST_Controller($this->options);

        // V2 REST API registration
        ( new Subscribers_Controller())->register_routes();
        ( new Subscriptions_Controller())->register_routes();
        ( new Lists_Controller())->register_routes();
        ( new ExtraFields_Controller())->register_routes();
        ( new Newsletters_Controller())->register_routes();
    }

}
