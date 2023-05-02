<?php
defined('ABSPATH') || exit;
/* @var $controls NewsletterControls */

?>

<?php if (strpos($controls->data['html'], '{message}') === false) { ?>
<div class="tnp-warning">
    The HTML does not contain the required <code>{message}</code> tag.
</div>
<?php } ?>
<script>
    var templateEditor;
    jQuery(function () {
        templateEditor = CodeMirror.fromTextArea(document.getElementById("options-html"), {
            lineNumbers: true,
            mode: 'htmlmixed',
            lineWrapping: true,
            extraKeys: {"Ctrl-Space": "autocomplete"}
        });
    });
</script>
<?php $controls->textarea_preview('html', '100%', 600); ?>
