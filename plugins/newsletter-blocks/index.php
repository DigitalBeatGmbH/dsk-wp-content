<?php $module = NewsletterBlocks::$instance; ?>

<div class="wrap" id="tnp-wrap">
    <?php @include NEWSLETTER_DIR . '/tnp-header.php' ?>
    <div id="tnp-heading">

        <h2>Newsletter Blocks</h2>

    </div>
    <div id="tnp-body">

        <?php
        $blocks = $module->scan(__DIR__ . '/blocks');
        ?>
    </div>
    <?php @include NEWSLETTER_DIR . '/tnp-footer.php' ?>
</div>
