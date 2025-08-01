<?php
	/*
		Template Name: Error page
	*/
	get_header();
?>
<div class="error-p-wrap">
	<div class="error-p-inner-wrap">
		<section class="page_404">
			<div class="container">
				<div class="row">	
				<div class="col-sm-12 ">
				<div class="col-sm-12 text-center">
				<div class="four_zero_four_bg">
					<h1 class="text-center ">404</h1>
				
				
				</div>
				
				<div class="contant_box_404">
				<h3 class="h2">
				Look like you're lost
				</h3>
				
				<p>the page you are looking for not avaible!</p>
				
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="link_404">Go to Home</a>
			</div>
				</div>
				</div>
				</div>
			</div>
		</section>	
	</div>
</div>
<?php
	get_footer();
?>