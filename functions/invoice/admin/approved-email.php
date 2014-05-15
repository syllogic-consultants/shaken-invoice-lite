<?php
/**
 * Send e-mail when the quote has been approved
 *
 * @author Sawyer Hollenshead
 * @since 1.0.0
 *
 **/
 
global $post;
global $authordata;

$sitename = strtolower( $_SERVER['SERVER_NAME'] );
if ( substr( $sitename, 0, 4 ) == 'www.' ) {
	$sitename = substr( $sitename, 4 );
}

$from = 'wordpress@' . $sitename;	

$headers = "From: ".$from."\n";				
$headers .= "Reply-To: ".$from."\n";
$headers .= 'MIME-Version: 1.0' . "\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\n";

$to = get_the_author_meta('user_email', $authordata->ID);													
$subject = sh_get_invoice_type().' # '.sh_get_invoice_number().' - '.get_the_title().' - has been approved';		

if(!$to || !$message)							
{
	die;
}

/* Mail it!
-------------------------------------*/
if ( mail($to,$subject,$message,$headers) ) 
{
	update_post_meta($post->ID, 'approval_email_sent', 'true');
} 
?>