<?php
defined('ABSPATH') || exit;
/* @var $controls NewsletterControls */

?>

<table class="form-table">
    <!--
    <tr>
        <th>Logo</th>
        <td>
            <?php $controls->yesno('logo'); ?>
            <p class="description">If supported by your theme</p>
        </td>
    </tr>
    -->
    <tr>
        <th>Header banner</th>
        <td>
            <?php $controls->media('logo'); ?>
            <p class="description">Could be your logo or a custom made banner (suggested) for your series topic.</p>
        </td>
    </tr>
    <tr>
        <th>Font</th>
        <td>
            <?php $controls->css_font_family('font_family'); ?>
            <?php $controls->css_font_size('font_size'); ?>
        </td>
    </tr>
    <tr>
        <th>Link color</th>
        <td>
            <?php $controls->color('link_color'); ?>
        </td>
    </tr>
    <tr>
        <th>Closing text</th>
        <td>
            <?php $controls->textarea('closing'); ?>
            <p class="description">Leave empty if you add a closing text on each step message.</p>
        </td>
    </tr>
    <tr>
        <th>Labels</th>
        <td>
            <?php $controls->text('label_view_online', 50, 'View online'); ?>
            <?php $controls->text('label_profile', 50, 'Change your preferences'); ?>
        </td>
    </tr>
</table>
