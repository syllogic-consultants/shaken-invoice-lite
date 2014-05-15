<?php get_header(); sh_get_menu();

if (have_posts()) : ?>
	
	<header class="page-title">
		<h1><?php _e('Search Results', 'sh_invoice'); ?></h1>
	</header>
	
	<section class="page-entry full-width">
    <ul>
    <?php while (have_posts()) : the_post(); ?>
		<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
    <?php endwhile; ?>
    </ul>
	</section>
	
<?php else: ?>
	<header class="page-title">
		<h1><?php _e('Nothing found', 'sh_invoice'); ?></h1>
	</header>
    
    <section class="page-entry full-width">
		<?php _e('Sorry, we couldn\'t find any results for your search.', 'sh_invoice'); ?>
	</section>
<?php endif; ?>

<?php get_footer(); ?>