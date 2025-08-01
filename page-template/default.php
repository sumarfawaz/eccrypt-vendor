<?php
	/*
		Template Name: Default Template
	*/
	get_header();
?>
 <section class="braedcumb-section dark-lightmode dark-font-change">
    <div class="small-middle-wrap">
        <?php the_breadcrumb(); ?>
    </div>
 </section>
<section class="simple-padding-bottom dark-lightmode dark-font-change">
    <div class="middle-wrap">
        <div class="the-content-div simple-padding-top">
            <?php the_content(); ?>
        </div>
    </div>
</section>
<?php
	get_footer();
?>