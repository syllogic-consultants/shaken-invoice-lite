<?php if (have_posts()) : ?>
	   
	<?php 
		while (have_posts()) : the_post(); 
		
		// Custom post fields
		$soy_embed = get_post_meta($post->ID, 'soy_embed', true);
	?>

        <div <?php post_class($content_class) ?> id="post-<?php the_ID(); ?>">
                
	        <?php the_title(); ?>
	        <?php the_time('F jS, Y') ?> <?php _e( 'by', 'shaken' ); ?> <?php the_author() ?> - <?php the_category(', ') ?> <?php edit_post_link('- Edit'); ?>
	
	    	<?php the_content('Continue Reading &raquo;'); ?>
	    	<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'shaken' ), 'after' => '</div>' ) ); ?>
	
	    
	    	<?php
			// Check if there are tags...
			$tags_list = get_the_tag_list( '', ', ' );
			if ( $tags_list ):
			?>
	        	<span class="tags"><?php printf( __( '%2$s', 'shaken' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?></span>
			<?php endif; ?>
			
	        <a href="<?php echo wp_get_shortlink(); ?>" class="shortlink tooltip" title="<?php echo wp_get_shortlink(); ?>"><?php _e( 'Shortlink', 'shaken' ); ?></a>
		</div><!-- #post -->

    <?php endwhile; ?>
	
        <div class="post-navigation">
            <div class="prev-post">
                <?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'shaken' ) ); ?>
            </div>
            <div class="next-post">
               <?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'shaken' ) ); ?>
            </div>
            <div class="clearfix"></div>
        </div>

<?php else : ?>
    <div class="post not-found">
    	<div class="post-header">
        	<h1><?php _e( 'Uh Oh', 'shaken' ); ?>&hellip;</h1>
        </div>
    	<div class="article">
            <p class="center"><?php _e( 'Sorry, but you are looking for something that isn\'t here. Try searching for it', 'shaken' ); ?>&hellip;</p>
            <?php get_search_form(); ?>
        </div><!-- #article -->
	</div><!-- #post -->
<?php endif; ?>