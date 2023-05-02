<?php
defined('ABSPATH') || exit;

/* @var $this NewsletterGeo */

@include_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$list = $wpdb->get_results("select distinct country, region from " . NEWSLETTER_USERS_TABLE . " where status='C' and country<>''");
$countries = array();
foreach ($list as $item) {
    if (!isset($countries[$item->country])) $countries[$item->country] = array();
    if (empty($item->region)) continue;
    $countries[$item->country][] = $item->region;
}
?>
<style>
    #tnp-body h3.ui-accordion-header {
        color: #000;
        margin-bottom: 0;
    }
</style>
<script>
    jQuery(function () {
        jQuery("#accordion").accordion();
    })
    </script>
<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">

        <h2>Summary of countries and regions</h2>

    </div>

    <div id="tnp-body">
        <div id="accordion">
            <?php foreach ($countries as $country=>$regions) { ?>

                <h3><?php echo esc_html($controls->countries[$country]) ?> (<?php echo $country ?>) - <?php echo count($regions)?> regions</h3>
                <div>
                    <?php foreach ($regions as $region) { ?>
                        <?php echo esc_html($region) ?>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

    </div>
</div>