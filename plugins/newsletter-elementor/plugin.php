<?php

class NewsletterElementor extends NewsletterAddon {

    /**
     * @var NewsletterElementor
     */
    static $instance;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('elementor', $version);
        
        add_action('elementor_pro/init', function() {
            $action = new NewsletterElementorAction();
            \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($action->get_name(), $action);
        });
    }

    function init() {
        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_subscription', array($this, 'hook_newsletter_menu_subscription'));
            }
        }
    }

    function hook_newsletter_menu_subscription($entries) {
        $entries[] = array('label' => '<i class="fas fa-pencil-alt"></i> Elementor', 'url' => '?page=newsletter_elementor_index', 'description' => '');
        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Elementor Addon', '<span class="tnp-side-menu">Elementor</span>', 'manage_options', 'newsletter_elementor_index',
                function () {
            require dirname(__FILE__) . '/index.php';
        });
    }

}

class NewsletterElementorAction extends \ElementorPro\Modules\Forms\Classes\Integration_Base {

    /**
     * @var NewsletterElementor 
     */
    var $module;

    public function __construct() {
        $this->module = NewsletterElementor::$instance;
    }

    public function get_label() {
        return 'Newsletter';
    }

    public function get_name() {
        return 'tnp';
    }

    public function on_export($element) {
        return $element;
    }

    /**
     * 
     * @param ElementorPro\Modules\Forms\Widgets\Form $widget
     */
    public function register_settings_section($widget) {

        $logger = $this->module->get_logger();

        $widget->start_controls_section(
                'section_tnp',
                [
                    'label' => __('Newsletter', 'text-domain'),
                    'condition' => [
                        'submit_actions' => $this->get_name(),
                    ],
                ]
        );

        $this->register_fields_map_control($widget);

        include NEWSLETTER_DIR . '/includes/controls.php';
        $controls = new NewsletterControls();

        $widget->add_control(
                'tnp_lists',
                [
                    'label' => 'Add subscriber to:',
                    'type' => \Elementor\Controls_Manager::SELECT2,
                    'placeholder' => '',
                    'label_block' => true,
                    'separator' => 'before',
                    'multiple' => true,
                    'description' => '',
                    'options' => $controls->get_list_options()
                ]
        );
        $widget->end_controls_section();
    }

    /**
     * NOTE: when this code is changed is does not affect OLD forms, we don't know why...
     * 
     * @return array
     */
    protected function get_fields_map_control_options() {
        $options = [
            'default' => [
                [
                    'remote_id' => 'consent',
                    'remote_label' => 'Consent checkbox',
                    'remote_type' => 'text'
                ],
                [
                    'remote_id' => 'email',
                    'remote_label' => 'Email',
                    'remote_type' => 'email',
                    'remote_required' => true,
                ],
                [
                    'remote_id' => 'name',
                    'remote_label' => 'First Name',
                    'remote_type' => 'text',
                ],
                [
                    'remote_id' => 'surname',
                    'remote_label' => 'Last Name',
                    'remote_type' => 'text',
                ]
            ]
        ];

        // Public lists
        $lists = Newsletter::instance()->get_lists_public();
        foreach ($lists as $list) {
            $tmp = ['remote_id' => 'list_' . $list->id,
                'remote_label' => $list->name,
                'remote_type' => 'text'];
            $options['default'][] = $tmp;
        }

        // Extra fields
        $fields = Newsletter::instance()->get_profiles_public();
        foreach ($fields as $field) {
            $tmp = ['remote_id' => 'profile_' . $field->id,
                'remote_label' => $field->name,
                'remote_type' => 'text'];
            $options['default'][] = $tmp;
        }

        return $options;
    }

    public function run($record, $ajax_handler) {
        $logger = $this->module->get_logger();
        //$logger->debug($record);

        $settings = $record->get('form_settings');
        $fields = $record->get('fields');
        $sent_data = $record->get('sent_data');
        $logger->debug($sent_data);
        $subscription = NewsletterSubscription::instance()->get_default_subscription();
        $newsletter = Newsletter::instance();

        //$logger->debug($settings);

        foreach ($settings['tnp_fields_map'] as $map_item) {
            $name = $map_item['remote_id'];
            $logger->debug('Subscriber field name: ' . $name);

            $form_field_name = $map_item['local_id'];
            $logger->debug('Form field name: ' . $form_field_name);

            // No mapping
            if (empty($form_field_name)) {
                $logger->debug('Field not mapped');
                continue;
            }

            if ($name === 'consent' && !isset($sent_data[$form_field_name])) {
                $logger->debug('Consent not provided');
                return;
            }

            // Checkboxes, for example
            if (!isset($sent_data[$form_field_name])) {
                continue;
            }

            $value = trim($sent_data[$form_field_name]);

            switch ($name) {
                case 'email':
                    $subscription->data->email = Newsletter::normalize_email($value);
                    continue 2;
                case 'name':
                    $subscription->data->name = Newsletter::normalize_name($value);
                    continue 2;
                case 'surname':
                    $subscription->data->surname = Newsletter::normalize_name($value);
                    continue 2;
            }


            if (strpos($name, 'list_') === 0) {
                if (isset($sent_data[$form_field_name])) {

                    $logger->info('List ' . $name);
                    $id = (int) substr($name, 5);
                    $logger->info('ID: ' . $id);
                    $list = $newsletter->get_list($id);
                    if ($list && !$list->is_private()) {
                        $subscription->data->lists[$id] = 1;
                    } else {
                        $logger->info('Not found or private');
                    }
                }
                continue;
            }



            if (strpos($name, 'profile_') === 0) {
                $logger->info('Profile field ' . $name);
                $id = (int) substr($name, 8);
                $logger->info('ID: ' . $id);
                $profile = $newsletter->get_profile($id);
                if ($profile && !$profile->is_private()) {
                    $subscription->data->profiles[$id] = $value;
                } else {
                    $logger->info('Not found or private');
                }
                continue;
            }
        }

        // Enforced lists
        $logger->debug($settings['tnp_lists']);
        if (!empty($settings['tnp_lists'])) {
            foreach ($settings['tnp_lists'] as $list_id) {
                $subscription->data->lists[$list_id] = 1;
            }
        }

        $form_id = $record->get_form_settings('form_name');
        $subscription->data->referrer = 'elementor-' . $form_id;

        $logger->debug($subscription);
        NewsletterSubscription::instance()->subscribe2($subscription);
    }

}
