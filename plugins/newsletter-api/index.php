<?php
/* @var $this NewsletterApi */
global $wpdb;
include_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
require_once __DIR__ . '/autoloader.php';

use TNP\API\V2\TNP_REST_Authentication;
use TNP\API\V2\TNP_REST_Authentication_Repository;

$controls = new NewsletterControls();
$module = NewsletterApi::$instance;

if (!$controls->is_action()) {
    $controls->data = $module->options;
} else {
    if ($controls->is_action('save')) {
        $module->save_options($controls->data);
        $controls->add_message_saved();
    }

    if ($controls->is_action('delete_v2_key')) {
        $key_id = (int) $controls->button_data;
        TNP_REST_Authentication_Repository::getInstance()->delete($key_id);
    }
}
?>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h2>Newsletter API</h2>
        <?php $controls->page_help('https://www.thenewsletterplugin.com/documentation/developers/newsletter-api-2/') ?>
    </div>
    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>
            <div id="tabs">

                <ul>
                    <li><a href="#tab-api-v2">API v2</a></li>
                    <li><a href="#tab-api-v1">API v1</a></li>
                </ul>

                <div id="tab-api-v2" class="tab-min-height">
                    <?php if ($controls->is_action('create_v2_key')) : ?>
                        <?php
                        $users = get_users(array('fields' => array('display_name', 'ID')));
                        $users_norm = [];
                        foreach ($users as $user) {
                            $users_norm[$user->ID] = $user->display_name;
                        }
                        ?>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('Description', 'newsletter') ?></th>
                                <td><?php $controls->text('api_key_description', 50); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('User', 'newsletter') ?></th>
                                <td><?php $controls->select2('api_user', $users_norm); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Permission', 'newsletter') ?></th>
                                <td><?php $controls->select('api_user_permission', TNP_REST_Authentication::PERMISSIONS); ?></td>
                            </tr>
                        </table>
                        <p class="submit">
                            <?php $controls->button('save_v2_key', 'Save'); ?>
                        </p>
                    <?php elseif ($controls->is_action('save_v2_key')) : ?>
                        <?php
                        $user_id = (int) $controls->get_value('api_user');
                        $description = $controls->get_value('api_key_description');
                        $permission = $controls->get_value('api_user_permission');

                        $key = TNP_REST_Authentication::generate_user_api_key($user_id, $permission, $description);
                        ?>
                        <div id="tnp-api-success"><?php _e('Please save Client Key and Client Secret for basic Auth', 'newsletter') ?></div>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('Description', 'newsletter') ?></th>
                                <td><?php echo $key->get_description() ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('User', 'newsletter') ?></th>
                                <td><?php echo $key->get_user()->display_name ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Permission', 'newsletter') ?></th>
                                <td><?php echo TNP_REST_Authentication::PERMISSIONS[$key->get_permissions()] ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Client key', 'newsletter') ?></th>
                                <td><?php echo $key->get_client_key() ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Client secret', 'newsletter') ?></th>
                                <td><?php echo $key->get_client_secret() ?></td>
                            </tr>
                            <tr style="display: none">
                                <th>ID</th>
                                <td><?php echo $key->get_id() ?></td>
                            </tr>
                        </table>
                        <?php $controls->button_link($_SERVER['REQUEST_URI'], __('Return', 'newsletter')) ?>
                    <?php else: ?>
                        <p class="submit"><?php $controls->button('create_v2_key', 'Create Key'); ?></p>
                        <?php $keys = TNP_REST_Authentication_Repository::getInstance()->all() ?>
                        <?php if (!empty($keys)): ?>
                            <table id="v2-api-key-table"
                                   class="widefat"
                                   style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th><?php _e('Description', 'newsletter') ?></th>
                                        <th><?php _e('User', 'newsletter') ?></th>
                                        <th><?php _e('Permission', 'newsletter') ?></th>
                                        <th><?php _e('Client key', 'newsletter') ?></th>
                                        <th><?php _e('Actions', 'newsletter') ?></th>
                                    </tr>
                                </thead>
                                <?php foreach ($keys as $key): ?>
                                    <tr>
                                        <td class="api-key-id"><?php echo $key->get_id() ?></td>
                                        <td><?php echo $key->get_description() ?></td>
                                        <td><?php echo $key->get_user()->display_name ?></td>
                                        <td><?php echo ucwords(str_replace('_', ' & ', $key->get_permissions())) ?></td>
                                        <td><?php echo '...' . substr($key->get_client_key(), - 5) ?></td>
                                        <td>
                                            <span id="delete-key-id-<?php echo $key->get_id() ?>">
                                                <?php $controls->button('delete_v2_key', __('Delete', 'newsletter'), 'this.form.btn.value=' . $key->get_id() . '; this.form.submit();') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div id="tab-api-v1" class="tab-min-height">
                    <table class="form-table">
                        <tr>
                            <th>API Key</th>
                            <td>
                                <?php $controls->text('key', 50); ?>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <?php $controls->button_primary('save', 'Save'); ?>
                    </p>
                </div>
            </div>
        </form>
    </div>
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
