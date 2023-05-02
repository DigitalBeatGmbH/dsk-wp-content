<?php
/* @var $this NewsletterWPForms */

defined('ABSPATH') || exit;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$form_id = (int) $_GET['id'];

if (!$controls->is_action()) {
    $controls->data = $this->get_form_options($form_id);

    // Migration code
    if (!isset($controls->data['lists'])) {
        $controls->data['lists'] = [];
        for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {
            if (!empty($controls->data['preferences_' . $i])) {
                $controls->data['lists'][] = $i;
            }
        }
    }
    // End of migration code
} else {
    if ($controls->is_action('save')) {
        $this->save_form_options($form_id, $controls->data);
        $controls->add_message_saved();
    }
}

$form = $this->get_form($form_id);

?>

<div class="wrap" id="tnp-wrap">
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>

    <div id="tnp-heading">
        <h3>WP Forms Integration</h3>
        <h2>Form "<?php echo esc_html($form->title) ?>" linking</h2>

        <p>
            See the <a href="https://www.thenewsletterplugin.com/documentation/addons/integrations/wpforms-extension/" target="_blank">official documentation</a>
            to correctly connect a WP Forms form to Newsletter.
        </p>
    </div>

    <?php $controls->show(); ?>

    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>
            <p>    
                <?php $controls->button_back('?page=newsletter_wpforms_index'); ?> 
                <?php $controls->button_save(); ?> 
            </p>


            <table class="form-table">
                <tr valign="top">
                    <th><?php _e('Opt-in', 'newsletter') ?></th>
                    <td>
                        <?php $controls->select('status', array(''=>'Default', 'double' => 'Double opt-in', 'single' => 'Single opt-in')); ?>
                        <p class="description">
                            Double opt-in asks for subscription confirmation with an activation email.
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th>Email field</th>
                    <td>
                        <?php $controls->select('email', $form->fields, 'Integration disabled'); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th>Subscription checkbox field</th>
                    <td>
                        <?php $controls->select('newsletter', $form->fields, 'Not present in the form'); ?>
                        <p class="description">
                            Add a "checkboxes" field with only one checkbox to your form. 
                            Please <a href="https://www.thenewsletterplugin.com/documentation/addons/integrations/wpforms-extension/" target="_blank">check the documentation (video)</a>.
                        </p>
                    </td>
                </tr>  
                <tr valign="top">
                    <th>First or full name field</th>
                    <td>
                        <?php $controls->select('name', $form->fields, 'Not available'); ?>
                    </td>
                </tr>  
                <tr valign="top">
                    <th>Last name field</th>
                    <td>
                        <?php $controls->select('surname', $form->fields, 'Not available'); ?>
                    </td>
                </tr>
            </table>

            
            <h3>Extra profile fields</h3>
            <table class="form-table">
                <tr>
                    <th style="vertical-align: top">
                        Extra profile fields<br>
                        <small><a href="?page=newsletter_subscription_profile" target="_blank">Manage the subscriber's profile fields</a></small>
                    </th>
                    <td>
                        
                        <?php
                        // Use an API for this
                        $profiles = Newsletter::instance()->get_profiles_public();
                        ?>
                        <table class="widefat" style="width: auto">
                            <thead>
                                <tr>
                                    <th>
                                        Subscriber field
                                    </th>
                                    <th>
                                        Form field
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                foreach ($profiles as $profile) {
                                    echo '<tr><td>' . esc_html($profile->name) . '</td><td>';

                                    $controls->select('profile_' . $profile->id, $form->fields, 'Select...');
                                    echo '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
            
            
            <h3>Automatically assigned lists</h3>
            <table class="form-table">
                <tr>
                    <th style="vertical-align: top">
                        Add subscribers to these lists<br>
                        <small><a href="?page=newsletter_subscription_lists" target="_blank">Manage the lists</a></small>
                    </th>
                    <td><?php $controls->lists() ?></td>
                </tr>
            </table>

            <p>    
                <?php $controls->button_back('?page=newsletter_wp_forms_index'); ?> 
                <?php $controls->button_save(); ?> 
            </p>
        </form>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>

</div>
