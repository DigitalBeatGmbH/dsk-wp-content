<?php

use TNP\Webhook\Webhook;

include_once __DIR__ . '/includes/webhook.class.php';

class NewsletterWebhooks extends NewsletterAddon {

    const ON_SUBSCRIBE = 'newsletter_user_confirmed';
    const ON_UNSUBSCRIBE = 'newsletter_user_unsubscribed';
    const ON_ENDED_SENDING_NEWSLETTER = 'newsletter_ended_sending_newsletter';

    /**
     * @var NewsletterWebhooks
     */
    static $instance;
    var $table_name;

    function __construct($version) {
        global $wpdb;

        self::$instance = $this;

        $this->table_name = $wpdb->prefix . 'newsletter_webhooks';
        parent::__construct('webhooks', $version);
        $this->setup_options();
    }

    function upgrade($first_install = false) {
        global $charset_collate, $wpdb;

        parent::upgrade($first_install);

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        dbDelta("CREATE TABLE `" . $this->table_name . "` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `url` varchar(255),
            `description` varchar(255),
            `http_verb` varchar(6),
            `events` varchar(255),
            PRIMARY KEY (`id`)) $charset_collate;");
    }

    function init() {
        parent::init();
        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_settings', array($this, 'hook_newsletter_menu_settings'));
            }
        }

        $this->register_webhook_triggers();

        add_action('newsletter_webhooks_call', function ($webhook, $data) {
            $this->webhook_call($webhook, $data);
        }, 10, 3);
    }

    function hook_newsletter_menu_settings($entries) {
        $entries[] = array(
            'label' => '<i class="fas fa-pencil-alt"></i> Webhooks',
            'url' => '?page=newsletter_webhooks_index',
            'description' => 'Webhooks configuration'
        );

        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Webhooks', '<span class="tnp-side-menu">Webhooks</span>', 'manage_options', 'newsletter_webhooks_index', function () {
            require dirname(__FILE__) . '/index.php';
        });
    }

    /**
     * 
     * @return Webhook[]
     */
    public function get_webhooks() {
        return NewsletterStore::instance()->get_all($this->table_name);
    }

    /**
     * 
     * @param int $webhook_id
     * @return TNP\Webhooks\Webhook
     */
    public function get_webhook($webhook_id) {
        return NewsletterStore::instance()->get_single($this->table_name, $webhook_id);
    }

    /**
     * 
     * @param TNP\Webhooks\Webhook $webhook
     * @return TNP\Webhooks\Webhook
     */
    public function save_webhook($webhook) {
        return NewsletterStore::instance()->save($this->table_name, $webhook);
    }

    /**
     * Instead of register an handle for each event type, and handle is registered for each webhook defined
     * bonded to the right event. Better or worse I don't know.
     */
    
    private function register_webhook_triggers() {

        //Retrieve all configured webhooks
        /* @var $webhooks Webhook[] */
        $webhooks = $this->get_webhooks();

        foreach ($webhooks as $webhook) {
            add_action($webhook->events, function ($data) use ($webhook) {
                $this->get_logger()->debug('Action called for webhook ' . $webhook->id . ' event ' . $webhook->events);
                // We schedule the action execution for later to have async running
                wp_schedule_single_event(time(), 'newsletter_webhooks_call', [$webhook, $data]);
            });
        }
    }

    /**
     * @param int $webhook_id
     */
    public function delete_webhook($webhook_id) {
        NewsletterStore::instance()->delete($this->table_name, $webhook_id);
    }

    /**
     * 
     * @param TNP\Webhooks\Webhook $webhook
     * @param stdClass $data
     * @return type
     */
    public function webhook_call($webhook, $data) {

        //$data = apply_filters( "newsletter_webhook_filter_data_on_$trigger_name", $data );
        $logger = $this->get_logger();
        $logger->info("Hook '$webhook->events' is calling webhook #$webhook->id");

        $curl = curl_init();

        if ($webhook->http_verb === 'JSON') {
            $body = json_encode($data);
        } else {
            $body = (array) $data;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $webhook->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $body,
        ));

        $response = curl_exec($curl);
        //$logger->debug($response);
        $response_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $logger->debug(curl_getinfo($curl));
        
        curl_close($curl);

        $this->logger->info("Response with HTTP status $response_status");

        return $response_status >= 200 && $response_status < 300;
    }

    function create_fake_subscriber() {
        $subscriber = new TNP_User();
        $subscriber->id = 42;
        $subscriber->email = "fake@email.org";
        $subscriber->name = 'John';
        $subscriber->surname = 'Doe';
        $subscriber->status = TNP_User::STATUS_CONFIRMED;
        $subscriber->token = NewsletterModule::get_token();
        $subscriber->sex = 'm';
        $subscriber->language = 'en';

        for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {
            $list_name = "list_$i";
            $list_value = rand(0, 1);

            $subscriber->$list_name = $list_value;
        }

        $subscriber->profile_1 = 'Profile 1 example text';
        $subscriber->profile_2 = 'Profile 2 example text';

        return $subscriber;
    }

    function create_fake_email() {

        $fake_email = new TNP_Email();
        $fake_email->id = 1;
        $fake_email->subject = "Hello world";
        $fake_email->message = "HTML email content";
        $fake_email->total = 100;
        $fake_email->sent = 100;
        $fake_email->open_count = 42;
        $fake_email->click_count = 42;

        return $fake_email;
    }

    public function test_webhook($webhook_id) {

        $webhook = $this->get_webhook($webhook_id);

        if ($webhook->events === self::ON_SUBSCRIBE) {

            $fake_data = $this->create_fake_subscriber();
        } else if ($webhook->events === self::ON_UNSUBSCRIBE) {

            $fake_data = $this->create_fake_subscriber();
            $fake_data->status = TNP_User::STATUS_UNSUBSCRIBED;
        } else if ($webhook->events === self::ON_ENDED_SENDING_NEWSLETTER) {

            $fake_data = $this->create_fake_email();
        }

        return $this->webhook_call($webhook, $fake_data);
    }

}
