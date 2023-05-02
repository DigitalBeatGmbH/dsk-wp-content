<?php
/* @var $this NewsletterLeads */

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$newsletter = Newsletter::instance();

$current_language = $this->get_current_language();

$is_all_languages = $this->is_all_languages();

$controls->add_language_warning();

if (!$controls->is_action()) {
    $controls->data = $this->get_options($current_language);
} else {

    if ($controls->is_action('save')) {

        if ($is_all_languages) {
            if (!is_numeric($controls->data['width'])) {
                $controls->data['width'] = 600;
            }
            if (!is_numeric($controls->data['height'])) {
                $controls->data['height'] = 500;
            }
            if (!is_numeric($controls->data['days'])) {
                $controls->data['days'] = 365;
            }
            if (!is_numeric($controls->data['delay'])) {
                $controls->data['delay'] = 2;
            }
        }
        $this->save_options($controls->data, $current_language);
        $controls->add_message_saved();
    }
}
?>

<style>
<?php
include dirname(__FILE__) . '/css/leads-admin.css';
?>
    
</style>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">

        <h2>Newsletter Leads Configuration</h2>

        <?php $controls->show(); ?>
    </div>

    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>

            <p>
                <?php $controls->button_primary('save', __('Save', 'newsletter')); ?>
                <a href="<?php echo get_option('home'); ?>?newsletter_leads=1" target="home" class="button-primary">Preview on your website</a>
            </p>

            <div id="tabs">
                <ul>
                    <li><a href="#tabs-popup">Popup</a></li>
                    <li><a href="#tabs-bar">Bar</a></li>
                </ul>


                <div id="tabs-popup">

                    <?php if ($is_all_languages) { ?>
                        <input type="hidden" name="options[theme]" value="default">
                        <table class="form-table">

                            <tr>
                                <th>Enabled</th>
                                <td>
                                    <?php $controls->yesno('popup-enabled'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th>Show on</th>
                                <td>
                                    <?php $controls->select('count', array('0' => 'first', '1' => 'second', '2' => 'third', '4' => 'fourth')); ?>
                                    page view
                                </td>
                            </tr>

                            <tr>
                                <th>Show after</th>
                                <td>
                                    <?php $controls->text('delay', 6); ?> seconds
                                    <p class="description">
                                        How many seconds have to pass, after the page is fully loaded, before the pop up is shown.
                                        Decimal values allowed (for example 0.5 for half a second).
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th>Restart counting after</th>
                                <td>
                                    <?php $controls->text('days', 5); ?> days
                                    <p class="description">
                                        The number of days the system should retain memory of shown pop up to a user before
                                        restart the process.
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th>List</th>
                                <td>
                                    <?php $controls->public_lists_select('theme_list', 'None') ?>
                                    <p class="description">
                                        Only public lists are available
                                    </p>
                                </td>
                            </tr>

                        </table>

                        <h3>Layout</h3>
                        <table class="form-table">

                            <tr>
                                <th>Compatibility</th>
                                <td>
                                    <?php $controls->select('vanilla', [''=>'Standard', 'vanilla'=>'Without 3rd party libraries']); ?>
                                    <p class="description">
                                        If the popup shows problems or doesn't open, try the "Without 3rd party libraries" option.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>Size</th>
                                <td>
                                    <?php $controls->text('width', 5); ?> x <?php $controls->text('height', 5); ?> pixels
                                </td>
                            </tr>
                            <tr>
                                <th>Show the name field</th>
                                <td>
                                    <?php $controls->checkbox('theme_field_name'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Disable the privacy checkbox</th>
                                <td>
                                    <?php $controls->checkbox('theme_field_privacy'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th>Palette</th>
                                <td>

                                    <?php foreach (array_keys(NewsletterLeads::$leads_colors) AS $name) { ?>
                                        <span class="tnp-option-color <?php echo $name ?>">
                                            <input type="radio" name="options[theme_popup_color]" id="popup-<?php echo $name ?>" 
                                                   value="<?php echo $name ?>" <?php if ($controls->data['theme_popup_color'] == $name) { ?>checked<?php } ?>>
                                            <label for="popup-<?php echo $name ?>"><?php echo ucfirst($name) ?></label>
                                        </span>
                                    <?php } ?>
                                </td>
                            </tr>

                            <tr>
                                <th>Custom colors</th>
                                <td>
                                    <input type="radio" name="options[theme_popup_color]" id="popup-custom" value="custom" <?php ($controls->data['theme_popup_color'] == 'custom') ? 'checked' : '' ?>>
                                    Custom
                                    <br><br>
                                    Color 1 <?php $controls->color('theme_popup_color_1'); ?><br>
                                    Color 2 <?php $controls->color('theme_popup_color_2'); ?><br>
                                </td>
                            </tr>
                        </table>

                    <?php } else { ?>
                        <?php $controls->switch_to_all_languages_notice(); ?>
                    <?php } ?>


                    <h3>Texts and labels</h3>
                    <table class="form-table">
                        <tr>
                            <th>Title</th>
                            <td>
                                <?php $controls->text('theme_title'); ?> 
                            </td>
                        </tr>

                        <tr>
                            <th>Button</th>
                            <td>
                                <?php $controls->text('theme_subscribe_label', 70); ?>
                            </td>
                        </tr>


                        <tr>
                            <th>Pre Form Text</th>
                            <td>
                                <?php $controls->textarea('theme_pre'); ?> 
                            </td>
                        </tr>

                        <tr>
                            <th>Post Form Text</th>
                            <td>
                                <?php $controls->textarea('theme_post'); ?>
                            </td>
                        </tr>

                    </table>


                </div>


                <!-- BAR CONFIGURATION -->

                <div id="tabs-bar">

                    <?php if ($is_all_languages) { ?>
                        <table class="form-table">

                            <tr>
                                <th>Enabled</th>
                                <td>
                                    <?php $controls->yesno('bar-enabled'); ?>
                                </td>
                            </tr>


                            <tr>
                                <th>List</th>
                                <td>
                                    <?php $controls->public_lists_select('bar_list', 'None') ?>
                                    <p class="description">
                                        Only public lists are available
                                    </p>
                                </td>
                            </tr>
                        </table>


                        <h3>Layout</h3>
                        <table class="form-table">

                            <tr>
                                <th>Show on</th>
                                <td>
                                    <?php $controls->select('position', array('top' => 'Page top', 'bottom' => 'Page bottom')); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Disable the privacy checkbox</th>
                                <td>
                                    <?php $controls->checkbox('bar_field_privacy'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th>Palette</th>
                                <td>

                                    <?php foreach (array_keys(NewsletterLeads::$leads_colors) AS $name) { ?>
                                        <span class="tnp-option-color <?php echo $name ?>">
                                            <input type="radio" name="options[theme_bar_color]" id="popup-<?php echo $name ?>" 
                                                   value="<?php echo $name ?>" <?php if ($controls->data['theme_bar_color'] == $name) { ?>checked<?php } ?>>
                                            <label for="popup-<?php echo $name ?>"><?php echo ucfirst($name) ?></label>
                                        </span>
                                    <?php } ?>
                                </td>
                            </tr>

                            <tr>
                                <th>Custom colors</th>
                                <td>

                                    <input type="radio" name="options[theme_bar_color]" id="bar-custom" value="custom" <?php ($controls->data['theme_bar_color'] == 'custom') ? 'checked' : '' ?>>
                                    Custom

                                    <br><br>
                                    Color 1 <?php $controls->color('theme_bar_color_1'); ?><br>
                                    Color 2 <?php $controls->color('theme_bar_color_2'); ?><br>
                                </td>
                            </tr>

                        </table>
                    <?php } else { ?>
                        <?php $controls->switch_to_all_languages_notice(); ?>
                    <?php } ?>

                    <h3>Labels</h3>
                    <table class="form-table">

                        <tr>
                            <th>Button</th>
                            <td>
                                <?php $controls->text('bar_subscribe_label', 70); ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Email placeholder</th>
                            <td>
                                <?php $controls->text('bar_placeholder', 70); ?>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>

        </form>
    </div>
    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
