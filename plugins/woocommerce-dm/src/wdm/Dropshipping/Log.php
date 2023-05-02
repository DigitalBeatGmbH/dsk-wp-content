<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;

/**
 * The log specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class Log
{
    public $id;
    
    public $type; // not saved!
    public $start = null;
    public $until;
    public $title;
    public $updated;
    public $added;
    public $deactivated;
    public $updated_nr;
    public $added_nr;
    public $deactivated_nr;
    public $got_nr;
    public $ignored_nr;

    /**
     * @param unknown $type
     */
    public function __construct($type = 'wordpress')
    {
        $this->type = $type;
        
        if($type == 'wordpress') {
            return $this;
        }
        
        date_default_timezone_set(get_option('timezone_string'));
        
        $this->setTitle(($type == 'complete' ? 'Startet in Kürze: Vollimport' : 'Lagerbestands Update'));
    }
    
    /**
     * this is a wordpress function
     * to add some rows to backend table
     * 
     * @param unknown $columns
     * @return unknown
     */
    public function set_custom_columns($columns)
    {
        
        $columns['cdate'] = __( 'Date' );
        $columns['got_nr'] = __( 'Number of recieved products', 'wcustom' );
        $columns['updated_nr'] = __( 'Number of updated products', 'wcustom' );
        $columns['added_nr'] = __( 'Number of added products', 'wcustom' );
        $columns['deactivated_nr'] = __( 'Number of deactivated products', 'wcustom' );
        $columns['ignored_nr'] = __( 'Number of ignored products', 'wcustom' );
        
        return $columns;
    }
    
    /**
     * this is a wordpress function
     * to add some content to the wordpress backend rows
     *
     * @param unknown $column
     * @param unknown $post_id
     */
    public function old_columns($columns)
    {
        unset($columns['date']);
        return $columns;
    }
    
    /**
     * this is a wordpress function
     * to add some content to our custom backend rows
     * 
     * @param unknown $column
     * @param unknown $post_id
     */
    public function custom_columns($column, $post_id)
    {
        switch ( $column ) {
            case 'cdate':
                echo get_the_date('d.m.Y H:i:s', $post_id);
                break;
            case 'got_nr' :
                echo get_field('got_nr', $post_id);
                break;
            case 'updated_nr' :
                echo get_field('updated_nr', $post_id);
                break;
            case 'added_nr' :
                echo get_field('added_nr', $post_id);
                break;
            case 'deactivated_nr' :
                echo get_field('deactivated_nr', $post_id);
                break;
            case 'ignored_nr' :
                echo get_field('ignored_nr', $post_id);
                break;
        }
    }
    
    /**
     * @param unknown $id
     * @param unknown $product
     */
    public function add_product($id, $product)
    {
        $added = $this->getAdded();
        $added .= 'WP ID: ' . $id . ' - DM Data: ' . json_encode($product) ."\n";
        $this->setAdded($added);
        $this->incrAdded_nr();
    }
    
    /**
     * @param unknown $id
     * @param unknown $product
     */
    public function update_product($id, $product)
    {
        $updated = $this->getUpdated();
        $updated .= 'WP ID: ' . $id . ' - DM Data: ' . json_encode($product) ."\n";
        $this->setUpdated($updated);
        $this->incrUpdated_nr();
    }
    
    /**
     * @param unknown $id
     */
    public function deactivate_product($id, $product, $message)
    {
        $deactivated = $this->getDeactivated();
        $deactivated .= $id ." - ".$message."\n";
        $this->setDeactivated($deactivated);
        $this->incrDeactivated_nr();
    }
    
    /**
     * @param unknown $id
     * @return \Wcustom\Dropshipping\Log
     */
    public function load($id)
    {
        $this->setId($id);
        $this->setTitle(get_the_title($id));
        $this->setGot_nr(get_field('got_nr', $id));
        $this->setIgnored_nr(get_field('ignored_nr', $id));
        $this->setUpdated_nr(get_field('updated_nr', $id));
        $this->setAdded_nr(get_field('added_nr', $id));
        $this->setDeactivated_nr(get_field('deactivated_nr', $id));
        $this->setUpdated(get_field('updated', $id));
        $this->setAdded(get_field('added', $id));
        $this->setDeactivated(get_field('deactivated', $id));
        return $this;
    }
    
    /**
     * @param string $finished
     * @return unknown
     */
    public function save($finished = false)
    {
        $title = '';
        if($finished) {
            $title = 'Fertiggestellt: ';
        } elseif($this->getStart() !== null) {
            $title = 'Läuft: '. ($this->getAdded_nr() + $this->getUpdated_nr() + $this->getDeactivated_nr() + $this->getIgnored_nr()) . ' von ' . $this->getGot_nr() . ', ';
        } else {
            $title = 'Startet in Kürze: ';
        }
        
        $title = ($this->getType() == 'complete' ? ($title . ' Vollimport') : 'Lagerbestands Update');
        
        if($this->getId()) {
            $log = get_post($this->getId());
        }
        
        if(empty($log)) {
            $id = wp_insert_post([
                'post_status' => 'publish',
                'post_title' => $title,
                'post_type' => 'dm_log',
            ]);
        } else {
            $id = $log->ID;
            $log_data = [
                'ID' => $id,
                'post_title' => $title,
            ];
            wp_update_post( $log_data );
        }
        
        update_field('got_nr', $this->getGot_nr(), $id);
        update_field('ignored_nr', $this->getIgnored_nr(), $id);
        update_field('updated_nr', $this->getUpdated_nr(), $id);
        update_field('added_nr', $this->getAdded_nr(), $id);
        update_field('deactivated_nr', $this->getDeactivated_nr(), $id);
        update_field('updated', $this->getUpdated(), $id);
        update_field('added', $this->getAdded(), $id);
        update_field('deactivated', $this->getDeactivated(), $id);
        
        $this->setId($id);
        return $id;
    }
    
    /**
     * change some text on admin pages
     * @param string $translation
     * @param string $text
     * @param string $domain
     * @return string
     */
    public function adminGetText($translation, $text, $domain) {
        if ($domain == 'default') {
            if ($text == 'Edit &#8220;%s&#8221;') {
                $translation = 'View &#8220;%s&#8221;';
            }
        }
        
        return $translation;
    }
    
    /**
     * remove views we don't need from post list
     * @param array $views
     * @return array
     */
    public function adminViewsEdit($views) {
        unset($views['publish']);
        unset($views['draft']);
        
        return $views;
    }
    
    /**
     * remove unwanted actions from post list
     * @param array $actions
     * @param WP_Post $post
     * @return array
     */
    public function adminPostRowActions($actions, $post) {
        unset($actions['inline hide-if-no-js']);        // "quick edit"
        unset($actions['trash']);
        unset($actions['edit']);
        
        if ($post && $post->ID) {
            // add View link
            $actions['view'] = sprintf('<a href="%s" title="%s">%s</a>',
                get_edit_post_link($post->ID),
                __('View', 'log-emails'), __('View'));
            
            // add Delete link
            $actions['delete'] = sprintf('<a href="%s" title="%s" class="submitdelete">%s</a>',
                get_delete_post_link($post->ID, '', true),
                __('Delete', 'log-emails'), __('Delete'));
        }
        
        return $actions;
    }
    
    /**
     * change the list of available bulk actions
     * @param array $actions
     * @return array
     */
    public function adminBulkActionsEdit($actions) {
        unset($actions['edit']);
        
        return $actions;
    }
    
    /**
     * change the screen layout
     */
    public function adminScreenLayout() {
        // set max / default layout as single column
        add_screen_option('layout_columns', array('max' => 1, 'default' => 1));
    }
    
    /**
     * drop all the metaboxes and output what we want to show
     */
    public function adminEditAfterTitle($post) {
        global $wp_meta_boxes;
        
        $wp_meta_boxes['dm_log']['side'] = [];
    }
    
    /**
     * replace Trash bulk actions with Delete
     * NB: WP admin already handles the delete action, it just doesn't expose it as a bulk action
     */
    public function adminPrintFooterScripts() {
        ?>
        <script>
            jQuery(document).ready(function($){
                jQuery("select[name='action'],select[name='action2']").find("option[value='trash']").each(function() {
                    this.value = 'delete';
                    jQuery(this).text("<?php esc_attr_e('Delete'); ?>");
                });
                jQuery('#acf-group_5d8f3ebd28063 input, #acf-group_5d8f3ebd28063 textarea, [name="post_title"]').prop('disabled', true);
            });
        </script>

        <?php
    }
    
    /**
     * @return the $start
     */
    public function getStart()
    {
        return $this->start;
    }
    
    /**
     * @return the $until
     */
    public function getUntil()
    {
        return $this->until;
    }
    
    /**
     * @param field_type $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }
    
    /**
     * @param field_type $until
     */
    public function setUntil($until)
    {
        $this->until = $until;
    }
    
    /**
     * @return the $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return the $updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return the $added
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @return the $deactivated
     */
    public function getDeactivated()
    {
        return $this->deactivated;
    }

    /**
     * @param field_type $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param field_type $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @param field_type $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * @param field_type $deactivated
     */
    public function setDeactivated($deactivated)
    {
        $this->deactivated = $deactivated;
    }
    /**
     * @return the $updated_nr
     */
    public function getUpdated_nr()
    {
        return $this->updated_nr;
    }

    /**
     * @return the $added_nr
     */
    public function getAdded_nr()
    {
        return $this->added_nr;
    }

    /**
     * @return the $deactivated_nr
     */
    public function getDeactivated_nr()
    {
        return $this->deactivated_nr;
    }

    /**
     * @param field_type $updated_nr
     */
    public function setUpdated_nr($updated_nr)
    {
        $this->updated_nr = $updated_nr;
    }

    /**
     * @param field_type $added_nr
     */
    public function setAdded_nr($added_nr)
    {
        $this->added_nr = $added_nr;
    }

    /**
     * @param field_type $deactivated_nr
     */
    public function setDeactivated_nr($deactivated_nr)
    {
        $this->deactivated_nr = $deactivated_nr;
    }
    
    /**
     * increase number by 1
     */
    public function incrUpdated_nr()
    {
        $this->updated_nr++;
    }
    
    /**
     * increase number by 1
     */
    public function incrAdded_nr()
    {
        $this->added_nr++;
    }
    
    /**
     * increase number by 1
     */
    public function incrDeactivated_nr()
    {
        $this->deactivated_nr++;
    }
    /**
     * @return the $got_nr
     */
    public function getGot_nr()
    {
        return $this->got_nr;
    }

    /**
     * @return the $ignored_nr
     */
    public function getIgnored_nr()
    {
        return $this->ignored_nr;
    }

    /**
     * @param field_type $got_nr
     */
    public function setGot_nr($got_nr)
    {
        $this->got_nr = $got_nr;
    }

    /**
     * @param field_type $ignored_nr
     */
    public function setIgnored_nr($ignored_nr)
    {
        $this->ignored_nr = $ignored_nr;
    }
    /**
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param field_type $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param unknown $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    /**
     * @return the $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }






}