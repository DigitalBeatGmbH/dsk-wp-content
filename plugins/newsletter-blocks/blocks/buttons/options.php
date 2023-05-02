<?php
/* @var $fields NewsletterFields */

$fields->controls->data['schema'] = '';
$max_buttons = 3;
?>

<?php $fields->select('schema', __('Schema', 'newsletter'), array('' => 'Custom', 'bright' => 'Bright', 'dark' => 'Dark'), ['after-rendering' => 'reload']) ?>

<div class="tnp-field-row">
    <div class="tnp-field-col-2">
        <?php $fields->select('buttons_number', __('Number of buttons'), [1 =>'1', 2 => '2', 3 => '3'], ['reload' => true]); ?>
    </div>
    <div class="tnp-field-col-2">
        <?php $fields->size('button_width', __('Width', 'newsletter')) ?>
    </div>
</div>

<?php for ( $i = 1; $i <= $max_buttons; $i ++ ) { ?>
	<?php $display_style = $i <= $fields->controls->data['buttons_number'] ? '' : 'display:none;'; ?>
    <div style="<?php echo $display_style; ?>">
		<?php $fields->button( "button$i", "Button $i layout",
			[
				'family_default' => true,
				'size_default'   => true,
				'weight_default' => true
			] ) ?>
    </div>
<?php } ?>

<?php $fields->block_commons() ?>
