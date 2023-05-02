<?php
/*
 * Name: Columns
 * Section: content
 * Description: Two or three columns
 *
 */

/* @var $options array */
/* @var $wpdb wpdb */

$default_options = array(
    'image_1' => '',
    'text_1' => '',
    'url_1' => '',
    'image_2' => '',
    'text_2' => '',
    'url_2' => '',
    'image_3' => '',
    'text_3' => '',
    'url_3' => '',
    'font_family' => '',
    'font_size'   => '',
    'font_color'  => '',
    'font_weight' => '',
    'block_padding_left' => 0,
    'block_padding_right' => 0,
    'block_padding_top' => 15,
    'block_padding_bottom' => 15,
    'block_background' => ''
);

$options = array_merge($default_options, $options);

$col_count = 2;
$column_class = 'mj-column-per-50';
$mso_width = 300;
$size = [200, 200, false];
if (!empty($options['text_3']) || !empty($options['image_3']['id'])) {
    $column_class = 'mj-column-per-33';
    $mso_width = 200;
    $col_count = 3;
    $size = [170, 170, false];
}

$image_1 = '';
if (!empty($options['image_1']['id'])) {
    $image_1 = tnp_resize_2x($options['image_1']['id'], $size);
}

$image_2 = '';
if (!empty($options['image_2']['id'])) {
    $image_2 = tnp_resize_2x($options['image_2']['id'], $size);
}

$image_3 = '';
if (!empty($options['image_3']['id'])) {
    $image_3 = tnp_resize_2x($options['image_3']['id'], $size);
}

$url_1 = $options['url_1'];
$url_2 = $options['url_2'];
$url_3 = $options['url_3'];

$text_font_family = empty( $options['font_family'] ) ? $global_text_font_family : $options['font_family'];
$text_font_size   = empty( $options['font_size'] ) ? $global_text_font_size : $options['font_size'];
$text_font_color  = empty( $options['font_color'] ) ? $global_text_font_color : $options['font_color'];
$text_font_weight = empty( $options['font_weight'] ) ? $global_text_font_weight : $options['font_weight'];

?>
<style>
    .columns-text {
        font-family: <?php echo $text_font_family?>;
        font-size: <?php echo $text_font_size?>px;
        color: <?php echo $text_font_color?>;
        font-weight: <?php echo $text_font_weight?>;
        margin-top: 15px;
    }
    .columns-td {
        font-size:0px;
        padding:10px 15px;
        word-break:break-word;
    }
</style>

<table border="0" cellpadding="0" align="center" valign="top" cellspacing="0" width="100%" style="font-size: 0; border: 0; padding: 0; margin: 0;vertical-align:top;">
    <tr>
        <td align="center">
<!--[if mso | IE]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="" style="vertical-align:top;width:<?php echo $mso_width?>px;">
<![endif]-->
<div class="<?php echo $column_class?> outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
        <tr>
            <td align="center" class="columns-td">
                <?php if (!empty($url_1)) { ?>
                <a href="<?php echo $url_1?>" style="display: block; text-decoration: none;">
                <?php } ?>
                <?php if (!empty($image_1)) { ?>
                    <?php echo TNP_Composer::image($image_1) ?>
                <?php } ?>
                <div class="columns-text"><?php echo $options['text_1']?></div>
                <?php if (!empty($url_1)) { ?>
                </a>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>
<!--[if mso | IE]>
</td>
<td class="" style="vertical-align:top;width:<?php echo $mso_width?>px;">
<![endif]-->
<div class="<?php echo $column_class?> outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
        <tr>
            <td align="center" class="columns-td">
                <?php if (!empty($url_2)) { ?>
                <a href="<?php echo $url_2?>" style="display: block; text-decoration: none;">
                <?php } ?>
                <?php if (!empty($image_2)) { ?>
                <?php echo TNP_Composer::image($image_2) ?>
                <?php } ?>
                <div class="columns-text"><?php echo $options['text_2']?></div>
                <?php if (!empty($url_2)) { ?>
                </a>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>
<!--[if mso | IE]>
</td>
<![endif]-->
<?php if ($col_count == 3) { ?>
<!--[if mso | IE]>
<td class="" style="vertical-align:top;width:<?php echo $mso_width?>px;">
<![endif]-->
<div class="<?php echo $column_class?> outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
        <tr>
            <td align="center" class="columns-td">
                <?php if (!empty($url_3)) { ?>
                <a href="<?php echo $url_3?>" style="display: block; text-decoration: none;">
                <?php } ?>
                <?php if (!empty($image_3)) { ?>
                <?php echo TNP_Composer::image($image_3) ?>
                <?php } ?>
                <div class="columns-text"><?php echo $options['text_3']?></div>
                <?php if (!empty($url_3)) { ?>
                </a>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>
<!--[if mso | IE]>
</td>
<![endif]-->
<?php } ?>
<!--[if mso | IE]>
</tr>
</table>
<![endif]-->
        </td>
    </tr>
</table>
