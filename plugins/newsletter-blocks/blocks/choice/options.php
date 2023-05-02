<?php
/* @var $options array contains all the options the current block we're ediging contains */
/* @var $controls NewsletterControls */
/* @var $fields NewsletterFields */
?>

<?php $fields->section('Choice 1')?>
<?php $fields->lists('list_1', 'List to activate') ?>
<?php $fields->text('url_1', 'Page URL where to land') ?>
<?php $fields->media('media_1', 'Image') ?>
<?php $fields->text('label_1', 'Label') ?>

<?php $fields->section('Choice 2')?>
<?php $fields->lists('list_2', 'List to activate') ?>
<?php $fields->text('url_2', 'Page URL where to land') ?>
<?php $fields->media('media_2', 'Image') ?>
<?php $fields->text('label_2', 'Label') ?>

<?php $fields->font( 'font', __( 'Text font', 'newsletter' ), [
	'family_default' => true,
	'size_default'   => true,
	'weight_default' => true
] ) ?>

<?php $fields->block_commons()?>
<p>
    You can use the same landing page for your choices. For icons consider <a href="https://thenounproject.com/" target="_blank">the noun project</a>.
</p>
