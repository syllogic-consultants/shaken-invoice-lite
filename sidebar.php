<?php 
/* The following tests which page is being shown
 * and determines which sidebar should be displayed.
 *
 * If there are no widgets added to the sidebar, nothing
 * will be shown.
*/
?>

<aside class="g4 last" id="sidebar">
	<?php dynamic_sidebar( 'page-sidebar' ); ?>
</aside>