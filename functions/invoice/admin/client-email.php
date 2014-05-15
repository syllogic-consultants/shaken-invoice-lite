<?php
/**
 * Send Invoice as HTML Email
 *
 * @author Sawyer Hollenshead
 * @since 1.0.0
 *
 **/
 
global $post;

$from = sh_get_invoice_email();															// 1. Get From email
update_post_meta($post->ID, 'invoice_sent', date_i18n('j/m/Y'));					// 2. Set sent custom field

$headers = "From: ".$from."\n";														// 3. Set Email Headers
$headers .= "Reply-To: ".$from."\n";
$headers .= 'MIME-Version: 1.0' . "\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\n";

$to = sh_get_invoice_client_email();													// 4. Send email to ...
if(sh_invoice_get_emailrecipients() == 'both')
{
	$to .= ','.$from;
}

$subject = sh_get_invoice_type().' # '.sh_get_invoice_number().' - '.get_the_title();		// 5. Email Subject

if(!$to)																			// 6. Quick validation check
{
	echo '<p class="error">'.__('Error: No recipient email address found','sh_invoice').'</p>';
	die;
}
if(!$message)
{
	echo '<p class="error">'.__('Error: No message body found','sh_invoice').'</p>';	
	die;
}


/* Mail it!
-------------------------------------*/
if ( mail($to,$subject,$message,$headers) ) 
{
	$edit_link = home_url().'/wp-admin/post.php?post='.$post->ID.'&action=edit&sent=success';
	if($edit_link)
	{
		wp_redirect($edit_link);
	}
	else
	{
		echo '<p class="success">'.__('Email was successfully sent!','sh_invoice').'</p>';
	}
} 
else 
{
	// set sent custom field
	update_post_meta($post->ID, 'invoice_sent', 'Not yet');
	$edit_link = home_url().'/wp-admin/post.php?post='.$post->ID.'&action=edit&sent=fail';
	if($edit_link)
	{
		wp_redirect($edit_link);
	}
	else
	{
		echo '<p class="error">'.__('Email failed to send','sh_invoice').'</p>';
	}
   
}

?>