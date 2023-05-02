<?php

global $controls;
global $wpdb;
require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$module = NewsletterAutoresponder::$instance;
$controls = new NewsletterControls();

$autoresponder_id = (int) $_GET['id'];

$autoresponder = $module->get_autoresponder($autoresponder_id);

if (!$controls->is_action()) {
    $controls->data = $autoresponder->theme;
    
    if ($controls->data === false) {
        $controls->data = array();
    }
} else {
    if ($controls->is_action('save')) {
        $autoresponder->theme = $controls->data;
        $module->save_autoresponder($autoresponder);
        $controls->messages = 'Saved';
    }
    
    if ($controls->is_action('change')) {
        $autoresponder->theme = $controls->data;
        $controls->messages = 'Theme loaded you should save when done with settings.';
    }
    
    if ($controls->is_action('reset')) {
        $controls->data = array('theme'=>$controls->data['theme']);
        $controls->messages = 'Theme re-loaded you should save when done with settings.';
    }    
    
    if ($controls->is_action('test')) {

        // Get test content
        ob_start();
        include __DIR__ . '/test-content.php';
        $message = ob_get_clean();
        
        // Temporary setting to apply the template
        $autoresponder->theme = $controls->data;
        $message = $module->apply_template($message, $autoresponder);
        
        $users = NewsletterUsers::instance()->get_test_users();
        if (count($users) == 0) {
            $controls->errors = '<strong>' . __('There are no test subscribers to send to', 'newsletter') . '</strong>';
        } else {
            $emails = array();
            
            foreach ($users as $user) {
                Newsletter::instance()->mail($user->email, 'Newsletter Autoresponder theme test ' . $autoresponder_id, $message);
                $emails[] = $user->email;
            }
            $controls->messages = 'Test sent to: ' . implode(', ', $emails);
            $controls->messages .= '<br><strong>On test messages the view online link does not work</strong>';
        }
    }
    
    if ($controls->is_action('preview')) {
        $theme = $module->get_theme($controls->data['theme']);
        $theme_options = $controls->data;
        $theme_defaults_file = $theme['dir'] .  '/theme-defaults.php';
        if (file_exists($theme_defaults_file)) {
            @include $theme_defaults_file;
            if (is_array($theme_defaults)) {
                $theme_options = array_merge($theme_defaults, $theme_options); 
            }
        }
        include $theme['dir'] . '/theme.php';
        die();
    }
}

$theme = $module->get_theme($controls->data['theme']);
if (is_null($theme)) $theme = $module->get_theme('default');

$theme_options_file = $theme['dir'] . '/theme-options.php';

$theme_defaults_file = $theme['dir'] .  '/theme-defaults.php';
if (file_exists($theme_defaults_file)) {
    include $theme_defaults_file;
    $controls->data = array_merge($theme_defaults, $controls->data); 
}

$themes = $module->get_themes();
$theme_select_options = array();
foreach ($themes as $t) {
    $theme_select_options[$t['id']] = $t['name'];
}

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/codemirror.css" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/addon/hint/show-hint.css">
<style>    
.CodeMirror {
            height: 100%;
        }
        
        table.form-table {
            margin-top: 0!important;
        }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/codemirror.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/xml/xml.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/css/css.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/javascript/javascript.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/htmlmixed/htmlmixed.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/addon/hint/show-hint.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/addon/hint/xml-hint.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/addon/hint/html-hint.js"></script>
<script>
    function tnp_autoresponder_preview() {
        var form = document.getElementById("tnp-autoresponder");
        form.action = "?page=newsletter_autoresponder_theme&noheader=1&id=<?php echo $autoresponder_id?>";
        form.elements["act"].value = "preview";
        form.target = "tnp-autoresponder-preview-desktop";
        form.submit();
        form.target = "";
        form.action = "";
    }
    
    jQuery(function () {
        tnp_autoresponder_preview();
        
//        templateEditor = CodeMirror.fromTextArea(document.getElementById("options-template"), {
//            lineNumbers: true,
//            mode: 'htmlmixed',
//            extraKeys: {"Ctrl-Space": "autocomplete"}
//        });
        
        
    });
    
    
</script>
<div class="wrap" id="tnp-wrap">
    <?php @include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">

        <h2>Autoresponder Theme</h2>

        <?php $controls->show(); ?>

    </div>
    <div id="tnp-body">

        <form method="post" action="" id="tnp-autoresponder">
            <?php $controls->init(); ?>

            <p>
                <?php $controls->button_back('?page=newsletter_autoresponder_index') ?>
                
                <?php $controls->button_primary('test', 'Test'); ?>
                <?php $controls->button_primary('save', 'Save'); ?>
                
                &nbsp;&nbsp;&nbsp;&nbsp;
                
                <?php $controls->select('theme', $theme_select_options); ?>
                <?php $controls->button_primary('change', 'Load'); ?>
                <?php $controls->button_confirm('reset', 'Reset', 'Proceed?'); ?>
                <?php if ($theme['preview']) { ?>
                <input class="button-primary" type="button" onclick="tnp_autoresponder_preview()" value="Refresh">
                <?php } ?>
            </p>
            
            <?php if ($theme['preview']) { ?>
            <div class="row">
                <div class="col-md-4">
                    <div style="background-color: #fff">
                    <?php include $theme_options_file ?>
                    </div>
                </div>
                
                <div class="col-md-7">
                    <iframe name="tnp-autoresponder-preview-desktop" id="tnp-autoresponder-preview-desktop" style="width: 700px; height: 600px"></iframe>
                </div>
                
                <!--
                <div class="col-md-2">
                </div>
                -->
                
            </div>
            <?php } else { ?>
            <?php include $theme_options_file ?>
            <?php } ?>

        </form>

    </div>
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>