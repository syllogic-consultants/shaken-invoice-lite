<?php
	
	$the_detail = NULL;
	$detailCount=NULL;
	
	$detailTitle = NULL;
	$detailDescription = NULL;
	$detailType = NULL;
	$detailRate = NULL;
	$detailDuration = NULL;
	$detailSubtotal = NULL;
	
	
	
	
	/*--------------------------------------------------------------------------------------------
										Invoice_has_details
										
	* This function is called before the detail loop. 
	* Populates the detail's data array's
	* Checks that there are details
	* Then it returns either true or false.
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_has_details()
	{
		global $post, $detailCount, $detailTitle, $detailDescription, $detailType, $detailRate, $detailDuration, $detailSubtotal;
		$detailCount=0;
		
		$detailTitle = get_post_meta($post->ID, 'detail_title', true);
		$detailDescription = get_post_meta($post->ID, 'detail_description', true);
		//$detailDescription = unserialize($detailDescription);
		$detailType = get_post_meta($post->ID, 'detail_type', true);
		$detailRate = get_post_meta($post->ID, 'detail_rate', true);
		$detailDuration = get_post_meta($post->ID, 'detail_duration', true);
		$detailSubtotal = get_post_meta($post->ID, 'detail_subtotal', true);
		
		if($detailTitle[0])
		{
			return true;	
		}
		else
		{
			return false;	
		}
	}
	
	
	/*--------------------------------------------------------------------------------------------
										 Invoice_detail
										
	* This function is called at the start of the detail loop. 
	* It sets up the detail data and returns either true or false.
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_detail()
	{
		global $the_detail, $detailCount, $detailTitle, $detailDescription, $detailType, $detailRate, $detailDuration, $detailSubtotal;
		if($detailTitle[$detailCount] != '')
		{
			$the_detail = array(
				$detailTitle[$detailCount],
				$detailDescription[$detailCount],
				$detailType[$detailCount],
				$detailRate[$detailCount],
				$detailDuration[$detailCount],
				$detailSubtotal[$detailCount]
			);
			
			$detailCount++;
			return true;
		}
		else
		{
			return false;	
		}
	}
	
	
	/*--------------------------------------------------------------------------------------------
										 the_detail_title
	--------------------------------------------------------------------------------------------*/
	function sh_get_the_detail_title()
	{
		global $the_detail;
		return $the_detail[0];
	}
	
	function sh_the_detail_title()
	{
		echo sh_get_the_detail_title();
	}
	
	
	/*--------------------------------------------------------------------------------------------
										the_detail_description
	--------------------------------------------------------------------------------------------*/
	function sh_get_the_detail_description()
	{
		global $the_detail;
		//return stripslashes($the_detail[1]);
		return $the_detail[1];
	}
	function sh_the_detail_description()
	{
		echo nl2br(sh_get_the_detail_description());
	}
	
	
	/*--------------------------------------------------------------------------------------------
										the_detail_type
	--------------------------------------------------------------------------------------------*/
	function sh_get_the_detail_type()
	{
		global $the_detail;
		return $the_detail[2];
	}
	
	function sh_the_detail_type()
	{
		echo sh_get_the_detail_type();
	}
	
	
	/*--------------------------------------------------------------------------------------------
										the_detail_rate
	--------------------------------------------------------------------------------------------*/
	function sh_get_the_detail_rate()
	{
		global $the_detail;
		return $the_detail[3];
	}
	function sh_the_detail_rate()
	{
		echo sh_invoice_format_amount(sh_get_the_detail_rate());
	}
	
	
	/*--------------------------------------------------------------------------------------------
										the_detail_duration
	--------------------------------------------------------------------------------------------*/
	function sh_get_the_detail_duration()
	{
		global $the_detail;
		return $the_detail[4];
	}
	function sh_the_detail_duration()
	{
		echo sh_get_the_detail_duration();
	}
	
	
	/*--------------------------------------------------------------------------------------------
										the_detail_subtotal
	--------------------------------------------------------------------------------------------*/
	function sh_get_the_detail_subtotal()
	{
		global $the_detail;
		return number_format($the_detail[5], 2, '.', ''); 
	}
	function sh_the_detail_subtotal()
	{
		echo sh_invoice_format_amount(sh_get_the_detail_subtotal());
	}
	


	/*--------------------------------------------------------------------------------------------
										invoice_template_url
	--------------------------------------------------------------------------------------------*/
	function get_invoice_template_url()
	{
		if(file_exists(get_stylesheet_directory().'/invoice/invoice.php'))
		{
			return get_stylesheet_directory_uri().'/invoice';
			
		}
		else
		{
			global $sh_invoice;
			return $sh_invoice->dir.'template';
		}
	}
	
	function invoice_template_url()
	{
		echo get_invoice_template_url();
	}





	/*--------------------------------------------------------------------------------------------
										invoice_number		
	--------------------------------------------------------------------------------------------*/
	function sh_get_invoice_number() 
	{
		global $post;
		return get_post_meta($post->ID, 'invoice_number', true)? get_post_meta($post->ID, 'invoice_number', true): sh_get_next_invoice_number();	
	}
	
	function sh_invoice_number() 
	{
		echo sh_get_invoice_number();
	}
	
	function sh_get_next_invoice_number()
	{
		$newNumber = 0;
		$invoices = get_posts(array('post_type' => 'invoice', 'numberposts' => '-1'));
		foreach($invoices as $invoice)
		{
			$tempNumber = intval(get_post_meta($invoice->ID, 'invoice_number', true));
			if($tempNumber > $newNumber){$newNumber = $tempNumber;}
		}
		$newNumber +=1;
		return $newNumber;
	}
	
	
	
	
	/*--------------------------------------------------------------------------------------------
										sh_get_invoice_type
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_type($postID = NULL)
	{
		if(!$postID){global $post; $postID = $post->ID;}
		echo sh_get_invoice_type($postID);
	}
	
	function sh_get_invoice_type($postID = NULL)
	{
		if( !$postID ){ global $post; $postID = $post->ID; }
		return get_post_meta($postID, 'invoice_type', true)? get_post_meta($postID, 'invoice_type', true): __('Invoice','sh_invoice');
	}
	
	
	
	/*--------------------------------------------------------------------------------------------
										sh_get_invoice_sent
	--------------------------------------------------------------------------------------------*/
	function sh_get_invoice_sent()
	{
		global $post;
		return get_post_meta($post->ID, 'invoice_sent', true)? get_post_meta($post->ID, 'invoice_sent', true): 'Not yet';
	}
	
	function sh_get_invoice_sent_pretty()
	{
		$sent = sh_get_invoice_sent();
		$months = array('',__('Jan','sh_invoice'), __('Feb','sh_invoice'), __('Mar','sh_invoice'), __('Apr','sh_invoice'), __('May','sh_invoice'), __('Jun','sh_invoice'), __('Jul','sh_invoice'), __('Aug','sh_invoice'), __('Sep','sh_invoice'), __('Oct','sh_invoice'), __('Nov','sh_invoice'), __('Dec','sh_invoice'));
		if($sent == 'Not yet')
		{
			return $sent;	
		}
		else
		{
			$sent = explode('/',$sent);
			return $months[intval($sent[1])] . ' ' . $sent[0] . ', ' . $sent[2];
		}
	}
	
	function sh_invoice_sent()
	{
		echo sh_get_invoice_sent();
	}
	
	function sh_invoice_has_sent($postID)
	{
		$invoice_sent = get_post_meta($postID, 'invoice_sent', true)? get_post_meta($postID, 'invoice_sent', true): 'Not yet';
		if($invoice_sent != 'Not yet')
		{
			return true;	
		}
		else
		{
			return false;	
		}
	}
	
	
	/*--------------------------------------------------------------------------------------------
										sh_get_invoice_paid
	--------------------------------------------------------------------------------------------*/
	function sh_get_invoice_paid()
	{
		global $post;
		return get_post_meta($post->ID, 'invoice_paid', true)? get_post_meta($post->ID, 'invoice_paid', true): 'Not yet';
	}
	
	function sh_get_invoice_paid_pretty()
	{
		$sent = sh_get_invoice_paid();
		$months = array('',__('Jan','sh_invoice'), __('Feb','sh_invoice'), __('Mar','sh_invoice'), __('Apr','sh_invoice'), __('May','sh_invoice'), __('Jun','sh_invoice'), __('Jul','sh_invoice'), __('Aug','sh_invoice'), __('Sep','sh_invoice'), __('Oct','sh_invoice'), __('Nov','sh_invoice'), __('Dec','sh_invoice'));
		if($sent == 'Not yet')
		{
			return $sent;	
		}
		else 
		{
			$sent = explode('/',$sent);
			return $months[intval($sent[1])] . ' ' . $sent[0] . ', ' . $sent[2];
		}
	}
	
	function sh_invoice_has_paid($postID)
	{
		$invoice_paid = get_post_meta($postID, 'invoice_paid', true)? get_post_meta($postID, 'invoice_paid', true): 'Not yet';
		if($invoice_paid != 'Not yet')
		{
			return true;	
		}
		else
		{
			return false;	
		}
	}
	
	/*--------------------------------------------------------------------------------------------
										get_invoice_status
	--------------------------------------------------------------------------------------------*/
	function sh_get_invoice_status($postID = NULL) 
	{
		if(!$postID){global $post; $postID = $post->ID;}
		$invoice_paid = get_post_meta($postID, 'invoice_paid', true);
		$invoice_sent = get_post_meta($postID, 'invoice_sent', true);
		if($invoice_paid && $invoice_paid != 'Not yet')
		{
			return __('Paid','sh_invoice');
		}
		elseif($invoice_sent && $invoice_sent != 'Not yet')
		{
			$invoice_sent = explode('/',$invoice_sent);
			$invoice_sent = intval($invoice_sent[2]).'-'.intval($invoice_sent[1]).'-'.intval($invoice_sent[0]);
	
			$days = sh_invoice_date_diff($invoice_sent, date_i18n('Y-m-d'));
			if($days == 0){return __('Sent today','sh_invoice');}
			elseif($days == 1){return __('Sent 1 day ago','sh_invoice');}
			else{ return __('Sent ','sh_invoice').$days.__(' days ago','sh_invoice');} 
		}
		else
		{
			return __('Not sent yet','sh_invoice');
		}
	}
	function sh_invoice_status() 
	{
		echo sh_get_invoice_status();	
	}
	
	function sh_invoice_date_diff($start, $end) 
	{
		$start_ts = strtotime($start);
		$end_ts = strtotime($end);
		$diff = $end_ts - $start_ts;
		return round($diff / 86400);
	}
	
	
	/*--------------------------------------------------------------------------------------------
										get_invoice_approval
	--------------------------------------------------------------------------------------------*/	
	function sh_get_quote_approved($postID = NULL) 
	{
		if(!$postID){global $post; $postID = $post->ID;}
		$quote_approved= get_post_meta($postID, 'quote_approved', true);
		if($quote_approved)
		{
			return $quote_approved;
		}
		else
		{
			return 'Not yet';
		}
	}
	function sh_quote_approved() 
	{
		$approved = sh_get_quote_approved();
		if($approved && $approved != 'Not yet'){
			echo $approved;
		} else{
			echo _('Not yet', 'sh_invoice');
		}
	}
	
	function sh_get_quote_approved_pretty()
	{
		$approved = sh_get_quote_approved();
		$months = array('',__('Jan','sh_invoice'), __('Feb','sh_invoice'), __('Mar','sh_invoice'), __('Apr','sh_invoice'), __('May','sh_invoice'), __('Jun','sh_invoice'), __('Jul','sh_invoice'), __('Aug','sh_invoice'), __('Sep','sh_invoice'), __('Oct','sh_invoice'), __('Nov','sh_invoice'), __('Dec','sh_invoice'));
		if($approved && $approved == 'Not yet')
		{
			return __('Not yet','sh_invoice');	
		}
		else 
		{
			$approved = explode('/',$approved);
			return $months[intval($approved[1])] . ' ' . $approved[0] . ', ' . $approved[2];
		}
	}
	
	
	/*--------------------------------------------------------------------------------------------
											Currency		
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_currency()
	{
		echo sh_invoice_get_currency();
	}
	
	function sh_invoice_get_currency()
	{
		
	global $post;
	$terms = get_the_terms($post->ID , 'client');
	if($terms)
	{	
		$terms = array_values($terms);
		$client_type = get_term_meta($terms[0]->term_id, 'client_type', true);
		if($client_type == "Domestic"):
			$type='';
		else:
			$type="_int";
		endif;
	}
	else
	{
		$type='';	
	}
		$sh_invoice_currency =  get_option('sh_invoice_currency'.$type);	
		if($sh_invoice_currency)
		{
			return $sh_invoice_currency;
		}
		else
		{
			return 'US'; // USA is default	
		}
	}
	  function sh_invoice_get_currency_int()
	 {
		$sh_invoice_currency_int =  get_option('sh_invoice_currency_int');	
		if($sh_invoice_currency_int)
		{
			return $sh_invoice_currency_int;
		}
		else
		{
			return 'US'; // USA is default	
		}
	}
	function sh_invoice_currency_code()
	{
		echo sh_invoice_get_currency_code();
	}
	
	function sh_invoice_get_currency_code()
	{
		$countries = sh_invoice_get_countries();
		return $countries[sh_invoice_get_currency()]['currency']['code'];
	}
	
	function sh_invoice_currency_format()
	{
		echo sh_invoice_get_currency_format();
	}
	
	function sh_invoice_get_currency_format()
	{
		$countries = sh_invoice_get_countries();
		return $countries[sh_invoice_get_currency()]['currency']['format'];
	}
	
	function sh_invoice_format_amount($amount)
	{
		return str_replace('#',$amount,sh_invoice_get_currency_format());
	}
	
	function sh_invoice_get_countries() 
	{
		$countries = array();
		$countries['CA'] = array('name'=>'Canada','currency'=>array('code'=>'CAD','format'=>'$#')); 
		$countries['US'] = array('name'=>'USA','currency'=>array('code'=>'USD','format'=>'$#')); 
		$countries['GB'] = array('name'=>'United Kingdom','currency'=>array('code'=>'GBP','format'=>'£#')); 
		$countries['DZ'] = array('name'=>'Algeria','currency'=>array('code'=>'DZD','format'=>'# د.ج')); 
		$countries['AR'] = array('name'=>'Argentina','currency'=>array('code'=>'ARS','format'=>'$#'));
		$countries['AW'] = array('name'=>'Aruba','currency'=>array('code'=>'AWG','format'=>'ƒ#'));
		$countries['AU'] = array('name'=>'Australia','currency'=>array('code'=>'AUD','format'=>'$#'));
		$countries['AT'] = array('name'=>'Austria','currency'=>array('code'=>'EUR','format'=>'€#'));
		$countries['BB'] = array('name'=>'Barbados','currency'=>array('code'=>'BBD','format'=>'$#'));
		$countries['BS'] = array('name'=>'Bahamas','currency'=>array('code'=>'BSD','format'=>'$#'));
		$countries['BH'] = array('name'=>'Bahrain','currency'=>array('code'=>'BHD','format'=>'ب.د #'));
		$countries['BE'] = array('name'=>'Belgium','currency'=>array('code'=>'EUR','format'=>'# €'));
		$countries['BR'] = array('name'=>'Brazil','currency'=>array('code'=>'BRL','format'=>'R$#'));
		$countries['BG'] = array('name'=>'Bulgaria','currency'=>array('code'=>'BGN','format'=>'# лв.'));
		$countries['CL'] = array('name'=>'Chile','currency'=>array('code'=>'CLP','format'=>'$#'));
		$countries['CN'] = array('name'=>'China','currency'=>array('code'=>'CNY','format'=>'¥#'));
		$countries['CO'] = array('name'=>'Colombia','currency'=>array('code'=>'COP','format'=>'$#'));
		$countries['CR'] = array('name'=>'Costa Rica','currency'=>array('code'=>'CRC','format'=>'₡#'));
		$countries['HR'] = array('name'=>'Croatia','currency'=>array('code'=>'HRK','format'=>'# kn'));
		$countries['CY'] = array('name'=>'Cyprus','currency'=>array('code'=>'CYP','format'=>'£#'));
		$countries['CZ'] = array('name'=>'Czech Republic','currency'=>array('code'=>'CZK','format'=>'# Kč'));
		$countries['DK'] = array('name'=>'Denmark','currency'=>array('code'=>'DKK','format'=>'# kr')); 
		$countries['DO'] = array('name'=>'Dominican Republic','currency'=>array('code'=>'DOP','format'=>'$#')); 
		$countries['EC'] = array('name'=>'Ecuador','currency'=>array('code'=>'ESC','format'=>'$#')); 
		$countries['EG'] = array('name'=>'Egypt','currency'=>array('code'=>'EGP','format'=>'£#'));
		$countries['EE'] = array('name'=>'Estonia','currency'=>array('code'=>'EEK','format'=>'# EEK'));
		$countries['FI'] = array('name'=>'Finland','currency'=>array('code'=>'EUR','format'=>'€#'));
		$countries['FR'] = array('name'=>'France','currency'=>array('code'=>'EUR','format'=>'€#'));
		$countries['DE'] = array('name'=>'Germany','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['GR'] = array('name'=>'Greece','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['GP'] = array('name'=>'Guadeloupe','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['GT'] = array('name'=>'Guatemala','currency'=>array('code'=>'GTQ','format'=>'Q#')); 
		$countries['HK'] = array('name'=>'Hong Kong','currency'=>array('code'=>'HKD','format'=>'$#')); 
		$countries['HU'] = array('name'=>'Hungary','currency'=>array('code'=>'HUF','format'=>'# Ft')); 
		$countries['IS'] = array('name'=>'Iceland','currency'=>array('code'=>'ISK','format'=>'# kr.')); 
		$countries['IN'] = array('name'=>'India','currency'=>array('code'=>'INR','format'=>'₨#')); 
		$countries['ID'] = array('name'=>'Indonesia','currency'=>array('code'=>'IDR','format'=>'Rp #')); 
		$countries['IE'] = array('name'=>'Ireland','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['IL'] = array('name'=>'Israel','currency'=>array('code'=>'ILS','format'=>'₪ #')); 
		$countries['IT'] = array('name'=>'Italy','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['JM'] = array('name'=>'Jamaica','currency'=>array('code'=>'JMD','format'=>'$#')); 
		$countries['JP'] = array('name'=>'Japan','currency'=>array('code'=>'JPY','format'=>'¥#')); 
		$countries['LV'] = array('name'=>'Latvia','currency'=>array('code'=>'LVL','format'=>'# Ls')); 
		$countries['LT'] = array('name'=>'Lithuania','currency'=>array('code'=>'LTL','format'=>'# Lt')); 
		$countries['LU'] = array('name'=>'Luxembourg','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['MY'] = array('name'=>'Malaysia','currency'=>array('code'=>'MYR','format'=>'RM#')); 
		$countries['MT'] = array('name'=>'Malta','currency'=>array('code'=>'MTL','format'=>'€#')); 
		$countries['MX'] = array('name'=>'Mexico','currency'=>array('code'=>'MXN','format'=>'$#')); 
		$countries['NL'] = array('name'=>'Netherlands','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['NZ'] = array('name'=>'New Zealand','currency'=>array('code'=>'NZD','format'=>'$#')); 
		$countries['NG'] = array('name'=>'Nigeria','currency'=>array('code'=>'NGN','format'=>'₦#'));
		$countries['NO'] = array('name'=>'Norway','currency'=>array('code'=>'NOK','format'=>'kr #')); 
		$countries['PK'] = array('name'=>'Pakistan','currency'=>array('code'=>'PKR','format'=>'₨#')); 
		$countries['PE'] = array('name'=>'Peru','currency'=>array('code'=>'PEN','format'=>'S/. #')); 
		$countries['PH'] = array('name'=>'Philippines','currency'=>array('code'=>'PHP','format'=>'Php #')); 
		$countries['PL'] = array('name'=>'Poland','currency'=>array('code'=>'PLZ','format'=>'# zł')); 
		$countries['PT'] = array('name'=>'Portugal','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['PR'] = array('name'=>'Puerto Rico','currency'=>array('code'=>'USD','format'=>'$#')); 
		$countries['RO'] = array('name'=>'Romania','currency'=>array('code'=>'ROL','format'=>'# lei'));
		$countries['RU'] = array('name'=>'Russia','currency'=>array('code'=>'RUB','format'=>'# руб')); 
		$countries['SG'] = array('name'=>'Singapore','currency'=>array('code'=>'SGD','format'=>'$#')); 
		$countries['SK'] = array('name'=>'Slovakia','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['SI'] = array('name'=>'Slovenia','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['ZA'] = array('name'=>'South Africa','currency'=>array('code'=>'ZAR','format'=>'R#')); 
		$countries['KR'] = array('name'=>'South Korea','currency'=>array('code'=>'KRW','format'=>'₩#')); 
		$countries['ES'] = array('name'=>'Spain','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$countries['VC'] = array('name'=>'St. Vincent','currency'=>array('code'=>'XCD','format'=>'$#')); 
		$countries['SE'] = array('name'=>'Sweden','currency'=>array('code'=>'SEK','format'=>'# kr')); 
		$countries['CH'] = array('name'=>'Switzerland','currency'=>array('code'=>'CHF','format'=>"# CHF")); 
		$countries['TW'] = array('name'=>'Taiwan','currency'=>array('code'=>'TWD','format'=>'NT$#')); 
		$countries['TH'] = array('name'=>'Thailand','currency'=>array('code'=>'THB','format'=>'#฿')); 
		$countries['TT'] = array('name'=>'Trinidad and Tobago','currency'=>array('code'=>'TTD','format'=>'TT$#')); 
		$countries['TR'] = array('name'=>'Turkey','currency'=>array('code'=>'TRL','format'=>'# TL')); 
		$countries['UA'] = array('name'=>'Ukraine','currency'=>array('code'=>'UAH','format'=>'# ₴')); 
		$countries['AE'] = array('name'=>'United Arab Emirates','currency'=>array('code'=>'AED','format'=>'Dhs. #')); 
		$countries['UY'] = array('name'=>'Uruguay','currency'=>array('code'=>'UYP','format'=>'$#')); 
		$countries['VE'] = array('name'=>'Venezuela','currency'=>array('code'=>'VUB','format'=>'Bs. #')); 
		return apply_filters('shopp_countries',$countries);
	}
	
	
	/*--------------------------------------------------------------------------------------------
												Tax	
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_tax($type='')
	{
		global $post;
		echo sh_get_sh_invoice_tax($post->ID,$type);
	}
	
	function sh_get_sh_invoice_tax($invoiceID = NULL,$type='')
	{
		
		if($type == ''):
			$terms = get_the_terms($post->ID , 'client');
			if($terms)
			{	
				$terms = array_values($terms);
				$client_type = get_term_meta($terms[0]->term_id, 'client_type', true);
				if($client_type == "Domestic"):
					$type='';
				else:
					$type="_int";
				endif;
			}
			else
			{
				$type='';	
			}
		endif;
		
		if(get_post_meta($invoiceID, 'invoice_tax', true))
		{
			return get_post_meta($invoiceID, 'invoice_tax', true);
		}
		elseif(get_option('sh_invoice_tax'.$type))
		{
			return get_option('sh_invoice_tax'.$type);
		}
		else
		{
			return '0.00';	
		}
	}
	
	function sh_invoice_has_tax()
	{
		global $post;
		if(sh_get_sh_invoice_tax($post->ID) == '0.00')
		{
			return false;	
		}
		else
		{
			return true;	
		}
	}
	
	
	
	/*--------------------------------------------------------------------------------------------
											Email Recipients
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_get_emailrecipients()
	{
		$sh_invoice_emailrecipients = get_option('sh_invoice_emailrecipients');	
		if($sh_invoice_emailrecipients)
		{
			return $sh_invoice_emailrecipients;
		}
		else
		{
			return 'client';	
		}
	}
	
	
	/*--------------------------------------------------------------------------------------------
											Invoice Permalinks
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_get_permalink()
	{
		$sh_invoice_permalink = get_option('sh_invoice_permalink');	
		if($sh_invoice_permalink)
		{
			return $sh_invoice_permalink;
		}
		else
		{
			return 'encoded';	
		}
	}
	
	
	/*--------------------------------------------------------------------------------------------
										Invoice Content Editor
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_get_content_editor()
	{
		$sh_invoice_content_editor = get_option('sh_invoice_content_editor');	
		if($sh_invoice_content_editor)
		{
			return $sh_invoice_content_editor;
		}
		else
		{
			return 'enabled';	
		}
	}
	
	
	/*--------------------------------------------------------------------------------------------
										Invoice Email
	--------------------------------------------------------------------------------------------*/
	function sh_get_invoice_email()
	{
		$sh_invoice_email = get_option('sh_invoice_email');	
		$current_user = wp_get_current_user();
		if($sh_invoice_email)
		{
			return $sh_invoice_email;
		}
		elseif($current_user)
		{
			return $current_user->user_email;
		}
		else
		{
			return '';	
		}
	}
	
	function sh_invoice_email()
	{
		echo sh_get_invoice_email();
	}
	
	/*--------------------------------------------------------------------------------------------
										Payment Gateways
	--------------------------------------------------------------------------------------------*/
	
	/**
	 * sh_invoice_payment_gateway
	 *
	 * @since 1.0.0
	 *
	 * Returns an array of files found in the gateway folder
	 **/
	function sh_get_payment_gateway()
	{
		$sh_invoice_payment_gateway = get_option('sh_invoice_payment_gateway');	
		if($sh_invoice_payment_gateway)
		{
			return $sh_invoice_payment_gateway;
		}
		else
		{
			return 'None';	
		}
	}
   function sh_get_payment_gateway_int()
	{
		$sh_invoice_payment_gateway_int = get_option('sh_invoice_payment_gateway_int');	
		if($sh_invoice_payment_gateway_int)
		{
			return $sh_invoice_payment_gateway_int;
		}
		else
		{
			return 'None';	
		}
	}
	
	function sh_invoice_payment_gateway()
	{
		echo sh_get_payment_gateway();
	}
		
	/**
	 * sh_invoice_payment_gateway_account
	 *
	 * @since 1.0.0
	 *
	 **/
	 function sh_get_payment_gateway_account($type='')
	{
		$sh_invoice_payment_gateway_account = get_option('sh_invoice_payment_gateway_account'.$type);	
		if($sh_invoice_payment_gateway_account)
		{
			return $sh_invoice_payment_gateway_account;
		}
		else
		{
			return '';	
		}
	}
	function sh_invoice_payment_gateway_account()
	{
		echo sh_get_payment_gateway_account($type);
	}
	
	
	/*--------------------------------------------------------------------------------------------
										the_invoice_total
	--------------------------------------------------------------------------------------------*/
	function sh_get_the_invoice_subtotal()
	{
		global $post;
		return sh_invoice_get_invoice_subtotal($post->ID);
	}
	function the_invoice_subtotal()
	{
		global $post;
		echo sh_invoice_format_amount(sh_invoice_get_invoice_subtotal($post->ID));
	}
	
	function sh_get_the_invoice_tax()
	{
		global $post;
		return sh_invoice_get_invoice_tax($post->ID);
	}
	function the_invoice_tax()
	{
		global $post;
		echo sh_invoice_format_amount(sh_invoice_get_invoice_tax($post->ID));
	}
	
	function get_the_invoice_total()
	{
		global $post;
		return sh_invoice_get_invoice_total($post->ID);
	}
	function the_invoice_total()
	{
		global $post;
		echo sh_invoice_format_amount(sh_invoice_get_invoice_total($post->ID));
	}
	
	
	
	/*--------------------------------------------------------------------------------------------
												Invoice Subtotal
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_get_invoice_subtotal($invoiceID)
	{
		$total = 0.00;
		$detailSubtotal = get_post_meta($invoiceID, 'detail_subtotal', true);
		if($detailSubtotal)
		{
			foreach($detailSubtotal as $subtotal)
			{
				$total += floatval($subtotal);
			}
		}
		return number_format($total, 2, '.', '');
	}
	
	
	/*--------------------------------------------------------------------------------------------
												Invoice Tax
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_get_invoice_tax($invoiceID)
	{
		$total = floatval(sh_invoice_get_invoice_subtotal($invoiceID) * sh_get_sh_invoice_tax($invoiceID));
		return number_format($total, 2, '.', ''); 
	}
	
	
	/*--------------------------------------------------------------------------------------------
												Invoice Total
	--------------------------------------------------------------------------------------------*/
	function sh_invoice_get_invoice_total($invoiceID)
	{
		$total = floatval(sh_invoice_get_invoice_subtotal($invoiceID) + sh_invoice_get_invoice_tax($invoiceID));
		return number_format($total, 2, '.', ''); 
	}
	
	/**
	 * sh_invoice_payment_gateway_button
	 *
	 * @since 1.0.0
	 *
	 * Creates the chosen payment gateway button
	 **/
	function sh_invoice_payment_gateway_button()
	{
		global $sh_invoice;
		$sh_invoice->invoice->sh_invoice_payment_gateway_button();
	}
	
	/**
	 * Company details
	 *
	 * @author Sawyer Hollenshead
	 * @since 1.0.0
	 *
	 * Get options that were set in the Invoice >> Options page
	 **/
	 
	 function sh_invoice_get_option($val = false)
	 {
	 	if($val){
			$val = get_option($val);	
			if($val)
			{
				return $val;
			}
			else
			{
				return false;	
			}
		}
	}
	 
	 function sh_invoice_option($val = false)
	 {
	 	if($val){
			$val = get_option($val);	
			if($val)
			{
				echo $val;
			}
			else
			{
				echo '';	
			}
		}
	}
	
	/*--------------------------------------------------------------------------------------------
												Return Menu
	--------------------------------------------------------------------------------------------*/
	if( !function_exists('sh_get_menu') ): 
	function sh_get_menu($id = false, $key = false, $verify = false){
		
		$continue = true;
		
		// Verify that the key matches
		if($verify){
			if($id && $key == sh_get_key($id)){
				$continue = true;
			} else{
				$continue = false;
			}
		}
		
		// Check for a cookie
		if(isset($_COOKIE['sh_client_id']) && !$id){
			$id = $_COOKIE['sh_client_id'];
		}
		
		 $menu = '<nav><ul>';
				
			if($id && $continue){
				
				// Set a current class name if we're viewing the Dashboard
				if(is_home()){
					$li_class = 'dashboard-link current-menu-item';
				} else{
					$li_class = 'dashboard-link';	
				}
				
				$menu .= '<li class="'.$li_class.'"><a href="'.get_home_url().'/?key='.sh_get_key($id).'&client_id='.$id.'">'.__('Dashboard', 'sh_invoice').'</a></li>';
			}
		$menu .= wp_nav_menu( array('container' => '', 'theme_location' => 'main_menu' , 'items_wrap' => '%3$s', 'fallback_cb' => false, 'echo' => 0) ).'</ul>			
			</nav>
			<div class="clearfix"></div>
		</header>
		
		<section id="page" role="main">';
		
		echo $menu;		
	}
	endif;
	
	/* sh_get_key()
	 *
	 * Takes an ID and encrypts it using a pre-defined SALT. 
	 * Returns either the key or false
	 *
	 * @since 1.0.0
	 * @author Sawyer Hollenshead
	 */
	if( !function_exists('sh_get_key') ):
	function sh_get_key($id = false){
		if($id){
			/* Never change the SALT unless you want 
			 * to screw up all previous URLS you've sent
			 */
			$key = crypt($id, 'shaken');
			return $key;
		} else {
			return false;
		}
	}
	endif;
?>