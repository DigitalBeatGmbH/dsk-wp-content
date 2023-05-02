<?php
/*
 * Name: WooCommerce products
 * Section: content
 * Description: Add some WooCommerce products to your newsletter
 * Type: dynamic
 *
 * WooCommerce functions reference
 * https://www.skyverge.com/blog/get-woocommerce-page-urls/
 *
 * A single block of CSS can be added where one or more classes can be definied as
 *
 * .classname {
 *   option: value;
 *   option: value;
 * }
 *
 * each element with class="classname" will be modified replacing the previous attribute with a style="..."
 * with all CSS values. This is called inlining CSS.
 * The CSS block is removed after the inlining.
 *
 * Some variables are available:
 * - $options contains all the options collected with the block configuration form
 * - $wpdb is the global WP object to access the database
 */

/* @var $options array */
/* @var $wpdb wpdb */

$defaults = array(
    'block_background' => '',
    'block_padding_left' => 15,
    'block_padding_right' => 15,
    'block_padding_top' => 20,
    'block_padding_bottom' => 20,
    'show_price' => 1,
    'product_button' => 'view',
    'product_button_text' => 'View product',
    'show_excerpt' => 1,
    'max' => 4,
    //'size' => 'medium',
    'button_background' => '#256F9C',
    'show_only_featured' => 0,
    'include_out_of_stock' => 0,
    'show_only_on_sale' => 0,
    'on_sale_label' => 'ON SALE',
    'columns' => 1,
    'automated_include' => 'new',
    'automated_no_contents' => 'No new products by now!',
    'automated' => '',
    'language' => '',
    'include_hidden_products' => 0,
    'font_family' => '',
    'font_size' => '',
    'font_color' => '',
    //'title_weight' => '',
    'title_font_family' => '',
    'title_font_size' => '',
    'title_font_color' => '',
    'title_font_weight' => '',
);

$options = array_merge($defaults, $options);

$image_width = round((600 - $options['block_padding_left'] - $options['block_padding_right'] - 10 * 2 * $options['columns']) / $options['columns']);

// check if WooCommerce is installed and active
if (!class_exists('WooCommerce')) {
    ?>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td bgcolor="#F5F7FA" align="center" style="padding: 20px 15px 20px 15px;" class="section-padding">
                WooCommerce plugin is not active, please install and activate it.
            </td>
        </tr>
    </table>
    <?php
    return;
}

include_once NEWSLETTER_INCLUDES_DIR . '/helper.php';

$args = array('post_type' => 'product');

$args['tax_query'] = array();

if (!empty($options['categories'])) {
    $args['tax_query'][] = array(
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => $options['categories'],
        'operator' => 'IN'
    );
}

if (!empty($options['tags'])) {
    $args['tax_query'][] = array(
        'taxonomy' => 'product_tag',
        'field' => 'slug',
        'terms' => $options['tags'],
        'operator' => 'IN'
    );
}

if (!empty($options['show_only_featured'])) {
    $args['tax_query'][] = array(
        'taxonomy' => 'product_visibility',
        'field' => 'name',
        'terms' => 'featured',
    );
}

if ($options['include_hidden_products'] == 0) {
    $args['tax_query'][] = array(
        'taxonomy' => 'product_visibility',
        'field' => 'name',
        'terms' => ['exclude-from-search', 'exclude-from-catalog'],
        'operator' => 'NOT IN'
    );
}

if ($options['include_out_of_stock'] == 0) {
    $args['meta_query'][] = array(
        'key' => '_stock_status',
        'value' => ['instock', 'onbackorder'],
        'operator' => 'IN'
    );
}

if (!empty($options['show_only_on_sale'])) {
    //Variable products are not handled
    $args['meta_query'][] = array(// Simple products type
        'key' => '_sale_price',
        'value' => 0,
        'compare' => '>',
        'type' => 'numeric'
    );
}

if (!empty($options['ids'])) {
    $args['post__in'] = explode(",", $options['ids']);
}

if (!empty($options['max'])) {
    $args['posts_per_page'] = (int) $options['max'];
}

Newsletter::instance()->switch_language( $options['language'] );

if ($context['type'] != 'automated') {

    $products = Newsletter::instance()->get_posts($args, $options['language']);
} else {

    if (!empty($options['automated_disabled'])) {

        $products = Newsletter::instance()->get_posts($args, $options['language']);
    } else {

        // Can be empty when composing...
        if (!empty($context['last_run'])) {
            $args['date_query'] = array(
                'after' => gmdate('c', $context['last_run'])
            );
        }

        $products = Newsletter::instance()->get_posts($args, $options['language']);

        if (empty($products)) {
            if ($options['automated'] == '1') {
                $out['stop'] = true;
                return;
            } else if ($options['automated'] == '2') {
                $out['skip'] = true;
                return;
            } else {
                echo '<div inline-class="nocontents">', $options['automated_no_contents'], '</div>';
                return;
            }
        } else {
            if ($options['automated_include'] == 'max') {
                unset($args['date_query']);
                $products = Newsletter::instance()->get_posts($args, $options['language']);
            }
        }
    }
}

if (!empty($products)) {
    $out['subject'] = $products[0]->post_title;
}


// Available since WC 2.5
$cart_url = wc_get_cart_url();

// Style variables
$button_background = $options['button_background'];

$scaled_title_font_size = floor($global_title_font_size * ( 1.1 - 0.15 * $options['columns'] ));

$title_font_family = empty($options['title_font_family']) ? $global_title_font_family : $options['title_font_family'];
$title_font_size = empty($options['title_font_size']) ? $scaled_title_font_size : $options['title_font_size'];
$title_font_color = empty($options['title_font_color']) ? $global_title_font_color : $options['title_font_color'];
$title_font_weight = empty($options['title_font_weight']) ? $global_title_font_weight : $options['title_font_weight'];

