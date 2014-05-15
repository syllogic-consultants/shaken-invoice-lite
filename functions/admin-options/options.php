<?php

/**
* This file should only load if options.php isn't present in the theme.
*
*/

/* Defaults the settings to 'optionsframework' */

function optionsframework_option_name() {
	
	$optionsframework_settings = get_option('optionsframework');
	$optionsframework_settings['id'] = 'optionsframework';
	update_option('optionsframework', $optionsframework_settings);
}

/**
 * Displays a message that options aren't available in the current theme
 *  
 */

function optionsframework_options() {
		
		/* ********************************************************
								Thank You
		******************************************************** */
		$options[] = array( "name" => "Thank You",
							"type" => "heading");
		
		$options[] = array( "name" => "Thank you!",
							"desc" => "Thank you for downloading from <a href=\"http://shakenandstirredweb.com\">Shaken and Stirred</a>. To stay up to date with the latest theme updates and releases, <a href=\"http://twitter.com/shakenweb\">follow us on Twitter</a>",
							"type" => "info");
		
		$options[] = array( "name" => "Documentation",
							"desc" => "<ul>
							<li><a href=\"http://support.shakenandstirredweb.com/shaken-invoice-lite\">Documentation, FAQ, and Changelog</a></li>
							<li><a href=\"http://shakenandstirredweb.com/tips-tricks\">Tips &amp; Tricks</a></li>
							</ul>",
							"type" => "info");
		
		$options[] = array( "name" => "Look and Feel",
							"type" => "heading");
							
		$options[] = array( "name" => "Upgrade to Premium",
							"desc" => 'Upgrade to the premium version of Shaken Invoice and enjoy additional customization options like uploading your own logo and customizing the appearance of your theme without touching code. <strong><a href="http://shakenandstirredweb.com/theme/shaken-invoice">Learn more about the premium version &raquo;</a></strong>',
							"type" => "info");
															
	return $options;
}