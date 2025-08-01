<!doctype html>
<html>

<head>
   <meta charset="<?php bloginfo('charset'); ?>">
   <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
   <title><?php bloginfo('name');
   echo ' | ';
   is_front_page() ? bloginfo('description') : wp_title(''); ?></title>
   <meta name="description" content="">
   <meta name="keywords" content="">
   <!-- Facebook Meta Tags -->
   <meta property="og:url" content="<?php echo site_url(); ?>">
   <meta property="og:type" content="website">
   <meta property="og:title" content="<?php bloginfo('name');
   echo ' | ';
   is_front_page() ? bloginfo('description') : wp_title(''); ?>">
   <meta property="og:description" content="">
   <meta property="og:image" content="">
   <!-- Twitter Meta Tags -->
   <meta name="twitter:card" content="summary_large_image">
   <meta property="twitter:domain" content="">
   <meta property="twitter:url" content="<?php echo site_url(); ?>">
   <meta name="twitter:title" content="<?php bloginfo('name');
   echo ' | ';
   is_front_page() ? bloginfo('description') : wp_title(''); ?>">
   <meta name="twitter:description" content="">
   <meta name="twitter:image" content="">
   <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
   <?php
   get_template_part('template-parts/default-blocks/header/block');
   get_template_part('template-parts/default-blocks/banner/block');
   ?>