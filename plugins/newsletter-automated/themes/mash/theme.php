<?php
if (empty($theme_options['theme_post_image_size'])) {
    $theme_options['theme_post_image_size'] = 'thumbnail';
}

if (empty($theme_options['theme_view_online_link_text'])) {
    $theme_options['theme_view_online_link_text'] = 'View online';
}

$font_family = 'Helvetica Neue, Helvetica, Arial, sans-serif';
$body_background = '#dddddd';
$body_width = 600;
$title_font_family = $theme_options['theme_title_font_family'];
if (empty($title_font_family))
    $title_font_family = 'Trebuchet MS, Georgia, Tahoma, sans-serif';


$title_font_size = '32px';

$theme_title_color = $theme_options['theme_title_color'];
if (empty($theme_title_color))
    $theme_title_color = '#ffffff';

$theme_title_background = $theme_options['theme_title_background'];
if (empty($theme_title_background))
    $theme_title_background = '#52BAD5';

$post_title_font_family = $theme_options['theme_post_title_font_family'];
if (empty($post_title_font_family))
    $post_title_font_family = 'Trebuchet MS, Georgia, Tahoma, sans-serif';

$post_title_font_size = '24px';

$post_title_color = $theme_options['theme_post_title_color'];
if (empty($post_title_color))
    $post_title_color = '#000000';

$link_color = $theme_options['theme_link_color'];
if (empty($link_color))
    $link_color = '#666';

$read_more_label = $theme_options['theme_read_more_label'];
if (empty($read_more_label))
    $read_more_label = 'Read more...';
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <style>
            a {
                color: <?php echo $link_color ?>;
                text-decoration: none;
            }
        </style>
    </head>
    <body style="font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; color: #666; margin: 0; padding: 0;">

        <table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0">

            <!-- View online -->
            <tr>
                <td bgcolor="#ffffff">
                    <table align="center" width="100%" style="max-width: 600px; width: 100%" cellpadding="20" cellspacing="0" border="0">
                        <tr>
                            <td align="center">
                                <a href="{email_url}"><?php echo $theme_options['theme_view_online_label']; ?></a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Title -->
            <tr>
                <td bgcolor="<?php echo $theme_title_background ?>">
                    <?php if (empty($theme_options['theme_logo']['url'])) { ?>
                        <table width="100%" style="max-width: 600px; width: 100%" cellpadding="15" cellspacing="0" align="center">
                            <tr>
                                <td align="center" style="color: <?php echo $theme_title_color ?>; font-family: <?php echo $title_font_family ?>">
                                    <br>
                                    <div style="font-size: <?php echo $title_font_size ?>;">
                                        <?php echo $theme_options['theme_title']; ?>

                                        <?php if (!empty($theme_options['theme_subtitle'])) { ?>
                                            <div style="font-size: .75em">
                                                <?php echo $theme_options['theme_subtitle']; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <br>
                                </td>
                            </tr>
                        </table>
                    <?php } else { ?>
                        <table width="100%" style="max-width: 600px; width: 100%" cellpadding="0" cellspacing="0" align="center">
                            <tr>
                                <td align="center"><img src="<?php echo $theme_options['theme_logo']['url'] ?>" style="max-width: 100%"></td>
                            </tr>
                        </table>
                    <?php } ?>
                </td>
            </tr>

            <tr>
                <td bgcolor="#ffffff">
                    <?php if (!empty($theme_options['theme_header'])) { ?>
                        <table width="100%" style="max-width: 600px; width: 100%" cellpadding="15" cellspacing="0" align="center">

                            <tr>
                                <td style="font-family: <?php echo $font_family ?>; line-height: 1.5em; font-size: 16px">
                                    <?php echo $theme_options['theme_header']; ?>
                                </td>
                            </tr>


                        </table>
                    <?php } ?>

                    <table width="100%" style="max-width: 600px; width: 100%" cellpadding="15" cellspacing="0" bgcolor="#ffffff" align="center">

                        <?php foreach ($new_posts as $post) { ?>

                            <tr>
                                <td>
                                    <br>
                                    <a href="<?php echo $post->link ?>"><img src="<?php echo $post->images['large'] ?>" style="max-width: 100%"></a> 
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a style="font-family: <?php echo $post_title_font_family ?>; font-weight: bold; text-decoration: none; font-size: <?php echo $post_title_font_size ?>; color: <?php echo $post_title_color ?>" href="<?php echo $post->link ?>"><?php echo $post->title ?></a>
                                </td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #ccc">
                                    <a href="<?php echo $post->link ?>" style="text-decoration: none; font-size: 14px; color: #000000; display: block; line-height: 1.5em">
                                        <?php echo $post->excerpt ?>
                                    </a>
                                    <br>
                                    <?php if (!empty($read_more_label)) { ?>
                                        <div align="right">
                                            <a href="<?php echo $post->link ?>" style="text-decoration: none; font-size: 14px; color: <?php echo $theme_title_color ?>; display: inline-block; padding: 10px; background-color: <?php echo $post_title_color ?>; border-radius: 3px">
                                                <?php echo $read_more_label ?>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </td>
                            </tr>  


                        <?php } ?>
                    </table>

                    <table width="100%" style="max-width: 600px; width: 100%" cellpadding="15" cellspacing="0" bgcolor="#ffffff" align="center">
                        <tr>
                            <td>
                                <?php include dirname(__FILE__) . '/../social.php'; ?>
                            </td>
                        </tr>
                    </table>

                    <?php if ($theme_options['theme_old_posts'] == 1 && !empty($old_posts)) { ?>

                        <table width="100%" style="max-width: 600px; width: 100%" cellpadding="15" cellspacing="0" bgcolor="#ffffff" align="center">
                            <tr>
                                <td style="line-height: 1.5em">
                                    <div style="font-size: 16px"><strong><?php echo $theme_options['theme_old_posts_title']; ?></strong></div>
                                    <br>
                                    <?php
                                    foreach ($old_posts as $post) {
                                        ?>
                                        <a href="<?php echo $post->link; ?>"><?php echo $post->title; ?></a><br>
                                    <?php } ?>
                                </td>
                            </tr>

                        </table>
                    <?php } ?>

                </td>
            </tr>
        </table>


        <table width="100%" bgcolor="#dddddd" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <table align="center" width="100%" style="max-width: 600px; width: 100%" cellpadding="15" cellspacing="0" border="0">
                        <tr>                    

                            <td style="color: #000000">
                                <?php echo $theme_options['theme_footer']; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <br><br>

    </body>
</html>