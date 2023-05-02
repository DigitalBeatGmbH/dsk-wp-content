<?php

class NewsletterComments extends NewsletterAddon {

    /**
     * @var NewsletterComments
     */
    static $instance;
    
    // To coordinate the two comment form hooks 
    var $injected = false;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('comments', $version);
        $this->setup_options();
    }

    function init() {

        if (!empty($this->options['enabled'])) {
            add_action('comment_post', array($this, 'hook_comment_post'), 10, 2);
            add_action('comment_form_submit_field', array($this, 'hook_comment_form_submit_field'), 90);
            add_action('comment_form', array($this, 'hook_comment_form'), 90);
        }

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_subscription', array($this, 'hook_newsletter_menu_subscription'));
            }
        }
    }

    function hook_newsletter_menu_subscription($entries) {
        $entries[] = array('label' => '<i class="fa fa-envelope-o"></i> WP Comments', 'url' => '?page=newsletter_comments_index', 'description' => 'Integration with blog comments');
        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'WP Comments', '<span class="tnp-side-menu">WP Comments</span>', 'manage_options', 'newsletter_comments_index', array($this, 'menu_page_index'));
    }

    function menu_page_index() {
        global $wpdb;
        require dirname(__FILE__) . '/index.php';
    }

    //function hook_comment_form() {
    function hook_comment_form_submit_field($field) {
        $this->injected = true;
        $buffer = "\n\n<div class='tnp-comments'>";

        $buffer .= '<label for="tnp-comments-checkbox"><input type="checkbox" value="1" name="newsletter" id="tnp-comments-checkbox"';
        if (!empty($this->options['checked'])) {
            $buffer .= ' checked';
        }

        $buffer .= '>&nbsp;' . $this->options['label'] . "</label></div>\n\n";
        return $buffer . $field;
    }
    
    function hook_comment_form() {
        if ($this->injected) return;
        $buffer = "\n\n<div class='tnp-comments'>";

        $buffer .= '<label for="tnp-comments-checkbox"><input type="checkbox" value="1" name="newsletter" id="tnp-comments-checkbox"';
        if (!empty($this->options['checked'])) {
            $buffer .= ' checked';
        }

        $buffer .= '>&nbsp;' . $this->options['label'] . "</label></div>\n\n";
        echo $buffer;
    }

    function hook_comment_post($comment_id, $status) {
        global $wpdb;
        $logger = $this->get_logger();
        $logger->debug('hook_comment_post start');

        // Attached image processing
        if (empty($_POST['newsletter'])) {
            $logger->debug('Subscription not requested');
            return;
        }

        if ($status !== 0 && $status !== 1) {
            $logger->debug('Apparently spam. Status: ' . $status);
            return;
        }

        $comment = get_comment($comment_id);

        $newsletter = Newsletter::instance();
        $user = $newsletter->get_user($comment->comment_author_email);

        if ($user) {
            $logger->debug('Already subscribed');
            return;
        }

        $_REQUEST['ne'] = $comment->comment_author_email;
        $_REQUEST['nn'] = $comment->comment_author;
        $_REQUEST['nr'] = 'comment';
        
        
        // With or without welcome emails
        $status = null; // Default opt in
        if ($this->options['optin'] == 'single')
            $status = 'C';
        else if ($this->options['optin'] == 'double')
            $status = 'S';

        $emails = true;
        if ($status === 'C') {
            $emails = !empty($this->options['welcome_disable']);
        }
        $user = NewsletterSubscription::instance()->subscribe($status, $emails);
        
        // Force the lists
        $user = array('id'=>$user->id);
        if (isset($this->options['lists'])) {
            foreach ($this->options['lists'] as $list)
            $user['list_' . $list] = 1;
            NewsletterSubscription::instance()->save_user($user);
        }

        $logger->debug('Subscribed');
    }

}

