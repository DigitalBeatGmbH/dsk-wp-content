<?php
/* @var $this NewsletterCF7 */
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
    <?php include NEWSLETTER_DIR . '/tnp-header.php' ?>

    <div id="tnp-heading">
        <h3>Contact Form 7 Integration</h3>
        <h2>Form "<?php echo esc_html($form->title) ?>" linking</h2>

        <p>
            See the <a href="https://www.thenewsletterplugin.com/documentation/addons/integrations/contact-form-7-extension/" target="_blank">official documentation</a>
            to correctly configure your Contact Form 7 forms.
        </p>
    </div>

    <?php $controls->show(); ?>

    <div id="tnp-body">
        <form action="" method="post">
            <?php $controls->init(); ?>
            <p>    
                <?php $controls->button_back('?page=newsletter_cf7_index'); ?> 
                <?php $controls->button_save(); ?> 
            </p>


            <table class="form-table">
                <tr valign="top">
                    <th>Email field</th>
                    <td>
                        <?php $controls->select('email', $form->fields, 'Integration disabled'); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th>Subscription checkbox field</th>
                    <td>
                        <?php $controls->select('newsletter', $form->fields, 'Not present'); ?>
                        <p class="description">
                            Add a checkbox type field in the form to be used as subscription indicator for
                            example <code>[checkbox newsletter "Subscribe to my newsletter"]</code>. 
                            If you leave "Not present" EVERY contact will be subscribed.
                        </p>
                    </td>
                </tr>  
                <tr valign="top">
                    <th>First or full name field</th>
                    <td>
                        <?php $controls->select('name', $form->fields, 'Not present'); ?>
                    </td>
                </tr>  
                <tr valign="top">
                    <th>Last name field</th>
                    <td>
                        <?php $controls->select('surname', $form->fields, 'Not present'); ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th>Gender field</th>
                    <td>
                        <?php $controls->select('gender', $form->fields, 'Not present'); ?>
                        <p>Warning: the valued collected by CF7 must be "f" or "m". For example [select gender "Female|f" "Male|m"]</p>
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
                                        Public Subscriber field
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
                    <th style="vertical-align: top">
                        Add subscribers to these lists<br>
                        <small><a href="?page=newsletter_subscription_lists" target="_blank">Manage the lists</a></small>
                    </th>
                    <td><?php $controls->lists('preferences') ?></td>
                </tr>
            </table>

            <p>    
                <?php $controls->button_back('?page=newsletter_cf7_index'); ?> 
                <?php $controls->button_save(); ?> 
            </p>
        </form>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php' ?>

</div>