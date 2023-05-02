<?php
/**
 * Name: Default
 * Preview: true
 */
$width = 600;

$font_family = $theme_options['font_family'];
$font_size = $theme_options['font_size'] . 'px';
$link_color = $theme_options['link_color'];

$logo = '';
if (!empty($theme_options['logo']['id'])) {
    require_once NEWSLETTER_INCLUDES_DIR . '/helper.php';
    $logo = tnp_media_resize($theme_options['logo']['id'], array(600, 0));
}

?><!DOCTYPE html>
<!-- DO NOT CHANGE THIS FILE IT WILL BE OVERWRITTEN ON NEXT UPDATE -->
<html>
    <head>
        <title>{email_subject}</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            * {
                font-family: sans-serif;
                box-sizing: border-box;
            }
            a {
                color: <?php echo $link_color ?>;
                text-decoration: none;
            }
            img {
                max-width: 100%!important;
                height: auto!important;
            }
            
            .online {
                font-size: 12px!important;
                color: #666!important;
            }
            .footer-link {
                font-size: 12px; 
                color: #666;
            }
            .message {
                font-size: <?php echo $font_size ?>;
                line-height: 24px;
                font-family: <?php echo $font_family ?>; 
                text-align: left; 
                padding: 20px; 
                background-color: #ffffff;
            }
        </style>
    </head>
    <body style="margin: 0; padding: 0;">
        <!-- Main wrapper for background -->
        <table width="100%" bgcolor="#eeeeee" cellpadding="0" cellspacing="0" border="0" style="width: 100%!important">
            <tr>
                <td>
                    <br>

                    <!-- Outlook adapter -->
                    <!--[if mso]><table border="0" cellpadding="0" align="center" cellspacing="0" width="<?php echo $width ?>"><tr><td width="<?php echo $width ?>"><![endif]-->

                    <!-- Responsive wrapper -->
                    <table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: <?php echo $width ?>px; margin: 0 auto" align="center">
                        <?php if (!empty($theme_options['label_view_online'])) { ?>
                        <tr>
                            <td align="center" bgcolor="#eeeeee">
                                <br>
                                <a href="{email_url}" class="online"><?php echo $theme_options['label_view_online'] ?></a>
                                <br><br>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td style="font-family: <?php echo $font_family ?>; text-align: center">
                                
                                
                                <?php if (!empty($logo)) { ?>
                                <!-- Header -->
                                <table class="header-table" width="100%" style="width: 100%!important;" cellpadding="0" cellspacing="0" border="0" align="center">
                                    <tr>
                                        <td class="header" style="font-family: <?php echo $font_family ?>; text-align: center" align="center">
                                            <img src="<?php echo $logo?>" style="max-width: 100%">
                                        </td>
                                    </tr>
                                </table>
                                <?php } ?>

                                <!-- Step message -->
                                <table class="message-table" width="100%" style="background-color: #ffffff; width: 100%!important;" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td class="message" align="left">
                                            {message}  
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Closing message -->
                                <table class="message-table" width="100%" style="background-color: #ffffff; width: 100%!important;" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td class="message" align="left">
                                            <?php echo $theme_options['closing'] ?>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Footer -->
                                <table class="footer-table" width="100%" style="background-color: #eeeeee; width: 100%!important;" cellpadding="20" cellspacing="0" border="0">
                                    <tr>
                                        <td class="footer" style="font-family: <?php echo $font_family ?>; text-align: center" align="center">
                                            <!--<a class="footer-link" href="{email_url}"><?php echo $theme_options['label_view_online'] ?></a> - -->
                                            <?php if (!empty($theme_options['label_profile'])) { ?>
                                            <a class="footer-link" href="{profile_url}"><?php echo $theme_options['label_profile'] ?></a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                                
                            </td>
                        </tr>
                    </table>

                    <!--[if mso]></td></tr></table><![endif]-->
                </td>
            </tr>
        </table>

    </body>
</html>