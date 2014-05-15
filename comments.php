<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.  The actual display of comments is
 * handled by a callback to shaken_comment which is
 * located in the functions.php file.
 */
?>

<?php if ( have_comments() || comments_open() ) : ?>
			<div id="comments">
<?php if ( post_password_required() ) : ?>
				<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'shaken' ); ?></p>
			</div><!-- #comments -->
<?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;
?>

<?php
	// You can start editing here -- including this comment!
?>

<?php if ( have_comments() ) : ?>
			<h3 id="comments-title"><?php _e( 'What Others Are Saying', 'shaken' ); ?></h3>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'shaken' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'shaken' ) ); ?></div>
                <div class="clearfix"></div>
			</div> <!-- .navigation -->
<?php endif; // check for comment navigation ?>

			<ol class="commentlist">
				<?php
					/* Loop through and list the comments. Tell wp_list_comments()
					 * to use shaken_comment() to format the comments.
					 * If you want to overload this in a child theme then you can
					 * define shaken_comment() and that will be used instead.
					 * See shaken_comment() in shaken/functions.php for more.
					 */
					wp_list_comments( array( 'callback' => 'shaken_comment' ) );
				?>
			</ol>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'shaken' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'shaken' ) ); ?></div>
                <div class="clearfix"></div>
			</div><!-- .navigation -->
<?php endif; // check for comment navigation ?>

<?php else : // or, if we don't have comments:
	
	if (comments_open() ) : ?>
	
    <h3 id="comments-title"><?php _e( 'Be The First To Comment', 'shaken' ); ?></h3>
	
	<?php
	/* If there are no comments and comments are closed,
	 * let's leave a little note, shall we?
	 */
	else:
?>
	<h3 id="comments-title"><?php _e( 'Discussion', 'shaken' ); ?></h3>
	<p class="nocomments"><?php _e( 'Comments are closed.', 'shaken' ); ?></p>
<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>

<?php comment_form(); ?>

</div><!-- #comments -->

<?php endif; // end have_comments() or comments_open() ?>