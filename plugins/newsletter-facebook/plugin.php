<?php

class NewsletterFacebook extends NewsletterAddon {

    static $instance;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('facebook', $version);
        $this->setup_options();
    }
    
    function upgrade($first_install = false) {
        $this->setup_options();
        $this->save_options(array_merge(array('enabled'=>0, 'app_id'=>'', 'app_secret'=>'', 'button_label'=>'Subscribe', 'welcome'=>0, 'redirect'=>0), $this->options));
    }

    function init() {

        if (is_admin()) {
            if (current_user_can('administrator')) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_subscription', array($this, 'hook_newsletter_menu_subscription'));
            }
        } else {
            add_action('wp_head', array($this, 'hook_wp_head'), 99);
        }
        add_filter('newsletter_replace', array($this, 'hook_replace'), 10, 3);
        add_shortcode('newsletter_facebook', array($this, 'shortcode_newsletter_facebook'));
    }
    
    function shortcode_newsletter_facebook($attrs, $content) {
        if (!is_array($attrs)) $attrs = array();
        $attrs = array_merge(array('label'=>$this->options['button_label']), $attrs);
        return '<a href="' . plugins_url('newsletter-facebook') . '/login.php" class="newsletter-facebook-button">' . $attrs['label'] . '</a>';
    }

    function hook_newsletter_menu_subscription($entries) {
        $entries[] = array('label' => '<i class="fa fa-facebook"></i> Facebook', 'url' => '?page=newsletter_facebook_index', 'description' => 'Single click signup with Facebook');
        return $entries;
    }

    function hook_admin_menu() {       
        add_submenu_page('newsletter_main_index', 'Facebook', '<span class="tnp-side-menu">Facebook</span>', 'administrator', 'newsletter_facebook_index', array($this, 'menu_page_index'));
    }

    function menu_page_index() {
        global $wpdb;
        require dirname(__FILE__) . '/index.php';
    }

    function hook_replace($text, $user_id, $email_id) {
        $newsletter = Newsletter::instance();
        $text = $newsletter->replace_url($text, 'FACEBOOK_URL', plugins_url('newsletter-facebook') . "/login.php");
        $label = 'Sign up with Facebook';
        if (!empty($this->options['button_label'])) {
            $label = $this->options['button_label'];
        }
        $text = str_replace('{facebook_button}', '<a href="' . plugins_url('newsletter-facebook') . '/login.php" class="newsletter-facebook-button">' . $label . '</a>', $text);
        return $text;
    }

    function hook_wp_head() {
        echo '<style>
            a.newsletter-facebook-button, a.newsletter-facebook-button:visited, a.newsletter-facebook-button:hover {
            /*display: inline-block;*/
            background-color: #3B5998;
            border-radius: 3px!important;
            color: #fff!important;
            text-decoration: none;
            font-size: 14px;
            padding: 7px!important;
            line-height: normal;
            margin: 0;
            border: 0;
            text-align: center;
            }
            </style>';
    }

}

