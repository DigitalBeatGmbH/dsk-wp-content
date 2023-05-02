<div class="wrap">
    <div class="options-page-header">
        <h1 class="options-header-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
    </div>
    <p>
    	<b><?php _e('Import DM delivery times', 'wcustom') ?></b><br>
    	<a href="#" class="dm_ajax_call" data-type="import_dtimes">Start</a>
    </p>
    <p>
    	<b><?php _e('Import DM units', 'wcustom') ?></b><br>
    	<a href="#" class="dm_ajax_call" data-type="import_units">Start</a>
    </p>
    <p>
    	<b><?php _e('Import DM taxes', 'wcustom') ?></b><br>
    	<a href="#" class="dm_ajax_call" data-type="import_taxes">Start</a>
    </p>
    <p>
    	<b><?php _e('Start import of all products', 'wcustom') ?></b><br>
    	<a href="#" class="dm_ajax_call" data-type="import_all">Start</a>
    </p>
    <p>
    	<b><?php _e('Start stock update of all products', 'wcustom') ?></b><br>
    	<a href="#" class="dm_ajax_call" data-type="import_stock">Start</a>
    </p>
</div>