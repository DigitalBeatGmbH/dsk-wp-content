<?php
/*
 * Name: Woocommerce - default
 * Type: automated
 *
 */

include NEWSLETTER_INCLUDES_DIR . '/helper.php';
include __DIR__ . '/theme-defaults.php';

$theme_options = array_merge($theme_default_options, $theme_options);

$color = $theme_options['theme_color'];
$font_family = $theme_options['theme_font_family'];
$body_background = $theme_options['theme_background'];
$width = $theme_options['theme_width'];

$product_count = 0;

$sections = array();

for ($i = 1; $i <= 3; $i++) {

    if (!isset($theme_options['theme_enabled_' . $i])) {
        $sections[$i] = array();
        continue;
    }

    $args = array('post_type' => 'product');
    $args['tax_query'] = array();

    if (!empty($theme_options['theme_categories_' . $i])) {
        $args['tax_query'][] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $theme_options['theme_categories_' . $i],
                'operator' => 'IN'
            )
        );
    }

    if (empty($theme_options['theme_show_old_' . $i])) {
        $args['date_query'] = array(
            'after' => date('Y-m-d H:i:s', $last_run)
        );
    }
    
    $args['posts_per_page'] = (int)isset($theme_options['theme_max_' . $i])?$theme_options['theme_max_' . $i]:10;

    $products = get_posts($args);
    $sections[$i] = $products;
    $product_count += count($products);

    if (count($products) > 0 && empty($theme_subject)) {
        $theme_subject = $products[0]->post_title;
    }
}

if (empty($product_count)) {
    return;
}

$cart_url = wc_get_cart_url();

$logo = '';
if (function_exists('tnp_media_resize')) {
    if (!empty($theme_options['theme_logo']['id'])) {
        $logo = tnp_media_resize($theme_options['theme_logo']['id'], array(600, 200));
    }
} else {
    if (!empty($theme_options['theme_logo']['url'])) {
        $logo = $theme_options['theme_logo']['url'];
    }
}

