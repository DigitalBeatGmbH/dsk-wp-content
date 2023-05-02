<?php
header('Content-Type: text/html;charset=UTF-8');

$module = NewsletterLeads::$instance;

$newsletter = Newsletter::instance();

if (!empty($_GET['language'])) {
    $newsletter->set_current_language($_GET['language']);
}

$current_language = $newsletter->get_current_language();

$options = $module->get_options($current_language);

$subscription = NewsletterSubscription::instance();
$profile_options = $subscription->get_options('profile', $current_language);
?>

<div id="tnp-modal-html" class="tnp-modal">

    <h1><?php echo $options['theme_title'] ?></h1>

    <div class="tnp-popup-pre">
        <?php echo $options['theme_pre']; ?>
    </div>

    <div class="tnp-popup-main">
        <form action="#" method="post" onsubmit="tnp_leads_submit(this); return false;">

            <input type="hidden" name="nr" value="popup">

            <?php if (!empty($options['theme_list'])) { ?>
                <input name="nl[]" value="<?php echo esc_attr($options['theme_list']) ?>" type="hidden">
            <?php } ?>

            <?php if (isset($options['theme_field_name'])) { ?>
                <div class="tnp-field tnp-field-name">
                    <label><?php echo $profile_options['name'] ?></label>
                    <input type="text" name="nn" class="tnp-name" <?php echo $profile_options['name_rules'] == 1 ? 'required' : '' ?>>
                </div>
            <?php } ?>

            <div class="tnp-field tnp-field-email">
                <label><?php echo $profile_options['email'] ?></label>
                <input type="email" name="ne" class="tnp-email" type="email" required>
            </div>

            <?php
            if (empty($options['theme_field_privacy'])) {
                echo $subscription->get_privacy_field('<div class="tnp-field tnp-privacy-field">', '</div>');
            }
            ?>

            <div class="tnp-field tnp-field-submit">
                <input type="submit" value="<?php echo esc_attr($options['theme_subscribe_label']) ?>" class="tnp-submit">
            </div>
        </form>

        <div class="tnp-popup-post"><?php echo $options['theme_post']; ?></div>
    </div>

</div>

