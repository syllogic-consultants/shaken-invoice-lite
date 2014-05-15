<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php bloginfo('name'); ?> | <?php the_title(); ?></title>
</head>
<body>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	
	<?php if( get_option('sh_invoice_email_content') ):
		echo nl2br( get_option('sh_invoice_email_content') ); 
	else: ?>
		<p><?php _e('To view your', 'sh_invoice'); ?> <?php sh_invoice_type(); ?><?php _e(', or to print a copy for your records, click the link below:', 'sh_invoice'); ?></p>	
	<?php endif; ?>
	
	<p><a href="<?php the_permalink(); ?>"><?php the_permalink(); ?></a></p>
	
	<?php 
	$terms = get_the_terms($post->ID , 'client');
	$id = false;
	if($terms)
	{	
		$terms = array_values($terms);
		$id = $terms[0]->term_id;
			
	}
	
	if($id):
		$key = sh_get_key($id);
	?>
	
	<p>
	<strong><?php _e('For your records&hellip;', 'sh_invoice'); ?></strong><br />
	<?php _e('Your client ID is:', 'sh_invoice'); ?> <?php echo $id; ?><br />
	<?php _e('Your client key is:', 'sh_invoice'); ?> <?php echo $key; ?></p>
	
	<?php endif; ?>
	
	<p><?php _e('Best regards,', 'sh_invoice'); ?><br />
	<?php if(sh_invoice_get_option('sh_invoice_company')){ echo sh_invoice_get_option('sh_invoice_company'); } ?>
	</p>

<?php endwhile; endif; ?>
</body>
</html>
