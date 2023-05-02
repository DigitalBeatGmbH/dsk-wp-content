<?php
global $wpdb;

$email_ids = array_map('intval', explode(',', $_GET['email_ids']));

$urls = $wpdb->get_results("select url, count(distinct user_id) as number from " . NEWSLETTER_STATS_TABLE . " where url<>'' and email_id in (" . implode(',', $email_ids) . ") group by url order by number desc");
$total = $wpdb->get_var("select count(distinct user_id) from " . NEWSLETTER_STATS_TABLE . " where url<>'' and email_id in (" . implode(',', $email_ids) . ")");
?>
<table>
    <colgroup>
        <col class="w-80">
        <col class="w-10">
        <col class="w-10">
    </colgroup>
    <thead>
        <tr class="text-left">
            <th>Clicked URLs</th>
            <th>Clicks</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($urls)) : ?>
            <tr>
                <td colspan="3">No clicks by now.</td>
            </tr>
        <?php else: ?>
            <?php for ($i = 0; $i < count($urls); $i ++) : ?>
                <tr>
                    <td>
                        <a href="<?php echo esc_url($urls[$i]->url) ?>" target="_blank"><?php echo esc_html($urls[$i]->url) ?></a>
                    </td>
                    <td><?php echo $urls[$i]->number ?></td>
                    <td>
                        <?php echo NewsletterModule::percent($urls[$i]->number, $total); ?>
                    </td>
                </tr>
            <?php endfor; ?>
        <?php endif; ?>
    </tbody>
</table>
