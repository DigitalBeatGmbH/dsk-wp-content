<?php
global $wpdb;

$countries = $wpdb->get_results("select country, count(*) as total from " . NEWSLETTER_USERS_TABLE . " where status='C' and country<>'' group by country order by total");

$data = array();
foreach ($countries as $country) {
    $data[strtolower($country->country)] = (int)$country->total;
}
?>

<p>A global overview of the geolocation data of your subscribers.</p>

<div id="tnp-map-chart" style="width: 800px; height: 500px"></div>

<script type="text/javascript">
    var world_data = <?php echo json_encode($data) ?>;
    jQuery(document).ready(function ($) {
        $('#tnp-map-chart').vectorMap({
            map: 'world_en',
            backgroundColor: null,
            color: '#ffffff',
            hoverOpacity: 0.7,
            selectedColor: '#666666',
            enableZoom: true,
            showTooltip: true,
            values: world_data,
            scaleColors: ['#C8EEFF', '#006491'],
            normalizeFunction: 'polynomial',
            onLabelShow: function (event, label, code) {
                label.text(label.text() + ': ' + world_data[code]);
            }
        });
    });
</script>
                                