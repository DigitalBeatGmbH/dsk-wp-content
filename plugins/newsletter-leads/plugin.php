<?php

class NewsletterLeads extends NewsletterAddon {

    /**
     * @return NewsletterLeads
     */
    static $instance;
    static $leads_colors = array(
        'autumn' => array('#db725d', '#5197d5'),
        'winter' => array('#38495c', '#5197d5'),
        'summer' => array('#eac545', '#55ab68'),
        'spring' => array('#80c99d', '#ee7e33'),
        'sunset' => array('#d35400', '#ee7e33'),
        'night' => array('#204f56', '#ee7e33'),
        'sky' => array('#5197d5', '#55ab68'),
        'forest' => array('#55ab68', '#5197d5'),
    );
    var $labels;
    var $vanilla = false;
    var $test = false;
    var $popup_enabled = false;
    var $bar_enabled = false;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('leads', $version);

        $this->setup_options();
        $this->vanilla = !empty($this->options['vanilla']);
        $this->test = isset($_GET['newsletter_leads']);
        $this->popup_enabled = !empty($this->options['popup-enabled']);
        $this->bar_enabled = !empty($this->options['bar-enabled']);
    }

    function upgrade($first_install = false) {
        parent::upgrade($first_install);

        $this->merge_defaults([
            'width' => 650,
            'height' => 500,
            'delay' => 2,
            'count' => 0,
            'days' => 30,
            'theme_title' => 'Subscribe to stay tuned!',
            'theme_subscribe_label' => 'Subscribe',
            'theme_popup_color' => 'winter',
            'theme_bar_color' => 'winter'
        ]);
    }

    function init() {

        parent::init();

        if (!is_admin()) {
            if ($this->popup_enabled || $this->bar_enabled || $this->test) {
                add_action('wp_footer', array($this, 'hook_wp_footer'), 99);
                add_action('wp_enqueue_scripts', array($this, 'hook_wp_enqueue_scripts'));
            }
        } else {
            if ($this->is_allowed()) {
                add_action('admin_menu', array($this, 'hook_admin_menu'), 100);
                add_filter('newsletter_menu_subscription', array($this, 'hook_newsletter_menu_subscription'));
            }
        }

        add_action('newsletter_action', [$this, 'hook_newsletter_action']);
    }

    function hook_newsletter_action($action) {
        switch ($action) {
            case 'leads-popup':
                if (!$this->vanilla) {
                    include __DIR__ . '/iframe.php';
                } else {
                    include __DIR__ . '/modal.php';
                }
                die();
        }
    }

    function hook_newsletter_menu_subscription($entries) {
        $entries[] = array('label' => '<i class="fa fa-clone"></i> Leads', 'url' => '?page=newsletter_leads_index', 'description' => 'Simple subscription systems');
        return $entries;
    }

    function hook_admin_menu() {
        $parent = 'newsletter_main_index';
        add_submenu_page($parent, 'Leads', '<span class="tnp-side-menu">Leads</span>', 'exist', 'newsletter_leads_index', array($this, 'menu_page_index'));
    }

    function menu_page_index() {
        global $wpdb;
        require dirname(__FILE__) . '/index.php';
    }

    function hook_wp_enqueue_scripts() {

        wp_enqueue_style('newsletter-leads', plugins_url('newsletter-leads') . '/css/leads.css', array(), $this->version);
        if (is_rtl()) {
            wp_enqueue_style('newsletter-leads-rtl', plugins_url('newsletter-leads') . '/css/leads-rtl.css', [], $this->version);
        }

        if (!$this->vanilla) {
            wp_enqueue_script('simplemodal', plugins_url('newsletter-leads') . '/libs/simplemodal/jquery.simplemodal.js', ['jquery'], $this->version, true);
        }

        if ($this->popup_enabled || $this->test) {
            if ($this->options['theme_popup_color'] == 'custom') {
                $theme_popup_color = array($this->options['theme_popup_color_1'], $this->options['theme_popup_color_2']);
            } else {
                $theme_popup_color = NewsletterLeads::$leads_colors[$this->options['theme_popup_color']];
            }

            ob_start();
            ?>
            #tnp-modal-content {
                height:<?php echo (int) $this->options['height']; ?>px;
                width:<?php echo (int) $this->options['width']; ?>px;
                background-color: <?php echo $theme_popup_color[0] ?> !important;
            }
            
            #tnp-modal-content input.tnp-submit {
                background-color: <?php echo $theme_popup_color[1] ?> !important;
                border: none;
                background-image: none;
                color: #fff;
                cursor: pointer;
            }
            
            #tnp-modal-content input.tnp-submit:hover {
                background-color: <?php echo $theme_popup_color[1] ?> !important;
                filter: brightness(110%);
            }
            
            #simplemodal-container {
            height:<?php echo (int) $this->options['height']; ?>px;
            width:<?php echo (int) $this->options['width']; ?>px;
            }

            .tnp-modal {
            background-color: <?php echo $theme_popup_color[0] ?> !important;
            font-family: "Lato", sans-serif;
            text-align: center;
            padding: 30px;
            }

            #simplemodal-container input.tnp-submit {
            background-color: <?php echo $theme_popup_color[1] ?> !important;
            border: none;
            background-image: none;
            color: #fff;
            cursor: pointer;
            }

            #simplemodal-container input[type="submit"]:hover {
            background-color: <?php echo $theme_popup_color[1] ?> !important;
            filter: brightness(110%);
            }

            <?php
            $css = ob_get_clean();
            wp_add_inline_style('newsletter-leads', $css);
        }

        if ($this->bar_enabled || $this->test) {
            if (isset($this->options['theme_bar_color'])) {
                if ($this->options['theme_bar_color'] == 'custom') {
                    $theme_bar_color = array($this->options['theme_bar_color_1'], $this->options['theme_bar_color_2']);
                } else {
                    $theme_bar_color = NewsletterLeads::$leads_colors[$this->options['theme_bar_color']];
                }
            }
            ob_start();
            ?>
            #tnp-leads-topbar {
            <?php if ($this->options['position'] == "top") { ?>
                top: -200px;
                transition: top 1s;
            <?php } else { ?>
                bottom: -200px;
                transition: bottom 1s;
            <?php } ?>
            }
            #tnp-leads-topbar.tnp-leads-topbar-show {
            <?php if ($this->options['position'] == "top") { ?>
                <?php if (is_admin_bar_showing()) { ?>
                    top:32px;
                <?php } else { ?>
                    top:0px;
                <?php } ?>
            <?php } else { ?>
                bottom:0px;
            <?php } ?>
            }
            #tnp-leads-topbar {
            background-color: <?php echo $theme_bar_color[0] ?> !important;
            }
            #tnp-leads-topbar .tnp-subscription-minimal input.tnp-email {
            width: auto!important;
            }
            #tnp-leads-topbar .tnp-subscription-minimal input.tnp-submit {
            background-color: <?php echo $theme_bar_color[1] ?> !important;
            width: auto!important;
            }
            <?php
            $css = ob_get_clean();
            wp_add_inline_style('newsletter-leads', $css);
        }
    }

    function hook_wp_footer() {

        // If not in test mode and the current visitor is subscribed, do not activate
        if (!$this->test) {
            $user = Newsletter::instance()->check_user();
            if ($user && $user->status == 'C') {
                return;
            }
        }

        $current_language = $this->get_current_language();

        if ($this->bar_enabled || $this->test) {
            ?>
            <div id="tnp-leads-topbar">
                <?php echo $this->getBarMinimalForm(); ?>
                <label id="tnp-leads-topbar-close" onclick="tnp_leads_close_topbar()"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 24 24"><g  transform="translate(0, 0)"><circle fill="#fff" stroke="#fff" stroke-width="1" stroke-linecap="square" stroke-miterlimit="10" cx="12" cy="12" r="11" stroke-linejoin="miter"/><line data-color="color-2" fill="#fff" stroke="#343434" stroke-width="1" stroke-linecap="square" stroke-miterlimit="10" x1="16" y1="8" x2="8" y2="16" stroke-linejoin="miter"/><line data-color="color-2" fill="none" stroke="#343434" stroke-width="1" stroke-linecap="square" stroke-miterlimit="10" x1="16" y1="16" x2="8" y2="8" stroke-linejoin="miter"/></g></svg></label>
            </div>
            <script>
                var tnp_leads_restart = <?php echo (int) $this->options['days'] * 24 * 3600 * 1000 ?>;
                var tnp_leads_test = <?php echo $this->test ? 'true' : 'false' ?>;
                function tnp_leads_close_topbar() {
                    window.localStorage.setItem('tnp-leads-topbar', '' + (new Date().getTime()));
                    document.getElementById('tnp-leads-topbar').className = '';
                }
                document.addEventListener("DOMContentLoaded", function () {
                    let time = window.localStorage.getItem('tnp-leads-topbar');
                    if (!tnp_leads_test && time !== null && (new Date().getTime()) < parseInt(time) + tnp_leads_restart) {
                        document.getElementById('tnp-leads-topbar').style.display = 'none';
                    } else {
                        document.getElementById('tnp-leads-topbar').className = 'tnp-leads-topbar-show';
                    }
                });
            </script>
            <?php
        }

        if (!$this->vanilla) {
            if ($this->popup_enabled || $this->test) {
                ?>
                <script>
                    function newsletter_set_cookie(name, value, time) {
                        var e = new Date();
                        e.setTime(e.getTime() + time * 24 * 60 * 60 * 1000);
                        document.cookie = name + "=" + value + "; expires=" + e.toGMTString() + "; path=/";
                    }
                    function newsletter_get_cookie(name, def) {
                        var cs = document.cookie.toString().split('; ');
                        var c, n, v;
                        for (var i = 0; i < cs.length; i++) {
                            c = cs[i].split("=");
                            n = c[0];
                            v = c[1];
                            if (n == name)
                                return v;
                        }
                        return def;
                    }
                    jQuery(document).ready(function () {

                <?php if ($this->test) { ?>
                            newsletter_leads_open();
                <?php } else { ?>
                            if (newsletter_get_cookie("newsletter", null) == null) {
                                var newsletter_leads = parseInt(newsletter_get_cookie("newsletter_leads", 0));
                                newsletter_set_cookie("newsletter_leads", newsletter_leads + 1, <?php echo (int) $this->options['days']; ?>);
                                if (newsletter_leads == <?php echo (int) $this->options['count']; ?>) {
                                    setTimeout(newsletter_leads_open, <?php echo $this->options['delay'] * 1000; ?>);
                                }
                            }
                <?php } ?>

                    });

                    function newsletter_leads_open() {
                        jQuery.get("<?php echo Newsletter::add_qs(home_url('/'), 'na=leads-popup&language=' . $current_language); ?>", function (html) {
                            jQuery.tnpmodal(html,
                                    {
                                        autoResize: true,
                                        barClose: true,
                                        zIndex: 99000,
                                        onOpen: function (dialog) {
                                            dialog.overlay.fadeIn('fast');
                                            dialog.container.fadeIn('slow');
                                            dialog.data.fadeIn('slow');
                                        },
                                        closeHTML: '<a class="modalCloseImg" title="Close"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 24 24"><g  transform="translate(0, 0)"><circle fill="#fff" stroke="#fff" stroke-width="1" stroke-linecap="square" stroke-miterlimit="10" cx="12" cy="12" r="11" stroke-linejoin="miter"/><line data-color="color-2" fill="#fff" stroke="#343434" stroke-width="1" stroke-linecap="square" stroke-miterlimit="10" x1="16" y1="8" x2="8" y2="16" stroke-linejoin="miter"/><line data-color="color-2" fill="none" stroke="#343434" stroke-width="1" stroke-linecap="square" stroke-miterlimit="10" x1="16" y1="16" x2="8" y2="8" stroke-linejoin="miter"/></g></svg></a>'
                                    });
                        });
                    }
                </script>
                <?php
            }
        }

        if ($this->vanilla) {
            if ($this->popup_enabled || $this->test) {
                ?>
                <div id="tnp-modal">
                    <div id="tnp-modal-content">
                        <div id="tnp-modal-close">&times;</div>
                        <div id="tnp-modal-body">
                        </div>
                    </div>
                </div>

                <script>
                    var tnp_leads_test = <?php echo $this->test ? 'true' : 'false' ?>;
                    var tnp_leads_delay = <?php echo $this->options['delay'] * 1000 ?>; // milliseconds
                    var tnp_leads_days = '<?php echo (int) $this->options['days'] ?>';
                    var tnp_leads_count = <?php echo (int) $this->options['count']; ?>;
                    var tnp_leads_url = '<?php echo Newsletter::add_qs(home_url('/'), 'na=leads-popup&language=' . $current_language) ?>';
                    var tnp_leads_post = '<?php echo home_url('/') . '?na=ajaxsub' ?>';
                </script>
                <script src="<?php echo plugins_url('newsletter-leads') ?>/public/leads.js"></script>
                <?php
            }
        }
    }

    private function getBarMinimalForm() {

        $subscription = NewsletterSubscription::instance();

        $language = $subscription->get_current_language();
        $fields = $subscription->get_options('profile', $language);
        $options = $this->get_options($language);

        if (empty($options['bar_subscribe_label'])) {
            $options['bar_subscribe_label'] = $fields['subscribe'];
        }
        if (empty($options['bar_placeholder'])) {
            $options['bar_placeholder'] = $fields['email'];
        }


        $form = '<div class="tnp tnp-subscription-minimal">';
        $form .= '<form action="' . esc_attr($subscription->get_subscribe_url()) . '" method="post">';

        if (!empty($this->options['bar_list'])) {
            $form .= "<input type='hidden' name='nl[]' value='" . esc_attr($this->options['bar_list']) . "'>\n";
        }
        $form .= '<input type="hidden" name="nr" value="leads-bar">';
        $form .= '<input type="hidden" name="nlang" value="' . esc_attr($language) . '">' . "\n";
        $form .= '<input class="tnp-email" type="email" required name="ne" value="" placeholder="' . esc_attr($options['bar_placeholder']) . '">';
        $form .= '<input class="tnp-submit" type="submit" value="' . esc_attr($options['bar_subscribe_label']) . '">';

        // If SET it DISABLES the privacy field
        if (empty($options['bar_field_privacy'])) {
            $privacy_field = $subscription->get_privacy_field();
            if (!empty($privacy_field)) {
                $form .= '<div class="tnp-privacy-field">' . $privacy_field . '</div>';
            }
        }

        $form .= "</form></div>\n";

        return $form;
    }

}
