<?php

/* @var $options array contains all the options the current block we're ediging contains */
/* @var $fields NewsletterFields */

?>

<p>
    Extracts the full text from a post. It can produce bad formatting is the post contains
    HTML code not compatible with email clients.
</p>
<?php $fields->posts('post', 'Post', 150) ?>
<?php $fields->select('layout', 'Layout', array('full'=>'Full post', 'excerpt'=>'Excerpt')) ?>
<?php $fields->font('title_font', __('Title font', 'newsletter'), ['family_default'=>true, 'size_default'=>true, 'weight_default'=>true] ) ?>


<?php $fields->section('For excerpt layout') ?>

<?php $fields->font('font', __('Excerpt font', 'newsletter'), ['family_default'=>true, 'size_default'=>true, 'weight_default'=>true] ) ?>
<?php $fields->button('button', 'Button', ['url'=> false,'family_default'=>true, 'size_default'=>true, 'weight_default'=>true]) ?>

<div class="tnp-field">
<label class="tnp-label"><?php _e('Dates and images', 'newsletter')?></label>
<div class="tnp-field-row">
    <div class="tnp-field-col-2">
        <?php $fields->checkbox('show_image', __('Show image', 'newsletter')) ?>
    </div>
    <div class="tnp-field-col-2">
        <?php $fields->checkbox('show_date', __('Show date', 'newsletter')) ?>
    </div>
    <div style="clear: both"></div>
</div>
</div>

<?php $fields->block_commons() ?>
