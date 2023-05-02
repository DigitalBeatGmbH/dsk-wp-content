<?php

class NewsletterWpUsers extends NewsletterAddon {

    static $instance;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('wpusers', $version);
        $this->setup_options();
    }

    function upgrade($first_install = false) {
        $opt_in = (int) NewsletterSubscription::instance()->options['noconfirmation'];
        $this->merge_defaults(['login' => 1, 'status' => $opt_in ? 'S' : 'C', 'subscribe' => 0, 'subscribe_label' => '']);
    }

    function init() {
        parent::init();

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_subscription', array($this, 'hook_newsletter_menu_subscription'));
            }

            add_action('edit_user_profile', array($this, 'hook_edit_user_profile'));
            add_filter('newsletter_lists_notes', array($this, 'hook_newsletter_lists_notes'), 10, 2);

            add_filter('manage_users_columns', function ($columns) {
                $columns['newsletter'] = 'Newsletter';
                return $columns;
            });

            add_filter('manage_users_custom_column', function ($val, $column_name, $user_id) {
                if ($column_name !== 'newsletter') {
                    return $val;
                }
                $subscriber = Newsletter::instance()->get_user_by_wp_user_id($user_id);
                if (!$subscriber) {
                    return $val;
                }
                return '<a href="admin.php?page=newsletter_users_edit&id=' . $subscriber->id . '">' . esc_html(Newsletter::instance()->get_user_status_label($subscriber)) . '</a>';
            }, 20, 3);
        }
        add_action('delete_user', array($this, 'hook_delete_user'));
        if (isset($this->options['login']) && $this->options['login']) {
            add_action('wp_login', array($this, 'hook_wp_login'));
        }
        if (!empty($this->options['subscribe']) && $this->options['subscribe'] != 1) {
            add_action('register_form', array($this, 'hook_register_form'));
        }
        // The hook is always active so the module can be activated only on registration (otherwise we should check that
        // option on every page load. The registration code should be moved inside the module...
        add_action('user_register', array($this, 'hook_user_register'));

        add_filter('newsletter_send_user', array($this, 'hook_newsletter_send_user'));
    }

    function get_logger() {
        $logger = parent::get_logger();
        if (!empty($this->options['log_level'])) {
            if ($this->options['log_level'] > $logger->level) {
                $logger->level = $this->options['log_level'];
            }
        }
        return $logger;
    }

    function get_label($key) {
        if (!empty($this->options[$key])) {
            return $this->options[$key];
        }

        switch ($key) {
            case 'subscribe_label': return __('Subscribe to our newsletter', 'newsletter-wpusers');
        }
    }

    /**
     *
     * @param TNP_User $user
     */
    function hook_newsletter_send_user($user) {
        global $wpdb;

        if (empty($user->wp_user_id)) {
            return $user;
        }
        $logger = $this->get_logger();

        //$logger->debug('send> Has wp_user_id: ' . $user->wp_user_id);
        // TODO: possibly name extraction
        $wp_user_email = $wpdb->get_var($wpdb->prepare("select user_email from $wpdb->users where id=%d limit 1", $user->wp_user_id));

        if (!empty($wp_user_email)) {
            $user->email = $wp_user_email;
            //$logger->debug('send> Email replaced with: ' . $user->email);
            $name = get_user_meta($user->wp_user_id, 'first_name', true);
            if (!empty($name)) {
                $user->name = $name;
            }
            $surname = get_user_meta($user->wp_user_id, 'last_name', true);
            if (!empty($surname)) {
                $user->surname = $surname;
            }
        } else {
            //$logger->debug('send> This WP user has not an email');
        }
        return $user;
    }

    function hook_newsletter_lists_notes($notes, $list_id) {
        if (!isset($this->options['lists'])) {
            return $notes;
        }
        foreach ($this->options['lists'] as $list) {
            if ($list == $list_id) {
                $notes[] = 'Assigned by WP Users Addon';
                return $notes;
            }
        }
        return $notes;
    }

    function hook_edit_user_profile($wp_user) {
        global $wpdb;
        if (!current_user_can('administrator')) {
            return;
        }
        echo '<h3>Connected subscriber</h3>';
        $newsletter = Newsletter::instance();
        $user = $newsletter->get_user_by_wp_user_id($wp_user->ID);
        if (!$user) {
            echo '<p>No subscriber linked to this user.</p>';
            return;
        }

        echo '<p>Subscriber #' . $user->id . ' connected. <a href="admin.php?page=newsletter_users_edit&id=' . $user->id . '">Edit</a>.</p>';
    }

    function hook_plugin_action_links($links) {
        $settings_link = '<a href="admin.php?page=' . $this->prefix . '_index">' . __('Settings') . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

    function hook_newsletter_menu_subscription($entries) {
        $entries[] = array('label' => '<i class="fab fa-wordpress"></i> WP Users Integration', 'url' => '?page=newsletter_wpusers_index', 'description' => 'Subscribe on WP registration');
        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'WP Users Integration', '<span class="tnp-side-menu">WP Users Integration</span>', 'manage_options', 'newsletter_wpusers_index', array($this, 'menu_page_index'));
    }

    function menu_page_index() {
        global $wpdb;
        require dirname(__FILE__) . '/index.php';
    }

    /**
     * See wp-includes/user.php function wp_signon().
     */
    function hook_wp_login($user_login) {
        $logger = $this->get_logger();

        $logger->debug('Logged in user: ' . $user_login);
        $wp_user = get_user_by('login', $user_login);
        if (!empty($wp_user)) {
            //$logger->info($wp_user);
            // We have a user able to login, so his subscription can be confirmed if not confirmed
            $user = Newsletter::instance()->get_user($wp_user->user_email);
            if (empty($user)) {
                $logger->debug('No connected subscription found');
            } else {
                if ($user->status == 'S') {
                    $logger->debug('Confirming connected subscription');
                    NewsletterSubscription::instance()->confirm($user->id, $this->options['welcome'] == 1);
                } else {
                    $logger->debug('Logged in user was not waiting for confirmation');
                }
            }
        }
    }

    function hook_delete_user($id) {
        global $wpdb;
        //$logger = $this->get_logger();
        //$logger->debug('User deletion: ' . $id);
        if ($this->options['delete'] == 1) {
            //$logger->debug('Subscriber deletion');
            $res = $wpdb->delete(NEWSLETTER_USERS_TABLE, ['wp_user_id' => $id]);
            //$logger->debug('Deleted: ' . $res);
        }
    }

    function hook_register_form() {


        echo '<p>';
        echo '<label><input type="checkbox" value="1" name="newsletter"';
        if ($this->options['subscribe'] == 3) {
            echo ' checked';
        }
        echo '>&nbsp;';
        echo $this->get_label('subscribe_label');
        echo '</label></p>';
    }

    function hook_user_register($wp_user_id) {
        global $wpdb;
        //Could hook be called multiple times?!
        static $last_wp_user_id = 0;

        $logger = $this->get_logger();

        $logger->info('New user registration with id ' . $wp_user_id);
        
        $res = apply_filters('newsletter_wpusers_register', true);
        
//        if (!$res) {
//            $logger->info('Subscription on registration blocked by filter');
//            return;
//        }

        // If the integration is disabled...
        if ($this->options['subscribe'] == 0) {
            $logger->info('The integration is disabled');
            return;
        }

        if ($last_wp_user_id == $wp_user_id) {
            $logger->fatal('Called twice with the same user id!');
            return;
        }

        $last_wp_user_id = $wp_user_id;

        // If not forced and the user didn't choose the newsletter...
        if ($this->options['subscribe'] != 1) {
            if (!isset($_REQUEST['newsletter'])) {
                $logger->info('Opt-in checkbox required but not set');
                $logger->debug($_REQUEST);
                return;
            }
        }

        $wp_user = $wpdb->get_row($wpdb->prepare("select * from $wpdb->users where id=%d limit 1", $wp_user_id));
        if (empty($wp_user)) {
            $logger->fatal('User not found?!');
            return;
        }

        // Yes, some registration procedures allow empty email
        if (!NewsletterModule::is_email($wp_user->user_email)) {
            $logger->error('User without a valid email?!');
            return;
        }

        $subscription_module = NewsletterSubscription::instance();
        $subscription = $subscription_module->get_default_subscription();
        $subscription->optin = $this->options['status'] == 'C' ? 'single' : 'double';
        $subscription->send_emails = ( $this->options['status'] == 'S' && $this->options['confirmation'] == 1 ) || ( $this->options['status'] == 'C' && $this->options['welcome'] == 1 );

        $subscription->data->email = $wp_user->user_email;
        $subscription->data->name = get_user_meta($wp_user_id, 'first_name', true);
        $subscription->data->surname = get_user_meta($wp_user_id, 'last_name', true);
        $subscription->data->referrer = 'registration';

        if (!empty($this->options['lists'])) {
            foreach ($this->options['lists'] as $list) {
                $subscription->data->lists['' . $list] = 1;
            }
        }

        $user = NewsletterSubscription::instance()->subscribe2($subscription);

        if ($user instanceof WP_Error) {
            $logger->fatal('Unable to create the subscription ');
            $logger->fatal($user);
            return;
        }

        // Now we associate it with wp
        Newsletter::instance()->set_user_wp_user_id($user->id, $wp_user_id);
    }

}
