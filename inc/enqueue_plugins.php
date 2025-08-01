<?php

// slick slider 
function enqueue_slick_slider()
{
    // Register Slick css
    wp_enqueue_style('slick-css', get_template_directory_uri() . '/plugins/slick/slick.css', [], null);
    // Register Slick Theme CSS (Optional)
    wp_enqueue_style('slick-theme-css', get_template_directory_uri() . '/plugins/slick/slick-theme.css', ['slick-css'], '1.8.1');


    // Register jQuery (Slick requires jQuery)
    wp_enqueue_script('jquery');

    // Register Slick JS
    wp_enqueue_script('slick-js', get_template_directory_uri() . '/plugins/slick/slick.min.js', ['jquery'], '1.8.1', true);

    // Custom JS to Initialize Slick
    wp_enqueue_script('custom-slick-init', get_template_directory_uri() . '/assets/js/slick-init.js', ['slick-js'], '1.0', true);

}

add_action('wp_enqueue_scripts', 'enqueue_slick_slider');

// fancybox
function enqueue_fancy_box() {
    // Enqueue Fancybox CSS
    wp_enqueue_style('fancybox-css', get_template_directory_uri() . '/plugins/fancybox/fancybox.css', [], null);

    // Enqueue Fancybox JS
    wp_enqueue_script('fancybox-js', get_template_directory_uri() . '/plugins/fancybox/fancybox.umd.js', ['jquery'], null, true);

    // Enqueue Custom Fancybox Initialization Script
    wp_enqueue_script('custom-fancybox-init', get_template_directory_uri() . '/assets/js/fancybox-init.js', ['fancybox-js', 'jquery'], '1.0', true);
}

add_action('wp_enqueue_scripts', 'enqueue_fancy_box');