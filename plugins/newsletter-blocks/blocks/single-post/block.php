<?php
/*
 * Name: A single full post
 * Section: content
 * Description: Use the full content of post
 *
 */

/* @var $options array */
/* @var $wpdb wpdb */

require_once NEWSLETTER_INCLUDES_DIR . '/helper.php';

if (!function_exists('tnp_gallery_shortcode')) {

    function tnp_gallery_shortcode($atts) {
        global $post;
        $buffer = '<div style="text-align: center" class="single-post-gallery">';
        if (isset($atts['ids'])) {
            $ids = explode(',', $atts['ids']);
            foreach ($ids as $id) {
                $src = wp_get_attachment_image_src($id, 'thumbnail');
                $buffer .= '<img src="' . $src[0] . '"> ';
            }
        } else {
            $attachments = get_children(array('post_parent' => $options['post'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order'));
            if (!empty($attachments)) {
                foreach ($attachments as $id => &$attachment) {
                    $src = wp_get_attachment_image_src($id, 'thumbnail');
                    if (!$src) {
                        continue;
                    }
                    $buffer .= '<img src="' . $src[0] . '"> ';
                }
            }
        }

        $buffer .= '</div>';
        return $buffer;
    }
}

$default_options = array(
    'post' => '',
    'layout' => 'full',
    'enable shortcodes' => 1,
    'title_font_family' => '',
    'title_font_size'   => '',
    'title_font_color'  => '',
    'title_font_weight' => '',
    'font_family'       => '',
    'font_size'         => '',
    'font_color'        => '',
    'font_weight'       => '',
    'button_label'       => __( 'Read more...', 'newsletter' ),
    'button_background'  => '',
    'button_font_color'  => '',
    'button_font_family' => '',
    'button_font_size'   => '',
    'button_font_weight' => '',
    'block_padding_top' => 15,
    'block_padding_bottom' => 15,
    'block_padding_left' => 15,
    'block_padding_right' => 15,
    'block_background' => ''
);

$options = array_merge($default_options, $options);

$post = null;
if (!empty($options['post'])) {
    $post = get_post($options['post']);
}

if (!$post) {
    include __DIR__ . '/block-empty.php';
    return;
}

$title_font_family = empty( $options['title_font_family'] ) ? $global_title_font_family : $options['title_font_family'];
$title_font_size   = empty( $options['title_font_size'] ) ? $global_title_font_size : $options['title_font_size'];
$title_font_color  = empty( $options['title_font_color'] ) ? $global_title_font_color : $options['title_font_color'];
$title_font_weight = empty( $options['title_font_weight'] ) ? $global_title_font_weight : $options['title_font_weight'];

$text_font_family = empty( $options['font_family'] ) ? $global_text_font_family : $options['font_family'];
$text_font_size   = empty( $options['font_size'] ) ? $global_text_font_size : $options['font_size'];
$text_font_color  = empty( $options['font_color'] ) ? $global_text_font_color : $options['font_color'];
$text_font_weight = empty( $options['font_weight'] ) ? $global_text_font_weight : $options['font_weight'];

$button_background = $options['button_background'];
$button_label = $options['button_label'];
$button_font_family = $options['button_font_family'];
$button_font_size = $options['button_font_size'];
$button_color = $options['button_font_color'];

$button_options = $options;
$button_options['button_font_family'] = empty( $options['button_font_family'] ) ? $global_button_font_family : $options['button_font_family'];
$button_options['button_font_size']   = empty( $options['button_font_size'] ) ? $global_button_font_size : $options['button_font_size'];
$button_options['button_font_color']  = empty( $options['button_font_color'] ) ? $global_button_font_color : $options['button_font_color'];
$button_options['button_font_weight'] = empty( $options['button_font_weight'] ) ? $global_button_font_weight : $options['button_font_weight'];
$button_options['button_background']  = empty( $options['button_background'] ) ? $global_button_background_color : $options['button_background'];

$url = tnp_post_permalink($post);
$media = null;

if ($options['layout'] == 'full') {
    if (!empty($options['show_image'])) {
        $media = tnp_composer_block_posts_get_media($post, ['width' => 600, 'height' => 0]);
        if ($media) {
            $media->set_width(600 - $options['block_padding_left'] - $options['block_padding_right']);
        }
    }
    include __DIR__ . '/block-full.php';
} else {
    if (!empty($options['show_image'])) {
        $media = tnp_composer_block_posts_get_media($post, ['width' => 300, 'height' => 0]);
        if ($media) {
            $media->set_width(105);
        }
    }
    include __DIR__ . '/block-excerpt.php';
}
?>
