<?php

namespace TNP\API\V2\Subscribers;

use Newsletter;
use NewsletterModule;
use NewsletterSubscription;
use NewsletterUsers;
use stdClass;
use TNP;
use TNP\API\V2\REST_Controller;
use TNP_Subscription;
use TNP_Subscription_Data;
use TNP_User;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Subscribers_Controller extends REST_Controller {

    const SUBSCRIBER_STATUS_LIST = [
        TNP_User::STATUS_CONFIRMED => 'confirmed',
        TNP_User::STATUS_NOT_CONFIRMED => 'not_confirmed',
        TNP_User::STATUS_UNSUBSCRIBED => 'unsubscribed',
        TNP_User::STATUS_BOUNCED => 'bounced'
    ];
    const SUBSCRIBER_SEX_ENUM = [
        'n' => null,
        'm' => 'M',
        'f' => 'F',
    ];

    protected $saved_lists;
    protected $saved_profiles;

    public function __construct() {
        parent::__construct();
        $this->rest_base = 'subscribers';

        $this->saved_lists = Newsletter::instance()->get_lists();
        $this->saved_profiles = Newsletter::instance()->get_profiles();
    }

    /**
     * Registers the subscribers routes
     */
    public function register_routes() {
        register_rest_route($this->namespace, "/$this->rest_base",
                array(
                    'schema' => array($this, 'get_public_item_schema'),
                    // GET /subscribers
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        //'args' => $this->get_collection_params(),
                        'callback' => array($this, 'get_items'),
                        'permission_callback' => array($this, 'permissions_check')
                    ),
                    // POST /subscribers
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        //'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
                        'callback' => array($this, 'create_item'),
                        'permission_callback' => array($this, 'permissions_check')
                    )
                )
        );
        register_rest_route($this->namespace, "/$this->rest_base/(?P<id>[\d]+)",
                array(
                    //'schema' => array($this, 'get_public_item_schema'),
                    'args' => array(
                        'id' => array(
                            'description' => __('Unique identifier for the subscriber.', 'newsletter'),
                            'type' => 'integer',
                            'required' => true
                        ),
                    ),
                    // GET /subscribers/#id
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_item'),
                        'permission_callback' => array($this, 'permissions_check')
                    ),
                    // PUT /subscribers/#id
                    array(
                        'methods' => 'PUT',
                        //'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
                        'callback' => array($this, 'update_or_create_item_from_id'),
                        'permission_callback' => array($this, 'permissions_check')
                    ),
                    // DELETE /subscribers/#id
                    array(
                        'methods' => 'DELETE',
                        'callback' => array($this, 'delete_subscriber_from_id'),
                        'permission_callback' => array($this, 'permissions_check')
                    )
                )
        );
        register_rest_route($this->namespace, "/$this->rest_base/(?P<email>[\S-]+)",
                array(
                    //'schema' => array($this, 'get_public_item_schema'),
                    'args' => array(
                        'email' => array(
                            'description' => __('Subscriber\'s email', 'newsletter'),
                            'required' => true,
                            'type' => 'string',
                            'format' => 'email',
                            'validation_callback' => 'is_email'
                        ),
                    ),
                    // GET /subscribers/#email
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_item_by_email'),
                        'permission_callback' => array($this, 'permissions_check')
                    ),
                    // PUT /subscribers/#email
                    array(
                        'methods' => 'PUT',
                        'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
                        'callback' => array($this, 'update_or_create_item_from_email'),
                        'permission_callback' => array($this, 'permissions_check')
                    ),
                    // DELETE /subscribers/#email
                    array(
                        'methods' => 'DELETE',
                        'callback' => array($this, 'delete_subscriber_from_email'),
                        'permission_callback' => array($this, 'permissions_check')
                    )
                )
        );
    }

    public function get_item_schema() {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'subscriber',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'description' => esc_html__('Unique identifier for subscriber.', 'newsletter'),
                    'type' => 'integer',
                    'readonly' => true,
                ],
                'email' => [
                    'description' => esc_html__('The subscriber\'s email.', 'newsletter'),
                    'type' => 'string',
                    'arg_options' => [
                        'required' => true,
                        'validate_callback' => 'is_email',
                    ],
                ],
                'first_name' => [
                    'description' => esc_html__('The subscriber\'s first name.', 'newsletter'),
                    'type' => ['string', 'null'],
                ],
                'last_name' => [
                    'description' => esc_html__('The subscriber\'s last name.', 'newsletter'),
                    'type' => ['string', 'null'],
                ],
                'gender' => [
                    'description' => esc_html__('The subscriber\'s sex.', 'newsletter'),
                    'type' => ['string', 'null'],
                    'enum' => ['M', 'F']
                ],
                'country' => [
                    'description' => esc_html__('The subscriber\'s country in ISO 3166-1 alpha-2 format (2 letters).', 'newsletter'),
                    'type' => ['string', 'null'],
                    'minLength' => 0,
                    'maxLength' => 2
                ],
                'region' => [
                    'description' => esc_html__('The subscriber\'s region.', 'newsletter'),
                    'type' => ['string', 'null'],
                ],
                'city' => [
                    'description' => esc_html__('The subscriber\'s city.', 'newsletter'),
                    'type' => ['string', 'null'],
                ],
                'lists' => [
                    'description' => esc_html__('Lists', 'newsletter'),
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => [
                                'description' => esc_html__('Unique identifier for list.', 'newsletter'),
                                'type' => 'integer',
                                'minimum' => 1,
                                'maximum' => NEWSLETTER_LIST_MAX
                            ],
                            'value' => [
                                'description' => esc_html__('List value. 0 = not set | 1 = set', 'newsletter'),
                                'type' => 'integer',
                                'enum' => [0, 1]
                            ]
                        ]
                    ],
                ],
                'extra_fields' => [
                    'description' => esc_html__('Subscriber Extra fields', 'newsletter'),
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => [
                                'description' => esc_html__('Unique identifier for profile extra field.', 'newsletter'),
                                'type' => 'integer',
                                'minimum' => 1,
                                'maximum' => NEWSLETTER_PROFILE_MAX
                            ],
                            'value' => [
                                'description' => esc_html__('Profile extra field value.', 'newsletter'),
                                'type' => ['string', 'null'],
                            ],
                        ]
                    ],
                ],
                'status' => [
                    'description' => esc_html__('The subscriber\'s status.', 'newsletter'),
                    'type' => 'string',
                    'enum' => array_values(self::SUBSCRIBER_STATUS_LIST),
                    'arg_options' => ['default' => self::SUBSCRIBER_STATUS_LIST[TNP_User::STATUS_CONFIRMED]],
                ],
            ],
        ];
    }

    public function get_item($request) {
        $subscriber_id = $request->get_param('id');
        $subscriber = \NewsletterUsers::instance()->get_user($subscriber_id);

        if (is_null($subscriber)) {
            return new WP_Error('-1', __('Subscriber does not exist', 'newsletter'), array('status' => 404));
        }

        return $this->prepare_item_for_response($subscriber, $request);
    }

    public function get_item_by_email($request) {
        $subscriber_email = $request->get_param('email');
        $subscriber = NewsletterUsers::instance()->get_user($subscriber_email);

        if (is_null($subscriber)) {
            return new WP_Error('-1', __('Subscriber does not exist', 'newsletter'), array('status' => 404));
        }

        return $this->prepare_item_for_response($subscriber, $request);
    }

    public function get_collection_params() {
        $params = parent::get_collection_params();
        unset($params['context']);
        unset($params['search']);

        return $params;
    }

    public function get_items($request) {

        $args = [
            'page' => $request->get_param('page'),
            'per_page' => $request->get_param('per_page'),
        ];

        $subscribers = NewsletterUsers::instance()->get_users($args);

        $subscribers = array_map(function ($subscriber) use ($request) {
            $data = $this->prepare_item_for_response($subscriber, $request);

            return $this->prepare_response_for_collection($data);
        }, $subscribers);

        $response = rest_ensure_response($subscribers);

        return $response;
    }

    public function create_item($request) {

        $subscriber = NewsletterUsers::instance()->get_user($request->get_param('email'));

        return $this->update_or_create($request, $subscriber);
    }

    public function update_or_create_item_from_id($request) {

        $subscriber = NewsletterUsers::instance()->get_user($request->get_param('id'));

        return $this->update_or_create($request, $subscriber);
    }

    public function update_or_create_item_from_email($request) {

        $subscriber = NewsletterUsers::instance()->get_user($request->get_param('email'));

        return $this->update_or_create($request, $subscriber);
    }

    /**
     * @param WP_REST_Request $request
     * @param TNP_User $subscriber
     *
     * @return WP_REST_Response
     */
    private function update_or_create($request, $subscriber) {

        if (is_null($subscriber)) {

            $prepared_subscriber = $this->prepare_item_for_database($request);
            //Strips any null fields;
            $prepared_subscriber = $this->strips_all_nullish_subscriber_properties($prepared_subscriber);
            //Insert
            $subscriber = NewsletterUsers::instance()->save_user((array) $prepared_subscriber);

            if ($subscriber instanceof WP_Error) {
                return new WP_REST_Response($subscriber, 400);
            } else {
                $response = $this->prepare_item_for_response($subscriber, $request);
                $response->set_status(201);

                return $response;
            }
        } else {

            $prepared_subscriber = $this->prepare_item_for_database($request);
            $prepared_subscriber->id = $subscriber->id;

            //I'm updating email?
            if ($prepared_subscriber->email != $subscriber->email) {
                $can_subscriber_email_be_updated = $this->can_subscriber_email_be_updated($prepared_subscriber->email, $subscriber->id);
                if ($can_subscriber_email_be_updated instanceof WP_Error) {
                    return new WP_REST_Response(__('The email is already present', 'newsletter'), 400);
                }
            }

            //Strips any null fields;
            $prepared_subscriber = $this->strips_all_nullish_subscriber_properties($prepared_subscriber);
            //Update
            $subscriber = NewsletterUsers::instance()->save_user((array) $prepared_subscriber);

            if (false === $subscriber || is_null($subscriber)) {

                return new WP_REST_Response(__('Error on subscriber data update', 'newsletter'), 500);
            } else {

                $response = $this->prepare_item_for_response($subscriber, $request);
                $response->set_status(200);

                return $response;
            }
        }
    }

    private function strips_all_nullish_subscriber_properties($subscriber) {
        return (object) array_filter((array) $subscriber, function ($props) {
                    return !is_null($props);
                });
    }

    private function can_subscriber_email_be_updated($new_email, $old_subscriber_id) {

        $user_of_new_email = NewsletterUsers::instance()->get_user($new_email);
        if (!is_null($user_of_new_email) && $user_of_new_email->id != $old_subscriber_id) {
            return new WP_Error('email_already_exists', __('The email is already present', 'newsletter'));
        }

        return true;
    }

    private function transform_to_tnp_internal_status($subscriber_status) {
        if (empty($subscriber_status)) {
            return null;
        }

        $tnp_status_format = array_search($subscriber_status, self::SUBSCRIBER_STATUS_LIST);

        if (empty($tnp_status_format)) {
            return null;
        }

        return $tnp_status_format;
    }

    public function delete_subscriber_from_id($request) {

        $subscriber = NewsletterUsers::instance()->get_user($request->get_param('id'));

        return $this->delete_subscriber($subscriber);
    }

    public function delete_subscriber_from_email($request) {

        $subscriber = NewsletterUsers::instance()->get_user($request->get_param('email'));

        return $this->delete_subscriber($subscriber);
    }

    private function delete_subscriber($subscriber) {

        if (is_null($subscriber)) {
            return new WP_Error('-1', __('Subscriber does not exist', 'newsletter'), array('status' => 400));
        }

        $ret = TNP::delete_subscriber(['email' => $subscriber->email]);

        if (is_wp_error($ret)) {
            return $ret;
        }

        return new WP_REST_Response('Deleted', 200);
    }

    /**
     * Prepares one item for create or update operation.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return stdClass|WP_Error The prepared item, or WP_Error object on failure.
     * @since 4.7.0
     *
     */
    protected function prepare_item_for_database($request) {

        $subscriber = new stdClass();

        $email = NewsletterModule::normalize_email($request->get_param('email'));
        if ($email) {
            $subscriber->email = $email;
        }
        $subscriber->name = is_null($request->get_param('first_name')) ? null : NewsletterModule::normalize_name($request->get_param('first_name'));
        $subscriber->surname = $request->get_param('last_name');
        $subscriber->sex = is_null($request->get_param('gender')) ? null : NewsletterModule::normalize_sex(array_search($request->get_param('gender'), self::SUBSCRIBER_SEX_ENUM));
        $subscriber->language = $request->get_param('language');
        $subscriber->country = $request->get_param('country');
        $subscriber->region = $request->get_param('region');
        $subscriber->city = $request->get_param('city');
        $subscriber->status = is_null($request->get_param('status')) ? null : array_search($request->get_param('status'), self::SUBSCRIBER_STATUS_LIST);
        $subscriber->updated = time();

        $lists = $request->get_param('lists');
        if ($lists) {
            foreach ($lists as $list) {
                if (is_array($list)) {
                    $prop = 'list_' . (int) $list['id'];
                    $subscriber->$prop = (int) $list['value'];
                } else {
                    $id = (int) $list;
                    $value = $id > 0 ? 1 : 0;
                    $id = abs($id);
                    if ($id == 0 || $id > NEWSLETTER_LIST_MAX) {
                        continue;
                    }
                    $prop = 'list_' . $id;
                    $subscriber->$prop = $value;
                }
            }
        }

        $extra_fields = $request->get_param('extra_fields');
        if ($extra_fields) {
            foreach ($extra_fields as $field) {
                $prop = 'profile_' . (int) $field['id'];
                $subscriber->$prop = sanitize_text_field($field['value']);
            }
        }

        return $subscriber;
    }

    /**
     * Prepares a single subscriber output for response.
     *
     * @param TNP_User $subscriber
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response Response object.
     */
    public function prepare_item_for_response($subscriber, $request) {

        $lists = [];
        foreach ($this->saved_lists as $list) {
            if (!empty($subscriber->{'list_' . $list->id})) {
                $l = new stdClass();
                $l->id = $list->id;
                $l->name = $list->name;

                $lists[] = $l;
            }
        }

        $extra_fields = [];
        foreach ($this->saved_profiles as $profile) {
            if (!empty($subscriber->{'profile_' . $profile->id})) {
                $extra_field = new stdClass();
                $extra_field->id = $profile->id;
                $extra_field->value = $subscriber->{'profile_' . $profile->id};

                $extra_fields[] = $extra_field;
            }
        }

        $subscriber_dto = new Subscriber_DTO();
        $subscriber_dto->id = (int) $subscriber->id;
        $subscriber_dto->email = $subscriber->email;
        $subscriber_dto->first_name = $subscriber->name;
        $subscriber_dto->last_name = $subscriber->surname;
        $subscriber_dto->gender = self::SUBSCRIBER_SEX_ENUM[$subscriber->sex];
        $subscriber_dto->country = $subscriber->country;
        $subscriber_dto->region = $subscriber->region;
        $subscriber_dto->city = $subscriber->city;
        $subscriber_dto->lists = $lists;
        $subscriber_dto->extra_fields = $extra_fields;
        $subscriber_dto->status = self::SUBSCRIBER_STATUS_LIST[$subscriber->status];

        return rest_ensure_response($subscriber_dto);
    }

}
