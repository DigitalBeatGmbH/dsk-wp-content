<?php

include '../../../wp-load.php';

$module = NewsletterApi::$instance;
$newsletter = Newsletter::instance();

$key = stripslashes($_REQUEST['nk']);
if (empty($module->options['key']) || $key != $module->options['key'])
    die('Wrong API key');

$email = $newsletter->normalize_email(stripslashes($_REQUEST['ne']));
$r = $wpdb->query($wpdb->prepare("delete from " . NEWSLETTER_USERS_TABLE . " where email=%s", $email));
die($r = 0 ? 'ko' : 'ok');