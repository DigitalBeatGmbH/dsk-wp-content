<?php
/* @var $this NewsletterReports */

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

wp_enqueue_script('tnp-chart');

$email_id = (int) $_GET['id'];
$email = Newsletter::instance()->get_email($email_id);

$report = $this->get_statistics($email);

$is_autoresponder = strpos($email->type, 'autoresponder') === 0;
if ($is_autoresponder) {
    $send_mode = 'continuous';
} else {
    $send_mode = $this->get_email_send_mode($email->type);
}

$is_continuous = $send_mode === 'continuous';

if (empty($email->track)) {
    $controls->warnings[] = __('This newsletter has the tracking disabled. No statistics will be available.', 'newsletter');
}
?>
<style>
    .tnp-widget .tnp-warning {
        border: 1px solid #FFB900;
        background-color: #FFd930;
        margin: 20px;
    }
</style>

<link rel="stylesheet" href="<?php echo plugins_url('newsletter-reports') ?>/admin/style.css" type="text/css">

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">
        <h2 class="">Newsletter report</h2>

        <div class="tnp-statistics-general-box">
            <p>
                <span class="tnp-statistics-general-title">Subject</span> "<?php echo esc_html($email->subject); ?>"
            </p>

            <?php if ($email->status != 'new' && !$is_continuous) { ?>
                <p><span class="tnp-statistics-general-title">Status</span>
                    <?php if ($email->status == 'sending'): ?>
                        Sending...
                    <?php elseif ($email->status == 'paused'): ?>
                        Paused
                    <?php else: ?>
                        Sent on <?php echo strftime('%a, %e %b %Y', $email->send_on) ?>
                    <?php endif; ?>
                </p>
            <?php } ?>
        </div>

        <?php $controls->show(); ?>

    </div>


    <div id="tnp-body" style="min-width: 500px">

        <?php if (false && $email->status == 'new') : ?>

            <div class="tnp-warning"><?php _e('No data, newsletter not sent yet.', 'newsletter') ?></div>

        <?php else: ?>

            <form action="" method="post">
                <?php $controls->init(); ?>

                <div class="tnp-cards-container">
                    <div class="tnp-card">
                        <div class="tnp-card-title">Reach</div>
                        <div class="tnp-card-value">
                            <span class="tnp-counter-animationx"><?php echo $report->total ?></span>
                            <div class="tnp-card-description">Total people that got your email</div>
                        </div>
                        <div class="tnp-card-icon"><div class="tnp-card-icon-business-contact"></div></div>
                        <div class="tnp-card-button-container">
                            <a href="admin.php?page=newsletter_reports_view_users&id=<?php echo $email->id ?>">
                                Subscriber list</a>
                        </div>
                    </div>
                    <div class="tnp-card">
                        <div class="tnp-card-title">Opens</div>
                        <div class="tnp-card-value">
                            <span class="tnp-counter-animationx percentage"><?php echo $report->open_rate; ?></span>%
                            <div class="tnp-card-description">
                                <span class="value"><?php echo $report->open_count ?></span> total people that
                                opened your email
                            </div>
                        </div>
                        <div class="tnp-card-icon"><div class="tnp-card-icon-preview"></div></div>
                        <div class="tnp-card-button-container">
                            <a href="admin.php?page=newsletter_reports_view_retarget&id=<?php echo $email->id ?>">Retargeting</a>
                        </div>
                    </div>
                    <div class="tnp-card">
                        <div class="tnp-card-title">Clicks</div>
                        <div class="tnp-card-value">
                            <span class="tnp-counter-animationx percentage"><?php echo $report->click_rate; ?></span>%
                            <div class="tnp-card-description">
                                <span class="value"><?php echo $report->click_count ?></span> total people that
                                clicked a link in your email
                            </div>
                        </div>
                        <div class="tnp-card-icon"><div class="tnp-card-icon-mouse"></div></div>
                        <div class="tnp-card-button-container">
                            <a href="admin.php?page=newsletter_reports_view_retarget&id=<?php echo $email->id ?>">Retargeting</a>
                        </div>
                    </div>
                    <div class="tnp-card">
                        <div class="tnp-card-title">Reactivity</div>
                        <div class="tnp-card-value">
                            <span class="tnp-counter-animationx percentage"><?php echo $report->reactivity ?></span>%
                            <div class="tnp-card-description">
                                <span class="value"><?php echo $report->click_count ?></span> clicks out of
                                <span class="value"><?php echo $report->open_count ?></span> opens
                            </div>
                        </div>
                        <div class="tnp-card-icon"><div class="tnp-card-icon-rabbit"></div></div>
                    </div>
                </div>
                <div class="tnp-cards-container">
                    <div class="tnp-card">
                        <div class="tnp-card-title">Opens/Sent</div>
                        <div class="tnp-card-chart">
                            <canvas id="tnp-opens-sent-chart" class="mini-chart"></canvas>
                        </div>
                    </div>
                    <div class="tnp-card">
                        <div class="tnp-card-title">Clicks/Opens</div>
                        <div class="tnp-card-chart">
                            <canvas id="tnp-clicks-opens-chart" class="mini-chart"></canvas>
                        </div>
                    </div>
                    <div class="tnp-card">
                        <div class="tnp-card-title">Unsubscribed</div>
                        <div class="tnp-card-value">
                            <span class="tnp-counter-animationx"><?php echo $report->unsub_count ?></span>
                            <div class="tnp-card-description">
                                Cancellations started from this newsletter (cannot always be tracked)
                            </div>
                        </div>
                        <div class="tnp-card-icon"><div class="tnp-card-icon-filter-remove"></div></div>
                    </div>
                    <div class="tnp-card">
                        <div class="tnp-card-title">Errors</div>
                        <div class="tnp-card-value">
                            <span class="tnp-counter-animationx"><?php echo $report->error_count ?></span>
                            <div class="tnp-card-description">
                                Errors encountered while delivery, usually due to a faulty mailing service.
                            </div>
                            <div class="tnp-card-button-container">
                                <a href="admin.php?page=newsletter_reports_view_users&id=<?php echo $email->id ?>&status=error">
                                    Subscriber list</a>
                            </div>
                        </div>
                        <div class="tnp-card-icon"><div class="tnp-card-icon-remove"></div></div>
                    </div>
                </div>
                <div class="tnp-cards-container">
                    <div class="tnp-card">
                        <div class="tnp-card-title">World opens distribution</div>
                        <div class="tnp-card-chart">
                            <?php
                            $countries = $wpdb->get_results($wpdb->prepare("select n.country as country, count(distinct user_id) as total from {$wpdb->prefix}newsletter_sent ns join " . NEWSLETTER_USERS_TABLE . " n on n.id=ns.user_id where ns.open > 0 and ns.email_id=%d and n.country<>'' group by n.country order by total", $email_id));
                            $world_data = array();
                            foreach ($countries as $country) {
                                $world_data[strtolower($country->country)] = (int) $country->total;
                            }
                            ?>

                            <?php if (empty($countries)) : ?>
                                <p class="tnp-map-legend">No data available, just wait some time to let the
                                    processor work to resolve the countries. Thank you.</p>
                            <?php else: ?>
                                <div id="tnp-map-chart"></div>
                            <?php endif; ?>

                        </div>
                        <?php if (!class_exists('NewsletterGeo')) : ?>
                            <div class="tnp-note">Geo data is available with the Geo Addon</div>
                        <?php endif; ?>
                    </div>
                    <?php
                    $days = 10;
                    if ($send_mode == 'standard') {
                        $start_time = $email->send_on;
                    } else {
                        $start_time = time() - 30 * DAY_IN_SECONDS;
                        $days = 31;
                    }
                    ?>

                    <div class="tnp-card">
                        <div class="tnp-card-title">Interactions over time</div>
                        <div class="tnp-card-chart h-400">
                            <?php
                            $open_events = $this->get_open_events($email_id);

                            $events_data = array();
                            $events_labels = array();

                            for ($i = 0; $i < $days; $i++) {
                                $events_labels[] = strftime('%a, %e %b', $start_time + $i * 86400);
                                $opens = 0;
                                foreach ($open_events as $e) {
                                    if (date("Y-m-d", $start_time + $i * 86400) == $e->event_day) {
                                        $opens = (int) $e->events_count;
                                    }
                                }
                                $events_data[] = $opens;
                            }
                            ?>
                            <?php if (empty($events_data)) : ?>
                                <p>Still no data.</p>
                            <?php else: ?>
                                <canvas id="tnp-events-chart-canvas"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Link list -->
                <div class="tnp-cards-container">
                    <div class="tnp-card">
                        <div class="tnp-card-table">
                            <?php
                            $urls = $wpdb->get_results("select url, count(distinct user_id) as number from " . NEWSLETTER_STATS_TABLE . " where url<>'' and email_id=" . ( (int) $email_id ) . " group by url order by number desc");
                            $total = $wpdb->get_var("select count(distinct user_id) from " . NEWSLETTER_STATS_TABLE . " where url<>'' and email_id=" . ( (int) $email_id ));
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
                                        <?php for ($i = 0; $i < count($urls); $i++) : ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo esc_attr($urls[$i]->url) ?>" target="_blank">
                                                        <?php echo esc_html($urls[$i]->url) ?>
                                                    </a>
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
                        </div>
                        <div class="tnp-note mt-25">You can use the retargeting feature to contact
                            subscribers by clicked URL
                        </div>
                    </div>
                </div>

            </form>

        <?php endif; // if "new"       ?>

    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>

