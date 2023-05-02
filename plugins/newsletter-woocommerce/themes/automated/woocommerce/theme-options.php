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

if (!class_exists('WooCommerce')) {
    echo '<p>WooCommerce is not active.</p>';
    return;
}

/* @var $controls NewsletterControls */


$all_categories = array();
$product_categories = get_terms($args = array(
    'taxonomy' => "product_cat",
    'hide_empty' => false
        ));
foreach ($product_categories as $cat) {
    $all_categories[$cat->term_id] = $cat->name;
}

include __DIR__ . '/theme-defaults.php';

// Mandatory!
$controls->merge_defaults($theme_default_options);
?>


<h3>General settings</h3>
<table class="form-table">
    <!--
    <tr valign="top">
        <th>Post image size</th>
        <td>
    <?php $controls->select('theme_post_image_size', array('' => 'None', 'thumbnail' => 'Thumbnail', 'medium' => 'Medium', 'large' => 'Large')); ?>
        </td>
    </tr>
    -->

    <tr valign="top">
        <th>Pre-header</th>
        <td>
            <?php $controls->text('theme_pre_header', 70); ?>
        </td>
    </tr>

    <tr valign="top">
        <th>View online label</th>
        <td>
            <?php $controls->text('theme_view_online_label', 70); ?>
        </td>
    </tr>

    <tr valign="top">
        <th>Top banner/logo</th>
        <td>
            <?php $controls->media('theme_logo'); ?>
        </td>
    </tr>
    <!--
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
    -->
    <tr valign="top">
        <th>Header text</th>
        <td>
            <?php $controls->wp_editor('theme_header'); ?>
            <p class="description">Shown before the last post list.</p>
        </td>
    </tr>
    <tr valign="top">
        <th>Footer text</th>
        <td>
            <?php $controls->wp_editor('theme_footer'); ?>
        </td>
    </tr>
    <tr valign="top">
        <th>Product images</th>
        <td>
            <?php $controls->text('theme_image_width'); ?> &times; <?php $controls->text('theme_image_height'); ?>
            <?php $controls->select('theme_image_crop', array(0 => 'Do not crop', 1 => 'Crop')) ?>
        </td>
    </tr>
    <!--
    <tr>
        <th>Base color</th>
        <td>
    <?php $controls->color('theme_color'); ?>
            <p class="description">
                A main color tone to skin the neutral theme with your main blog color.
            </p>
        </td>
    </tr>
    -->
    <!--
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
    -->
</table>

<h3>Product layout</h3>
<table class="form-table">
    <tr>
        <th>Show price</th>
        <td>
            <?php $controls->select('theme_show_price', array('' => 'No', 'left' => 'Yes, currency left', 'right' => 'Yes, currency right')); ?>
        </td>
    </tr>
    <tr>
        <th>Show "add to cart"</th>
        <td>
            <?php $controls->yesno('theme_show_add_to_cart'); ?>
            <?php $controls->color('theme_add_to_cart_color'); ?>

        </td>
    </tr>
    <tr>
        <th>Show the excerpt</th>
        <td>
            <?php $controls->yesno('theme_show_excerpt'); ?>
        </td>
    </tr>
</table>

<?php for ($i = 1; $i <= 3; $i++) { ?>
    <h3>Product section <?php echo $i ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th>Enabled</th>
            <td>
                <?php $controls->checkbox('theme_enabled_' . $i); ?>
            </td>
        </tr>
        <tr valign="top">
            <th>Title</th>
            <td>
                <?php $controls->text('theme_title_' . $i, 70); ?>
            </td>
        </tr>
        <tr>
            <th>Max products</th>
            <td>
                <?php $controls->text('theme_max_' . $i) ?>
                <?php $controls->checkbox('theme_show_old_' . $i, 'Show even products already sent in last newsletter') ?>
            </td>
        </tr>
        <tr>
            <th>Categories</th>
            <td>
                <?php $controls->checkboxes_group('theme_categories_' . $i, $all_categories) ?>
            </td>
        </tr>
    </table>
<?php } ?>

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