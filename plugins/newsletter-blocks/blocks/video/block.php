<?php
/*
 * Name: Video
 * Section: content
 * Description: Embed a video from YouTube
 *
 */

/* @var $options array */
/* @var $wpdb wpdb */

require_once ABSPATH . 'wp-includes/class-oembed.php';

$default_options = array(
    'url' => '',
    'font_family' => 'Helvetica, Arial, sans-serif',
    'font_size' => 16,
    'block_padding_left' => 0,
    'block_padding_right' => 0,
    'block_padding_top' => 15,
    'block_padding_bottom' => 15,
    'block_background' => ''
);

$options = array_merge($default_options, $options);
$options['url'] = trim($options['url']);

if (!empty($options['url'])) {
    $wp_oembed = new WP_oEmbed();
    $provider = $wp_oembed->get_provider($options['url']);


    if ($provider) {
        $response = wp_remote_get($provider . '?url=' . urlencode($options['url']) . '&maxwidth=640');
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response));

            if (isset($data->thumbnail_width)) {
                $width = $data->thumbnail_width;
            } else {
                $width = 600;
            }

            if ($width > 600) {
                $width = 600;
            }
            // Vimeo
            if (isset($data->thumbnail_url_with_play_button)) {
                $src = $data->thumbnail_url_with_play_button;
                $show_play = false;
            } else {
                $src = $data->thumbnail_url;
                if (strtolower($data->provider_name) === 'youtube') {
                    $tmp_src = str_replace('hqdefault.jpg', 'maxresdefault.jpg', $src);
                    $response = wp_remote_head($tmp_src);
                    if (wp_remote_retrieve_response_code($response) == 200) {
                        $src = $tmp_src;
                        $width = 600;
                    }
                }
                $show_play = true;
            }
            $link = $options['url'];
        } else {
            echo $response->get_error_message();
        }
    }
}

if (!isset($src)) {
    $src = plugins_url('newsletter-blocks') . '/blocks/video/images/placeholder.jpg';
    $link = '#';
}
?>
<style>
    .image {
        max-width: 100%!important;
        height: auto;
        display: block;
        border: 0;
    }
    .image-a {
        display: block;
        border: 0;
    }
</style>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center">
            <a inline-class="image-a" href="<?php echo esc_attr($options['url']) ?>" target="_blank">
                <img inline-class="image" width="<?php echo esc_attr($width) ?>" src="<?php echo esc_attr($src) ?>">
            </a>
        </td>
    </tr>
    <?php if (false && $show_play) { ?>
        <tr>
            <td align="center" style="background-color: black">
                <a href="<?php echo esc_attr($options['url']) ?>" target="_blank">
                    <img src="<?php echo plugins_url('newsletter-blocks') . '/blocks/video/images/play.png' ?>">
                </a>
            </td>
        </tr>
    <?php } ?>
</table>
