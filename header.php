<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title><?php title_tag(); ?></title>

<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="all" />
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/print.css" type="text/css" media="print" />

<?php if(of_get_option('skin') && of_get_option('skin') != 'default'): ?>
	<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/skins/<?php echo of_get_option('skin'); ?>.css" media="screen" />
<?php endif; ?>

<style type="text/css" media="all">
<?php if(of_get_option('label_text')): ?>
	.status a{
		color: <?php echo of_get_option('label_text'); ?>;
		text-shadow: none;
	}
<?php endif; ?>
<?php if(of_get_option('approved_bg')): ?>
	.approved a{
		background: <?php echo of_get_option('approved_bg'); ?>
	}
<?php endif; ?>
<?php if(of_get_option('paid_bg')): ?>
	.paid a{
		background: <?php echo of_get_option('paid_bg'); ?>
	}
<?php endif; ?>
<?php if(of_get_option('pending_bg')): ?>
	.pending a{
		background: <?php echo of_get_option('pending_bg'); ?>
	}
<?php endif; ?>
</style>

<?php wp_head(); ?>
<?php /* The custom.css file will overwrite all other stylesheets */ ?>
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/custom.css" />

<script src="<?php echo get_template_directory_uri(); ?>/js/modernizr-1.7.min.js"></script>
</head>
<body <?php body_class(); ?>>

	<header id="head" role="banner">
		<div id="site-title">
			<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
				<?php if(of_get_option('logo_img')) { ?>
					<h1 id="logo" class="logo-img">
		        	<img src="<?php echo of_get_option('logo_img'); ?>" alt="<?php bloginfo( 'name' ); ?>" />
		        	</h1>
		        <?php } else { ?>
		    		<h1 id="logo"><?php bloginfo( 'name' ); ?></h1>
		    		<?php if(get_bloginfo('description')) : ?><p id="tagline"><?php bloginfo( 'description' ); ?></p><?php endif; ?>
		        <?php } ?>
	        </a>
		</div>