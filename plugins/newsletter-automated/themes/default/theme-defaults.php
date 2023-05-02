<?php

$theme_defaults = array(
    'theme_post_image_size' => 'thumbnail',
    'theme_view_online_label' => 'Click here if this email doesn\'t show properly',
    'theme_title' => get_option('blogname'),
    'theme_title_background' => '#666666',
    'theme_title_color' => '#ffffff',
    'theme_header' => '',
    'theme_footer' => '<p>You\'re receiving this email because you subscribed to it at ' . get_option('blogname') .
    ' as {email}.</p><p>To modify or cancel your subscription, <a href="{profile_url}">click here</a>.',
    'theme_color' => '#0088cc',
    'theme_max_posts' => '10',
    'theme_full_post' => '0',
    'theme_old_posts' => '1',
    'theme_old_posts_title' => 'Older posts you may have missed',
    'theme_font_family' => 'Helvetica, Arial, sans-serif',
    'theme_font_size' => '16',
    'theme_font_weight' => 'normal',
    'theme_title_font_family' => 'Helvetica, Arial, sans-serif',
    'theme_title_font_size' => '24',
    'theme_title_font_weight' => 'normal',
);
