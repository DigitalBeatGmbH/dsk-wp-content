<?php

class NewsletterCF7 extends NewsletterFormManagerAddon {

    /**
     * @var NewsletterCF7
     */
    static $instance;
    var $current_form_id = null;
    // Local copy of processed posted data by CF7
    var $posted_data;

    function __construct($version) {
        $this->menu_title = 'Contact Form 7';
        self::$instance = $this;
        parent::__construct('cf7', $version, __DIR__);
    }

    function init() {
        parent::init();
        // To add the language field since on ajax posts seems to no be always available
        add_filter('wpcf7_form_hidden_fields', array($this, 'hook_wpcf7_form_hidden_fields'));
        add_filter('wpcf7_posted_data', array($this, 'hook_wpcf7_posted_data'));

        // Max priority to be axctive before the redirect plugin "Contact Form 7 - Success Page Redirects"
        add_action('wpcf7_before_send_mail', array($this, 'hook_wpcf7_before_send_mail'), 1);

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_filter('newsletter_lists_notes', array($this, 'hook_newsletter_lists_notes'), 10, 2);
            }
        }
    }

    function hook_newsletter_lists_notes($notes, $list_id) {

        $forms = get_posts(array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => 100));
        foreach ($forms as $form) {
            $settings = get_option('newsletter_cf7_' . $form->ID, array());
            if (empty($settings['preferences'])) {
                continue;
            }
            if (in_array($list_id, $settings['preferences'])) {
                $notes[] = 'Assigned by CF7 form "' . $form->post_title . '"';
            }
        }

        return $notes;
    }

    function hook_wpcf7_posted_data($posted_data) {
        $this->posted_data = $posted_data;
        return $posted_data;
    }

    function hook_wpcf7_form_hidden_fields($fields) {
        $language = Newsletter::instance()->get_current_language();
        if ($language) {
            $fields['nlang'] = $language;
        }
        return $fields;
    }

    /**
     *
     * @param WPCF7_ContactForm $form
     */
    function hook_wpcf7_before_send_mail($form) {

        $logger = $this->get_logger();

        $logger->debug('Form submitted');
        $logger->debug($form);

        $this->current_form_id = $form->id();

        $form_options = get_option('newsletter_cf7_' . $form->id(), null);
        if (empty($form_options)) {
            $logger->debug('No configuration for form ' . $form->id());
            return;
        }

        if (!empty($form_options['newsletter']) && !isset($_REQUEST[$form_options['newsletter']])) {
            $logger->debug('No consent');
            return;
        }

        $logger->debug('Intercepting form data');

        $subscription = NewsletterSubscription::instance()->get_default_subscription();
        $subscription->data->email = $_REQUEST[$form_options['email']];
        $subscription->data->referrer = 'cf7-' . $form->id();

        if (!empty($form_options['name']) && isset($_REQUEST[$form_options['name']])) {
            $subscription->data->name = stripslashes($_REQUEST[$form_options['name']]);
        }
        if (!empty($form_options['surname']) && isset($_REQUEST[$form_options['surname']])) {
            $subscription->data->surname = stripslashes($_REQUEST[$form_options['surname']]);
        }

        // Gender
        if (!empty($form_options['gender'])) {
            $subscription->data->sex = $this->posted_data[$form_options['gender']][0];
        }

        $public_profiles = Newsletter::instance()->get_profiles_public();
        foreach ($public_profiles as $profile) {
            $id = $profile->id;
            if (empty($form_options['profile_' . $id])) {
                continue;
            }
            $form_field = $form_options['profile_' . $id];
            if (!isset($_REQUEST[$form_field])) {
                continue;
            }
            if (is_array($_REQUEST[$form_field])) {
                $value = $_REQUEST[$form_field][0];
            } else {
                $value = $_REQUEST[$form_field];
            }

            $subscription->data->profiles['' . $id] = stripslashes($value);
        }

        // Accept only public lists from the form
        $public_lists = Newsletter::instance()->get_lists_public();
        foreach ($public_lists as $list) {
            if (isset($_REQUEST['list_' . $list->id])) {
                $subscription->data->lists['' . $list->id] = 1;
            }
        }

        if (isset($_REQUEST['nlang'])) {
            $subscription->data->language = $_REQUEST['nlang'];
        }

        $subscription->data->add_lists($form_options['preferences']);

        $user = NewsletterSubscription::instance()->subscribe2($subscription);
    }

    public function get_forms() {
        $forms = get_posts(array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => 100));

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
        $form = WPCF7_ContactForm::get_instance($form_id);

        if (method_exists($form, 'scan_form_tags')) {
            $form_fields = $form->scan_form_tags();
        } else {
            $form_fields = $form->form_scan_shortcode();
        }

        foreach ($form_fields as $form_field) {
            $field_name = str_replace('[]', '', $form_field['name']);
            if (empty($field_name)) {
                continue;
            }
            $tnp_form->fields[$field_name] = $field_name;
        }

        $tnp_form->title = $form->title();
        $tnp_form->id = $form_id;

        return $tnp_form;
    }

}
