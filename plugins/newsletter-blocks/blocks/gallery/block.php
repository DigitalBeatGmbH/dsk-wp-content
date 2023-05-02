<?php
/*
 * Name: Gallery
 * Section: content
 * Description: Extract an embed all the media from a post gallery
 *
 */

/* @var $options array */
/* @var $wpdb wpdb */

$default_options = array(
    'layout' => 1,
    'url' => home_url()
);

$options = array_merge($default_options, $options);

$layout = $options['layout'];
$size = $layout == 1 ? 'thumbnail' : 'medium';

$images = array();
for ($i = 1; $i <= 9; $i++) {
    if (empty($options['image_' . $i]['id'])) {
        continue;
    }

    $media = tnp_resize($options['image_' . $i]['id'], $size);
    if (!$media) {
        continue;
    }

    if (!empty($options['image_' . $i  . '_alt'])) {
        $media->alt = $options['image_' . $i . '_alt'];
    } else {
        $alt_texts = array('picture', 'image', 'pic', 'photo');
        $media->alt = $alt_texts[array_rand($alt_texts)];
    }
    $media->link = !empty($options['url_' . $i])?$options['url_' . $i]:$options['url'];

    $images[] = $media;
}
?>
<style>
    .medium-img {
        max-width: 100%!important;
        width: 280px!important;
    }

</style>

<table border="0" cellpadding="0" align="center" cellspacing="0" width="100%">
    <tr>
        <td style="padding: 15px 20px">
            <?php if (!empty($images)) { ?>
                <?php if ($layout == 1) { ?>
                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                        <tr>
                            <td align="center" style="text-align: center">
                                <?php foreach ($images as $media) { ?>

                                    <span class="tnpc-row-edit gallery-thumbnail-img" data-type="image" style="display: inline-block;">
                                        <a href="<?php echo $media->link ?>" target="_blank"><img src="<?php echo esc_attr($media->url) ?>" alt="<?php echo esc_attr($media->alt) ?>"></a>
                                    </span>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                <?php } ?>

                <?php if ($layout == 2) { ?>
                    <table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
                        <tr>
                            <td align="center" style="text-align: center">

                                <?php foreach ($images as $media) { ?>
                                    <div class="gallery-medium tnpc-row-edit" data-type="image">
                                        <a href="<?php echo $media->link ?>" target="_blank"><img class="gallery-medium-img" src="<?php echo esc_attr($media->url) ?>" alt="<?php echo esc_attr($media->alt) ?>"></a>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                <?php } ?>



            <?php } else { ?>

                <div style="text-align: center">
                    <img style="max-width: 100%" src="<?php echo plugins_url('newsletter-blocks') . '/blocks/gallery/images/placeholder.png' ?>">
                </div>

            <?php } ?>

        </td>
    </tr>
</table>
