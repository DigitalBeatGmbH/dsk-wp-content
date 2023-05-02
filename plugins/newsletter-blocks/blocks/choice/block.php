<?php
/*
 * Name: Choice
 * Section: content
 * Description: Subscribers can make a choice and will be classified in a specific list
 *
 */

/* @var $options array */
/* @var $wpdb wpdb */

$default_options = array(
    'label_1'=>'First choice',
    'label_2'=>'Second choice',
    'font_family' => '',
    'font_size'   => '',
    'font_color'  => '',
    'font_weight' => '',
    'block_background'=>'',
    'block_padding_top'=>15,
    'block_padding_bottom'=>15,
    'block_padding_left'=>0,
    'block_padding_right'=>0
);

$options = array_merge($default_options, $options);

$url_1 = home_url('/') . '?na=profile-change&list=' . $options['list_1'] . '&value=1&nk={key}&nek={email_key}&redirect=' . urlencode($options['url_1']);
$url_2 = home_url('/') . '?na=profile-change&list=' . $options['list_2'] . '&value=1&nk={key}&nek={email_key}&redirect=' . urlencode($options['url_2']);

if (empty($options['media_1']['id'])) {
    $img_1 = plugins_url('newsletter-blocks') . '/blocks/choice/images/a.png';
} else {
    $img_1 = tnp_media_resize($options['media_1']['id'], array(150, 150, true));
    if (is_wp_error($img_1)) $img_1 = plugins_url('newsletter-blocks') . '/blocks/choice/images/a.png';
}

if (empty($options['media_2']['id'])) {
    $img_2 = plugins_url('newsletter-blocks') . '/blocks/choice/images/b.png';
} else {
    $img_2 = tnp_media_resize($options['media_2']['id'], array(150, 150, true));
    if (is_wp_error($img_2)) $img_2 = plugins_url('newsletter-blocks') . '/blocks/choice/images/b.png';
}

$text_font_family = empty( $options['font_family'] ) ? $global_text_font_family : $options['font_family'];
$text_font_size   = empty( $options['font_size'] ) ? $global_text_font_size : $options['font_size'];
$text_font_color  = empty( $options['font_color'] ) ? $global_text_font_color : $options['font_color'];
$text_font_weight = empty( $options['font_weight'] ) ? $global_text_font_weight : $options['font_weight'];

?>
<style>
    .choice-label {
        font-family: <?php echo $text_font_family?>;
        font-size: <?php echo $text_font_size?>px;
        color: <?php echo $text_font_color?>;
        font-weight: <?php echo $text_font_weight?>;
        line-height: normal;
        padding-top: 15px;
    }
</style>
<table border="0" cellpadding="0" align="center" cellspacing="0" width="100%">
    <tr>
        <td align="center">
            <a href="<?php echo esc_attr($url_1)?>" target="_blank"><img src="<?php echo $img_1?>" style="max-width: 100%"></a>

            <div class="choice-label">
            <?php echo $options['label_1']?>
            </div>
        </td>
        <td align="center">
            <a href="<?php echo esc_attr($url_2)?>" target="_blank"><img src="<?php echo $img_2?>" style="max-width: 100%"></a>

            <div class="choice-label">
            <?php echo $options['label_2']?>
            </div>
        </td>
    </tr>
</table>
