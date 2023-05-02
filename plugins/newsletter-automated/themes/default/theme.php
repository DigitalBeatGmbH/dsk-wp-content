<?php
if (empty($new_posts)) {
    return;
}
@include_once NEWSLETTER_INCLUDES_DIR . '/helper.php';
@include __DIR__ . '/theme-defaults.php';

$theme_options = array_merge($theme_defaults, $theme_options);


$color = $theme_options['theme_color'];
$font_family = $theme_options['theme_font_family'];
$font_size = $theme_options['theme_font_size'];
$font_weight = $theme_options['theme_font_weight'];

$body_background = '#dddddd';
$body_width = 600;

$layout = $theme_options['theme_post_image_size'];

$logo = '';
if (!empty($theme_options['theme_logo']['id'])) {
    $logo = tnp_media_resize($theme_options['theme_logo']['id'], array(600, 0));
}
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

            .title {
                font-size: <?php echo $theme_options['theme_title_font_size'] ?>px; 
                color: #000000!important;
                font-family: <?php echo $theme_options['theme_title_font_family'] ?>;
                font-weight: <?php echo $theme_options['theme_title_font_weight'] ?>;
                text-decoration: none;
            }

            .text {
                line-height: 140%; 
                text-decoration: none; 
                font-size: <?php echo $font_size ?>px; 
                color: #000000!important;
                font-family: <?php echo $font_family ?>;
                font-weight: <?php echo $font_weight ?>;
            }

            .online {
                font-family: <?php echo $font_family ?>;
                color: #444444;
                font-size: 12px; 
            }

            .footer {
                font-family: <?php echo $font_family ?>;
                color: #444444;
                font-size: 12px; 
            }

            @media all and (max-width: 480px) {
                td[class=col-2] {
                    display: block;
                    width: 100%;
                    padding: 0!important;
                    padding-bottom: 15px!important;
                }
                .post-title {
                    text-align: center;
                    display: block;
                }
                table[class="responsive-table"]{
                    width:100%!important;
                    max-width: 100%!important;
                }
                img[class=image] {
                    width: 100%!important;
                }
            }

        </style>
    </head>
    <body style="font-family: <?php echo $font_family ?>; font-size: <?php echo $font_size ?>px; color: #666; margin: 0 auto; padding: 0;">

        <table width="100%" bgcolor="<?php echo $body_background ?>" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <!--[if mso]><table border="0" cellpadding="0" align="center" cellspacing="0" width="<?php echo $body_width ?>"><tr><td width="<?php echo $body_width ?>"><![endif]-->

                    <br>
                    <table align="center" width="100%" style="max-width: 600px; width: 100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>

                                <table align="center" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" class="online"><br><a href="{email_url}"><?php echo $theme_options['theme_view_online_label']; ?></a><br></td>
                                    </tr>
                                </table>

                                <br>

                                <?php if (!$logo) { ?>
                                    <table width="100%" cellpadding="15" cellspacing="0" bgcolor="<?php echo $theme_options['theme_title_background'] ?>" align="center">
                                        <tr>
                                            <td style="padding: 25px 15px">

                                                <div style="color: <?php echo $theme_options['theme_title_color']; ?>; font-size: 24px;">
                                                    <?php echo $theme_options['theme_title']; ?>
                                                </div>
                                                <?php if (!empty($theme_options['theme_subtitle'])) { ?>
                                                    <br>
                                                    <div style="color: <?php echo $theme_options['theme_title_color']; ?>; font-size: 16px;">
                                                        <?php echo $theme_options['theme_subtitle']; ?>
                                                    </div>
                                                <?php } ?>

                                            </td>
                                        </tr>
                                    </table>
                                <?php } else { ?>
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td align="center"><img src="<?php echo $logo ?>" style="max-width: 100%"></td>
                                        </tr>
                                    </table>
                                <?php } ?>
                                <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" align="center">


                                    <?php if (!empty($theme_options['theme_header'])) { ?>
                                        <tr>
                                            <td style="font-family: <?php echo $font_family ?>; padding: 0 15px">
                                                <?php echo $theme_options['theme_header']; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                </table>

                                <?php if ($layout === 'thumbnail') { ?>
                                    <?php include __DIR__ . '/layout-thumbnail.php'; ?>
                                <?php } else { ?>


                                    <table width="100%" cellpadding="15" cellspacing="0" bgcolor="#ffffff" align="center">

                                        <?php foreach ($new_posts as $post) { ?>
                                            <?php
                                            $link = $post->link;
                                            ?>

                                            <tr>
                                                <td style="padding: 30px 30px 10px 15px; font-family: <?php echo $font_family ?>;" align="<?php is_rtl() ? 'right' : 'left' ?>">
                                                    <a class="title" href="<?php echo $post->link ?>"><?php echo $post->title ?></a>
                                                </td>
                                            </tr>
                                            <?php if ($layout === 'large') { ?>
                                                <?php
                                                $image = tnp_post_thumbnail_src($post, array(600, 0));
                                                ?>
                                                <tr>
                                                    <td style="font-family: <?php echo $font_family ?>;">
                                                        <a href="<?php echo $link ?>"><img src="<?php echo $image ?>" width="<?php echo $body_width - 30 ?>" style="max-width: 100%; display: block"></a> 
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="<?php is_rtl() ? 'right' : 'left' ?>" style="font-family: <?php echo $font_family ?>;">
                                                        <a href="<?php echo $link ?>" class="text">

                                                            <?php echo $post->excerpt ?>
                                                        </a>
                                                    </td>
                                                </tr>


                                            <?php } else if ($layout === 'medium') { ?>
                                                <?php
                                                $image = tnp_post_thumbnail_src($post, array(480, 250, true));
                                                ?>                             
                                                <tr>
                                                    <td valign="top" style="padding-right: 10px; font-family: <?php echo $font_family ?>;">

                                                        <table width="100%" cellpadding="0" cellspacing="0"  border="0">
                                                            <tr>
                                                                <td width="50%" class="col-2" valign="top" align="center" style="padding-right: 10px">
                                                                    <a href="<?php echo $link ?>"><img src="<?php echo $image ?>" style="display: block"></a>
                                                                </td>
                                                                <td width="50%" class="col-2" valign="top" style="padding-left: 10px">
                                                                    <a class="text" href="<?php echo $link ?>">
                                                                        <?php echo $post->excerpt ?>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>

                                                </tr>
                                            <?php } else { ?>
                                                <tr>

                                                    <td valign="top" align="<?php is_rtl() ? 'right' : 'left' ?>">
                                                        <a class="text" href="<?php echo $link ?>">
                                                            <?php echo $post->excerpt ?>
                                                        </a>
                                                    </td>

                                                </tr>
                                            <?php } ?>    

                                        <?php } ?>
                                    </table>
                                <?php } ?>

                                <table width="100%" cellpadding="15" cellspacing="0" bgcolor="#ffffff" align="center">
                                    <tr>
                                        <td style="font-family: <?php echo $font_family ?>;">
                                            <?php include dirname(__FILE__) . '/../social.php'; ?>
                                        </td>
                                    </tr>



                                    <?php if ($theme_options['theme_old_posts'] == 1 && !empty($old_posts)) { ?>
                                        <tr>
                                            <td>
                                                <div class="title"><?php echo $theme_options['theme_old_posts_title']; ?></div>
                                                <br>
                                                <?php foreach ($old_posts as $post) { ?>
                                                    <a href="<?php echo $post->link; ?>"><?php echo $post->title; ?></a><br>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>

                                <!-- Footer -->
                                <table width="100%" cellpadding="15" cellspacing="0" bgcolor="#cccccc" align="center">
                                    <tr>
                                        <td class="footer">
                                            <?php echo $theme_options['theme_footer']; ?>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </table>
                    <br>
                    <!--[if mso]></td></tr></table><![endif]-->
                </td>
            </tr>
        </table>
    </body>
</html>