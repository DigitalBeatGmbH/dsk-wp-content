<?php
/* @var $options array contains all the options the current block we're ediging contains */
/* @var $controls NewsletterControls */
/* @var $fields NewsletterFields */
?>

<?php if ($context['type'] == 'automated') { ?>

    <?php
    $fields->select('automated_disabled', '',
        [
            '' => 'Check for new products since last newsletter',
            '1' => 'Do not check for new products'
        ])
    ?>

    <div class="tnp-field-row">
        <div class="tnp-field-col-2">
            <?php
            $fields->select('automated_include', __('If there are new products...', 'newsletter'),
                [
                    'new' => __('Include only new products', 'newsletter'),
                    'max' => __('Include specified max products', 'newsletter')
                ],
                ['description' => 'This option is effective only when the newsletter is generated, not while composing'])
            ?>
        </div>
        <div class="tnp-field-col-2">
            <?php
            $fields->select('automated', __('If there are no new products...', 'newsletter'),
                [
                    '' => 'Show the message below',
                    '1' => 'Do not send the newsletter',
                    '2' => 'Remove the block'
                ],
                ['description' => 'Works only on automatic newsletter creation'])
            ?>
            <?php $fields->text('automated_no_contents', 'No products text') ?>
        </div>
    </div>

<?php } ?>


<div class="tnp-field-row">
    <div class="tnp-field-col-3">
        <?php $fields->select_number('max', 'Max products', 1, 40) ?>
    </div>
    <div class="tnp-field-col-3">
        <?php $fields->yesno('show_price', 'Show price'); ?>
    </div>
    <div class="tnp-field-col-3">
        <?php $fields->yesno('show_excerpt', 'Show the excerpt'); ?>
    </div>
</div>

<div class="tnp-field-row">
    <div class="tnp-field-col-2">
        <?php $fields->select_number('columns', 'Columns number', 1, 4) ?>
    </div>
    <!--
    <div class="tnp-field-col-3">
        <?php $fields->select('size', 'Thumbnail size', array("thumbnail" => "Thumbnail", "medium" => "Medium", "large" => "Large", "full" => "Full")); ?>
    </div>
    -->
    <div class="tnp-field-col-2">
        <?php $fields->yesno('show_only_featured', 'Only featured'); ?>
    </div>
</div>

<div class="tnp-field-row">
    <div class="tnp-field-col-3">
        <?php $fields->select('product_button', 'Button', array('' => 'No', 'cart' => 'Add to cart', 'view' => 'View product')); ?>
    </div>
    <div class="tnp-field-col-3">
        <?php $fields->text('product_button_text', 'Label'); ?>
    </div>
    <div class="tnp-field-col-3">
        <?php $fields->color('button_background', 'Color') ?>
    </div>
</div>

<?php $fields->language(); ?>

<div class="tnp-field-row">
    <div class="tnp-field-col-2">
        <?php $fields->yesno('show_only_on_sale', 'Show only on sale products'); ?>
    </div>
    <div class="tnp-field-col-2">
        <?php $fields->text('on_sale_label', 'On sale label'); ?>
    </div>
</div>

<div class="tnp-field-row">
    <div class="tnp-field-col-2">
        <?php $fields->yesno('include_out_of_stock', 'Include out of stock products'); ?>
    </div>
    <div class="tnp-field-col-2">
        <?php $fields->yesno('include_hidden_products', 'Include hidden products'); ?>
    </div>
</div>

<?php $fields->terms('product_cat', 'Categories', ['name' => 'categories']); ?>

<div class="tnp-field-row">
    <div class="tnp-field-col-2">
        <?php $fields->text('tags', 'Tags', ['description' => 'comma separated']); ?>
    </div>
    <div class="tnp-field-col-2">
        <?php $fields->text('ids', 'Product IDs', ['description' => 'comma separated']); ?>
    </div>
</div>

<?php $fields->font( 'title_font', __( 'Title font', 'newsletter' ), ['family_default'=>true, 'size_default'=>true, 'weight_default'=>true] ) ?>
<?php $fields->font( 'font', __( 'Excerpt font', 'newsletter' ), ['family_default'=>true, 'size_default'=>true, 'weight_default'=>true] ) ?>

<?php $fields->block_commons() ?>

<script>
    // FIX for automatic updade button text on button type change
    (function autoUpdateButtonText() {
        var productButtonEl = document.getElementById('options-product_button');
        if (productButtonEl) {
            productButtonEl.addEventListener('change', function (e) {
                var buttonType = e.target.value;
                var buttonTextInput = document.getElementById('options-product_button_text');
                if (buttonType === 'cart') {
                    buttonTextInput.value = 'Add to cart';
                }
                if (buttonType === 'view') {
                    buttonTextInput.value = 'View product';
                }
            });
        }
    })();
</script>
