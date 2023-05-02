<?php

global $wpdb;

echo '<!-- newsletter list -->';

/* @var $emails TNP_Email[] */
$emails = $wpdb->get_results($wpdb->prepare("select id, subject, s.status, s.error, s.open, s.time from " . NEWSLETTER_EMAILS_TABLE . " e join " . NEWSLETTER_SENT_TABLE . " s on s.email_id=e.id and s.user_id=%d order by s.time desc", $user_id));
echo '<table class="widefat" id="user-newsletters">';
echo '<thead><tr><th>ID</th><th>Subject</th><th>Sent at</th><th>Delivered</th><th>Read</th><th>Clicked</th><th>Error</th></tr></thead>';
foreach ($emails as $email) {
    
    echo '<tr>';
    echo '<td>';
    echo $email->id;
    echo '</td>';

    echo '<td>';
    echo esc_html($email->subject);
    echo '</td>';
    
    echo '<td>';
    echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $email->time + get_option('gmt_offset') * 3600);
    echo '</td>';
 
    echo '<td>';

    echo $email->status ? '<span style="font-size: 1.5em; font-weight: bold; color: #990000">&#10007;</span>' : '<span style="font-size: 1.5em; font-weight: bold; color: #009900">&#10003;</span>';
    echo '</td>';

    echo '<td>';
    echo $email->open > 0 ? '<span style="font-size: 1.5em; font-weight: bold; color: #009900">&#10003;</span>' : '';
    echo '</td>';

    echo '<td>';
    echo $email->open == 2 ? '<span style="font-size: 1.5em; font-weight: bold; color: #009900">&#10003;</span>' : '';
    echo '</td>';

    echo '<td>';
    echo esc_html($email->error);
    echo '</td>';

    echo '<tr>';
}
echo '</table>';
?>