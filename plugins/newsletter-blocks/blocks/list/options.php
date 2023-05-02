<?php
/* @var $options array contains all the options the current block we're ediging contains */
/* @var $controls NewsletterControls */
/* @var $fields NewsletterFields */

$bullets = array();
for ($i=1; $i<=20; $i++) {
    $bullets["$i"] = 'Bullet ' . $i;
}
?>

<?php $fields->select('bullet', __('Bullet', 'newsletter'), $bullets) ?>

<?php $fields->multitext('text', __('Items', 'newsletter'), 10) ?>

<?php $fields->font( 'font', __( 'Font', 'newsletter' ), [
    'family_default' => true,
    'size_default'   => true,
    'weight_default' => true
] ) ?>

<?php $fields->block_commons() ?>
