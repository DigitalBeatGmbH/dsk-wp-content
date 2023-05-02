<?php
/*
 * Name: List
 * Section: content
 * Description: A well designed list for your strength points
 *
 */

/* @var $options array */
/* @var $wpdb wpdb */

$default_options = array(
    'bullet' => '1',
    'text_1' => 'Element 1',
    'text_2' => 'Element 2',
    'text_3' => 'Element 3',
    'font_size'   => '',
    'font_color'  => '',
    'font_weight' => '',
    'font_family' => '',
    'block_padding_top' => 20,
    'block_padding_bottom' => 20,
    'block_padding_left' => 15,
    'block_padding_right' => 15,
    'block_background' => ''
);

$options = array_merge($default_options, $options);

$text_font_family = empty( $options['font_family'] ) ? $global_text_font_family : $options['font_family'];
$text_font_size   = empty( $options['font_size'] ) ? $global_text_font_size : $options['font_size'];
$text_font_color  = empty( $options['font_color'] ) ? $global_text_font_color : $options['font_color'];
$text_font_weight = empty( $options['font_weight'] ) ? $global_text_font_weight : $options['font_weight'];

?>

<style>
    .list-item {
        font-family: <?php echo $text_font_family ?>;
        font-size: <?php echo $text_font_size ?>px;
        font-weight: <?php echo $text_font_weight ?>;
        color: <?php echo $text_font_color ?>;
        text-align: left;
        line-height: normal;
    }
</style>
<table cellspacing="0" cellpadding="5" align="left">
    <?php
    for ($i = 1; $i <= 10; $i++) {
        if (empty($options['text_' . $i])) {
            continue;
        }
        ?>
        <tr>
            <td align="left" width="<?php echo round($text_font_size*1.3)+5 ?>" valign="top"><img style="width:<?php echo $text_font_size*1.3 ?>px;" src="<?php echo plugins_url('newsletter-blocks') ?>/blocks/list/images/bullet-<?php echo $options['bullet'] ?>.png"></td>
            <td width="1">&nbsp;</td>
            <td align="left" inline-class="list-item">
                <?php echo $options['text_' . $i] ?>
            </td>
        </tr>
        <?php
    }
    ?>
</table>


