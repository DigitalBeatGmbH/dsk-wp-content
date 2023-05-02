<?php

namespace TNP\Forms;

use Newsletter;
use NewsletterSubscription;
use NewsletterStore;
use TNP_Profile;
use TNP_Profile_Service;

defined('ABSPATH') || exit;

class Form {
    
}

class Addon extends \NewsletterAddon {

    static $instance;
    var $table = null;
    var $store = null;

    /**
     * @return NewsletterForms
     */
    static function instance() {
        if (self::$instance == null) {
            self::$instance = new Addon();
        }
        return self::$instance;
    }

    function __construct($version) {
        global $wpdb;
        $this->table = $wpdb->prefix . 'newsletter_forms';
        parent::__construct('forms', $version);
        $this->store = NewsletterStore::singleton();
    }

    function upgrade($first_install = false) {
        global $wpdb, $charset_collate;

        parent::upgrade($first_install);

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE `" . $wpdb->prefix . "newsletter_forms` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL DEFAULT '',
            `config` longtext,
            PRIMARY KEY (`id`)) $charset_collate;";

        $res = dbDelta($sql);
        $this->get_logger()->info($res);
    }

    function init() {
        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_subscription', array($this, 'hook_newsletter_menu_subscription'));
            }
            if (isset($_GET['page']) && strpos($_GET['page'], 'newsletter_forms') === 0) {
                add_action('admin_enqueue_scripts', array($this, 'hook_admin_enqueue_scripts'));
            }
        } else {
            add_shortcode('tnp_form', [$this, 'shortcode_form']);
        }
    }

    function hook_newsletter_menu_subscription($entries) {
        $entries[] = array('label' => '<i class="far fa-edit"></i> Form designer', 'url' => '?page=newsletter_forms_index', 'description' => 'Visually design your forms');
        return $entries;
    }

    function hook_admin_enqueue_scripts() {
        $url = plugins_url('newsletter-forms');
        wp_enqueue_script('tnp-forms-designer-vue', $url . '/vendor/vue/vue.global.prod.min.js', [], time());
        wp_enqueue_script('tnp-forms-designer', $url . '/assets/tnpfd.umd.js', ['tnp-forms-designer-vue'], time());
        wp_enqueue_style('tnp-forms-designer', $url . '/assets/style.css', [], time());
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Forms', '<span class="tnp-side-menu">Forms</span>', 'exist', 'newsletter_forms_index', function () {
            require __DIR__ . '/admin/index.php';
        });
        add_submenu_page(null, 'Edit', 'Edit', 'exist', 'newsletter_forms_edit', function () {
            require __DIR__ . '/admin/edit.php';
        });
    }

    function shortcode_form($attrs) {
        $form = $this->get_form($attrs['id']);
        return $this->render($form);
    }

    function get_forms() {
        $forms = $this->store->get_all($this->table);
//        foreach ($autoresponders as $autoresponder) {
//            $this->deserialize_autoresponder($autoresponder);
//        }
        return $forms;
    }

    function get_form($id) {
        $form = $this->store->get_single($this->table, $id);
        //$form->config = json_decode($form->config);
        return $form;
    }

    function deserialize_form($form) {
        
    }

    function save_form($form) {
        return $this->store->save($this->table, $form);
    }

    function delete_form($id) {
        $this->store->delete($this->table, $id);
    }

    /**
     * Returns the part of the form designer configuration that defines the standard 
     * draggable fields.
     */
    function get_designer_fields() {
        $si = NewsletterSubscription::instance();
        // Se the NewsletterSubscription class... should be done in a different way...
        $form_options = $si->get_form_options();

        $input = [
            [
                'name' => 'Email',
                'content' => '',
                'placeholder' => '',
                'label' => $form_options['email'],
                'icon' => 'IconTitle',
                'type' => 'InputText',
                'id' => 'email',
            ],
            [
                'name' => __('First name', 'newsletter'),
                'content' => '',
                'placeholder' => '',
                'label' => $form_options['name'],
                'icon' => 'IconTitle',
                'type' => 'InputText',
                'id' => 'first_name',
                'required' => true,
            ],
            [
                'name' => __('Last name', 'newsletter'),
                'content' => '',
                'placeholder' => '',
                'label' => $form_options['surname'],
                'icon' => 'IconTitle',
                'type' => 'InputText',
                'id' => 'last_name',
                'required' => true
            ],
            [
                'name' => __('Gender', 'newsletter'),
                'label' => $form_options['sex'],
                'options' => [
                    ['value' => $form_options['sex_none'], 'hidden' => false],
                    ['value' => $form_options['sex_female'], 'hidden' => false],
                    ['value' => $form_options['sex_male'], 'hidden' => false],
                ],
                'value' => '',
                'icon' => 'IconTitle',
                'type' => 'InputGender',
                'id' => 'gender',
                'required' => true
            ],
            [
                'name' => 'Privacy',
                'label' => 'Accetto l\'informativa sulla privacy',
                'placeholder' => '',
                'value' => false,
                'icon' => 'IconTitle',
                'type' => 'InputCheckBox',
                'id' => 'privacy'
            ],
            [
                'name' => __('Submit', 'newsletter'),
                'placeholder' => '',
                'label' => $form_options['subscribe'],
                'icon' => 'IconTitle',
                'type' => 'InputButton',
                'id' => 'submit'
            ]
        ];

        return $input;
    }

    // Profile extra fields
    function get_designer_extra_fields() {
        $fields = \NewsletterSubscription::instance()->get_profiles_public();

        $extraInput = [];
        foreach ($fields as $field) {
            $block = [
                'name' => $field->name,
                'placeholder' => $field->placeholder,
                'label' => $field->name,
                'icon' => 'IconTitle',
                'type' => 'InputText',
                'id' => 'np' . $field->id,
            ];

            if ($field->type === TNP_Profile::TYPE_SELECT) {
                $block['type'] = 'InputSelectExtra';

                $block['options'] = [];
                foreach ($field->options as $option) {
                    $block['options'][] = ['value' => $option];
                }
            }
            $extraInput[] = $block;
        }

        return $extraInput;
    }

    /**
     * Returns the designer configuration for the settings tab.
     * 
     * @return array
     */
    function get_designer_settings($config) {

        // Builds the list of subscription lists to be shown (only the public ones - visible or not)
        $lists = \Newsletter::instance()->get_lists_public();
        $options = [];
        foreach ($lists as $list) {
            $options[] = ['id' => $list->id, 'name' => $list->name, 'hidden' => false];
        }

        // Finds the "value" for the list fo subscription lists checking all the saved setting for the right one
        $value = [];
        foreach ($config->settings as $s) {
            if ($s->id === 'lists') {
                $value = $s->value;
            }
        }

        $settings = [
            [
                'name' => 'Referrer',
                'placeholder' => '',
                'label' => 'Referrer',
                'type' => 'InputTextSettings',
                'id' => 'referrer',
                'value' => '',
            ],
            [
                'name' => 'Link Thank you page',
                'placeholder' => 'Inserire link',
                'label' => 'Thank you page',
                'type' => 'InputTextSettings',
                'id' => 'welcome_url',
                'value' => '',
            ],
            [
                'name' => 'Assigned lists',
                'label' => 'Lista di sottoscrizioni preselezionate',
                'type' => 'InputCheckBoxListSettings',
                'value' => $value,
                'options' => $options,
                'id' => 'lists',
            ]
        ];

        return $settings;
    }

    function get_builder_config($form) {
        $titles = [
            'Input' => 'Fields',
            'Layout' => 'Other',
            'Extra' => 'Extra',
            'DropArea' => 'Form',
            'Settings' => 'Settings',
            'CustomizeElement' => 'Field settings',
            'PremiumText' => '',
            'Save' => 'Save',
            'Cancel' => 'Cancel'
        ];

        $blocks = [
            'layout' => [
                ['name' => 'Divider', 'icon' => 'IconTitle', 'type' => 'Divider', 'id' => 'Divider 0'],
                ['name' => 'Paragraph', 'icon' => 'IconTitle', 'type' => 'Paragraph', 'text' => 'Write anything', 'id' => 'Paragraph 0']
            ],
            'input' => $this->get_designer_fields(),
            'extraInput' => $this->get_designer_extra_fields()
        ];

        // The saved designed form (fields and settings)
        $t = json_decode($form->config);

        // Settings
        if (!isset($t->settings)) {
            $t->settings = [];
        }

        $lists = \Newsletter::instance()->get_lists_for_subscription();
        $options = [];
        foreach ($lists as $list) {
            $options[] = ['id' => $list->id, 'name' => $list->name, 'hidden' => false];
        }



        // Dragged and dropped fields
        $input = [];
        if (isset($t->blocks)) {
            $input = $t->blocks; // Viene dai dati salvati...
        }

        $designer_config = [
            'titles' => $titles,
            'settings' => $this->get_designer_settings($t),
            'input' => $input, // Blocks in the toolset
            'blocks' => $blocks // Blocks in the designer
        ];

        return $designer_config;
    }

    function field_label($name, $attrs, $suffix = null) {

        if (!$suffix) {
            $suffix = $name;
        }
        $buffer = '<label for="' . esc_attr($attrs['id']) . '">';
        if (isset($attrs['label'])) {
            if (empty($attrs['label'])) {
                return;
            } else {
                $buffer .= esc_html($attrs['label']);
            }
        } else {
            if (isset($this->form_options[$name])) {
                $buffer .= esc_html($this->form_options[$name]);
            }
        }
        $buffer .= "</label>\n";
        return $buffer;
    }

    function render($form) {
        $config = json_decode($form->config);
        $si = NewsletterSubscription::instance();

        $language = $this->get_current_language();
        $action = esc_attr($si->build_action_url('s'));
        $class = 'tnp-subscription tnp-form-' . $form->id;
        if ($language) {
            $class .= ' tnp-language-' . $language;
        }
        $b = '<form method="post" class="' . esc_attr($class) . '" action="' . $action . '">';

        $b .= "<input type='hidden' name='nfid' value='" . esc_attr($form->id) . "'>\n";

        foreach ($config->settings as $setting) {
            if ($setting->id === 'lists') {
                foreach ($setting->value as $id) {
                    $b .= "<input type='hidden' name='nl[]' value='" . esc_attr($id) . "'>\n";
                }
            }

            if ($setting->id === 'referrer' && !empty($setting->value)) {
                $b .= "<input type='hidden' name='nr' value='" . esc_attr($setting->value) . "'>\n";
            }

            if ($setting->id === 'welcome_url' && !empty($setting->value)) {
                $b .= "<input type='hidden' name='ncu' value='" . esc_attr($setting->value) . "'>\n";
            }
        }

        $idx = 0;

        foreach ($config->blocks as $block) {
            $idx++;
            $id = 'tnp-' . $idx;
            $esc_id = esc_attr($id);

            if ($block->type === 'Paragraph') {
                $b .= '<p>' . wp_kses_data($block->text) . '</p>';
                continue;
            }

            if ($block->type === 'Divider') {
                $b .= '<hr>';
                continue;
            }

            if (isset($block->label)) {
                $label = '<label for="' . $esc_id . '">' . esc_html($block->label) . '</label>';
            }

            if ($block->id === 'email') {
                $b .= '<div class="tnp-field tnp-field-email">';
                $b .= $label;

                $b .= '<input class="tnp-email" type="email" name="ne" id="' . $esc_id . '" value=""';
                $b .= ' placeholder="' . esc_attr($block->placeholder) . '"';
                $b .= ' required>';
                $b .= "</div>\n";

                continue;
            }

            if ($block->id === 'first_name') {
                $b .= '<div class="tnp-field tnp-field-firstname">';
                $b .= $label;

                $b .= '<input class="tnp-name" type="text" name="nn" id="' . $esc_id . '" value=""';
                $b .= ' placeholder="' . esc_attr($block->placeholder) . '"';
                if ($block->required) {
                    $b .= ' required';
                }
                $b .= '>';
                $b .= "</div>\n";
                continue;
            }

            if ($block->id === 'last_name') {
                $b .= '<div class="tnp-field tnp-field-lastname">';
                $b .= $label;

                $b .= '<input class="tnp-name" type="text" name="ns" id="' . $esc_id . '" value=""';
                $b .= ' placeholder="' . esc_attr($block->placeholder) . '"';
                if ($block->required) {
                    $b .= ' required';
                }
                $b .= '>';
                $b .= "</div>\n";
                continue;
            }

            // Se the NewsletterSubscription class... should be done in a different way...
            $form_options = $si->get_form_options();

            if ($block->id === 'gender') {
                $b .= '<div class="tnp-field tnp-field-gender">';
                $b .= $label;

                $b .= '<select name="nx" class="tnp-gender" id="tnp-gender"';
                if ($block->required) {
                    $b .= ' required ';
                }
                $b .= '>';

                $b .= '<option value=""></option>';

                $b .= '<option value="n">' . esc_html($form_options['sex_none']) . '</option>';
                $b .= '<option value="f">' . esc_html($form_options['sex_female']) . '</option>';
                $b .= '<option value="m">' . esc_html($form_options['sex_male']) . '</option>';
                $b .= '</select>';
                $b .= "</div>\n";
                continue;
            }

            if ($block->id === 'submit') {
                $b .= '<div class="tnp-field tnp-field-button">';

                $button_style = '';
//                if (!empty($attrs['button_color'])) {
//                    $button_style = 'style="background-color:' . esc_attr($attrs['button_color']) . '"';
//                }

                $b .= '<input class="tnp-submit" type="submit" value="' . esc_attr($block->label) . '" ' . $button_style . '>' . "\n";
                $b .= "</div>\n";
                continue;
            }

            if ($block->id === 'privacy') {

                $b .= '<div class="tnp-field tnp-field-checkbox tnp-field-privacy">';

                $b .= '<label>';
                $b .= '<input type="checkbox" name="ny" required class="tnp-privacy"> ';
                $url = $si->get_privacy_url();
                if ($url) {
                    $b .= '<a target="_blank" href="' . esc_attr($url) . '">';
                }
                $b .= $block->label;
                if ($url) {
                    $b .= '</a>';
                }
                $b .= '</label>';
                $b .= '</div>';

                continue;
            }

            if (strpos($block->id, 'np') === 0) {

                $number = (int) substr($block->id, 2);

                $profile = TNP_Profile_Service::get_profile_by_id($number, $language);

                if (!$profile) {
                    //return $this->build_field_admin_notice('Extra profile ' . $number . ' is not configured, cannot be shown.');
                }

                if ($profile->status == 0) {
                    //return $this->build_field_admin_notice('Extra profile ' . $number . ' is private, cannot be shown.');
                }

                $size = 20; //isset($attrs['size']) ? $attrs['size'] : '';
                $b .= '<div class="tnp-field tnp-field-profile">';
                $b .= $label;

                if (empty($block->placeholder)) {
                    $block->placeholder = $profile->placeholder;
                }

                // Text field
                if ($profile->type == TNP_Profile::TYPE_TEXT) {
                    $b .= '<input class="tnp-profile tnp-profile-' . $number . '" id="' . $esc_id . '" type="text" size="' . esc_attr($size) . '" name="np' . $number . '" placeholder="' . esc_attr($block->placeholder) . '"';
                    if (isset($block->required)) {
                        $b .= ' required';
                    }
                    $b .= '>';
                }

                // Select field
                if ($profile->type == TNP_Profile::TYPE_SELECT) {
                    $b .= '<select class="tnp-profile tnp-profile-' . $number . '" id="' . $esc_id . '" name="np' . $number . '"';
                    if (isset($block->required)) {
                        $b .= ' required';
                    }
                    $b .= '>';
                    if (!empty($block->placeholder)) {
                        $b .= '<option value="" selected disabled>' . esc_html($block->placeholder) . '</option>';
                    }
                    foreach ($profile->options as $option) {
                        $b .= '<option>' . esc_html(trim($option)) . '</option>';
                    }
                    $b .= "</select>\n";
                }

                $b .= "</div>\n";
                continue;
            }
        }

        return $b . '</form>';
    }

}
