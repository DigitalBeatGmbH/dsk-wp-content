<?php
/* @var $this NewsletterReports */

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';

$coreModule = Newsletter::instance();
$statisticsModule = NewsletterStatistics::instance();
$controls = new NewsletterControls();

$email_id = (int) $_GET['id'];
$email = $coreModule->get_email($email_id);

if (!$email) {
    die('Something really wrong happened, newsletter not found');
}

if (!isset($controls->data['status'])) {
    if (isset($_GET['status'])) {
        $controls->data['status'] = $_GET['status'];
    } else {
        $controls->data['status'] = '';
    }
}

$items_per_page = 20;

$count = $this->get_subscriber_count([
    'email_id' => $email->id,
    'status' => $controls->data['status']
        ]);

if ($controls->is_action()) {
    if ($controls->is_action('reset')) {
        $controls->data = [];
    }
    $controls->data['search_page'] = (int) $controls->data['search_page'] - 1;
}

$last_page = (int) floor($count / $items_per_page) - ($count % $items_per_page == 0 ? 1 : 0);
if ($last_page < 0)
    $last_page = 0;
if ($controls->is_action('last')) {
    $controls->data['search_page'] = $last_page;
}
if ($controls->is_action('first')) {
    $controls->data['search_page'] = 0;
}
if ($controls->is_action('next')) {
    $controls->data['search_page'] = (int) $controls->data['search_page'] + 1;
}
if ($controls->is_action('prev')) {
    $controls->data['search_page'] = (int) $controls->data['search_page'] - 1;
}
if ($controls->is_action('search')) {
    $controls->data['search_page'] = 0;
}

// Eventually fix the page
if (!isset($controls->data['search_page']) || $controls->data['search_page'] < 0) {
    $controls->data['search_page'] = 0;
}

if ($controls->data['search_page'] > $last_page)
    $controls->data['search_page'] = $last_page;

$list_args = array(
    'page' => $controls->data['search_page'],
    'items_per_page' => $items_per_page,
    'email_id' => $email->id,
    'status' => $controls->data['status']
);

$list = $this->get_subscribers($list_args);

