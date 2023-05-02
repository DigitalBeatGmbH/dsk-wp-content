<?php


defined('ABSPATH') || exit; 

header('Content-Type: text/html;charset=UTF-8');

if (!check_admin_referer('preview')) {
    wp_die('Invalid nonce key');
}
$module = NewsletterAutomated::$instance;

$id = $_GET['id'];
$feed = $module->get_channel($id);

if (isset($_GET['real'])) {
    $email = $module->create_email($feed, $feed->last_run);
} else {
    $email = $module->create_email($feed, -1);
}

if (empty($email['message'])) {
    if ($feed->theme_type == NewsletterAutomated::THEME_TYPE_CLASSIC) {
        echo '<p>The theme has not found new content since last newsletter to generate a new message.</p>';
    } else {
        echo '<p>The template has one or more required blocks which have not found new content and stopped the newsletter generation.</p>';
    }
}
else {
    echo str_replace('{email_subject}', $email['subject'], $module->inline_css($email['message']));
}