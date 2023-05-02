<?php
/* @var $this NewsletterNinjaForms */
defined('ABSPATH') || exit;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$form = $this->get_form($_GET['id']);

if (!$controls->is_action()) {
    $controls->data = $this->get_form_options($form->id);
} else {
    if ($controls->is_action('save')) {
        $this->save_form_options($form->id, $controls->data);
        $controls->add_message_saved();
    }
}
?>


<div class="wrap" id="tnp-wrap">
    <?php @include NEWSLETTER_DIR . '/tnp-header.php' ?>

    <div id="tnp-heading">
        <h3>Ninja Forms Integration</h3>
        <h2>Form "<?php echo esc_html($form->title) ?>" linking</h2>

        <p>
            See the <a href="https://www.thenewsletterplugin.com/documentation/addons/integrations/ninjaforms-extension/" target="_blank">official documentation</a>
            to correctly connect a Ninja Forms form to Newsletter.
        </p>
    </div>

    <?php $controls->show(); ?>

    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>
            <p>    
                <?php $controls->button_back('?page=newsletter_ninjaforms_index'); ?> 
                <?php $controls->button_save(); ?> 
            </p>


            <table class="form-table">
                <tr valign="top">
                    <th><?php _e('Opt-in', 'newsletter') ?></th>
                    <td>
                        <?php $controls->select('status', array('' => 'Default', 'double' => 'Double opt-in', 'single' => 'Single opt-in')); ?>
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
                            Add a checkbox type field in the form to be used as subscription option.
                        </p>
                    </td>
                </tr>  
                <tr valign="top">
                    <th>First or full name field</th>
                    <td>
                        <?php $controls->select('name', $form->fields, 'Select...'); ?>
                    </td>
                </tr>  
                <tr valign="top">
                    <th>Last name field</th>
                    <td>
                        <?php $controls->select('surname', $form->fields, 'Select...'); ?>
                    </td>
                </tr>
            </table>
            
            <h3>Extra profile fields</h3>
            <table class="form-table">
                <tr valign="top">
                    <th>
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

                                    $controls->select('profile_' . $profile->id, $form->fields, 'Not present');
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
                    <th>Add subscribers to these lists</th>
                    <td><?php $controls->preferences() ?></td>
                </tr>
            </table>
            <p>    
                <?php $controls->button_back('?page=newsletter_ninjaforms_index'); ?> 
                <?php $controls->button_save(); ?> 
            </p>
        </form>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>

</div>