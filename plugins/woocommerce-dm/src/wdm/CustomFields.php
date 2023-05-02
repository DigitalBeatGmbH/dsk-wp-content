<?php

namespace Wcustom\Wdm;

use Wcustom\Wdm\Helper;

/**
 *
 * This class defines all custom fields. 
 *
 * @since      1.0.0
 * @author     WebZap <kontakt@webzap.eu>
 */
class CustomFields
{
    
    /**
     * The version of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    
    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $version )
    {
        $this->version = $version;
    }
    
    public function cptui_register_my_cpts_dm_log() {
        
        /**
         * Post Type: DM Logs.
         */
        
        $labels = array(
            "name" => __( "DM Logs", "twentynineteen" ),
            "singular_name" => __( "DM Log", "twentynineteen" ),
        );
        
        $args = array(
            "label" => __( "DM Logs", "twentynineteen" ),
            "labels" => $labels,
            "description" => "",
            "public" => false,
            "publicly_queryable" => false,
            "show_ui" => true,
            "delete_with_user" => false,
            "show_in_rest" => false,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            'menu_position' => 100,
            "show_in_nav_menus" => true,
            "exclude_from_search" => true,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => array( "slug" => "dm_log", "with_front" => true ),
            "query_var" => true,
            'capabilities' => array(
                'create_posts' => false,
            ),
            'supports' => array( 'title' ), 
        );
        