$add_to_cart_color = $theme_options['theme_add_to_cart_color'];
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <style type="text/css" media="all">
            a {
                text-decoration: none;
                color: <?php echo $color; ?>;
            }
            img {
                max-width: 100%;
            }
        </style>
    </head>
    <body style="font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; color: #000; margin: 0 auto; padding: 0;">

        <!--[if mso]><table border="0" cellpadding="0" align="center" cellspacing="0" width="<?php echo $width ?>"><tr><td width="<?php echo $width ?>"><![endif]-->




        <!-- CONTAINER -->
        <table width="100%" style="width: 100%!important;" bgcolor="<?php echo $body_background ?>" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>

                    <br>

                    <table align="center" width="100%" style="max-width: <?php echo $width ?>px!important; width: 100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>
                                <br>
                                <!-- PREHEADER -->
                                <table border="0" cellpadding="10" align="center" cellspacing="0" width="100%" style="width: 100%!important;">
                                    <tr>
                                        <td align="center" width="50%" style="font-family: <?php echo $font_family ?>; padding: 0 15px; font-size: 12px; color: #999">
                                            <?php echo $theme_options['theme_pre_header']; ?>
                                        </td>

                                        <td align="center" width="50%">
                                            <a href="{email_url}" style="font-family: <?php echo $font_family ?>; font-size: 12px; color: #999"><?php echo $theme_options['theme_view_online_label']; ?></a>
                                        </td>
                                    </tr>
                                </table>

                                <!-- HEADER -->
                                <?php if ($logo) { ?>
                                    <table width="100%" cellpadding="0" cellspacing="0" align="center" border="0" style="width: 100%!important">
                                        <tr>
                                            <td align="center">
                                                <br>
                                                <img src="<?php echo $logo ?>" style="max-width: 100%">
                                            </td>
                                        </tr>
                                    </table>
                                <?php } ?>

                                <!-- OPENING -->
                                <?php if (!empty($theme_options['theme_header'])) { ?>
                                    <table width="100%" cellpadding="15" cellspacing="0" align="center" border="0" style="width: 100%!important">
                                        <tr>
                                            <td style="font-family: <?php echo $font_family ?>; line-height: 20px">
                                                <?php echo $theme_options['theme_header']; ?>
                                            </td>
                                        </tr>
                                    </table>
                                <?php } ?>

                                <!-- BODY -->
                                <?php for ($i = 1; $i <= 3; $i++) { ?>
                                    <?php
                                    $products = $sections[$i];
                                    if (!count($products)) {
                                        continue;
                                    }
                                    ?>
                                    <table width="100%" cellpadding="15" cellspacing="0" align="center" style="width: 100%!important">

                                        <?php if (!empty($theme_options['theme_title_' . $i])) { ?>
                                        <!-- SECTION TITLE -->
                                        <tr>
                                            <td align="<?php is_rtl() ? 'right' : 'left' ?>" valign="middle" style="font-size: 28px">

                                                <?php echo $theme_options['theme_title_' . $i] ?>
                                            </td>
                                        </tr>
                                        <?php } ?>

                                        <?php foreach ($products as $product) { ?>

                                            <?php
                                            $wc_product = wc_get_product($product->ID);
                                            $url = get_permalink($product->ID);
                                            if (NEWSLETTER_VERSION >= '5.2.4') {
                                                $image = tnp_post_thumbnail_src($product, array($theme_options['theme_image_width'], $theme_options['theme_image_height'], (boolean)$theme_options['theme_image_crop']));
                                            } else {
                                                $image = tnp_post_thumbnail_src($product, 'large');
                                            }
                                            ?>
                                            <!-- PRODUCT -->

                                            <!-- TITLE -->
                                            <tr>
                                                <td style="" align="<?php is_rtl() ? 'right' : 'left' ?>">
                                                    <a style="font-weight: bold; font-family: <?php echo $font_family ?>; text-decoration: none; font-size: 18px; color: #000000" href="<?php echo $url ?>"><?php echo $product->post_title ?></a>
                                                </td>
                                            </tr>

                                            <!-- PICTURE -->
                                            <?php if ($image) { ?>
                                                <tr>
                                                    <td align="center" valign="middle">
                                                        <a href="<?php echo $url; ?>" target="_blank"><img src="<?php echo $image ?>" style="display: block; max-width: 100%" alt="<?php echo tnp_post_title($product); ?>" border="0"></a>
                                                    </td>
                                                </tr>
                                            <?php } ?>

                                            <!-- DESCRIPTION -->
                                            <?php if ($theme_options['theme_show_excerpt']) { ?>
                                                <tr>
                                                    <td align="center" style="font-family: <?php echo $font_family ?>; color: #444; font-size: 16px; line-height: 20px;">
                                                        <?php echo tnp_post_excerpt($product) ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>

                                            <!-- PRICE -->
                                            <?php if ($theme_options['theme_show_price']) { ?>
                                                <tr>
                                                    <td align="center" style="font-weight: bolder; font-family: <?php echo $font_family ?>; color: #666666; font-size: 23px;">
                                                        <?php if ($theme_options['theme_show_price'] == "left") { ?>
                                                            <?php echo get_woocommerce_currency_symbol(); ?>&nbsp;<?php echo $wc_product->get_price(); ?>
                                                        <?php } else { ?>
                                                            <?php echo $wc_product->get_price(); ?>&nbsp;<?php echo get_woocommerce_currency_symbol(); ?>
                                                        <?php } ?>
                                                    </td>
                                                </tr>    
                                            <?php } ?>

                                            <!-- BUTTON -->
                                            <?php if ($theme_options['theme_show_add_to_cart']) { ?>    
                                                <tr>
                                                    <td align="center">
                                                        <a href="<?php echo NewsletterModule::add_qs($cart_url, 'add-to-cart=' . $product->ID) ?>" target="_blank" style="font-size: 15px; font-family: Helvetica, Arial, sans-serif; font-weight: normal; color: #ffffff; text-decoration: none; background-color: <?php echo $add_to_cart_color?>; border-top: 10px solid <?php echo $add_to_cart_color?>; border-bottom: 10px solid <?php echo $add_to_cart_color?>; border-left: 20px solid <?php echo $add_to_cart_color?>; border-right: 20px solid <?php echo $add_to_cart_color?>; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; display: inline-block;" class="mobile-button"><?php echo $wc_product->single_add_to_cart_text() ?></a>
                                                    </td>
                                                </tr>
                                            <?php } ?>

                                        <?php } ?>
                                    </table>

                                <?php } ?>

                                <?php include WP_PLUGIN_DIR . '/newsletter-automated/themes/social.php'; ?>  


                                <!-- CLOSING -->
                                <table width="100%" cellpadding="10" cellspacing="0" bgcolor="#eeeeee" align="center">
                                    <tr>
                                        <td style="color: #000000">
                                            <?php echo $theme_options['theme_footer']; ?>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </table>
                    <br>
                </td>
            </tr>
        </table>

        <!--[if mso]></td></tr></table><![endif]-->
    </body>
</html>