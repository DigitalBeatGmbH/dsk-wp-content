<?php

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */

defined('ABSPATH') || exit;

class FFWPLM_Admin
{
    const FFWP_LICENSE_MANAGER_SETTINGS_SECTION      = 'ffwp-license-manager';
    const FFWP_LICENSE_MANAGER_SETTINGS_NONCE        = 'ffwp_license_manager_nonce';
    const FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY   = 'ffwp_license_key';
    const FFWP_LICENSE_MANAGER_OPTION_VALID_LICENSES = 'ffwp_valid_licenses';
    const FFWP_LICENSE_MANAGER_OPTION_CYPHER         = 'ffwp_encryption_cypher';
    const FFWP_LICENSE_MANAGER_OPTION_DB_VERSION     = 'ffwp_db_version';
    const FFWP_LICENSE_MANAGER_NOTICE_COUNT          = 'ffwp_notice_count';

    /** 
     * @since v1.8.0 Contains an array of plugin basenames and on which free plugin they depend.
     * @since v1.9.1 This array should only contain plugins that actually have parents!
     * 
     * @var array $plugin_deps 
     */
    private $plugin_deps = [
        'caos-pro/caos-pro.php'                           => 'host-analyticsjs-local/host-analyticsjs-local.php',
        'host-google-fonts-pro/host-google-fonts-pro.php' => 'host-webfonts-local/host-webfonts-local.php',
        'omgf-additional-fonts/omgf-additional-fonts.php' => 'host-webfonts-local/host-webfonts-local.php'
    ];

    /** @var string $plugin_text_domain Used in view/view-license-manager.php */
    private $plugin_text_domain = 'ffwp-license-manager';

    /**
     * FFWPLM_Admin constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_js_scripts'));
        //add_filter('plugin_action_links_' . plugin_basename(FFWPRESS_LICENSE_MANAGER_PLUGIN_FILE), [$this, 'add_settings_link']);
        add_action('admin_menu', [$this, 'create_menu']);
        add_filter('submenu_file', [$this, 'hide_menu_item']);
        add_action('admin_notices', [$this, 'add_notice']);
        add_action('all_plugins', [$this, 'check_license_keys']);
        add_action('all_plugins', [$this, 'maybe_block_updates']);
        add_filter('wp_get_update_data', [$this, 'add_update_count'], 10, 2);
        add_filter('site_transient_update_plugins', [$this, 'add_to_update_list']);

        // Add Manage License tabs to plugins.
        add_action('caos_settings_tab', [$this, 'add_license_manager_tab'], 5);
        add_action('omgf_settings_tab', [$this, 'add_license_manager_tab'], 4);

        new FFWPLM_Admin_Functions();
    }

    /**
     * Enqueue JS scripts for Administrator Area.
     *
     * @param $hook
     */
    public function enqueue_admin_js_scripts($hook)
    {
        if ($hook == 'settings_page_ffwp-license-manager') {
            wp_enqueue_script('ffwp_license_manager_admin', plugins_url('assets/js/ffwp-license-manager-admin.js', FFWPRESS_LICENSE_MANAGER_PLUGIN_FILE), ['jquery'], FFWPRESS_LICENSE_MANAGER_STATIC_VERSION, true);
        }
    }

    /**
     * @return array
     */
    public function add_settings_link($links)
    {
        $adminUrl     = $this->generate_link();
        $settingsLink = "<a href='$adminUrl'>" . __('Manage Licenses', $this->plugin_text_domain) . "</a>";
        array_push($links, $settingsLink);

        return $links;
    }

