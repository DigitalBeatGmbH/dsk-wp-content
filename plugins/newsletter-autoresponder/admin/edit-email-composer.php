<?php
/* @var $this NewsletterAutoresponder */
/* @var $wpdb wpdb */
global $wpdb;

require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$email_id = (int) $_GET['email_id'];
$autoresponder_id = (int) $_GET['id'];
$autoresponder = $this->get_autoresponder($autoresponder_id);

if ($controls->is_action('save') || $controls->is_action('test')) {

    $step = 1;
    foreach ($autoresponder->emails as $the_id) {
        if ($the_id == $email_id) {
            break;
        }
        $step++;
    }

    $email = Newsletter::instance()->get_email($email_id);
    TNP_Composer::update_email($email, $controls);
    $email->options['delay'] = (float)$email->options['delay'];
    $email->status = 'sent'; // Imposto lo stato a 'sent' perchè altrimenti non sarebbe possibile la visualizzazione online della mail
    $email->track = 1; // Attivo il tracking

    $email->options['utm_campaign'] = $autoresponder->utm_campaign;
    $email->options['utm_source'] = str_replace('{step}', $step, $autoresponder->utm_source);
    $email->options['utm_medium'] = $autoresponder->utm_medium;
    $email->options['utm_term'] = $autoresponder->utm_term;
    $email->options['utm_content'] = $autoresponder->utm_content;

    $email = Newsletter::instance()->save_email($email);

    $controls->add_message_saved();

    if ($controls->is_action('test')) {
        NewsletterEmails::instance()->send_test_email($email, $controls);
    }
}

$email = Newsletter::instance()->get_email($email_id);

// Inizializzo i campi della controls con i valori presenti nella email.
// Avendo l'oggetto $controls valorizzato riesco poi a costruire il form con i dati corretti
TNP_Composer::prepare_controls($controls, $email);
?>
<style>
<?php include __DIR__ . '/admin.css'; ?>
</style>
<div class="wrap" id="tnp-wrap">


    <?php $controls->show(); ?>


    <div id="tnp-body">

        <form id="tnpc-form" method="post" action="" onsubmit="tnpc_save(this); return true;">
            <?php $controls->init(); ?>
            <p>
                <?php $controls->button_back('?page=newsletter_autoresponder_index&id=' . $autoresponder_id, '') ?>
                <?php $controls->button_save(); ?>
                <?php $controls->button_test('test', 'Test'); ?>
            </p>

            <table class="form-table" style="width: auto; margin-bottom: 20px">
                <tr>
                    <th>Delay (hours)</th>
                    <td><?php $controls->text('options_delay') ?></td>
                </tr>
            </table>

            <?php $controls->composer_fields_v2() ?>

        </form>

        <?php $controls->composer_load_v2(true, false, 'autoresponder') ?>
    </div>

</div>