</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

        var opensSentChartData = {
            labels: [
                "Sent",
                "Opens"
            ],
            datasets: [
                {
                    data: [<?php echo $report->total - $report->open_count; ?>, <?php echo $report->open_count ?>],
                    backgroundColor: [
                        "#49a0e9",
                        "#27AE60",
                    ]
                }]
        };
        var opensSentChartConfig = {
            type: "doughnut",
            data: opensSentChartData,
            options: {
                responsive: true,
                legend: {display: false},
                elements: {
                    arc: {borderWidth: 0}
                }
            }
        };
        new Chart('tnp-opens-sent-chart', opensSentChartConfig);


        var clicksOpensChartData = {
            labels: [
                "Opens",
                "Clicks"
            ],

            datasets: [
                {
                    data: [<?php echo $report->open_count - $report->click_count; ?>, <?php echo $report->click_count ?>],
                    backgroundColor: [
                        "#49a0e9",
                        "#27AE60",
                    ]
                }]
        };
        var clicksOpensChartConfig = {
            type: "doughnut",
            data: clicksOpensChartData,
            options: {
                responsive: true,
                legend: {display: false},
                elements: {
                    arc: {borderWidth: 0}
                }
            }
        };
        new Chart('tnp-clicks-opens-chart', clicksOpensChartConfig);

        var world_data = <?php echo json_encode($world_data) ?>;
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

        var events_data = {
            labels: <?php echo json_encode($events_labels) ?>,
            datasets: [
                {
                    label: "Interactions",
                    fill: false,
                    strokeColor: "#2980b9",
                    backgroundColor: "#2980b9",
                    borderColor: "#2980b9",
                    pointBorderColor: "#2980b9",
                    pointBackgroundColor: "#2980b9",
                    data: <?php echo json_encode($events_data) ?>
                }
            ]
        };
        new Chart('tnp-events-chart-canvas', {
            type: "line",
            data: events_data,
            options: {
                maintainAspectRatio: false,
                scales: {
                    xAxes: [
                        {
                            type: "category",
                            gridLines: {display: true},
                            ticks: {fontColor: "#ECF0F1"}
                        }
                    ],
                    yAxes: [
                        {
                            type: "linear",
                            gridLines: {display: true},
                            ticks: {fontColor: "#ECF0F1"}
                        }
                    ]
                },
                legend: {
                    labels: {
                        fontColor: "#ECF0F1"
                    }
                }
            }
        });

    });

</script>
