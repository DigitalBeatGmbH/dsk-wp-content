<?php
/* @var $options array contains all the options the current block we're ediging contains */
/* @var $controls NewsletterControls */
/* @var $fields NewsletterFields */

$bullets = array();
for ($i=1; $i<=20; $i++) {
    $bullets["$i"] = 'Bullet ' . $i;
}
?>

<?php $fields->section('Column 1') ?>

<?php $fields->media('image_1', 'Image') ?>
<div class="tnp-field-row">
    <div class="tnp-field-col-2"><?php $fields->text('text_1', 'Text') ?></div>
    <div class="tnp-field-col-2"><?php $fields->url('url_1', 'Url') ?></div>
</div>



<?php $fields->section('Column 2') ?>

<?php $fields->media('image_2', 'Image') ?>
<div class="tnp-field-row">
    <div class="tnp-field-col-2"><?php $fields->text('text_2', 'Text') ?></div>
    <div class="tnp-field-col-2">
<?php $fields->url('url_2', 'Url') ?></div>
</div>

<?php $fields->section('Column 3') ?>

<?php $fields->media('image_3', 'Image') ?>
<div class="tnp-field-row">
    <div class="tnp-field-col-2"><?php $fields->text('text_3', 'Text') ?></div>
    <div class="tnp-field-col-2">
<?php $fields->url('url_3', 'Url') ?></div>
</div>

<?php $fields->font( 'font', __( 'Text font', 'newsletter' ), [
	'family_default' => true,
	'size_default'   => true,
	'weight_default' => true
] ) ?>

<?php $fields->block_commons() ?>
