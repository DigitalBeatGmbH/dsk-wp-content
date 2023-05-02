<?php

namespace TNP\API\V2;

use Newsletter;
use WP_Error;
use WP_REST_Controller;

class REST_Controller extends WP_REST_Controller {

    const ROOT_NAMESPACE = 'newsletter';

    public function __construct() {
        $this->namespace = self::ROOT_NAMESPACE . '/v2';
    }

    public function permissions_check($request) {
        $logger = \NewsletterApi::$instance->get_logger();
        if (!TNP_REST_Authentication::$is_authenticated_with_key) {
            $logger->debug('Authentication error: invalid keys');
            return new WP_Error('newsletter_rest_authentication_error', __('Authentication error: invalid keys', 'newsletter'), array('status' => 401));
        }

        if (!Newsletter::instance()->is_allowed()) {
            $logger->debug('Authentication error: user not allowed');
            return new WP_Error('newsletter_rest_authentication_error', __('Authentication error: user not allowed', 'newsletter'), array('status' => 401));
        }

        return true;
    }

    protected function object_to_array($object) {
        return json_decode(json_encode($object), true);
    }

    public function is_authenticated_request() {
        return TNP_REST_Authentication::$is_authenticated_with_key;
    }

}
