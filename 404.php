<?php 
/* 
 * 404 page
 *
 * @since 1.0.0
 *
 **/

// Get the menu
get_header(); sh_get_menu(); ?>

	<header class="page-title">
		<h1><?php _e('404: Page Not Found', 'sh_invoice'); ?></h1>
	</header>
	
	<section class="page-entry full-width">
		<p><?php _e('We\'re sorry but that page could not be found.', 'sh_invoice'); ?></p>
	</section>

<?php get_footer(); ?>