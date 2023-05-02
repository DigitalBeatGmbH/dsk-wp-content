<?php

class NewsletterNinjaForms extends NewsletterFormManagerAddon {

    /**
     * @var NewsletterNinjaForms
     */
    static $instance;

    function __construct($version) {
        self::$instance = $this;
        $this->menu_title = 'Ninja Forms';
        parent::__construct('ninjaforms', $version, __DIR__);
    }

    function init() {
        parent::init();

        add_action('ninja_forms_after_submission', array($this, 'ninja_forms_after_submission'), 1);
    }

    public function ninja_forms_after_submission($data) {
        $logger = $this->get_logger();

        $form_id = $data['form_id'];
        $logger->debug('Processing submission for form ' . $form_id);
        //$logger->debug($data);

        $form_options = $this->get_form_options($form_id);
        $logger->debug($form_options);

        // Builnding a flat fields representation of the subbmitted data
        $flat_fields = array();
        foreach ($data['fields'] as $field) {
            $id = (string) $field['id'];
            $flat_fields[$id] = $field['value'];
        }

        $logger->debug($flat_fields);

        if (isset($flat_fields[$form_options['newsletter']])) {
            $logger->debug('Newsletter indicator field value: ' . $flat_fields[$form_options['newsletter']]);
        } else {
            $logger->debug('Newsletter indicator field not found');
        }

        if (empty($form_options['newsletter']) || !empty($flat_fields[$form_options['newsletter']])) {

            $logger->debug('Subscription start');

            $subscription = NewsletterSubscription::instance()->get_default_subscription();

            // Building the subscription
            $email = $flat_fields[$form_options['email']];
            if (!NewsletterModule::is_email($email)) {
                $logger->error('The email field configured does not contain an email');
                return;
            }
            $subscription->data->email = $email;
            $subscription->data->referrer = 'ninjaforms-' . $form_id;

            if (!empty($form_options['name']) && isset($flat_fields[$form_options['name']])) {
                $subscription->data->name = stripslashes($flat_fields[$form_options['name']]);
            }
            if (!empty($form_options['surname']) && isset($flat_fields[$form_options['surname']])) {
                $subscription->data->surname = stripslashes($flat_fields[$form_options['surname']]);
            }

            if (!empty($form_options['status'])) {
                $subscription->optin = $form_options['status'];
            }

            $public_profiles = Newsletter::instance()->get_profiles_public();
            foreach ($public_profiles as $profile) {
                $id = $profile->id;
                if (empty($form_options['profile_' . $id])) {
                    continue;
                }
                $form_field = $form_options['profile_' . $id];
                if (!isset($flat_fields[$form_field])) {
                    continue;
                }

                $value = $flat_fields[$form_field];
                //$logger->debug($value);

                if (is_array($value)) {
                    $subscription->data->profiles['' . $id] = stripslashes(implode(',', $value));
                } else {
                    $subscription->data->profiles['' . $id] = stripslashes($value);
                }
            }

            for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {

                if (empty($form_options['preferences_' . $i])) {
                    continue;
                }

                $subscription->data->lists[(string) $i] = 1;
            }

            $logger->debug($subscription);

            $res = NewsletterSubscription::instance()->subscribe2($subscription);

            $logger->debug($res);
        }
    }

    public function get_form($form_id) {

        $tnp_form = new TNP_FormManager_Form();
        $form_id = (int) $form_id;
        $form = Ninja_Forms()->form($form_id)->get();
        $form_fields = Ninja_Forms()->form($form_id)->get_fields();

        $tnp_form->title = $form->get_setting('title');
        $tnp_form->id = $form_id;

        foreach ($form_fields as $field) {
            if ($field->get_settings('type') === 'submit') {
                continue;
            }
            $tnp_form->fields['' . $field->get_id()] = $field->get_settings('label');
        }
        return $tnp_form;
    }

    public function get_forms() {
        $forms = Ninja_Forms()->form()->get_forms();
        $list = [];
        foreach ($forms as $form) {
            $tnp_form = new TNP_FormManager_Form();
            $settings = $this->get_form_options($form->get_id());
            $tnp_form->id = $form->get_id();
            $tnp_form->connected = !empty($settings['email']);
            $tnp_form->title = $form->get_setting('title');
            $list[] = $tnp_form;
        }

        return $list;
    }

}
