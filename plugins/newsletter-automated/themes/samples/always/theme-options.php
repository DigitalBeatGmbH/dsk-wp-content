<?php
/*
 */
$theme_defaults = array(
    'theme_content' => '<p>Write here your fixed newsletter content</a>',
);

// Mandatory!
$controls->merge_defaults($theme_defaults);
?>
<table class="form-table">
    <tr valign="top">
        <th>Content</th>
        <td>
            <?php $controls->wp_editor('theme_content'); ?>
        </td>
    </tr>
</table>
