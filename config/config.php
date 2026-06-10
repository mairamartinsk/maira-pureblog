<?php
return array (
  'site_title' => 'Maira Martins',
  'site_tagline' => 'self-taught in everything',
  'site_description' => 'A good old-fashioned personal blog from an insomniac, programmer, photographer, day trader, dog-mother, bread baker, piano student, wannabe artist.',
  'site_email' => 'mairamartins@posteo.com',
  'custom_nav' => 'Archive | /archive
Search | /explore
RSS | /feed',
  'custom_routes' => '/archive | /content/includes/archive.php
/explore | /content/includes/search.php
/tags | /content/includes/search.php',
  'head_inject_page' => '',
  'head_inject_post' => '<link rel="stylesheet" href="/content/css/lite-yt-embed.css" />',
  'footer_inject_page' => '',
  'footer_inject_post' => '<script src="https://cdn.jsdelivr.net/gh/welpo/iine@main/iine.mini.js"></script>
<script src="/content/js/lite-yt-embed.js"></script>',
  'posts_per_page' => 20,
  'search_excerpt_length' => 2500,
  'homepage_slug' => '',
  'blog_page_slug' => '',
  'search_page_slug' => 'search',
  'search_page_notified' => true,
  'base_url' => 'https://mairamartins.com',
  'language' => 'en',
  'timezone' => 'Europe/Madrid',
  'date_format' => 'j M Y',
  'admin_username' => 'espresso9070',
  'admin_password_hash' => '$2y$10$ra9DLKuGFqfnBvL36Rr1dOmVOD9fvweMCK4hbBC6NjXjdRNwxnMBW',
  'cache' => 
  array (
    'enabled' => true,
    'rss_ttl' => 86400,
  ),
  'theme' => 
  array (
    'color_mode' => 'auto',
    'font_stack' => 'mono',
    'admin_font_stack' => 'mono',
    'admin_color_mode' => 'auto',
    'background_color' => '#FAFAFA',
    'text_color' => '#333',
    'accent_color' => '#732CB8',
    'border_color' => '#898EA4',
    'accent_bg_color' => '#E6D7F4',
    'background_color_dark' => '#212121',
    'text_color_dark' => '#DCDCDC',
    'accent_color_dark' => '#D6B1FB',
    'border_color_dark' => '#555',
    'accent_bg_color_dark' => '#552675',
    'post_list_layout' => 'excerpt',
  ),
  'assets' => 
  array (
    'favicon' => '/assets/images/favicon.png',
    'og_image' => '/assets/images/og-image.png',
    'og_image_preferred' => 'banner',
  ),
  'hide_homepage_title' => true,
  'hide_blog_page_title' => true,
  'admin_homepage' => 'dashboard',
  'admin_hide_dashboard' => false,
  'show_reading_time' => false,
  'enable_blog_posts' => true,
);
