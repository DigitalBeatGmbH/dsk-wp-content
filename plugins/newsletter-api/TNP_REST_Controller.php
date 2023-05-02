<?php

require_once NEWSLETTER_INCLUDES_DIR . '/TNP.php';

/**
 * Description of TNP_REST_Controller
 *
 * @author roby
 */
class TNP_REST_Controller {

    // Here initialize our namespace and resource name.
    public function __construct($options) {
        $this->namespace = 'newsletter/v1';
        $this->register_routes();
        if (empty($options['key'])) {
            $this->api_key = wp_generate_password();
        } else {
            $this->api_key = $options['key'];
        }
    }

    // Register our routes.
    public function register_routes() {

        register_rest_route($this->namespace, '/subscribe', array(
            'methods' => 'POST',
            'callback' => array($this, 'subscribe'),
            'permission_callback' => '__return_true',
            //'permission_callback' => array($this, 'check_api_key'),
        ));

        register_rest_route($this->namespace, '/unsubscribe', array(
            'methods' => 'POST',
            'callback' => array($this, 'unsubscribe'),
            'permission_callback' => array($this, 'check_api_key'),
        ));

        register_rest_route($this->namespace, '/subscribers', array(
            'methods' => 'POST',
            'callback' => array($this, 'subscribers'),
            'permission_callback' => array($this, 'check_api_key'),
        ));

        register_rest_route($this->namespace, '/subscribers/add', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_subscriber'),
            'permission_callback' => array($this, 'check_api_key'),
        ));

        register_rest_route($this->namespace, '/subscribers/delete', array(
            'methods' => 'POST',
            'callback' => array($this, 'delete_subscriber'),
            'permission_callback' => array($this, 'check_api_key'),
        ));

        register_rest_route($this->namespace, '/newsletters', array(
            'methods' => 'GET',
            'callback' => array($this, 'newsletters'),
            'permission_callback' => '__return_true',
            //'permission_callback' => array($this, 'check_api_key'),
        ));
    }

    /**
     * Check API key.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function check_api_key($request) {

        if ($request->get_param('api_key') != $this->api_key) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot perform this action.'), array('status' => 403));
        }
        return true;
    }

    /*
     * Subscribe
     */
    public function subscribe($request) {

        return rest_ensure_response(TNP::subscribe($request->get_params()));

    }

    /*
     * Unsubscribe
     */
    public function unsubscribe($request) {

        return rest_ensure_response(TNP::unsubscribe($request->get_params()));

    }

    /*
     * Subscribers list
     */
    public function subscribers($request) {

        return rest_ensure_response(TNP::subscribers($request->get_params()));

    }

    /*
     * Add a subscriber
     */
    public function add_subscriber($request) {

        return rest_ensure_response(TNP::add_subscriber($request->get_params()));

    }

    /*
     * Delete a subscriber
     */

    public function delete_subscriber($request) {

        return rest_ensure_response(TNP::delete_subscriber($request->get_params()));

    }

    /*
     * Newsletters list
     */
    public function newsletters($request) {

        return rest_ensure_response(TNP::newsletters($request->get_params()));

    }

}