// Move to base 1
$controls->data['search_page']++;
?>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">

        <h2>Subscribers targeted by "<?php echo esc_html($email->subject) ?>" <?php if ($email->status == 'sending') { ?>(in progress)<?php } ?></h2>

        <?php $controls->show(); ?>

    </div>

    <div id="tnp-body">

        <p>
            <?php $controls->button_back('admin.php?page=newsletter_reports_view&id=' . $email->id) ?>
            <?php $controls->btn_link('admin-ajax.php?action=newsletter_reports_export&email_id=' . $email->id, 'Export') ?>
        </p>

        <form id="channel" method="post" action="">
            <?php $controls->init(); ?>

            <div class="tnp-filters">
                <?php
                $controls->select('status',
                        array(
                            '' => __('Any status', 'newsletter'),
                            'error' => __('Error', 'newsletter'),
                            'success' => __('Success', 'newsletter'),
                            'open' => __('Open', 'newsletter'),
                            'click' => __('Click', 'newsletter'),
                        )
                )
                ?>
                <?php $controls->button('apply', __('Apply', 'newsletter')) ?>
            </div>

            <div class="tnp-paginator">
                <?php $controls->button('first', '«'); ?>
                <?php $controls->button('prev', '‹'); ?>
                <?php $controls->text('search_page', 3); ?> of <?php echo $last_page + 1 ?> <?php $controls->button('go', __('Go', 'newsletter')); ?>
                <?php $controls->button('next', '›'); ?>
                <?php $controls->button('last', '»'); ?>
                <?php echo $count ?> <?php _e('subscriber(s) found', 'newsletter') ?>
            </div>

            <table class="widefat">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Email/Name</th>
                        <th><?php _e('Status', 'newsletter') ?></th>
                        <th>Delivery</th>
                        <th>Open</th>
                        <th>Click</th>
                        <th>Error</th>
                    </tr>
                </thead>

                <?php foreach ($list as $s) { ?>
                    <tr>
                        <td>
                            <img src="https://www.gravatar.com/avatar/<?php echo md5($s->email) ?>?s=50" style="width: 50px; height: 50px">
                        </td>
                        <td>
                            <?php echo "<a href='" . $coreModule->get_user_edit_url($s->id) . "' class='tnp-table-link'>" . esc_html($s->email) . "</a><br>" ?>
                            <?php echo "<a href='" . $coreModule->get_user_edit_url($s->id) . "' class='tnp-table-link'>" . esc_html($s->name) . " " . esc_html($s->surname) . "</a>" ?>
                        </td>
                        <td>
                            <small>
                                <?php
                                switch ($s->status) {
                                    case 'S': _e('NOT CONFIRMED', 'newsletter');
                                        break;
                                    case 'C': _e('CONFIRMED', 'newsletter');
                                        break;
                                    case 'U': _e('UNSUBSCRIBED', 'newsletter');
                                        break;
                                    case 'B': _e('BOUNCED', 'newsletter');
                                        break;
                                }
                                ?>
                            </small>
                        </td>
                        <td>
                            <?php if ($s->sent_status) { ?>
                                <span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 48 48"><g ><path fill="#E86C60" d="M24,47C11.31738,47,1,36.68262,1,24S11.31738,1,24,1s23,10.31738,23,23S36.68262,47,24,47z"/>
                                    <polygon fill="#FFFFFF" points="35,31 28,24 35,17 31,13 24,20 17,13 13,17 20,24 13,31 17,35 24,28 31,35 "/></g></svg></span>

                            <?php } else { ?>
                                <span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 48 48"><g ><path fill="#72C472" d="M24,47C11.31738,47,1,36.68213,1,24S11.31738,1,24,1s23,10.31787,23,23S36.68262,47,24,47z"/>
                                    <polygon fill="#FFFFFF" points="20,34.82861 9.17188,24 12,21.17139 20,29.17139 36,13.17139 38.82812,16 "/></g></svg></span>
                            <?php } ?>
                        </td>

                        <td>
                            <?php if ($s->sent_open >= 1) { ?>
                                <span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 48 48"><g ><path fill="#72C472" d="M24,47C11.31738,47,1,36.68213,1,24S11.31738,1,24,1s23,10.31787,23,23S36.68262,47,24,47z"/>
                                    <polygon fill="#FFFFFF" points="20,34.82861 9.17188,24 12,21.17139 20,29.17139 36,13.17139 38.82812,16 "/></g></svg></span>
                            <?php } else { ?>
                                <span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 48 48"><g ><path fill="#E86C60" d="M24,47C11.31738,47,1,36.68262,1,24S11.31738,1,24,1s23,10.31738,23,23S36.68262,47,24,47z"/>
                                    <polygon fill="#FFFFFF" points="35,31 28,24 35,17 31,13 24,20 17,13 13,17 20,24 13,31 17,35 24,28 31,35 "/></g></svg></span>
                            <?php } ?>
                        </td>

                        <td>
                            <?php if ($s->sent_open == 2) { ?>
                                <span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 48 48"><g ><path fill="#72C472" d="M24,47C11.31738,47,1,36.68213,1,24S11.31738,1,24,1s23,10.31787,23,23S36.68262,47,24,47z"/>
                                    <polygon fill="#FFFFFF" points="20,34.82861 9.17188,24 12,21.17139 20,29.17139 36,13.17139 38.82812,16 "/></g></svg></span>
                            <?php } else { ?>
                                <span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 48 48"><g ><path fill="#E86C60" d="M24,47C11.31738,47,1,36.68262,1,24S11.31738,1,24,1s23,10.31738,23,23S36.68262,47,24,47z"/>
                                    <polygon fill="#FFFFFF" points="35,31 28,24 35,17 31,13 24,20 17,13 13,17 20,24 13,31 17,35 24,28 31,35 "/></g></svg></span>
                            <?php } ?>
                        </td>

                        <td>
                            <?php
                            if (isset($s->error)) {
                                echo esc_html($s->error);
                            }
                            ?>
                        </td>

                    </tr>
                <?php } ?>
            </table>

        </form>
    </div>
</div>
