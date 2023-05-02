
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="responsive-table">

    <?php foreach ($new_posts as $post) { ?>
        <?php
        $url = tnp_post_permalink($post);
        $media = tnp_resize(TNP_Composer::get_post_thumbnail_id($post), array(480, 320, true));
        if ($media) {
            $media->link = $url;
            $media->set_width(150);
        }
        ?>

        <tr>

            <td valign="top" style="padding: 20px 15px 0 15px;" class="td-1">

                <table width="100%" cellpadding="0" cellspacing="0" border="0" align="left" class="1-column" style="margin-bottom: 20px">
                    <tr>
                        <td>
                            <a class="title" href="<?php echo $post->link ?>"><?php echo $post->title ?></a>
                        </td>
                    </tr>
                </table>

                <?php if ($media) { ?>
                    <table width="20%" cellpadding="0" cellspacing="0" border="0" align="left" class="responsive-table" style="margin-bottom: 20px">
                        <tr>
                            <td>
                                <?php echo TNP_Composer::image($media) ?>
                            </td>
                        </tr>
                    </table>
                <?php } ?>

                <table width="<?php echo $media ? '78%' : '100%' ?>" cellpadding="0" cellspacing="0" border="0" class="responsive-table" align="right">
                    <tr>
                        <td dir="<?php echo $dir ?>">

                            <a href="<?php echo $post->link ?>" style="color: #444"><?php echo $post->excerpt; ?></a>

                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    <?php } ?>

</table>
