<?php
global $newsletter; // Newsletter object
global $post; // Current post managed by WordPress
?>

* <?php echo $theme_options['theme_title']; ?>


<?php
foreach ($posts as $post) {
    // Setup the post (WordPress requirement)
    setup_postdata($post);
?>
<?php the_title(); ?>

<?php the_permalink(); ?>


<?php } ?>


To change your subscription:
{profile_url}