    /**
     * Create WP menu-item
     */
    public function create_menu()
    {
        add_options_page(
            'Daan.dev License Manager',
            'Daan.dev Licenses',
            'manage_options',
            self::FFWP_LICENSE_MANAGER_SETTINGS_SECTION,
            [$this, 'create_license_manager_screen']
        );

        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Removes the menu item, but keeps the link reachable.
     * 
     * @param mixed $submenus 
     * @return void 
     */
    public function hide_menu_item($submenus)
    {
        remove_submenu_page('options-general.php', self::FFWP_LICENSE_MANAGER_SETTINGS_SECTION);
    }

    /**
     *
     */
    public function add_license_manager_tab()
    {
        $this->generate_tab('dashicons-admin-network', 'Manage License');
    }

    /**
     * @param      $id
     * @param null $icon
     * @param null $label
     */
    private function generate_tab($icon = null, $label = null)
    {
?>
        <a class="nav-tab dashicons-before <?= $icon; ?>" href="<?= $this->generate_link(); ?>">
            <?= $label; ?>
        </a>
    <?php
    }

    /**
     * @param $tab
     *
     * @return string
     */
    private function generate_link()
    {
        return admin_url('options-general.php?page=ffwp-license-manager');
    }

    /**
     *
     */
    public function create_license_manager_screen()
    {
        include_once('view/view-license-manager.php');
    }

    /**
     * @throws ReflectionException
     */
    public function register_settings()
    {
        foreach ($this->get_settings() as $constant => $value) {
            register_setting(
                self::FFWP_LICENSE_MANAGER_SETTINGS_SECTION,
                $value
            );
        }
    }

    /**
     * Get all settings for the current section using the constants in this class.
     *
     * @return array
     * @throws ReflectionException
     */
    public function get_settings()
    {
        $reflection = new ReflectionClass($this);
        $constants  = $reflection->getConstants();
        $needle     = 'FFWP_LICENSE_MANAGER_SETTING_';

        return array_filter(
            $constants,
            function ($key) use ($needle) {
                return strpos($key, $needle) !== false;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Add notice to admin screen.
     */
    public function add_notice()
    {
        FFWPLM_Admin_Notice::print_notice();
    }

    /**
     * This function makes sure the bottom border of the row is removed for plugins where a notice should be displayed.
     * 
     * @return void 
     */
    public function check_license_keys($active_plugins)
    {
        $premium_plugins = apply_filters('ffwp_license_manager_licenses', []);
        $valid_licenses  = FFWPLM::valid_licenses();

        foreach ($premium_plugins as $plugin_data) {
            $plugin_id = $plugin_data['id'] ?? null;

            if (!$plugin_id) {
                continue;
            }

            $expiry_date = isset($valid_licenses[$plugin_id]['expires']) ? strtotime($valid_licenses[$plugin_id]['expires']) : '';

            // Check if a license is entered.
            if (
                !isset($valid_licenses[$plugin_id])
                || (isset($valid_licenses[$plugin_id]) && $expiry_date > strtotime('now') && $expiry_date < strtotime('+30 days'))
                || (isset($valid_licenses[$plugin_id]) && $valid_licenses[$plugin_id]['license_status'] == 'invalid')
            ) {
                // Set update element to true, to remove bottom border in row.
                $active_plugins[plugin_basename($plugin_data['plugin_file'])]['update'] = true;

                add_action('after_plugin_row_' . plugin_basename($plugin_data['plugin_file']), [$this, 'add_license_notices'], 10, 3);
            }
        }

        return $active_plugins;
    }

    /**
     * This function does nothing more than insert an action IF an update for an Daan.dev premium plugin
     * is available.
     * 
     * @since v1.8.0
     * 
     * @return void 
     */
    public function maybe_block_updates($active_plugins)
    {
        $premium_plugins = apply_filters('ffwp_license_manager_licenses', []);

        foreach ($premium_plugins as $plugin_data) {
            $plugin_file = plugin_basename($plugin_data['plugin_file']);

            // Needs to run before wp_plugin_update_row(), which runs at priority 10.
            add_action("after_plugin_row_{$plugin_file}", [$this, 'maybe_block_plugin_update'], 1);
        }

        return $active_plugins;
    }

    /**
     * If a plugin is available for a premium plugin, this plugin checks if a plugin is available
     * for its parent as well.
     * 
     * If so, the parent needs to be updated first.
     * 
     * @since v1.8.0
     * 
     * @param string $file 
     * 
     * @return false|void 
     */
    public function maybe_block_plugin_update($file)
    {
        $available_updates = get_site_transient('update_plugins');
        $parent            = '';

        /**
         * @since v1.9.1 Some premium plugins are orphans, i.e. they don't have parents.
         */
        if (isset($this->plugin_deps[$file])) {
            $parent = $this->plugin_deps[$file];
        }

        if (!$parent) {
            return false;
        }

        /**
         * We only have to adjust the plugin update row if both plugins have an update available.
         */
        if (!isset($available_updates->response[$file]) || !isset($available_updates->response[$parent])) {
            // Make sure current plugins transient data is refreshed at next pageload.
            unset($available_updates->response[$file]);
            set_site_transient('update_plugins', $available_updates);

            return false;
        }

        // Block automatic update.
        $available_updates->response[$file]->package = '';

        set_site_transient('update_plugins', $available_updates);

        add_action("in_plugin_update_message-{$file}", [$this, 'append_update_notice']);
    }

    /**
     * @return void 
     */
    public function append_update_notice($plugin_data)
    {
        $plugin_file = $plugin_data['plugin'];
        $parent      = $this->plugin_deps[$plugin_file];
        $parent_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $parent);

        printf(' <strong><em>' . __('After updating %s, refresh this page to update.', $this->plugin_text_domain) . '</em></strong>', $parent_data['Name']);
    }

    /**
     * Checks if licenses are expired and/or soon to expire and adds it to the plugins updates counts.
     * 
     * @param mixed $update_data 
     * @param mixed $plugins 
     * @return mixed 
     */
    public function add_update_count($update_data, $plugins)
    {
        if (isset($_GET['plugin_status']) && $_GET['plugin_status'] == 'upgrade') {
            return $update_data;
        }

        $valid_licenses = FFWPLM::valid_licenses();

        foreach ($valid_licenses as $plugin_id => $plugin_data) {
            // Check if a license is entered.
            if (!isset($valid_licenses[$plugin_id])) {
                $update_data['counts']['plugins']++;
            }

            $expiry_date = isset($valid_licenses[$plugin_id]['expires']) ? strtotime($valid_licenses[$plugin_id]['expires']) : '';

            // Check if license will expire soon.
            if (isset($valid_licenses[$plugin_id]) && $expiry_date > strtotime('now') && $expiry_date < strtotime('+30 days')) {
                $update_data['counts']['plugins']++;
            }

            // Check if license is expired.
            if (isset($valid_licenses[$plugin_id]) && $valid_licenses[$plugin_id]['license_status'] == 'invalid') {
                $update_data['counts']['plugins']++;
            }
        }

        return $update_data;
    }

    /**
     * Adds premium plugins with expired license to the Update Available list.
     */
    public function add_to_update_list($transient)
    {
        if ($transient == false) {
            return $transient;
        }

        $valid_licenses = FFWPLM::valid_licenses();

        foreach ($valid_licenses as $plugin_id => $plugin_data) {
            $plugin_file = plugin_basename($plugin_data['plugin_file']);
            $expiry_date = isset($valid_licenses[$plugin_id]['expires']) ? strtotime($valid_licenses[$plugin_id]['expires']) : '';

            // Check if a license is entered.
            if (
                !isset($valid_licenses[$plugin_id])
                || (isset($valid_licenses[$plugin_id]) && $expiry_date > strtotime('now') && $expiry_date < strtotime('+30 days'))
                || (isset($valid_licenses[$plugin_id]) && $valid_licenses[$plugin_id]['license_status'] == 'invalid')
            ) {
                $transient->response[$plugin_file] = (object) [
                    'slug'        => pathinfo($plugin_data['plugin_file'])['basename'],
                    'plugin'      => $plugin_file,
                    'new_version' => ''
                ];

                add_action('in_plugin_update_message-' . $plugin_file, [$this, 'append_message'], 10, 2);
            }
        }

        return $transient;
    }

    /**
     * This little hack hides the "There is a new version..." notice for each update row, so we can display only our license notice.
     * 
     * @param mixed $plugin_date 
     * @param mixed $response 
     * @return void 
     */
    public function append_message($plugin_date, $response)
    {
        $row_id = str_replace('.', '\.', $response->slug);
    ?>
        <style>
            #<?php echo $row_id; ?>-update {
                display: none;
            }
        </style>
    <?php
    }

    /**
     * 
     * @param mixed $file 
     * @param mixed $plugin_data 
     * @param mixed $status 
     * @return void 
     */
    public function add_license_notices($file, $plugin_data, $status)
    {
        $slug            = $plugin_data['slug'] ?? '';
        $premium_plugins = apply_filters('ffwp_license_manager_licenses', []);
        $valid_licenses  = FFWPLM::valid_licenses();
        $notice          = '';
        $class           = 'error';

        /*

        foreach ($premium_plugins as $plugin_data) {
            // Only handle current plugin.
            if (strpos($plugin_data['plugin_file'], $file) == false) {
                continue;
            }

            $plugin_id = $plugin_data['id'] ?? null;

            if (!$plugin_id) {
                continue;
            }

            // Check if a license is entered.
            if (!isset($valid_licenses[$plugin_id])) {
                $notice = __('Please enter a valid license key in order to receive plugin updates and support.', $this->plugin_text_domain);
            }

            $expiry_date = isset($valid_licenses[$plugin_id]['expires']) ? strtotime($valid_licenses[$plugin_id]['expires']) : '';

            // Check if license will expire soon.
            if (isset($valid_licenses[$plugin_id]) && $expiry_date > strtotime('now') && $expiry_date < strtotime('+30 days')) {
                $notice = sprintf(__('Your license will expire soon. <a href="%s">Extend your license</a> to keep receiving plugin updates and support.', $this->plugin_text_domain), $this->generate_link());
                $class  = 'warning';
            }

            // Check if license is expired.
            if (isset($valid_licenses[$plugin_id]) && $valid_licenses[$plugin_id]['license_status'] == 'invalid') {
                $notice = sprintf(__('Your license is expired. <a href="%s">Renew your license</a> to receive plugin updates and support.', $this->plugin_text_domain), $this->generate_link());
            }
        }

        */

        if (!$notice) {
            return;
        }
    ?>
        <tr id='license-expired-<?= $slug; ?>' class='plugin-update-tr active' data-slug='<?= $slug; ?>' data-plugin='<?= $file; ?>'>
            <td class="plugin-update colspanchange" colspan="4">
                <div class="update-message notice inline notice-<?= $class; ?> notice-alt">
                    <p>
                        <?= $notice; ?>
                    </p>
                </div>
            </td>
        </tr>
<?php
    }
}