$text_font_family = empty($options['font_family']) ? $global_text_font_family : $options['font_family'];
$text_font_size = empty($options['font_size']) ? $global_text_font_size : $options['font_size'];
$text_font_color = empty($options['font_color']) ? $global_text_font_color : $options['font_color'];
$text_font_weight = empty($options['font_weight']) ? $global_text_font_weight : $options['font_weight'];
?>

<style>
    .title {
        font-family: <?php echo $title_font_family ?>;
        font-size: <?php echo $title_font_size ?>px;
        font-weight: <?php echo $title_font_weight ?>;
        color: <?php echo $title_font_color ?>;
        line-height: normal;
        padding: 15px 0 0 0;
        height: 60px;
    }

    .price {
        font-family: <?php echo $text_font_family ?>;
        font-size: <?php echo round($text_font_size * 1.2) ?>px;
        color: <?php echo $text_font_color ?>;
        font-weight: bold;
        line-height: 1.5em;
        padding: 15px 0 0 0;
    }

    .price-sale {
         color: #a4a4a4;
         font-family: <?php echo $text_font_family ?>;
         font-size: <?php echo $text_font_size ?>px;
         text-align: center;
         line-height: normal;
         padding: 0;
    }

    .excerpt {
        font-family: <?php echo $text_font_family ?>;
        font-size: <?php echo $text_font_size ?>px;
        font-weight: <?php echo $text_font_weight ?>;
        color: <?php echo $text_font_color ?>;
        line-height: 1.5em;
        padding: 5px 0 0 0;
    }

    .button {
        padding: 15px 0;
        line-height: normal;
    }
</style>
<?php
//$button_template = file_get_contents(plugin_dir_path(__FILE__) . 'templates' . DIRECTORY_SEPARATOR . 'product-button.php');
$price_template = file_get_contents(plugin_dir_path(__FILE__) . 'templates' . DIRECTORY_SEPARATOR . 'product-price.php');
$excerpt_template = file_get_contents(plugin_dir_path(__FILE__) . 'templates' . DIRECTORY_SEPARATOR . 'product-excerpt.php');
$product_template = file_get_contents(plugin_dir_path(__FILE__) . 'templates' . DIRECTORY_SEPARATOR . 'product.php');

$container = new TNP_Composer_Grid_System($options['columns']);

$items = [];

Newsletter::instance()->switch_language( $options['language'] );

$product_images        = tnp_resize_product_list_featured_image( $products, [ $image_width, 0 ] );
$image_max_height_html = "height=\"" . tnp_get_max_height_of( $product_images ) . "\"";

foreach ($products as $idx => $p) {

    setup_postdata($p);
    // Product initialization
    $wcproduct = wc_get_product($p->ID);

    $price_html = '';

    if (!empty($options['show_price']) && !empty($wcproduct->get_price_html())) {

        $onsale_label = '&nbsp;';
        if ($wcproduct->is_on_sale()) {
            $onsale_label = $options['on_sale_label'];
        }

        $price_html = str_replace('TNP_ON_SALE', $onsale_label, $price_template);
        $price_html = str_replace('TNP_WC_PRICE', $wcproduct->get_price_html(), $price_html);
    }

    $excerpt_html = '';
    if (!empty($options['show_excerpt'])) {
        $excerpt_html = str_replace('TNP_EXCERPT_PH', tnp_post_excerpt($p), $excerpt_template);
    }

    $button_html = '';
    if (!empty($options['product_button'])) {
        $button_options = [];
        $button_options['button_font_family'] = $text_font_family;
        $button_options['button_font_size'] = $text_font_size;
        $button_options['button_font_color'] = '#ffffff';
        $button_options['button_font_weight'] = $text_font_weight;
        $button_options['button_background'] = $button_background;

        if ($options['product_button'] == 'cart') {
            $button_url = add_query_arg(['add-to-cart' => $p->ID], $cart_url);
        } elseif ($options['product_button'] == 'view') {
            $button_url = $wcproduct->get_permalink();
        }

        $button_options['button_url'] = $button_url;
        $button_options['button_label'] = $options['product_button_text'];

        $button_html = '<tr><td align="center" inline-class="button">' . TNP_Composer::button($button_options) . '</td></tr>';
    }

	$image = $product_images[ $p->ID ];
    $image_html = '';
    if ($image) {
        $image->link = $wcproduct->get_permalink();
        $image_html = TNP_Composer::image($image);
    }


    $product_html = str_replace(
            [
                'TNP_PRODUCT_PERMALINK_PH',
                'TNP_PRODUCT_MEDIA_PH',
                'TNP_PRODUCT_MEDIA_HEIGHT_PH',
                'TNP_PRODUCT_TITLE_PH',
                'TNP_PRODUCT_EXCERPT_PH',
                'TNP_PRODUCT_PRICE_PH',
                'TNP_PRODUCT_BUTTON_PH',
            ],
            [
                get_permalink($p),
                $image_html,
	            $options['columns'] > 1 ? $image_max_height_html : '',
                tnp_post_title($p),
                $excerpt_html,
                $price_html,
                $button_html,
            ],
            $product_template);

    $items[] = $product_html;

    //$cell = new TNP_Composer_Grid_Cell($product_html);
    //$container->add_cell($cell);
}

//var_dump($items);
//echo $container;
echo TNP_Composer::grid($items, ['columns' => $options['columns'], 'width' => 600 - $options['block_padding_left'] - $options['block_padding_right']]);
?>
