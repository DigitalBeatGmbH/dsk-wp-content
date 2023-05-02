<?php
/* @var $options array contains all the options the current block we're ediging contains */
/* @var $controls NewsletterControls */
/* @var $fields NewsletterFields */
?>
<style>
    .gallery-img {
        float: left;
        width: 220px;
        /* height: 220px; */
        overflow: hidden;
        margin: 5px;
        border: 2px solid #eee;
        padding: 5px;
        box-sizing: border-box;
        text-align: center;
    }
    .gallery-images {
        overflow: auto;
        height: 300px;
        background-color: #fff;
        padding: 10px;
        margin-top: 10px;
        border: 1px solid #ddd;
    }
</style>
<table class="form-table">
    <tr>
        <th>Thumbnail size</th>
        <td><?php $controls->select('layout', array('1' => 'Thumbnails', '2' => 'Medium')) ?></td>
    </tr>
    <tr>
        <th>Default link URL</th>
        <td><?php $controls->text('url', 70) ?></td>
    </tr>
</table>

<div class="gallery-images">
    <?php for ($i = 1; $i <= 8; $i++) { ?>
        <div class="gallery-img">
            <?php $fields->media('image_' . $i, '', array('alt' => true)) ?>
            <?php $fields->url('url_' . $i) ?>
        </div>
    <?php } ?>
    <div style="clear: both"></div>
</div>

<?php $fields->block_commons() ?>
