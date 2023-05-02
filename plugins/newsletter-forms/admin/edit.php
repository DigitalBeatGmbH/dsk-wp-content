<?php
/* @var $this NewsletterForms */

defined('ABSPATH') || exit;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$id = (int) $_GET['id'];
$form = $this->get_form($id);

if ($controls->is_action('save')) {
    $controls->data['id'] = $form->id;
    $form = $this->save_form($controls->data);
    $controls->add_message_saved();
}

if ($controls->is_action('delete')) {
    $this->delete_form($id);
    $controls->js_redirect($this->get_admin_page_url('index'));
    return;
}

if (!$controls->is_action()) {
    $controls->set_data($form);
}
?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const {createApp} = Vue;
        createApp().use(window.tnpfdPlugin).mount('#tnpfd-app');
        <?php if (!$controls->is_action()) { ?>
            document.dispatchEvent(new Event('openDialog'));
        <?php } ?>
    });
    document.addEventListener('tnpfdsave', (e) => {
        document.getElementById('tnpc-form').act.value = 'save';
        document.getElementById('tnpc-form').submit();
    }, false);
</script>

<link href="<?php echo NEWSLETTER_URL ?>/style.css" rel="stylesheet" type="text/css">

<div class="wrap tnp-forms tnp-forms-edit" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">

        <h2><?php _e('Editing', 'newsletter') ?> <?php echo esc_html($controls->data['name']) ?></h2>

    </div>

    <div id="tnp-body">

        <form method="post" action="" id="tnpc-form">
            <div class="tnp-buttons">
                <?php $controls->button_icon_back('?page=newsletter_forms_index'); ?>
                <?php $controls->button_save(); ?>
            </div>
            <?php $controls->init(); ?>

            <div id="tabs">

                <ul>
                    <li><a href="#tabs-general"><?php _e('General', 'newsletter') ?></a></li>
                </ul>

                <div id="tabs-general" class="tnp-tab">


                    <table class="form-table">

                        <tr>
                            <th><?php _e('Name', 'newsletter'); ?></th>
                            <td>
                                <?php $controls->text('name'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Form', 'newsletter'); ?></th>
                            <td>
                                <a href="javascript:void(0)" class="button-primary" onclick="document.dispatchEvent(new Event('openDialog'))"><?php _e('Edit', 'newsletter'); ?></a>
                                <?php $controls->hidden('config'); ?>
                            </td>
                        </tr>

                    </table>
                </div>

            </div>

            <div class="tnp-buttons">
                <?php $controls->button_save(); ?>
                <?php $controls->button_delete(); ?>
            </div>

        </form>


    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
<?php
add_action('admin_footer', function () use ($form) {
    echo '<div id="tnpfd-app" style="position: absolute; top: 0; left: 0; z-index: 10000; padding: 20px"><form-designer-dialog target="options-config" :debug="false" :config="', esc_attr(json_encode($this->get_builder_config($form))), '"></form-designer-dialog></div>';
}, 10000);
