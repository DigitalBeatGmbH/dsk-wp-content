<?php

header('Content-Type: text/plain;charset=UTF-8');

if (!defined('ABSPATH')) {
    include '../../../wp-load.php';
}

if (!check_admin_referer('preview'))
    wp_die('Invalid nonce key');

$module = NewsletterAutomated::$instance;

$id = $_GET['id'];
$feed = $module->get_channel($id);

$email = $module->create_email($feed, -1);

echo $email['message_text'];
