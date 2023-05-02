<?php
defined('ABSPATH') || exit;

/* @var $this NewsletterInstasend */

@include_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();

$logger = $this->get_admin_logger();

if (!$controls->is_action()) {
    $controls->data = $this->options;
} else {
    
    $logger->info($controls->action);
    
    if ($controls->is_action('save')) {
        $this->save_options($controls->data);
        $controls->add_message_saved();
    }
}
?>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">

        <h2>Instasend</h2>

    </div>

    <div id="tnp-body">

        <form method="post" action="">
            <?php $controls->init(); ?>

            <p>Welcome to Instasend the Newsletter addon to create newsletters directly from your post editing page.</p>
            
            
            <p>
                Have any ideas, requests or comments? 
                <a href="https://forms.gle/5gqkUiqkgeGM4qZU7" target="_blank">Leave your feedback: we're planning the Instasend future, take the chance to design it!</a>
            </p>
            
            <iframe width="800" height="450" src="https://www.youtube.com/embed/2BYCMU44va8" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></iframe>
            
            <p>
             <?php //$controls->button_save('save') ?>
            </p>
        </form>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>