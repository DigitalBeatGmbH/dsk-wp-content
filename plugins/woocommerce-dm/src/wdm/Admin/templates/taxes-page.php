<?php
use Wcustom\Wdm\Dropshipping\Taxes;

/**
 * The form to be loaded on the plugin's admin page
 */
if( current_user_can( 'edit_users' ) ) {
    $taxes_form_response_nonce = wp_create_nonce( 'taxes_form_response_nonce' ); 
    
    if ( isset( $_REQUEST['admin_add_notice'] ) && $_REQUEST['admin_add_notice'] === "success") {
        $html =	'<div class="notice notice-success is-dismissible">
						<p><strong>'.__('The request was successful', 'wcustom').' </strong></p></div>';
        echo $html;
    }
    ?>
    <div class="wrap acf-settings-wrap">
        <h1 class="options-header-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
        
    	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="wcdm_taxes" >
        	<input type="hidden" name="action" value="taxes_form_response">
    		<input type="hidden" name="taxes_form_response_nonce" value="<?php echo $taxes_form_response_nonce ?>" />			
		
            <div id="poststuff">
    			<div id="post-body" class="metabox-holder columns-2">
    				<div id="postbox-container-1" class="postbox-container">
    					
    					<div id="side-sortables" class="meta-box-sortables ui-sortable">
        					<div id="submitdiv" class="postbox ">
        						<button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Bedienfeld umschalten: Veröffentlichen</span><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle ui-sortable-handle"><span>Veröffentlichen</span></h2>
                                <div class="inside">
                            		<div id="major-publishing-actions">
                            
                            			<div id="publishing-action">
                            				<span class="spinner"></span>
                            				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save') ?>">
                            			</div>
                            			
                						<div class="clear"></div>
                            		
                            		</div>
        						</div>
        					</div>
    					</div>						
    				</div>
    				
    				<div id="postbox-container-2" class="postbox-container">	
                        <table class="acf-table">
    						<thead>
                            	<tr>
                            		<th class="acf-th"><?php _e('Dropshipping Market', 'wcustom') ?></th>
                            		<th class="acf-th"><?php _e('WooCommerce', 'wcustom') ?></th>
                            	</tr>
    						</thead>
    						<tbody>
                                <?php 
                                
                                $sections = Taxes::get_wc_rates();
                                foreach($dm_taxes as $dm_tax) {
                                    ?>
                                	<tr class="acf-row">
                                		<td><?php echo get_field('dm_text', 'dm_taxes_' . $dm_tax->term_id) ?></td>
                                		<td>
                                			<select name="wcdm_tax_<?php echo $dm_tax->term_id ?>" data-name="dm_rate">
                                				<option value="0"></option>
                                				<?php 
                            				    foreach($sections as $slug => $label) {
                            				        $selected = get_option( 'woocommerce-dm_tax_' . $dm_tax->term_id);
                                				    echo '<option value="'.$slug.'" '.($selected == $slug ? "selected" : "").'>'.$label.'</option>';
                                				}
                                				?>
                                			</select>
                                		</td>
                                	</tr>
                                    <?php 
                                }
                                ?>
                            </tbody>
                        </table>
    				</div>
    			</div>
    			<br class="clear">
    		</div>
		</form>
	</div>
    <?php    
} else {  
    ?>
	<p> <?php __("You are not authorized to perform this operation.", $this->plugin_name) ?> </p>
	<?php   
}