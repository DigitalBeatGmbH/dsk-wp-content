<?php

class NewsletterLock extends NewsletterAddon {

    static $instance;
    
    function __construct($version) {
        self::$instance = $this;
        parent::__construct('lock', $version);
        $this->setup_options();
    }

    function init() {
        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_subscription', array($this, 'hook_newsletter_menu_subscription'));
            }
        }

        add_action('newsletter_action', array($this, 'hook_newsletter_action'));
        add_shortcode('newsletter_lock', array($this, 'shortcode_newsletter_lock'));

        // Lock configured for tags or categories?
        if (!empty($this->options['ids'])) {
            add_filter('the_content', array($this, 'hook_the_content'));
        }
    }

    /**
     * Compatibility code.
     */
    function hook_newsletter_action($action) {

        switch ($action) {

            case 'ul':
                $user = Newsletter::instance()->check_user();

                if ($user == null || $user->status != 'C') {
                    echo 'Subscriber not found, sorry.';
                    die();
                }

                setcookie('newsletter', $user->id . '-' . $user->token, time() + 60 * 60 * 24 * 365, '/');
                if (empty($this->options['url'])) {
                    header('Location: ' . home_url());
                } else {
                    header('Location: ' . $this->options['url']);
                }

                die();
        }
    }

    function hook_newsletter_menu_subscription($entries) {
        $entries[] = array('label' => '<i class="fa fa-lock"></i> Locked Content', 'url' => '?page=newsletter_lock_index', 'description' => __('Make your best content available only upon subscription', 'newsletter'));
        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'Locked Content', '<span class="tnp-side-menu">Locked Content</span>', 'exist', 'newsletter_lock_index', array($this, 'menu_page_index'));
    }

    function menu_page_index() {
        global $wpdb;
        require dirname(__FILE__) . '/index.php';
    }

    function hook_the_content($content) {
        global $post, $cache_stop;

        if (current_user_can('publish_posts')) {
            return $content;
        }
        
        if (!$post || !isset($post->post_name)) {
            return $content;
        }

        $ids = explode(',', str_replace(' ', '', $this->options['ids']));
        $ids = array_filter($ids);

        if (has_tag($ids) || in_category($ids) || in_array($post->post_name, $ids)) {
            $cache_stop = true;
            $user = Newsletter::instance()->check_user();
            if ($user == null || $user->status != 'C') {
                $language = $this->get_current_language();
                $key = 'message' . (empty($language)?'':'_' . $language);
                $buffer = Newsletter::instance()->replace($this->options[$key]);
                return '<div class="tnp-lock newsletter-lock">' . do_shortcode($buffer) . '</div>';
            }
        }

        return $content;
    }

    function shortcode_newsletter_lock_dummy($attrs, $content = null) {
        return $content;
    }

    function shortcode_newsletter_lock($attrs, $content = null) {
        global $hyper_cache_stop, $cache_stop;

        $hyper_cache_stop = true;
        $cache_stop = true;

        $this->found = true;

        if (current_user_can('publish_posts')) {
            return do_shortcode($content);
        }

        $user = Newsletter::instance()->check_user();
        if ($user != null && $user->status == 'C') {
            return do_shortcode($content);
        }
        
        $language = $this->get_current_language();
        $key = 'message' . (empty($language)?'':'_' . $language);

        $buffer = $this->options[$key];

        if (empty($buffer)) {
            $buffer = '[newsletter_form';
            if (isset($attrs['confirmation_url'])) {
                if ($attrs['confirmation_url'] == '#') {
                    $attrs['confirmation_url'] = $_SERVER['REQUEST_URI'];
                }
                $buffer .= ' confirmation_url="' . $attrs['confirmation_url'] . '"';
            }
            $buffer .= ']';
        } else {
            // Compatibility
            $buffer = str_ireplace('<form', '<form method="post" action="' . home_url('/') . '?na=subscribe"', $buffer);
            $buffer = Newsletter::instance()->replace($buffer, null, null, 'lock');
        }

        $buffer = do_shortcode($buffer);


        return '<div class="tnp-lock newsletter-lock">' . $buffer . '</div>';
    }

}
