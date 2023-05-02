<?php
/* @var $options array contains all the options the current block we're ediging contains */
/* @var $fields NewsletterFields */
?>

<?php if ($context['type'] == 'automated') { ?>

    <?php
    $fields->select('automated_disabled', '',
            [
                '' => 'Check for new posts since last newsletter',
                '1' => 'Do not check for new posts'
    ])
    ?>

    <div class="tnp-field-row">
        <div class="tnp-field-col-2">
            <?php
            $fields->select('automated_include', __('What to include', 'newsletter'),
                    [
                        'new' => __('Include only new posts', 'newsletter'),
                        'max' => __('Include specified max posts', 'newsletter')
                    ],
                    ['description' => 'This option is effective only when the newsletter is generated, not while composing'])
            ?>
        </div>
        <div class="tnp-field-col-2">
            <?php
            $fields->select('automated', __('If there are no new posts...', 'newsletter'),
                    [
                        '' => 'Show the message below',
                        '1' => 'Do not send the newsletter',
                        '2' => 'Remove the block'
                    ],
                    ['description' => 'Works only on automatic newsletter creation'])
            ?>
            <?php $fields->text('automated_no_contents', 'No posts text') ?>
        </div>
    </div>

<?php } ?>

<div class="tnp-field-row">
    <div class="tnp-field-col-2">
        <?php $fields->post_type('post_type', 'Post type', array('reload_form' => true)) ?>
    </div>
    <div class="tnp-field-col-2">
        <?php $fields->select_number('max', 'Max', 1, 20) ?>
    </div>
    <div style="clear: both"></div>
</div>

<?php $fields->select( 'layout', __( 'Layout', 'newsletter' ),
	array(
		'one'       => __( 'One column', 'newsletter' ),
		'one-2'     => __( 'One column variant', 'newsletter' ),
		'two'       => __( 'Two columns', 'newsletter' ),
		'big-image' => __( 'One column, big image', 'newsletter' ),
		'full-post' => __( 'Full post', 'newsletter' )
	) ) ?>

<div class="tnp-field-row">
    <div class="tnp-field-col-3">
	    <?php $fields->yesno('image', __('Show image', 'newsletter')) ?>
    </div>
    <div class="tnp-field-col-3">
		<?php $fields->yesno('date', __('Show date', 'newsletter')) ?>
    </div>
    <div class="tnp-field-col-3">
		<?php $fields->yesno('author', __('Show author', 'newsletter')) ?>
    </div>
    <div style="clear: both"></div>
</div>

<div class="tnp-field-row">
    <div class="tnp-field-col-2">
		<?php $fields->number( 'excerpt_length', __( 'Excerpt words', 'newsletter' ), array( 'min' => 0 ) ); ?>
    </div>
    <div class="tnp-field-col-2">
		<?php $fields->yesno( 'show_read_more_button', 'Show read more button' ) ?>
    </div>
    <div style="clear: both"></div>
</div>

<?php $fields->language(); ?>

<?php $fields->section(__('Filters', 'newsletter')) ?>

<?php
$taxonomies = get_object_taxonomies($options['post_type'], 'object');
foreach ($taxonomies as $taxonomy) {
	/* @var $taxonomy WP_Taxonomy */
	if (!$taxonomy->hierarchical) {
		continue;
	}
	$fields->terms($taxonomy->name, $taxonomy->label);
}


foreach ($taxonomies as $taxonomy) {
	/* @var $taxonomy WP_Taxonomy */
	if ($taxonomy->hierarchical) {
		continue;
	}
	$fields->text('tag_' . $taxonomy->name, $taxonomy->label, ['description'=>'Comma separated']);
}
?>

<?php $fields->section(__('Styles', 'newsletter')) ?>

<?php $fields->font( 'title_font', __( 'Title font', 'newsletter' ), [
	'family_default' => true,
	'size_default'   => true,
	'weight_default' => true
] ) ?>
<?php $fields->font( 'font', __( 'Excerpt font', 'newsletter' ), [
	'family_default' => true,
	'size_default'   => true,
	'weight_default' => true
] ) ?>
<?php $fields->button( 'button', __( 'Read more button', 'newsletter' ), [
	'url'            => false,
	'family_default' => true,
	'size_default'   => true,
	'weight_default' => true
] ) ?>

<?php $fields->block_commons() ?>