        register_post_type( "dm_log", $args );
    }
    
    
    public function cptui_register_my_taxes_dm_taxes()
    {
        
        /**
         * Taxonomy: DM Steuern.
         */
        
        $labels = array(
            "name" => __( "DM Steuern", "twentynineteen" ),
            "singular_name" => __( "DM Steuer", "twentynineteen" ),
        );
        
        $args = array(
            "label" => __( "DM Steuern", "twentynineteen" ),
            "labels" => $labels,
            "public" => false,
            "publicly_queryable" => false,
            "hierarchical" => false,
            "show_ui" => false,
            "show_in_menu" => false,
            "show_in_nav_menus" => false,
            "query_var" => false,
            "rewrite" => false,
            "show_admin_column" => false,
            "show_in_rest" => false,
            "rest_base" => "dm_taxes",
            "rest_controller_class" => "WP_REST_Terms_Controller",
            "show_in_quick_edit" => false,
        );
        register_taxonomy( "dm_taxes", array( "product" ), $args );
    }
    
    public function cptui_register_my_taxes_dm_dtimes()
    {
        
        /**
         * Taxonomy: DM Lieferzeiten.
         */
        
        $labels = array(
            "name" => __( "DM Lieferzeiten", "twentynineteen" ),
            "singular_name" => __( "DM Lieferzeit", "twentynineteen" ),
        );
        
        $args = array(
            "label" => __( "DM Lieferzeiten", "twentynineteen" ),
            "labels" => $labels,
            "public" => false,
            "publicly_queryable" => false,
            "hierarchical" => false,
            "show_ui" => false,
            "show_in_menu" => false,
            "show_in_nav_menus" => false,
            "query_var" => false,
            "rewrite" => false,
            "show_admin_column" => false,
            "show_in_rest" => false,
            "rest_base" => "dm_dtimes",
            "rest_controller_class" => "WP_REST_Terms_Controller",
            "show_in_quick_edit" => false,
        );
        register_taxonomy( "dm_dtimes", array( "product" ), $args );
    }
    
    public function cptui_register_my_taxes_dm_units()
    {
        
        /**
         * Taxonomy: DM Einheiten.
         */
        
        $labels = array(
            "name" => __( "DM Einheiten", "twentynineteen" ),
            "singular_name" => __( "DM Einheit", "twentynineteen" ),
        );
        
        $args = array(
            "label" => __( "DM Einheiten", "twentynineteen" ),
            "labels" => $labels,
            "public" => false,
            "publicly_queryable" => false,
            "hierarchical" => false,
            "show_ui" => false,
            "show_in_menu" => false,
            "show_in_nav_menus" => false,
            "query_var" => false,
            "rewrite" => false,
            "show_admin_column" => false,
            "show_in_rest" => false,
            "rest_base" => "dm_units",
            "rest_controller_class" => "WP_REST_Terms_Controller",
            "show_in_quick_edit" => false,
        );
        register_taxonomy( "dm_units", array( "product" ), $args );
    }

	public function register()
	{
	    $this->cptui_register_my_taxes_dm_dtimes();
	    $this->cptui_register_my_taxes_dm_units();
	    $this->cptui_register_my_taxes_dm_taxes();
	    $this->cptui_register_my_cpts_dm_log();
	    
	    $minDtimes = $maxDtimes = wp_count_terms('dm_dtimes');
	    $minUnits = $maxUnits = wp_count_terms('dm_units');
		
		if( function_exists('acf_add_local_field_group') ):
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d9338a57e376',
    		    'title' => 'DM Kategorie Felder',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d9338c251919',
    		            'label' => 'DM ID',
    		            'name' => 'dm_id',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'taxonomy',
    		                'operator' => '==',
    		                'value' => 'product_cat',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 0,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'top',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => '',
    		    'active' => true,
    		    'description' => '',
    		));
		
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d8f2afc56bcb',
    		    'title' => 'DM Media Felder',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d8f2b2e0e801',
    		            'label' => 'DM URL',
    		            'name' => 'dm_url',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'attachment',
    		                'operator' => '==',
    		                'value' => 'all',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 0,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'top',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => '',
    		    'active' => true,
    		    'description' => '',
    		));
		
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d74fc5e7d1a5',
    		    'title' => 'DM Einheiten Zuordnung',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d74fc5e830a7',
    		            'label' => 'DM Einheiten',
    		            'name' => 'dm_units',
    		            'type' => 'repeater',
    		            'instructions' => 'Bitte ordnen Sie hier jeder Dropshipping Marktplatz Einheit einen entsprechenden WooCommerce Wert zu.',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'collapsed' => '',
    		            'min' => $minUnits,
    		            'max' => $maxUnits,
    		            'layout' => 'table',
    		            'button_label' => '',
    		            'sub_fields' => array(
    		                array(
    		                    'key' => 'field_5d74fc5e854b0',
    		                    'label' => 'DM Einheit',
    		                    'name' => 'dm_unit',
    		                    'type' => 'taxonomy',
    		                    'instructions' => '',
    		                    'required' => 1,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'taxonomy' => 'dm_units',
    		                    'field_type' => 'radio',
    		                    'add_term' => 0,
    		                    'save_terms' => 0,
    		                    'load_terms' => 0,
    		                    'return_format' => 'id',
    		                    'multiple' => 0,
    		                    'allow_null' => 0,
    		                ),
    		                array(
    		                    'key' => 'field_5d74fc5e85479',
    		                    'label' => 'WC Einheit',
    		                    'name' => 'wc_unit',
    		                    'type' => 'taxonomy',
    		                    'instructions' => '',
    		                    'required' => 0,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'taxonomy' => 'product_unit',
    		                    'field_type' => 'select',
    		                    'allow_null' => 1,
    		                    'add_term' => 0,
    		                    'save_terms' => 0,
    		                    'load_terms' => 0,
    		                    'return_format' => 'id',
    		                    'multiple' => 0,
    		                ),
    		            ),
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'options_page',
    		                'operator' => '==',
    		                'value' => 'wcustom-punit',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 0,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'top',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => array(
    		        0 => 'permalink',
    		        1 => 'the_content',
    		        2 => 'excerpt',
    		        3 => 'discussion',
    		        4 => 'comments',
    		        5 => 'revisions',
    		        6 => 'slug',
    		        7 => 'author',
    		        8 => 'format',
    		        9 => 'page_attributes',
    		        10 => 'featured_image',
    		        11 => 'categories',
    		        12 => 'tags',
    		        13 => 'send-trackbacks',
    		    ),
    		    'active' => true,
    		    'description' => '',
    		));
		
		
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d74faa6d468e',
    		    'title' => 'DM Einheiten Felder',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d74faa6e398d',
    		            'label' => 'DM ID',
    		            'name' => 'dm_id',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d74faa6e39ca',
    		            'label' => 'DM Text',
    		            'name' => 'dm_text',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'taxonomy',
    		                'operator' => '==',
    		                'value' => 'dm_units',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 0,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'top',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => array(
    		        0 => 'permalink',
    		        1 => 'the_content',
    		        2 => 'excerpt',
    		        3 => 'discussion',
    		        4 => 'comments',
    		        5 => 'revisions',
    		        6 => 'slug',
    		        7 => 'author',
    		        8 => 'format',
    		        9 => 'page_attributes',
    		        10 => 'featured_image',
    		        11 => 'categories',
    		        12 => 'tags',
    		        13 => 'send-trackbacks',
    		    ),
    		    'active' => true,
    		    'description' => '',
    		));
		
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d74e47c4fa59',
    		    'title' => 'DM Lieferzeiten Felder',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d74e48679c31',
    		            'label' => 'DM ID',
    		            'name' => 'dm_id',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d74e49379c32',
    		            'label' => 'DM Text',
    		            'name' => 'dm_text',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'taxonomy',
    		                'operator' => '==',
    		                'value' => 'dm_dtimes',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 0,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'top',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => array(
    		        0 => 'permalink',
    		        1 => 'the_content',
    		        2 => 'excerpt',
    		        3 => 'discussion',
    		        4 => 'comments',
    		        5 => 'revisions',
    		        6 => 'slug',
    		        7 => 'author',
    		        8 => 'format',
    		        9 => 'page_attributes',
    		        10 => 'featured_image',
    		        11 => 'categories',
    		        12 => 'tags',
    		        13 => 'send-trackbacks',
    		    ),
    		    'active' => true,
    		    'description' => '',
    		));
    		
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d74d99d0e44f',
    		    'title' => 'DM Lieferzeiten Zuordnung',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d74d9b4c2f6f',
    		            'label' => 'DM Lieferzeiten',
    		            'name' => 'dm_dtimes',
    		            'type' => 'repeater',
    		            'instructions' => 'Bitte ordnen Sie hier jeder Dropshipping Marktplatz Lieferzeit einen entsprechenden WooCommerce Wert zu.',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'collapsed' => '',
    		            'min' => $minDtimes,
    		            'max' => $maxDtimes,
    		            'layout' => 'table',
    		            'button_label' => '',
    		            'sub_fields' => array(
    		                array(
    		                    'key' => 'field_5d74da43c2f71',
    		                    'label' => 'DM Lieferzeit',
    		                    'name' => 'dm_dtime',
    		                    'type' => 'taxonomy',
    		                    'instructions' => '',
    		                    'required' => 1,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'taxonomy' => 'dm_dtimes',
    		                    'field_type' => 'radio',
    		                    'allow_null' => 0,
    		                    'add_term' => 0,
    		                    'save_terms' => 0,
    		                    'load_terms' => 0,
    		                    'return_format' => 'id',
    		                    'multiple' => 0,
    		                ),
    		                array(
    		                    'key' => 'field_5d74d9eac2f70',
    		                    'label' => 'WC Lieferzeit',
    		                    'name' => 'wc_dtime',
    		                    'type' => 'taxonomy',
    		                    'instructions' => '',
    		                    'required' => 0,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'taxonomy' => 'product_delivery_time',
    		                    'field_type' => 'select',
    		                    'allow_null' => 1,
    		                    'add_term' => 0,
    		                    'save_terms' => 0,
    		                    'load_terms' => 0,
    		                    'return_format' => 'id',
    		                    'multiple' => 0,
    		                ),
    		            ),
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'options_page',
    		                'operator' => '==',
    		                'value' => 'wcustom-pdtime',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 0,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'top',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => '',
    		    'active' => true,
    		    'description' => '',
    		));
		
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d6bbfa17fa49',
    		    'title' => 'Dropshipping Marktplatz',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d6bbfc0d4eaf',
    		            'label' => 'Token',
    		            'name' => 'token',
    		            'type' => 'text',
    		            'instructions' => 'Bitte tragen Sie hier den Token des https://www.dropshipping-marktplatz.de ein',
    		            'required' => 1,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d6bd8d549e9c',
    		            'label' => 'Die beim DM eingetragene URL',
    		            'name' => 'yoururl',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 1,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		        array(
    		            'key' => 'field_5f6219a68d27d',
    		            'label' => 'User',
    		            'name' => 'user',
    		            'type' => 'user',
    		            'instructions' => 'Der User, dem die Produkte zugewiesen werden.
Falls dem User ein Shop zugewiesen ist, werden die Produkte diesem Shop zugeordnet.',
    		            'required' => 1,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'role' => '',
    		            'allow_null' => 0,
    		            'multiple' => 0,
    		            'return_format' => 'object',
    		        ),
    		        array(
    		            'key' => 'field_5d946e5e3ae5b',
    		            'label' => 'Max. Bilder Anzahl pro Produkt',
    		            'name' => 'max_images',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => 15,
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d946e743ae5c',
    		            'label' => 'Max. Produkte pro Cronjob Aufruf',
    		            'name' => 'max_products',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => 250,
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d753edc322f5',
    		            'label' => 'Aktualisierungseinstellungen',
    		            'name' => 'refresh_settings',
    		            'type' => 'group',
    		            'instructions' => 'Folgende Attribute werden beim Aktualisieren beachtet...',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'layout' => 'block',
    		            'sub_fields' => array(
    		                array(
    		                    'key' => 'field_5d753ef2322f6',
    		                    'label' => 'Hersteller',
    		                    'name' => 'manufacturer',
    		                    'type' => 'true_false',
    		                    'instructions' => '',
    		                    'required' => 0,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'message' => '',
    		                    'default_value' => 1,
    		                    'ui' => 1,
    		                    'ui_on_text' => '',
    		                    'ui_off_text' => '',
    		                ),
    		                array(
    		                    'key' => 'field_5d753f74322f7',
    		                    'label' => 'Name',
    		                    'name' => 'name',
    		                    'type' => 'true_false',
    		                    'instructions' => '',
    		                    'required' => 0,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'message' => '',
    		                    'default_value' => 1,
    		                    'ui' => 1,
    		                    'ui_on_text' => '',
    		                    'ui_off_text' => '',
    		                ),
    		                array(
    		                    'key' => 'field_5d753f7f322f8',
    		                    'label' => 'Beschreibung',
    		                    'name' => 'description',
    		                    'type' => 'true_false',
    		                    'instructions' => '',
    		                    'required' => 0,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'message' => '',
    		                    'default_value' => 1,
    		                    'ui' => 1,
    		                    'ui_on_text' => '',
    		                    'ui_off_text' => '',
    		                ),
    		                array(
    		                    'key' => 'field_5d753f91322f9',
    		                    'label' => 'Preise',
    		                    'name' => 'prices',
    		                    'type' => 'true_false',
    		                    'instructions' => '',
    		                    'required' => 0,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'message' => '',
    		                    'default_value' => 1,
    		                    'ui' => 1,
    		                    'ui_on_text' => '',
    		                    'ui_off_text' => '',
    		                ),
    		                array(
    		                    'key' => 'field_5d753f9e322fa',
    		                    'label' => 'EAN',
    		                    'name' => 'ean',
    		                    'type' => 'true_false',
    		                    'instructions' => '',
    		                    'required' => 0,
    		                    'conditional_logic' => 0,
    		                    'wrapper' => array(
    		                        'width' => '',
    		                        'class' => '',
    		                        'id' => '',
    		                    ),
    		                    'message' => '',
    		                    'default_value' => 1,
    		                    'ui' => 1,
    		                    'ui_on_text' => '',
    		                    'ui_off_text' => '',
    		                ),
    		            ),
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'options_page',
    		                'operator' => '==',
    		                'value' => 'wcustom-options',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 0,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'top',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => '',
    		    'active' => true,
    		    'description' => '',
    		));
    		
    		/* Product Data */
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d7540559d0ce',
    		    'title' => 'DM Produktfelder',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d754bce9fb53',
    		            'label' => 'DM Produkt ID',
    		            'name' => 'dm_product_id',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d75406059d24',
    		            'label' => 'DM Name',
    		            'name' => 'dm_name',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => 'hidden_wcdm',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d75409159d25',
    		            'label' => 'Hersteller',
    		            'name' => 'dm_manufacturer',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => 'hidden_wcdm',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d75450c59d26',
    		            'label' => 'Beschreibung',
    		            'name' => 'dm_description',
    		            'type' => 'textarea',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => 'hidden_wcdm',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'maxlength' => '',
    		            'rows' => '',
    		            'new_lines' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d75451c59d27',
    		            'label' => 'EAN',
    		            'name' => 'dm_ean',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => 'hidden_wcdm',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d91f8a563428',
    		            'label' => 'DM EK (netto)',
    		            'name' => 'dm_ek',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		        array(
    		            'key' => 'field_5df0e6b41608b',
    		            'label' => 'DM SKU',
    		            'name' => 'dm_sku',
    		            'type' => 'text',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'maxlength' => '',
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'post_type',
    		                'operator' => '==',
    		                'value' => 'product',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 10,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'left',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => '',
    		    'active' => true,
    		    'description' => '',
    		));
    		
    		acf_add_local_field_group(array(
    		    'key' => 'group_5d8f3ebd28063',
    		    'title' => 'DM Log Felder',
    		    'fields' => array(
    		        array(
    		            'key' => 'field_5d8f3eebedbd3',
    		            'label' => 'Aktualisiert',
    		            'name' => 'updated',
    		            'type' => 'textarea',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'maxlength' => '',
    		            'rows' => 20,
    		            'new_lines' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d8f3f02edbd4',
    		            'label' => 'Hinzugefügt',
    		            'name' => 'added',
    		            'type' => 'textarea',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'maxlength' => '',
    		            'rows' => 20,
    		            'new_lines' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d8f3f0dedbd5',
    		            'label' => 'Deaktiviert',
    		            'name' => 'deactivated',
    		            'type' => 'textarea',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'maxlength' => '',
    		            'rows' => 20,
    		            'new_lines' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d933f60180c2',
    		            'label' => 'Anzahl empfangener Artikel',
    		            'name' => 'got_nr',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d933e26e1385',
    		            'label' => 'Anzahl aktualisierter Artikel',
    		            'name' => 'updated_nr',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d933e41e1386',
    		            'label' => 'Anzahl ersteller Artikel',
    		            'name' => 'added_nr',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d933e4ee1387',
    		            'label' => 'Anzahl deaktivierter Artikel',
    		            'name' => 'deactivated_nr',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		        array(
    		            'key' => 'field_5d933f75180c3',
    		            'label' => 'Anzahl ignorierter Artikel',
    		            'name' => 'ignored_nr',
    		            'type' => 'number',
    		            'instructions' => '',
    		            'required' => 0,
    		            'conditional_logic' => 0,
    		            'wrapper' => array(
    		                'width' => '',
    		                'class' => '',
    		                'id' => '',
    		            ),
    		            'default_value' => '',
    		            'placeholder' => '',
    		            'prepend' => '',
    		            'append' => '',
    		            'min' => '',
    		            'max' => '',
    		            'step' => '',
    		        ),
    		    ),
    		    'location' => array(
    		        array(
    		            array(
    		                'param' => 'post_type',
    		                'operator' => '==',
    		                'value' => 'dm_log',
    		            ),
    		        ),
    		    ),
    		    'menu_order' => 0,
    		    'position' => 'normal',
    		    'style' => 'default',
    		    'label_placement' => 'top',
    		    'instruction_placement' => 'label',
    		    'hide_on_screen' => array(
    		        0 => 'permalink',
    		        1 => 'the_content',
    		        2 => 'excerpt',
    		        3 => 'discussion',
    		        4 => 'comments',
    		        5 => 'revisions',
    		        6 => 'slug',
    		        7 => 'author',
    		        8 => 'format',
    		        9 => 'page_attributes',
    		        10 => 'featured_image',
    		        11 => 'categories',
    		        12 => 'tags',
    		        13 => 'send-trackbacks',
    		    ),
    		    'active' => true,
    		    'description' => '',
    		));

		endif;
	} 
} 