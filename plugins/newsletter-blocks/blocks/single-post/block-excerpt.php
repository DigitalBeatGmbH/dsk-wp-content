<style>
    .date {
        font-family: <?php echo $text_font_family ?>;
        font-size: 13px;
        font-weight: <?php echo $text_font_weight ?>;
        color: <?php echo $text_font_color ?>;
        line-height: normal;
        padding-bottom: 10px;
    }

    .title {
        font-family: <?php echo $title_font_family ?>;
        font-size: <?php echo $title_font_size ?>px;
        font-weight: <?php echo $title_font_weight ?>;
        color: <?php echo $title_font_color ?>;
        line-height: normal;
        padding-bottom: 25px;
    }

    .excerpt {
        font-family: <?php echo $text_font_family ?>;
        font-size: <?php echo $text_font_size ?>px;
        font-weight: <?php echo $text_font_weight ?>;
        color: <?php echo $text_font_color ?>;
        line-height: 1.5em;
        padding-bottom: 20px;
    }

    .image {
        display: block;
        max-width: 100%!important;
        border: 0;
        margin: 0;
    }

    .image-a {
        display: block;
        text-decoration: none;
    }

    /* For mobile view */
    .image-td {
        padding-bottom: 20px;
    }
</style>


<?php if ($media) { ?>
    <table width="20%" cellpadding="0" cellspacing="0" border="0" align="left" class="mobile-full-width">
        <tr>
            <td inline-class="image-td">
                <a href="<?php echo tnp_post_permalink($post) ?>" target="_blank" inline-class="image-a">
                    <img src="<?php echo $media->url ?>"
                         width="<?php echo $media->width ?>"
                         height="<?php echo $media->height ?>"
                         alt="<?php echo esc_attr($media->alt) ?>"
                         border="0"
                         class="mobile-full-width"
                         inline-class="image">
                </a>
            </td>
        </tr>
    </table>
<?php } ?>

<table width="<?php echo $media ? '78%' : '100%' ?>" cellpadding="0" cellspacing="0" border="0" class="mobile-full-width" align="right">

    <?php if (!empty($options['show_date'])) { ?>
        <tr>
            <td align="left" inline-class="date">
                <?php echo tnp_post_date($post) ?>
            </td>
        </tr>
    <?php } ?>

    <tr>
        <td align="left" inline-class="title">
            <?php echo tnp_post_title($post) ?>
        </td>
    </tr>
    <tr>
        <td align="left" inline-class="excerpt">
            <?php echo tnp_post_excerpt($post) ?>
        </td>
    </tr>
    <tr>
        <td align="left">
	        <?php $button_options['button_url'] = esc_attr( $url ); ?>
	        <?php echo TNP_Composer::button( $button_options ) ?>
        </td>
    </tr>
</table>

