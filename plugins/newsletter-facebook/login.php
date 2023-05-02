<?php
header('Content-Type: text/html;charset=UTF-8');
$code = $_REQUEST["code"];

// Protect against plugin intercepting the "code" parameter and doing bad things...
unset($_REQUEST["code"]);
unset($_GET["code"]);



ob_start();
include '../../../wp-load.php';
ob_end_clean();


/* @var $module NewsletterFacebook */
$module = NewsletterFacebook::$instance;
$logger = $module->get_logger();

$r = session_start();
if (!$r) {
    $logger->fatal('Cannot start a session');
    die('Cannot start a session');
}

$url = plugins_url('newsletter-facebook') . "/login.php";

if (empty($code)) {
    $_SESSION['state'] = md5(uniqid(rand(), true));
    $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];

    header("Location: https://www.facebook.com/v2.8/dialog/oauth?client_id=" . $module->options['app_id'] . "&redirect_uri=" .
            $url . "&state=" . $_SESSION['state'] . "&scope=email");
} else {
    if (empty($_SESSION['state']) || ($_SESSION['state'] !== $_REQUEST['state'])) {
        $logger->info("Wrong state");
        die("No state, hack attempt?");
    }

    $token_url = "https://graph.facebook.com/v2.8/oauth/access_token?"
            . "client_id=" . $module->options['app_id'] . "&redirect_uri=" . urlencode($url)
            . "&client_secret=" . $module->options['app_secret'] . "&code=" . $code;

    $response = wp_remote_get($token_url);

    /* @var $response WP_Error */
    if (is_wp_error($response)) {
        $logger->fatal($response);
        die('Unable to contact Facebook: ' . $response->get_error_message());
    }

    if (wp_remote_retrieve_response_code($response) != 200) {
        $logger->fatal($response);
        die('Facebook returned an error (see the logs): ' . wp_remote_retrieve_response_code($response) . ' ' . wp_remote_retrieve_response_message($response));
    }
    
    $logger->debug($response);


    $params = json_decode(wp_remote_retrieve_body($response));
    $detail_url = "https://graph.facebook.com/me?fields=email,last_name,first_name,gender&access_token=" . $params->access_token;


    $response = wp_remote_get($detail_url);

    if (is_wp_error($response)) {
        $logger->fatal($response);
        die('Unable to contact Facebook: ' . $response->get_error_message());
    }

    if (wp_remote_retrieve_response_code($response) != 200) {
        $logger->fatal($response);
        die('Facebook returned an error (see the logs): ' . wp_remote_retrieve_response_code($response) . ' ' . wp_remote_retrieve_response_message($response));
    }

    $fb_user = json_decode(wp_remote_retrieve_body($response));

    if (!$fb_user) {
        $logger->fatal($response);
        die('Unable to decode the Facebook response (see logs)');
    }

    if (empty($fb_user->email)) {
        $response = wp_remote_get('https://graph.facebook.com/v2.8/me/permissions?access_token=' . $params->access_token, array('method' => 'DELETE'));
        if (is_wp_error($response)) {
            $logger->fatal($response);
            die('Unable to contact Facebook: ' . $response->get_error_message());
        }
        if (wp_remote_retrieve_response_code($response) != 200) {
            $logger->fatal($response);
            die('Facebook returned an error (see the logs): ' . wp_remote_retrieve_response_code($response) . ' ' . wp_remote_retrieve_response_message($response));
        }
        ?>
        <!doctype html>
        <html>
            <head>
                <style>
                    * {
                        font-family: sans-serif;
                    }
                    .container {
                        width: 500px;
                        max-width: 100%;
                        margin: 30px auto;
                        text-align: center;
                    }
                    .try-again-button {
                        display: inline-block;
                        padding: 10px;
                        background-color: darkblue;
                        color: white;
                        text-decoration: none;
                        font-size: 14px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Facebook Connection Error</h1>
                    <p>
                        Sorry but Facebook does not returned your email. Check if your email is verified in your Facebook account or if you declined the permission you can try again.
                    </p>
                    <p>
                        <a class="try-again-button" href="<?php echo plugins_url('newsletter-facebook') ?>/login.php">Try again</a>
                    </p>
                </div>
            </body>
        </html>
        <?php
        die();
    }
    
    $logger->debug($response);

    // Subscribe the user here...
    $_REQUEST['ne'] = $fb_user->email;
    $_REQUEST['nr'] = 'facebook';
    $_REQUEST['nn'] = $fb_user->first_name;
    $_REQUEST['ns'] = $fb_user->last_name;
    switch ($fb_user->gender) {
        case 'male':
            $_REQUEST['nx'] = 'm';
            break;
        case 'female':
            $_REQUEST['nx'] = 'f';
            break;
        default:
            $_REQUEST['nx'] = 'n';
    }

    $user = NewsletterSubscription::instance()->subscribe('C', $module->options['welcome'] == 1);
    if ($user->status == 'E') {
        NewsletterSubscription::instance()->show_message('error', $user->id);
    }
    if ($user->status == 'A') {
        NewsletterSubscription::instance()->show_message('already_confirmed', $user->id);
    }

    if ($module->options['redirect'] == 1 && !empty($_SESSION['referer'])) {
        header('Location: ' . $_SESSION['referer']);
    } else {
        if ($user->status == 'C') {
            NewsletterSubscription::instance()->show_message('confirmed', $user->id);
        }
    }
}