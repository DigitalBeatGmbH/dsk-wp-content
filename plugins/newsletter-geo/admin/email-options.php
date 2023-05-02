<?php
/* @var $controls NewsletterControls */
/* @var $email TNP_Email */


$subscriber_status = 'C';
if (!empty($email->options['status'])) {
    $subscriber_status = $email->options['status'];
}

$list = $wpdb->get_results($wpdb->prepare("select country, count(*) as total from " . NEWSLETTER_USERS_TABLE . " where status=%s and country<>'' group by country order by country", $subscriber_status));

$countries = array('' => 'All');
foreach ($list as $item) {
    if (empty($item->country))
        continue;
    if (empty($controls->countries[$item->country]))
        $countries[$item->country] = $item->country . ' (' . $item->total . ')';
    else
        $countries[$item->country] = $controls->countries[$item->country] . ' (' . $item->total . ')';
}

$list = $wpdb->get_results($wpdb->prepare("select region, count(*) as total from " . NEWSLETTER_USERS_TABLE . " where status=%s and region<>'' group by region order by region", $subscriber_status));

$regions = array();
foreach ($list as $item) {
    if (empty($item->region))
        continue;
    $regions[$item->region] = $item->region . ' (' . $item->total . ')';
}

$list = $wpdb->get_results($wpdb->prepare("select city as city, count(*) as total from " . NEWSLETTER_USERS_TABLE . " where status=%s and city<>'' group by lower(city) order by lower(city)", $subscriber_status));

$cities = array();
foreach ($list as $item) {
    if (empty($item->city))
        continue;
    $cities[strtolower($item->city)] = $item->city . ' (' . $item->total . ')';
}
?>
<h3>Geolocation</h3>

<table class="form-table">
    <tr valign="top">
        <th>Country</th>
        <td>
            <?php $controls->select2('options_countries', $countries, null, true); ?>
            <p class="description">
                Some country codes could have no meaning. Not all subscribers are resolved.<br>
                If you're targeting not confirmed subscribers, save to get the correct country list.
            </p>
        </td>
    </tr>
    <tr valign="top">
        <th>Regions</th>
        <td>
            <?php $controls->select2('options_regions', $regions, null, true); ?>
        </td>
    </tr>
    <tr valign="top">
        <th>Cities</th>
        <td>
            <?php $controls->select2('options_cities', $cities, null, true); ?>
        </td>
    </tr>
</table>
