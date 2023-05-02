<?php
defined('ABSPATH') || exit;

if (isset($_GET['id'])) {
    include __DIR__ . '/edit.php';
    return;
}

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$list = $this->get_forms();
?>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>

    <div id="tnp-heading">
        <h3>Ninja Forms Integration</h3>
        <h2>Form List</h2>

        <?php $controls->show(); ?>

        <p>
            See the <a href="https://www.thenewsletterplugin.com/documentation/addons/integrations/ninjaforms-extension/" target="_blank">official documentation</a>
            to correctly connect a Ninja Forms form to Newsletter.
        </p>
    </div>

    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>

            <table class="widefat" style="width: auto">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($list as $item) { ?>
                        <tr>
                            <td>
                                <?php echo esc_html($item->id) ?>
                            </td>
                            <td>
                                <?php echo esc_html($item->title) ?>
                            </td>
                            <td>
                                <?php echo esc_html($item->connected ? 'Connected' : 'Not connected') ?>
                            </td>
                            <td>
                                <a class="button-primary" href="?page=newsletter_ninjaforms_index&id=<?php echo urlencode($item->id) ?>">Configure</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </form>
    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>

</div>