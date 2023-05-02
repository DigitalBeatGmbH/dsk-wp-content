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

<div class="tnp-modal">

    <h1><?php echo $options['theme_title'] ?></h1>

    <div class="tnp-popup-pre">
        <?php echo $options['theme_pre']; ?>
    </div>

    <div class="tnp-popup-main">
        <form action="<?php echo $subscription->get_subscribe_url() ?>" method="post" id="tnp-popup-form">

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

<script type="text/javascript">
    jQuery("#tnp-popup-form").submit(function (e) {
        e.preventDefault();
        var form = jQuery('#tnp-popup-form').serialize();
        jQuery('.tnp-popup-main').html('<img src="<?php echo site_url("/wp-content/plugins/newsletter-leads/images/tnp-popup-loader.png"); ?>" class="tnp-popup-loader" alt="loading..." />');
        jQuery.post("<?php echo home_url('/') . '?na=ajaxsub' ?>", form)
        .done(function (data) {
            jQuery('.tnp-modal').html('<div class="tnp-success-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="120px" height="120px" viewBox="0 0 48 48"><g ><path fill="#fff" d="M22,45C10.4209,45,1,35.57959,1,24S10.4209,3,22,3c3.91211,0,7.72852,1.08301,11.03809,3.13184'
                + 'c0.46973,0.29053,0.61426,0.90674,0.32422,1.37646c-0.29102,0.47021-0.90723,0.61426-1.37695,0.32373'
                + 'C28.99219,5.97949,25.54004,5,22,5C11.52344,5,3,13.52344,3,24s8.52344,19,19,19s19-8.52344,19-19'
                + 'c0-1.69238-0.22266-3.37207-0.66211-4.99268c-0.14453-0.5332,0.16992-1.08252,0.70312-1.22705'
                + 'c0.53418-0.14209,1.08301,0.1709,1.22656,0.70361C42.75391,20.2749,43,22.13086,43,24C43,35.57959,33.5791,45,22,45z"/>'
                + '<path fill="#72C472" d="M22,29c-0.25586,0-0.51172-0.09766-0.70703-0.29297l-8-8c-0.39062-0.39062-0.39062-1.02344,0-1.41406'
                + 's1.02344-0.39062,1.41406,0L22,26.58594L43.29297,5.29297c0.39062-0.39062,1.02344-0.39062,1.41406,0s0.39062,1.02344,0,1.41406'
                + 'l-22,22C22.51172,28.90234,22.25586,29,22,29z"/></g></svg></div>' + data);
        })
        .fail(function (xhr, status, error) {
            jQuery('.tnp-modal').html(status + ' - ' + error);
        });

    });
</script>

