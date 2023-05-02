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
 */

/* @var $controls NewsletterControls */
@include __DIR__ . '/theme-defaults.php';

// Mandatory!
$controls->merge_defaults($theme_defaults);
$newsletter = Newsletter::instance();
?>
<table class="form-table">
    <?php if ($newsletter->is_multilanguage()) { ?>
        <tr valign="top">
            <th>Content language</th>
            <td>
                <?php $controls->language(); ?>
                <p class="description">Post contents will be translated with your multilanguage plugin.</p>
            </td>
        </tr>        
    <?php } ?>
    <tr valign="top">
        <th>Layout</th>
        <td>
            <?php $controls->select('theme_post_image_size', array('' => 'No image', 'thumbnail' => 'Small image', 'medium' => 'Medium image', 'large' => 'Large image')); ?>
            <p class="description">Some layouts can look the same on mobile</p>
        </td>
    </tr>
    <tr valign="top">
        <th>Excerpt font</th>
        <td>
            <?php $controls->css_font('theme_font') ?>
        </td>
    </tr>
    <tr valign="top">
        <th>Title font</th>
        <td>
            <?php $controls->css_font('theme_title_font') ?>
        </td>
    </tr>


    <tr valign="top">
        <th>View online label</th>
        <td>
            <?php $controls->text('theme_view_online_label', 70); ?>
        </td>
    </tr>
    <tr valign="top">
        <th>Top banner</th>
        <td>
            <?php $controls->media('theme_logo'); ?>
            <p class="description">Large at least 600px. If set title and subtitle are not used.
        </td>
    </tr>
    <tr valign="top">
        <th>Title</th>
        <td>
            <?php $controls->text('theme_title', 70); ?>
        </td>
    </tr>
    <tr valign="top">
        <th>Pay off/subtitle</th>
        <td>
            <?php $controls->text('theme_subtitle', 70); ?>
        </td>
    </tr>
    <tr valign="top">
        <th>Title colors</th>
        <td>
            text: <?php $controls->text('theme_title_color', 10); ?>
            background: <?php $controls->text('theme_title_background', 10); ?>
        </td>
    </tr>    
    <tr valign="top">
        <th>Header message</th>
        <td>
            <?php $controls->wp_editor('theme_header'); ?>
            <p class="description">Shown before the last post list.</p>
        </td>
    </tr>
    <tr valign="top">
        <th>Footer message</th>
        <td>
            <?php $controls->wp_editor('theme_footer'); ?>
        </td>
    </tr>
    <tr>
        <th>Base color</th>
        <td>
            <?php $controls->color('theme_color'); ?>
            <p class="description">
                A main color tone to skin the neutral theme with your main blog color.
            </p>
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

<h3>Social icons</h3>
<table class="form-table">
    <tr>
        <th>Social block</th>
        <td>
            <?php $controls->checkbox('theme_social_disable'); ?> Disable
            <p class="description">You can configure the social connection in the Company Info panel on Newsletter settings page.</p>

        </td>
    </tr>
</table>
