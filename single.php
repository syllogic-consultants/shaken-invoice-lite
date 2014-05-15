<?php 
/* 
 * Blog Post Template
 *
 * @since 1.0.0
 *
 **/

// Get the menu
get_header(); sh_get_menu();

if (have_posts()) : while (have_posts()) : the_post(); ?>
	
	<header class="page-title">
		<h1><?php the_title(); ?></h1>
	</header>
	
	<section class="page-entry">
		<article class="g8">
			<?php the_content(); ?>
			<?php edit_post_link(__('Edit Page', 'sh_invoice')); ?>
		</article>
		
		<?php get_sidebar(); ?>
		
	</section>
	
<?php endwhile; endif; ?>

<?php get_footer(); ?>