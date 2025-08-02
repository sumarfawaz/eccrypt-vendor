<?php
/*
    Template Name: Register
*/
get_header();


wp_enqueue_style('registration-page-css'); ?>

Registration Page

<?php include get_template_directory() . '/page-template/registration-page/vendor-form/registration-form.php'; ?>

<?php wp_enqueue_script('register-js', get_template_directory_uri() . '/assets/js/register-page.js', [], false, true); ?>


<?php
get_footer();
?>