<?php
/**
 * This theme is made to run even if there are not new posts since it generates a static content (but
 * you can code dynamic content of course).
 * 
 * If there is nothing to be sent on this run, see the variable $nothing_to_show.
 * 
 * $theme_options[] contains all the options you make available as theme configuration on
 * theme-options.php file.
 * 
 * Copy this theme to wp-content/extensions/newsletter-automated/themes to use and customize it.
 */

$nothing_to_show = false;


// Your logic here to detect if there is nothing to send.
// Set $nothing_to_show to true if this newsletter should not be generated.


// Early return with no content generated to signal this newsletter should be skipped
if ($nothing_to_show) {
    return;
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <style type="text/css" media="all">
            a {
                text-decoration: none;
                color: <?php echo $color; ?>;
            }
            img {
                max-width: 100%;
            }
        </style>
    </head>
    <body style="font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; color: #666; margin: 0 auto; padding: 0;">
        <?php echo $theme_options['theme_content']?>
    </body>
</html>