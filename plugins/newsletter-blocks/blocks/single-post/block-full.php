<style>
    .paragraph {
        font-family: <?php echo $text_font_family ?>;
        font-size: <?php echo $text_font_size ?>px;
        font-weight: <?php echo $text_font_weight ?>;
        color: <?php echo $text_font_color ?>;
        text-align: left;
        line-height: 1.5em;
    }
    .title {
        font-family: <?php echo $title_font_family ?>;
        font-size: <?php echo $title_font_size ?>px;
        font-weight: <?php echo $title_font_weight ?>;
        color: <?php echo $title_font_color ?>;
        line-height: normal;
        margin: 0;
        padding-bottom: 20px;
    }

    .date {
        font-family: <?php echo $text_font_family ?>;
        font-size: 13px;
        font-weight: <?php echo $text_font_weight ?>;
        color: <?php echo $text_font_color ?>;
        line-height: normal;
        padding-bottom: 10px;
    }

    .image {
        max-width: 100%;
        display: block;
    }
</style>
<table border="0" cellpadding="0" align="center" cellspacing="0" width="100%">
    <tr>
        <td inline-class="title">
            <?php echo $post->post_title ?>
        </td>
    </tr>
    <?php if (!empty($options['show_date'])) { ?>
        <tr>
            <td align="center" inline-class="date">
                <?php echo tnp_post_date($post) ?>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <td>
            <?php if ($media) { ?>
                <br>
                <img src="<?php echo $media->url ?>"
                     width="<?php echo $media->width ?>"
                     height="<?php echo $media->height ?>"
                     alt="<?php echo esc_attr($media->alt) ?>"
                     border="0"
                     class="mobile-full-width"
                     inline-class="image">
                <br>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php echo TNP_Composer::post_content( $post ) ?>
        </td>
    </tr>
</table>
