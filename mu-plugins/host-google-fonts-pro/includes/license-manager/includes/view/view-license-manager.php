<?php

/**
 * Developers (or me) can add extra license registration fields using the below filter.
 *
 * The result should be an array element containing the following information:
 * - 'id'         : The ID the plugin has in EDD.
 * - 'label'      : Will be displayed besides the license input field.
 * - 'version'    : The current version of the plugin.
 * - 'plugin_file': The path to the plugin file, required for EDD updates.
 */
$licenses     = apply_filters('ffwp_license_manager_licenses', []);
$just_renewed = __('<em><a href="#" data-key="%s" data-item-id="%s" class="check-license">Just renewed</a>?</em>', $this->plugin_text_domain);
?>
<div class="wrap">
    <h1><img class="ffwp-logo" alt="Daan.dev logo" src="<?= FFWPRESS_LICENSE_MANAGER_PLUGIN_URL; ?>assets/images/daan-dev-logo.png"> <?= __('License Manager', $this->plugin_text_domain); ?></h1>
    <p>
        <?= __('Activate your license key to start using your Daan.dev products on this site.', $this->plugin_text_domain); ?>
    </p>
    <h2>
        <?= __('Don\'t have a License Key?', $this->plugin_text_domain); ?>
    </h2>
    <p>
        <?= sprintf(__('Daan.dev products require a valid license key to function properly. If somehow you came across this plugin before acquiring one, you can purchase a license on <a href="%s" target="_blank">Daan.dev</a>.'), ''); ?>
    </p>
    <h2>
        <?= __('Where can I find my License Key(s)?', $this->plugin_text_domain); ?>
    </h2>
    <p>
        <?php echo sprintf(__('Your license key(s) were sent to the email address you entered upon purchase. Can\'t find the email? Simply <a href="%s">login to your account</a> and retrieve them.', $this->plugin_text_domain), FFWPLM::FFW_PRESS_URL_LICENSE_KEYS); ?>
    </p>
    <form id="ffwp-license-form" method="post" action="options.php">
        <?php
        settings_fields(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTINGS_SECTION);
        do_settings_sections(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTINGS_SECTION);
        wp_nonce_field(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTINGS_NONCE, FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTINGS_NONCE);
        ?>

        <table class="form-table">
            <?php if (!empty($licenses)) : ?>
                <?php foreach ($licenses as $license) : ?>
                    <tr>
                        <th scope="row">
                            <label for="<?= $license['id']; ?>">
                                <?= $license['label']; ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            $valid_licenses      = FFWPLM::valid_licenses();
                            $license_data        = $valid_licenses[$license['id']] ?? null;
                            $encrypted_key       = get_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY)[$license['id']]['key'] ?? '';
                            $expiry_date         = $license_data['expires'] ?? '';
                            $expiry_date_seconds = strtotime($expiry_date);
                            $decrypted_key       = '';

                            if ($encrypted_key) {
                                $decrypted_key = FFWPLM::decrypt($encrypted_key, $license['id']);
                            }
                            ?>
                            <?php if (!$decrypted_key) : ?>
                                <i class="ffwp-icon ffwp-invalid dashicons-before dashicons-no"></i>
                            <?php elseif ($expiry_date_seconds !== false && $expiry_date_seconds > strtotime('now') && $expiry_date_seconds < strtotime('+30 days')) : ?>
                                <i class="ffwp-icon ffwp-valid ffwp-warning">!</i>
                            <?php elseif (isset($license_data['license_status']) && $license_data['license_status'] == 'valid') : ?>
                                <i class="ffwp-icon ffwp-valid dashicons-before dashicons-yes"></i>
                            <?php else : ?>
                                <i class="ffwp-icon ffwp-invalid dashicons-before dashicons-no"></i>
                            <?php endif; ?>
                            <?php if ($license_data !== null && $decrypted_key) : ?>
                                <?php
                                $key_length = strlen($decrypted_key);
                                $masked_key = substr_replace($decrypted_key, str_repeat('*', $key_length - 10), 5, $key_length - 10);
                                ?>
                                <input disabled class="ffwp-input-field" style="width: 33%;" type="text" id="<?= $license['id']; ?>" value="<?= $masked_key;  ?>" />
                                <input type="button" class="button button-secondary ffwp-deactivate-license" data-key="<?= $encrypted_key; ?>" data-item-id="<?= $license['id']; ?>" value="<?= __('Deactivate License', $this->plugin_text_domain); ?>" />
                            <?php else : ?>
                                <input name="ffwp_license_key[<?= $license['id']; ?>][key]" style="width: 33%;" type="text" id="<?= $license['id']; ?>" />
                                <input type="hidden" name="ffwp_license_key[<?= $license['id']; ?>][plugin_file]" value="<?= esc_attr($license['plugin_file']); ?>" />
                            <?php endif; ?>
                            <p class="description">
                                <?php if (!$decrypted_key) : ?>
                                    <?= // Empty license fields.
                                    sprintf(__('Enter the license key you received upon purchase to validate %s.', $this->plugin_text_domain), $license['label']); ?>
                                <?php elseif ($expiry_date == 'lifetime') : ?>
                                    <?= // Lifetime licenses.
                                    sprintf(__('Your license for %s will never expire.', $this->plugin_text_domain), $license['label']); ?>
                                <?php elseif ($expiry_date_seconds !== false && $expiry_date_seconds > strtotime('now') && $expiry_date_seconds < strtotime('+30 days')) : ?>
                                    <?= // Licenses expiring within 30 days.
                                    sprintf(__('Your license for %s will expire on %s. <a target="_blank" href="%s">Click here</a> to extend your license.', $this->plugin_text_domain), $license['label'], date_i18n(get_option('date_format'), $expiry_date_seconds), sprintf(FFWPLM::FFW_PRESS_URL_RENEW_LICENSE, $license['id'], $decrypted_key)) . ' ' . sprintf($just_renewed, $encrypted_key, $license['id']); ?>
                                <?php elseif ($expiry_date_seconds !== false && $expiry_date_seconds < strtotime('now')) : ?>
                                    <?= // Expired licenses.
                                    sprintf(__('Your license for %s expired on %s. <a target="_blank" href="%s">Click here</a> to renew your license.', $this->plugin_text_domain), $license['label'], date_i18n(get_option('date_format'), $expiry_date_seconds), sprintf(FFWPLM::FFW_PRESS_URL_RENEW_LICENSE, $license['id'], $decrypted_key)) . ' ' . sprintf($just_renewed, $encrypted_key, $license['id']); ?>
                                <?php elseif ($expiry_date) : ?>
                                    <?= // Valid, non-expired licenses.
                                    sprintf(__('Your license for %s will expire on %s.', $this->plugin_text_domain), $license['label'], date_i18n(get_option('date_format'), $expiry_date_seconds)); ?>
                                <?php else : ?>
                                    <?= // Empty license fields.
                                    sprintf(__('Enter the license key you received upon purchase to validate %s.', $this->plugin_text_domain), $license['label']); ?>
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <th scope="row">
                        <?= __('No licenses found. Are your Daan.dev installed and activated?', $this->plugin_text_domain); ?>
                    </th>
                    <td></td>
                </tr>
            <?php endif; ?>
        </table>

        <?php submit_button(_n('Activate License', 'Activate Licenses', count($licenses), $this->plugin_text_domain), 'primary', 'submit', false); ?>

        <?php if (!defined(FFWPLM::DAAN_LM_ENC_KEY_LABEL)) : ?>
            <a href="#" class="button button-secondary pulse" id="ffwp_install_enc_key"><?php echo __('Install Missing Encryption Key', 'ffwp-license-manager'); ?></a>
        <?php endif; ?>
    </form>
</div>

<style>
    .ffwp-logo {
        height: 35px;
        vertical-align: middle;
    }

    .ffwp-icon {
        position: relative;
        float: left;
        height: 17px;
        width: 23px;
        padding: 6px 5px 6px 5px;
        border-radius: 2px;
    }

    .ffwp-icon:before {
        position: absolute;
        left: 0;
        bottom: 10px;
        font-size: 32px;
        color: white;
    }

    .ffwp-icon.ffwp-valid {
        background-color: #2ECC40;
    }

    .ffwp-icon.ffwp-valid.ffwp-warning {
        background-color: #FF851B;
        text-align: center;
        font-size: 24px;
        font-style: initial;
        font-weight: bold;
        color: white;
        line-height: .6;
    }

    .ffwp-icon.ffwp-invalid {
        background-color: #FF4136;
    }

    .pulse {
        margin: 0 auto;
        animation-name: stretch;
        animation-duration: .4s;
        animation-timing-function: ease-out;
        animation-direction: alternate;
        animation-iteration-count: 6;
        animation-play-state: running;
    }

    @keyframes stretch {
        0% {
            transform: scale(1);
        }

        100% {
            transform: scale(1.25);
        }
    }
</style>