<?php

class NewsletterWPForms extends NewsletterFormManagerAddon {

    /**
     * @var NewsletterWPForms
     */
    static $instance;

    function __construct($version) {
        $this->menu_title = 'WP Forms Addon';
        $this->menu_description = '';
        self::$instance = $this;
        parent::__construct('wpforms', $version, __DIR__);
    }

    function init() {
        parent::init();
        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_filter('wpforms_overview_row_actions', array($this, 'hook_wpforms_overview_row_actions'), 10, 2);
            }
        }

        add_action('wpforms_process_complete', array($this, 'process_entry'), 5, 4);
    }

    function hook_wpforms_overview_row_actions($row_actions, $form) {
        $row_actions['newsletter'] = sprintf(
                '<a href="%s" title="%s">%s</a>', '?page=newsletter_wpforms_index&id=' . $form->ID, 'Connect with Newsletter', 'Connect with Newsletter'
        );
        return $row_actions;
    }

    public function process_entry($fields, $entry, $form_data, $entry_id = 0) {
        $logger = $this->get_logger();

        $logger->debug('Processing...');

        $form_options = $this->get_form_options($form_data['id']);

        if (empty($form_options)) {
            $logger->debug('Form not connected');
            return;
        }

        // Destructure the complex WPForms fields
        $flat_fields = [];
        foreach ($fields as $field) {
            $id = 'f' . $field['id'];
            if ($field['type'] == 'name') {
                //$logger->debug($field);
                $flat_fields[$id] = $field['value'];
                $flat_fields[$id . ',first'] = $field['first'];
                $flat_fields[$id . ',last'] = $field['last'];
            } else if ($field['type'] == 'checkbox' && !empty($field['value'])) {
                $flat_fields[$id] = '1';
            } else {
                $flat_fields[$id] = $field['value'];
            }
        }

        if (!empty($form_options['newsletter']) && empty($flat_fields[$form_options['newsletter']])) {
            $logger->debug('Missing consent');
            return;
        }

        $logger->debug('Subscription accepted');

        // Building the subscription
        $email = $flat_fields[$form_options['email']];
        if (!NewsletterModule::is_email($email)) {
            $logger->debug('The email field configured does not contain an email');
            return;
        }

        $subscription_module = NewsletterSubscription::instance();

        $subscription = $subscription_module->get_default_subscription();

        $subscription->data->email = $email;
        $subscription->data->referrer = 'wpforms-' . $form_data['id'];

        if (!empty($form_options['status'])) {
            $subscription->optin = $form_options['status'];
        }

        if (!empty($form_options['name']) && isset($flat_fields[$form_options['name']])) {
            $subscription->data->name = stripslashes($flat_fields[$form_options['name']]);
        }

        if (!empty($form_options['surname']) && isset($flat_fields[$form_options['surname']])) {
            $subscription->data->surname = stripslashes($flat_fields[$form_options['surname']]);
        }

        $public_profiles = Newsletter::instance()->get_profiles_public();
        foreach ($public_profiles as $profile) {
            // Not mapped
            if (empty($form_options['profile_' . $profile->id])) {
                continue;
            }

            // Not received
            if (!isset($flat_fields[$form_options['name']])) {
                continue;
            }

            $value = stripslashes($flat_fields[$form_options['profile_' . $profile->id]]);

            $subscription->data->profiles['' . $profile->id] = $value;
        }

        // Imposed lists
        $subscription->data->add_lists($form_options['lists']);

        // Compatibility code
        for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {
            if (!empty($form_options['preferences_' . $i])) {
                $subscription->data->lists['' . $i] = 1;
            }
        }
        // End compatibility code

        NewsletterSubscription::instance()->subscribe2($subscription);
    }

    public function get_forms() {
        $forms = get_posts(array('post_type' => 'wpforms', 'nopaging' => true));
        $list = [];
        foreach ($forms as $form) {
            $tnp_form = new TNP_FormManager_Form();
            $settings = $this->get_form_options($form->ID);
            $tnp_form->connected = !empty($settings['email']);
            $tnp_form->title = $form->post_title;
            $tnp_form->id = $form->ID;
            $list[] = $tnp_form;
        }
        return $list;
    }

    public function get_form($form_id) {

        $tnp_form = new TNP_FormManager_Form();

        $provider = new NewsletterWPForms_Provider();

        $form = get_post($form_id);

        $tnp_form->title = $form->post_title;
        $tnp_form->id = $form->ID;

        $form_fields = $provider->get_form_fields($form);

        foreach ($form_fields as $field) {
            if ($field['type'] == 'checkbox') {
                $tnp_form->fields['f' . $field['id']] = $field['label'];
            } else if ($field['type'] == 'name') {
                //$this->get_logger()->debug($field);
                if ($field['format'] == 'simple') {
                    $tnp_form->fields['f' . $field['id']] = 'Full name';
                } else {
                    $tnp_form->fields['f' . $field['id'] . ',first'] = 'First name';
                    $tnp_form->fields['f' . $field['id'] . ',last'] = 'Last name';
                }
            } else {
                $tnp_form->fields['f' . $field['id']] = $field['label'];
            }
        }

        return $tnp_form;
    }

}

/**
 * Dummy class to access the form details.
 */
class NewsletterWPForms_Provider extends WPForms_Provider {

    public function init() {
        
    }

}
