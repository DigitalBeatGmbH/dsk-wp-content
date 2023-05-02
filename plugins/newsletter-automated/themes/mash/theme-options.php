<?php
/*
 * This is a pre packaged theme options page. Every option name
 * must start with "theme_" so Newsletter can distinguish them from other
 * options that are specific to the object using the theme.
 *
 * An array of theme default options should always be present and that default options
 * should be merged with the current complete set of options as shown below.
 *
 * Every theme can define its own set of options, the will be used in the theme.php
 * file while composing the email body. Newsletter knows nothing about theme options
 * (other than saving them) and does not use or relies on any of them.
 *
 * For multilanguage purpose you can actually check the constants "WP_LANG", until
 * a decent system will be implemented.
 */
$theme_defaults = array(
    'theme_post_image_size' => 'thumbnail',
    'theme_view_online_label' => 'Click here if this email doesn\'t show properly',
    'theme_title' => get_option('blogname'),
    'theme_title_background' => '#666666',
    'theme_title_color' => '#ffffff',
    'theme_header' => '',
    'theme_footer' => '<p>You\'re receiving this email because you subscribed to it at ' . get_option('blogname') .
    ' as {email}.</p><p>To modify or cancel your subscription, <a href="{profile_url}">click here</a>.',
    'theme_color' => '#0088cc',
    'theme_max_posts' => '10',
    'theme_full_post' => '0',
    'theme_old_posts' => '1',
    'theme_old_posts_title' => 'Older posts you may have missed',
    'theme_read_more_label' => 'Read more...',
);



// Mandatory!
$controls->merge_defaults($theme_defaults);
$fonts = array('Verdana, Arial, sans-serif' => 'Verdana', 'Tahoma, Arial, sans-serif' => 'Tahoma', 'Arial, sans-serif' => 'Arial', 'Trebuchet MS, sans-serif' => 'Trebuchet MS', 'Palatino' => 'Palatino', 'Georgia' => 'Georgia');
?>
<p>This theme (for perfect rendering) requires posts with featired images large at least 600 pixel.</p>

    <h3>General</h3>
    <div>
        <table class="form-table">
            <?php /*
              <tr valign="top">
              <th>Post image size</th>
              <td>
              <?php $controls->select('theme_post_image_size', array('thumbnail' => 'Thumbnail', 'medium' => 'Medium', 'large' => 'Large')); ?>
              </td>
              </tr>
             */ ?>


            <tr valign="top">
                <th>View online label</th>
                <td>
                    <?php $controls->text('theme_view_online_label', 70); ?>
                </td>
            </tr>
            <tr valign="top">
                <th>Links color</th>
                <td>
                    <?php $controls->color('theme_link_color', 'Links'); ?>
                </td>
            </tr>

            <tr valign="top">
                <th>Top banner</th>
                <td>
                    <?php $controls->media('theme_logo'); ?>
                    <p class="description">Should be your logo, the title background is applied.
                </td>
            </tr>
            <tr valign="top">
                <th>Title</th>
                <td>
                    <?php $controls->text('theme_title', 70); ?>
                    <p class="description">For example you blog name</p>
                    <?php $controls->color('theme_title_color', 'Foreground'); ?><br>
                    <?php $controls->color('theme_title_background', 'Background'); ?><br>
                    <?php $controls->select('theme_title_font_family', $fonts, null, 'Font'); ?>
                </td>
            </tr>
            <tr valign="top">
                <th>Pay off/subtitle</th>
                <td>
                    <?php $controls->text('theme_subtitle', 70); ?>
                </td>
            </tr>   
            <tr valign="top">
                <th>Header message</th>
                <td>
                    <?php $controls->wp_editor('theme_header'); ?>
                    <p class="description">Shown before the post list.</p>
                </td>
            </tr>
        </table>
    </div>

    <h3>Post list</h3>
    <div>
        <table class="form-table">
            <tr valign="top">
                <th>Post titles color and font</th>
                <td>
                    <?php $controls->color('theme_post_title_color', 'Color'); ?><br>
                    <?php $controls->select('theme_post_title_font_family', $fonts, null, 'Font'); ?>
                </td>
            </tr> 
            <tr valign="top">
                <th>Read more label</th>
                <td>
                    <?php $controls->text('theme_read_more_label', 50); ?>
                    <p class="description">Set empty to not show. It uses header title colors.</p>
                </td>
            </tr> 
            <tr>
                <th>Show old posts</th>
                <td>
                    <?php $controls->yesno('theme_old_posts'); ?><br>
                    List title: <?php $controls->text('theme_old_posts_title', 60); ?>
                    <p class="description">
                        The theme shows a light list of previous posts below the main content. You can disable it.
                    </p>
                </td>
            </tr>
        </table>
        <table class="form-table">
            <tr valign="top">
                <th>Footer message</th>
                <td>
                    <?php $controls->wp_editor('theme_footer'); ?>
                    <p class="description">
                        Write here your copyright notice and your address. Remeber to add the {profile_url} and eventually the {unsubscription_url}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <h3>Social icons</h3>
    <div>
        <table class="form-table">
            <tr>
                <th>Social block</th>
                <td>
                    <?php $controls->checkbox('theme_social_disable'); ?> Disable
                    <p class="description">You can configure the social connection in the Company Info panel on Newsletter settings page.</p>
                </td>
            </tr>
        </table>
    </div>
