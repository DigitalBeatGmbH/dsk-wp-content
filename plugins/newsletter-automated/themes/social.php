<?php

/*
 * This file can be included in a theme to generate the social link configured on
 * company info panel.
 */

if (isset($theme_options['theme_social_disable'])) return; 
if (!isset($theme_options['theme_social_icon_url'])) {
    $social_icon_url = plugins_url('newsletter-automated') . '/themes/images/social'; 
}
?>
<table cellpadding="5" align="center">
    <tr>
        <?php if (!empty($theme_options['main_facebook_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo $theme_options['main_facebook_url'] ?>"><img src="<?php echo $social_icon_url ?>/facebook.png" alt="Facebook"></a>
            </td>
        <?php } ?>

        <?php if (!empty($theme_options['main_twitter_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo $theme_options['main_twitter_url'] ?>"><img src="<?php echo $social_icon_url ?>/twitter.png"></a>
            </td>
        <?php } ?>
            
        <?php if (!empty($theme_options['main_googleplus_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo $theme_options['main_googleplus_url'] ?>"><img src="<?php echo $social_icon_url ?>/googleplus.png"></a>
            </td>
        <?php } ?>

        <?php if (!empty($theme_options['main_pinterest_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo $theme_options['main_pinterest_url'] ?>"><img src="<?php echo $social_icon_url ?>/pinterest.png"></a>
            </td>
        <?php } ?>
            
            <?php if (!empty($theme_options['main_linkedin_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo $theme_options['main_linkedin_url'] ?>"><img src="<?php echo $social_icon_url ?>/linkedin.png"></a>
            </td>
        <?php } ?>

        <?php if (!empty($theme_options['main_tumblr_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo $theme_options['main_tumblr_url'] ?>"><img src="<?php echo $social_icon_url ?>/tumblr.png"></a>
            </td>
        <?php } ?>

        <?php if (!empty($theme_options['main_youtube_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo esc_attr($theme_options['main_youtube_url']) ?>"><img src="<?php echo $social_icon_url ?>/youtube.png"></a>
            </td>
        <?php } ?>

        <?php if (!empty($theme_options['main_soundcloud_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo esc_attr($theme_options['main_soundcloud_url']) ?>"><img src="<?php echo $social_icon_url ?>/soundcloud.png"></a>
            </td>
        <?php } ?>
        <?php if (!empty($theme_options['main_instagram_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo esc_attr($theme_options['main_instagram_url']) ?>"><img src="<?php echo $social_icon_url ?>/instagram.png"></a>
            </td>
        <?php } ?>    
            <?php if (!empty($theme_options['main_vimeo_url'])) { ?>
            <td style="text-align: center; vertical-align: top" align="center" valign="top">
                <a href="<?php echo esc_attr($theme_options['main_vimeo_url']) ?>"><img src="<?php echo $social_icon_url ?>/vimeo.png"></a>
            </td>
        <?php } ?>    
    </tr>
</table>
<br>