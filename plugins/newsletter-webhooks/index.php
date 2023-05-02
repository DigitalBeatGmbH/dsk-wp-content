<?php
/* @var $this NewsletterWebhooks */

use TNP\Webhooks\Webhook;

defined('ABSPATH') || exit;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

if (!$controls->is_action()) {
    $controls->data = $this->options;
} else {
    if ($controls->is_action('add')) {

        $this->save_webhook($controls->data);

        $controls->add_message_saved();
        $controls->data = null;
    }

    if ($controls->is_action('delete')) {
        $webhook_id = (int) $controls->button_data;
        $this->delete_webhook($webhook_id);

        $controls->add_message_deleted();
    }

    if ($controls->is_action('test')) {
        $webhook_id = (int) $controls->button_data;

        $is_successful = $this->test_webhook($webhook_id);
        if ($is_successful) {
            $controls->add_message_done();
        } else {
            $controls->add_message(__('Webhook testing error! Enable debug mode in newsletter plugin general settings (Advanced settings tab) to check error.', 'newsletter'));
        }
    }
}

$webhooks = $this->get_webhooks();
?>

<div class="wrap tnp-webhooks" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h2>Webhooks</h2>
        <?php $controls->page_help('https://www.thenewsletterplugin.com/documentation/developers/newsletter-webhooks/') ?>
    </div>

    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>

            <div id="tabs">
                <ul>
                    <li><a href="#tabs-webhook-list">Webhooks</a></li>
                    <li><a href="#tabs-create-webhook">New</a></li>
                </ul>

                <div id="tabs-webhook-list">
                    <?php if (!empty($webhooks)): ?>
                        <table id="webhooks-table"
                               class="widefat"
                               style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th><?php _e('Description', 'newsletter') ?></th>
                                    <th><?php _e('URL', 'newsletter') ?></th>
                                    <th><?php _e('Trigger', 'newsletter') ?></th>
                                    <th><?php _e('Actions', 'newsletter') ?></th>
                                </tr>
                            </thead>
                            <?php foreach ($webhooks as $webhook): ?>
                                <tr>
                                    <td class="webhook-id"><?php echo esc_html($webhook->id) ?></td>
                                    <td><?php echo esc_html($webhook->description) ?></td>
                                    <td><?php echo esc_html($webhook->url) ?></td>
                                    <td><?php echo esc_html($webhook->events) ?></td>
                                    <td>
                                        <span id="test-webhook-<?php echo $webhook->id ?>">
                                            <?php $controls->button('test', __('Test', 'newsletter'), 'this.form.btn.value=' . $webhook->id . '; this.form.submit();') ?>
                                        </span>
                                        <span id="delete-webhook-<?php echo $webhook->id ?>">
                                            <?php $controls->button_delete($webhook->id, __('Delete', 'newsletter')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>

                <div id="tabs-create-webhook">
                    <div style="margin-top: 10px">
                        <div style="margin-bottom: 5px"><?php _e('Webhooks short description', 'newsletter') ?>:</div>
                        <?php $controls->text('description', 80); ?>
                    </div>
                    <div style="margin-top: 20px">
                        <div style="margin-bottom: 5px"><?php _e('Webhooks URL', 'newsletter') ?>:</div>
                        <div style="display: flex; align-items: center">
                            <?php $controls->text_url('url', 70); ?>
                        </div>
                    </div>
                    <div style="margin-top: 20px">
                        <div style="margin-bottom: 5px"><?php _e('Request type', 'newsletter') ?>:</div>
                        <div style="display: flex; align-items: center">
                            <?php $controls->select('http_verb', ['POST' => 'Standard POST', 'JSON' => 'Raw JSON POST']); ?>
                        </div>
                    </div>
                    <div style="margin-top: 20px">
                        <div style="margin-bottom: 5px"><?php _e('Event trigger', 'newsletter') ?>:</div>
                        <?php
                        $controls->select(
                                'events',
                                [
                                    NewsletterWebhooks::ON_SUBSCRIBE => __('On subscribe', 'newsletter'),
                                    NewsletterWebhooks::ON_UNSUBSCRIBE => __('On unsubscribe', 'newsletter'),
                                    NewsletterWebhooks::ON_ENDED_SENDING_NEWSLETTER => __('On newsletter sending completion', 'newsletter'),
                                ]
                        );
                        ?>
                    </div>
                    <div style="margin-top: 20px">
                        <?php $controls->button('add', __('Create', 'newsletter')); ?>
                    </div>
                </div>
            </div>

        </form>
    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>

</div>
