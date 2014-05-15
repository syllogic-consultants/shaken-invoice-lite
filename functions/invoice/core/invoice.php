<?php
class Invoice
{
	var $name;
	var $dir;
	var $theme_dir;
	var $theme_path;
	
	/**
	 * Invoice Constructor
	 *
	 * @since 1.0.0
	 * 
	 * @param object: sh_invoice to find parent variables.
	 **/
	  public $countries = array();
	   
		
		
	function Invoice($parent)
	{
		$this->name = $parent->name;					// Plugin Name
		$this->theme_dir = $parent->dir;				// Plugin directory
		$this->theme_path = $parent->path;				// Plugin Absolute Path
		$this->dir = get_template_directory_uri().'/functions/invoice';			// This directory
		
		
		// Set up Actions
		add_action('init', array($this, 'create_custom_post'));
		add_action('init', array($this, 'action_init'));
		
		add_filter('manage_edit-invoice_columns', array($this, 'invoice_columns_setup'));
		add_filter('manage_edit-invoice_sortable_columns', array($this, 'invoice_columns_setup_sortable'));
		add_filter('request', array($this, 'invoice_column_orderby') );
		add_action('manage_posts_custom_column', array($this, 'invoice_columns_data'));
		
		add_action('restrict_manage_posts', array($this, 'invoice_columns_filter'));
		//add_filter('pre_get_posts', array($this, 'invoice_number_order'));
		
		add_action('admin_menu', array($this, 'create_meta_boxes'));
		add_action('save_post', array($this, 'save_invoice'));
		add_action('template_redirect', array($this, 'invoice_template_redirect'));
		add_action( 'wp_ajax_nopriv_shaken_invoice_ajaxSubmission', array($this,'shaken_invoice_ajaxSubmission') );  
   	add_action( 'wp_ajax_shaken_invoice_ajaxSubmission', array($this,'shaken_invoice_ajaxSubmission') );  
		
		//add_filter('wp_footer', array($this, 'task_bar'));
		return true;
	}
	
	/**
	 * Creates Custom Posts type: Invoice
	 *
	 * @since 1.0.0
	 * 
	 **/
	function create_custom_post()
	{
		$labels = array(
			'name' => __( 'Invoices' ),
			'singular_name' => __( 'Invoice'),
			'search_items' =>  __( 'Search Invoices' ),
			'all_items' => __( 'All Invoices' ),
			'parent_item' => __( 'Parent Invoice' ),
			'parent_item_colon' => __( 'Parent Invoice:' ),
			'edit_item' => __( 'Edit Invoice' ), 
			'update_item' => __( 'Update Invoice' ),
			'add_new_item' => __( 'Add New Invoice' ),
			'new_item_name' => __( 'New Invoice Name' ),
			'view_item' => __( 'View Invoice / Quote' )
		); 	
		
		$supports = array('title');
		
		if(sh_invoice_get_content_editor() == 'enabled')
		{
			$supports[] = 'editor';
			
		}

		
		register_post_type('invoice', array(
			'labels' => $labels,
			'menu_icon' => $this->theme_dir.'/admin/images/menu-icon.png',
			'public' => true,
			'show_ui' => true,
			'_builtin' =>  false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array("slug" => "invoice"), // Permalinks format
			'query_var' => "invoice",
			'supports' => $supports,
		));
		
		
	}
	
	
	
	/**
	 * Creates Custom Posts type: Invoice
	 *
	 * @since 1.0.0
	 * 
	 **/
	function invoice_columns_setup($columns)
	{	
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"invoice_no" => "Invoice No.",
			"invoice_type" => "Type",
			"title" => "Title",
			"amount" => "Amount",
			"status" => "Status",
			"client" => "Client",
		);
		return $columns;
	}
	
	function invoice_columns_setup_sortable( $columns ) 
	{
		$columns["invoice_no"] = "invoice_no";
		$columns["invoice_type"] = "invoice_type";
		return $columns;
	}

	function invoice_column_orderby($vars) 
	{
		if(!isset($vars['orderby']))
		{
			return $vars;
		}
		
		if ($vars['orderby'] == 'invoice_no')
		{
			$vars = array_merge( $vars, array(
		      'meta_key' => 'invoice_number',
		      'orderby' => 'meta_value_num'
		    ) );
		}
		
		if ($vars['orderby'] == 'invoice_type')
		{
			$vars = array_merge( $vars, array(
		      'meta_key' => 'invoice_type',
		      'orderby' => 'meta_value'
		    ) );
		}

	 
		return $vars;
	}

	function invoice_columns_data($column)
	{
		global $post, $sh_invoice;
		if ("ID" == $column) echo $post->ID;
		elseif ("invoice_no" == $column) echo get_post_meta($post->ID, 'invoice_number', true);
		elseif ("invoice_type" == $column) sh_invoice_type($post->ID);
		elseif ("amount" == $column) echo sh_invoice_format_amount(sh_invoice_get_invoice_total($post->ID));
		elseif ("client" == $column) echo sh_get_invoice_client_edit($post->ID);
		elseif ("status" == $column) echo sh_get_invoice_status($post->ID); 
	}
	
	/**
	 * Orders Invoice by Invoice Number, not date created
	 *
	 * @since 1.0.0
	 * 
	 **/
	function sh_invoice_number_order( $query ) 
	{
		if(!is_admin())
		{
			return $query;
		}
			
		if($query->query['post_type'] == 'invoice') 
		{
			$query->set('meta_key', 'invoice_number' );
			$query->set('meta_compare', '>=');
			$query->set('meta_value', false );
			$query->set('orderby', 'meta_value');
			$query->set('order', 'ASC');
			//$query->set('post_status', 'publish,pending,draft,future,private');

		}
		return $query;
	}

	

	/**
	 * Adds filters to Invoice Columns
	 *
	 * @since 1.0.0
	 * 
	 **/
	function invoice_columns_filter()
	{
		global $wp, $post;
		$post_type = $wp->query_vars["post_type"];
		
		if($post_type == 'invoice')
		{
			$the_terms = get_terms('client','orderby=name&hide_empty=0' );
						
			$content  = '<select name="client" id="client" class="postform">';
			$content .= '<option value="0">'.__('View all Clients','sh_invoice').'</option>';
			foreach ($the_terms as $term){
				$content .= '<option value="' . $term->slug . '">'. $term->name . ' ('.$term->count.')</option>';
			}
			$content .= '</select>';
					
			$content = str_replace('post_tag', 'tag', $content);
			echo $content;
		}
		
	}
	
	/**
	 * action Init function
	 *
	 * @since 1.0.0
	 * 
	 **/
	function action_init()
	{
		// 1. flush and refresh permalinks
		global $wp_rewrite;
    	$wp_rewrite->flush_rules();
		
		// 2. Rewrite Permalinks
		$rewrite_rules = $wp_rewrite->generate_rewrite_rules('invoice/');
		$rewrite_rules['invoice/?$'] = 'index.php?paged=1';
	
		foreach($rewrite_rules as $regex => $redirect)
		{
			if(strpos($redirect, 'attachment=') === false)
				{
					$redirect .= '&post_type=invoice';
				}
			if(0 < preg_match_all('@\$([0-9])@', $redirect, $matches))
				{
					for($i = 0; $i < count($matches[0]); $i++)
					{
						$redirect = str_replace($matches[0][$i], '$matches['.$matches[1][$i].']', $redirect);
					}
				}
			$wp_rewrite->add_rule($regex, $redirect, 'top');
		}
		
		// 3. flush and refresh permalinks
		global $wp_rewrite;
    	$wp_rewrite->flush_rules();
     $this->country_array();
	}
	
	/**
	 * Meta Box: Invoice Details
	 *
	 * @author Sawyer Hollenshead
	 * @since 1.0.0
	 * 
	 **/
	function invoice_details() 
	{
		global $post;
		//echo $post->ID;
		// Use nonce for verification
  		echo '<input type="hidden" name="ei_noncename" id="ei_noncename" value="' .wp_create_nonce('ei-n'). '" />';
  	   $current_currency = sh_invoice_get_currency();
  	   $format = $this->countries[$current_currency]['currency']['format'];
	   $current_symbol = str_replace('#','',$format);
	   $sh_invoice_currency =  get_option('sh_invoice_currency');	
		if($sh_invoice_currency)
		{
			$domestic_currency = $sh_invoice_currency;
		}
		else
		{
			$domestic_currency = 'US'; // USA is default	
		}
	    $format1 = $this->countries[$domestic_currency]['currency']['format'];
	    $domestic_symbol = str_replace('#','',$format1);
	  
	 /*=================================================================*/ 
	   $sh_invoice_currency_int =  get_option('sh_invoice_currency_int');	
		if($sh_invoice_currency_int)
		{
			$international_currency =  $sh_invoice_currency_int;
		}
		else
		{
			$international_currency =  'US'; // USA is default	
		}
	    $format2 = $this->countries[$international_currency]['currency']['format'];
	    $international_symbol = str_replace('#','',$format2);
	  /*=================================================================*/
	   if(get_post_meta($post->ID, 'invoice_tax', true))
		{
			$domestic_tax = get_post_meta($post->ID, 'invoice_tax', true);
		}
		elseif(get_option('sh_invoice_tax'))
		{
			$domestic_tax = get_option('sh_invoice_tax');
		}
		else
		{
			$domestic_tax ='0.00';	
		}
	  /*=================================================================*/
	   if(get_post_meta($post->ID, 'invoice_tax', true))
		{
			$international_tax = get_post_meta($post->ID, 'invoice_tax', true);
		}
		elseif(get_option('sh_invoice_tax_int'))
		{
			$international_tax = get_option('sh_invoice_tax_int');
		}
		else
		{
			$international_tax = '0.00';	
		}
		?>

		<ul>
        	<li class="normal-detail">
            	<label><?php _e('ID Number:','sh_invoice'); ?> </label>
                <div class="front">
                	<span><?php sh_invoice_number(); ?></span>
                	<a href="#" class="sh_invoice-edit"><?php _e('Edit','sh_invoice'); ?></a>
                </div>
                <div class="back">
                	<input type="text" name="invoice-number" id="invoice-number" value="<?php sh_invoice_number(); ?>" size="2" />
                    <a href="#" class="button sh_invoice-ok"><?php _e('OK','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-cancel"><?php _e('Cancel','sh_invoice'); ?></a>
                </div>
            </li>
            <li class="normal-detail">
            	<label><?php _e('Type:','sh_invoice'); ?> </label>
                <div class="front">
                	<span><?php sh_invoice_type(); ?></span>
                	<a href="#" class="sh_invoice-edit"><?php _e('Edit','sh_invoice'); ?></a>
                </div>
                <div class="back">
                	<select name="invoice-type" id="invoice-type">
                    	<option value="<?php _e('Invoice','sh_invoice'); ?>" <?php if(sh_get_invoice_type() == __('Invoice','sh_invoice')){echo'selected="selected"';} ?>><?php _e('Invoice','sh_invoice'); ?></option>
                        <option value="<?php _e('Quote','sh_invoice'); ?>" <?php if(sh_get_invoice_type() == __('Quote','sh_invoice')){echo'selected="selected"';} ?>><?php _e('Quote','sh_invoice'); ?></option>
                    </select>
                    <a href="#" class="button sh_invoice-ok"><?php _e('OK','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-cancel"><?php _e('Cancel','sh_invoice'); ?></a>
                </div>
            </li>
            <li class="normal-detail">
            	<label><?php _e('Tax:','sh_invoice'); ?> </label>
                <div class="front">
                	<span><?php sh_invoice_tax(); ?></span>
                	<a href="#" class="sh_invoice-edit"><?php _e('Edit','sh_invoice'); ?></a>
                </div>
                <div class="back">
                	<input type="text" name="invoice-tax" id="invoice-tax" value="<?php sh_invoice_tax(); ?>" size="2" />
                    <a href="#" class="button sh_invoice-ok update-subtotal"><?php _e('OK','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-cancel update-subtotal"><?php _e('Cancel','sh_invoice'); ?></a>
                </div>
            </li>
            <li class="date-detail">
            	<label><?php _e('Sent:','sh_invoice'); ?> </label>
                <div class="front">
                	<span><?php echo sh_get_invoice_sent_pretty(); ?></span>
                	<a href="#" class="sh_invoice-edit"><?php _e('Edit','sh_invoice'); ?></a>
                </div>
                <div class="back">
                	<select name="mm" id="mm">
                    	<option></option>
                        <option value="01"><?php _e('Jan','sh_invoice'); ?></option>
                        <option value="02"><?php _e('Feb','sh_invoice'); ?></option>
                        <option value="03"><?php _e('Mar','sh_invoice'); ?></option>
                        <option value="04"><?php _e('Apr','sh_invoice'); ?></option>
                        <option value="05"><?php _e('May','sh_invoice'); ?></option>
                        <option value="06"><?php _e('Jun','sh_invoice'); ?></option>
                        <option value="07"><?php _e('Jul','sh_invoice'); ?></option>
                        <option value="08"><?php _e('Aug','sh_invoice'); ?></option>
                        <option value="09"><?php _e('Sep','sh_invoice'); ?></option>
                        <option value="10"><?php _e('Oct','sh_invoice'); ?></option>
                        <option value="11"><?php _e('Nov','sh_invoice'); ?></option>
                        <option value="12"><?php _e('Dec','sh_invoice'); ?></option>
            		</select>
                    <input type="text" maxlength="2" size="1" value="" name="dd" id="dd" />, 
                    <input type="text" maxlength="4" size="3" value="" name="yyyy" id="yyyy" />
                	<input type="hidden" name="invoice-sent" id="invoice-sent" value="<?php echo sh_get_invoice_sent(); ?>" />

                    <a href="#" class="button sh_invoice-ok"><?php _e('OK','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-clear"><?php _e('Reset','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-cancel"><?php _e('Cancel','sh_invoice'); ?></a>
                </div>
            </li>
            <li class="date-detail">
            	<label><?php _e('Paid:','sh_invoice'); ?> </label>
                <div class="front">
                	<span><?php echo sh_get_invoice_paid_pretty(); ?></span>
                	<a href="#" class="sh_invoice-edit"><?php _e('Edit','sh_invoice'); ?></a>
                </div>
                <div class="back">
                	<select name="mm" id="mm">
                    	<option></option>
                        <option value="01"><?php _e('Jan','sh_invoice'); ?></option>
                        <option value="02"><?php _e('Feb','sh_invoice'); ?></option>
                        <option value="03"><?php _e('Mar','sh_invoice'); ?></option>
                        <option value="04"><?php _e('Apr','sh_invoice'); ?></option>
                        <option value="05"><?php _e('May','sh_invoice'); ?></option>
                        <option value="06"><?php _e('Jun','sh_invoice'); ?></option>
                        <option value="07"><?php _e('Jul','sh_invoice'); ?></option>
                        <option value="08"><?php _e('Aug','sh_invoice'); ?></option>
                        <option value="09"><?php _e('Sep','sh_invoice'); ?></option>
                        <option value="10"><?php _e('Oct','sh_invoice'); ?></option>
                        <option value="11"><?php _e('Nov','sh_invoice'); ?></option>
                        <option value="12"><?php _e('Dec','sh_invoice'); ?></option>
            		</select>
                    <input type="text" maxlength="2" size="1" value="31" name="dd" id="dd" />, 
                    <input type="text" maxlength="4" size="3" value="2010" name="yyyy" id="yyyy" />
                	<input type="hidden" name="invoice-paid" id="invoice-paid" value="<?php echo sh_get_invoice_paid(); ?>" />

                    <a href="#" class="button sh_invoice-ok"><?php _e('OK','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-clear"><?php _e('Reset','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-cancel"><?php _e('Cancel','sh_invoice'); ?></a>
                </div>
            </li>
            <li class="date-detail">
            	<label><?php _e('Quote Approved:','sh_invoice'); ?> </label>
                <div class="front">
                	<span><?php echo sh_get_quote_approved_pretty(); ?></span>
                	<a href="#" class="sh_invoice-edit"><?php _e('Edit','sh_invoice'); ?></a>
                </div>
                <div class="back">
                	<select name="mm" id="mm">
                    	<option></option>
                        <option value="01"><?php _e('Jan','sh_invoice'); ?></option>
                        <option value="02"><?php _e('Feb','sh_invoice'); ?></option>
                        <option value="03"><?php _e('Mar','sh_invoice'); ?></option>
                        <option value="04"><?php _e('Apr','sh_invoice'); ?></option>
                        <option value="05"><?php _e('May','sh_invoice'); ?></option>
                        <option value="06"><?php _e('Jun','sh_invoice'); ?></option>
                        <option value="07"><?php _e('Jul','sh_invoice'); ?></option>
                        <option value="08"><?php _e('Aug','sh_invoice'); ?></option>
                        <option value="09"><?php _e('Sep','sh_invoice'); ?></option>
                        <option value="10"><?php _e('Oct','sh_invoice'); ?></option>
                        <option value="11"><?php _e('Nov','sh_invoice'); ?></option>
                        <option value="12"><?php _e('Dec','sh_invoice'); ?></option>
            		</select>
                    <input type="text" maxlength="2" size="1" value="31" name="dd" id="dd" />, 
                    <input type="text" maxlength="4" size="3" value="2010" name="yyyy" id="yyyy" />
                	<input type="hidden" name="quote_approved" id="quote_approved" value="<?php echo sh_get_quote_approved(); ?>" />

                    <a href="#" class="button sh_invoice-ok"><?php _e('OK','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-clear"><?php _e('Reset','sh_invoice'); ?></a>
                    <a href="#" class="sh_invoice-cancel"><?php _e('Cancel','sh_invoice'); ?></a>
                </div>
            </li>
		</ul>
        
        <input type="hidden" name="sh_invoice_hidden_currency" id="sh_invoice_hidden_currency"  value="<?php sh_invoice_currency_format(); ?>" />
        <input type="hidden" name="sh_invoice_hidden_tax" id="sh_invoice_hidden_tax"  value="<?php sh_invoice_tax($type); ?>" />
        <input type="hidden" name="sh_invoice_hidden_permalink" id="sh_invoice_hidden_permalink"  value="<?php echo sh_invoice_get_permalink(); ?>" />
        <input type="hidden" name="sh_invoice_hidden_password" id="sh_invoice_hidden_password"  value="<?php echo sh_get_invoice_client_password(); ?>" />
        <input type="hidden" name="current_symbol" id="current_symbol"  value="<?php echo $current_symbol; ?>" />
        <input type="hidden" name="domestic_symbol" id="domestic_symbol"  value="<?php echo $domestic_symbol; ?>" />
        <input type="hidden" name="domestic_tax" id="domestic_tax"  value="<?php echo $domestic_tax; ?>" />
        <input type="hidden" name="international_symbol" id="international_symbol"  value="<?php echo $international_symbol; ?>" />
        <input type="hidden" name="international_tax" id="international_tax"  value="<?php echo $international_tax; ?>" />
        <input type="hidden" name="ajaxURL" id="iajaxURL"  value="<?php echo get_bloginfo('url')?>/wp-admin/admin-ajax.php" />
        
   
		<?php
		
		
	}
	
	
	/*--------------------------------------------------------------------------------------------
										Send Invoice
	--------------------------------------------------------------------------------------------*/
	function invoice_send() 
	{
		global $post;
		?>
		<?php if($_GET['sent'] == 'success'): ?>
        	<div class="updated">
            	<p><?php _e('Invoice sent successfully!','sh_invoice'); ?></p>
            </div>
        <?php elseif($_GET['sent'] == 'fail'): ?>
        	<div class="error">
            	<p><?php _e('Invoice failed to send.','sh_invoice'); ?></p>
            </div>
        <?php endif; ?>
        <ul>
        	<?php /* <li>
            	<a href="<?php the_permalink(); ?>" class="button"><?php _e('View Invoice','sh_invoice'); ?></a> <?php _e('copy link, print as pdf, style invoice template','sh_invoice'); ?>
            </li>
            <li>
            	<a href="<?php echo add_query_arg('do', 'pdf', get_permalink($post->ID)); ?>" class="button">Save as PDF</a> 
            </li>
            <li>
            	<a href="<?php echo add_query_arg('email', 'view', get_permalink($post->ID)); ?>" class="button"><?php _e('View Email','sh_invoice'); ?></a> <?php _e('check before sending, style email template','sh_invoice'); ?>
            </li>
            */ ?>
            <li>
            	<?php if(sh_get_invoice_client_name()): ?>
					<?php if(sh_get_invoice_client_email()): ?>
                        <a href="<?php echo add_query_arg('email', 'send', get_permalink($post->ID)); ?>" class="button"><?php _e('Send Email','sh_invoice'); ?></a> <?php _e('to','sh_invoice'); ?> <?php sh_invoice_client_email(); ?> <a href="<?php sh_invoice_client_edit_link(); ?>"><?php _e('Edit Client','sh_invoice'); ?></a>  
                    <?php else: ?>
                        <a class="button disabled"><?php _e('Send Email','sh_invoice'); ?></a> <?php _e('no email address','sh_invoice'); ?> <a href="<?php sh_invoice_client_edit_link(); ?>"><?php _e('Edit Client','sh_invoice'); ?></a> 
                    <?php endif; ?>
                <?php else: ?>
                    <a class="button disabled"><?php _e('Send Email','sh_invoice'); ?></a> <?php _e('no Client Selected','sh_invoice'); ?>
                <?php endif; ?>
            </li>
        </ul>

        
		<?php
	}
	
	
	
	/*--------------------------------------------------------------------------------------------
											Project Breakdown
	--------------------------------------------------------------------------------------------*/
	
	
	function project_breakdown() 
	{
		global $post, $detailTitle;
		$detailCount = 0;
	
		
		$detailCount = count($detailTitle);
		
		// Use nonce for verification
  		echo '<input type="hidden" name="ei_noncename" id="ei_noncename" value="' .wp_create_nonce('ei-n'). '" />';
		?>
		
        <div class="detail detail-header">            
            <table cellpadding="0" cellspacing="0" width="100%">
            	<tr>
                	<td class="title"><?php _e('Title','sh_invoice'); ?></td>
					<td class="description"><?php _e('Description','sh_invoice'); ?></td>
					<td class="type"><?php _e('Type','sh_invoice'); ?></td>
                    <td class="rate"><?php _e('Rate','sh_invoice'); ?><span class="hr"></span></td>
                    <td class="duration"><?php _e('Quantity','sh_invoice'); ?></td>
                    <td class="subtotal"><?php _e('Subtotal','sh_invoice'); ?></td>
                </tr>
            </table>
        </div>
        <div class="details">
        <?php if(sh_invoice_has_details()): ?>
        	<?php while(sh_invoice_detail()): ?>
            	<div class="detail">
            		<table cellpadding="0" cellspacing="0" width="100%">
                   	<tr>
	                    <td class="title"><input type="text" name="detail-title[]" id="detail-title" value="<?php sh_the_detail_title(); ?>" /></td>
	                	<td class="description"><textarea name="detail-description[]" id="detail-description"><?php echo sh_get_the_detail_description(); ?></textarea></td>
                       	<td class="type">
                        	<select name="detail-type[]" id="detail-type">
                            	<option value="<?php _e('Timed','sh_invoice'); ?>" <?php if(sh_get_the_detail_type() == __('Timed','sh_invoice')){echo'selected="selected"';} ?>>Timed</option>
                            	<option value="<?php _e('Fixed','sh_invoice'); ?>" <?php if(sh_get_the_detail_type() == __('Fixed','sh_invoice')){echo'selected="selected"';} ?>>Fixed</option>
                            </select>
                        </td>
                        <td class="rate">
							<input size="2" onBlur="if (this.value == '') {this.value = '0.00';}" onFocus="if(this.value == '0.00') {this.value = '';}"  type="text" name="detail-rate[]" id="detail-rate" value="<?php echo sh_get_the_detail_rate(); ?>" />
                        </td>
                        <td class="duration">
                        	<input size="2" onBlur="if (this.value == '') {this.value = '0.0';}" onFocus="if(this.value == '0.0') {this.value = '';}"  type="text" name="detail-duration[]" id="detail-duration" value="<?php sh_the_detail_duration(); ?>" />
                        </td>
                        <td class="subtotal">
                        	<input type="hidden" name="detail-subtotal[]" id="detail-subtotal" value="<?php sh_the_detail_subtotal(); ?>" />
                            <p><?php echo sh_invoice_format_amount('<span id="detail-subtotal">'.sh_get_the_detail_subtotal().'</span>'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                    	<td colspan="6">
                    		<a class="delete" href="#" title="Remove Detail"><?php _e( 'Remove', 'shaken' ); ?></a>
		                    <div class="grab"><?php _e( 'Reorder', 'shaken' ); ?></div>
                    	</td>
                    </tr>
                    
                    </table> 
                </div>
            <?php endwhile; ?>
        <?php else: ?>
         		<div class="detail">
                	<table cellpadding="0" cellspacing="0" width="100%">
                    	<tr>
                        	<td class="title"><input type="text" name="detail-title[]" id="detail-title" /></td>
                            <td class="description"><textarea name="detail-description[]" id="detail-description"></textarea></td>
                            <td class="type">
                            <select name="detail-type[]" id="detail-type">
                                <option value="Timed"><?php _e('Timed','sh_invoice'); ?></option>
                                <option value="Fixed"><?php _e('Fixed','sh_invoice'); ?></option>
                            </select>
                            </td>
                            <td class="rate">
								<input size="2" onBlur="if (this.value == '') {this.value = '0.00';}" onFocus="if(this.value == '0.00') {this.value = '';}"  type="text" name="detail-rate[]" id="detail-rate" value="0.00" />
                    		</td>
                            <td class="duration">
	                            <input size="2" onBlur="if (this.value == '') {this.value = '0.0';}" onFocus="if(this.value == '0.0') {this.value = '';}"  type="text" name="detail-duration[]" id="detail-duration" value="0.0" />
                            </td>
                            <td class="subtotal">
                            	<input type="hidden" name="detail-subtotal[]" id="detail-subtotal" value="0.00" />
                                <p><?php echo sh_invoice_format_amount('<span id="detail-subtotal">0.00</span>'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
	                    	<td colspan="6">
	                    		<a class="delete" href="#" title="Remove Detail"><?php _e( 'Remove', 'shaken' ); ?></a>
			                    <div class="grab"><?php _e( 'Reorder', 'shaken' ); ?></div>
	                    	</td>
	                    </tr>
                    </table> 
                </div>
        <?php endif; ?> 
        </div>  
		<div class="detail detail-footer">
        <p>
        <strong><?php _e('Subtotal','sh_invoice'); ?>:</strong> <?php echo sh_invoice_format_amount('<span class="invoice-subtotal">'.sh_get_the_invoice_subtotal().'</span>'); ?>	
        &nbsp;&nbsp;&nbsp;
        <?php //if(sh_invoice_has_tax()): ?>
        <strong><?php _e('Tax','sh_invoice'); ?>:</strong> <?php echo sh_invoice_format_amount('<span class="invoice-tax">'.sh_get_the_invoice_tax().'</span>'); ?>
        &nbsp;&nbsp;&nbsp;
        <?php //endif; ?>
        <strong><?php _e('Total','sh_invoice'); ?>:</strong> <?php echo sh_invoice_format_amount('<span class="invoice-total">'.get_the_invoice_total().'</span>'); ?>
        &nbsp;&nbsp;&nbsp;
        <a class="add-detail button-primary" href="#" title="Add New Row"><?php _e('Add New Row','sh_invoice'); ?></a>
        </p>
        </div> 
		<?php
	}
	
	function create_meta_boxes() 
	{
		add_meta_box('invoice_details', 'Invoice Details', array($this, 'invoice_details'), 'invoice', 'normal', 'low');
		add_meta_box('project_breakdown', 'Project Breakdown', array($this, 'project_breakdown'), 'invoice', 'normal', 'high');
		add_meta_box('invoice_send', 'Send Email', array($this, 'invoice_send'), 'invoice', 'side', 'low');
	} 
	
	


	/*--------------------------------------------------------------------------------------------
										Save
	--------------------------------------------------------------------------------------------*/
	function save_invoice($post_id) {
		// verify this with nonce because save_post can be triggered at other times
		if (!wp_verify_nonce($_POST['ei_noncename'], 'ei-n')) return $post_id;
	
		// do not save if this is an auto save routine
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
		
		update_post_meta($post_id, 'invoice_number',$_POST['invoice-number']);
		update_post_meta($post_id, 'invoice_type',$_POST['invoice-type']);
		update_post_meta($post_id, 'invoice_tax',$_POST['invoice-tax']);
		update_post_meta($post_id, 'invoice_sent',$_POST['invoice-sent']);
		update_post_meta($post_id, 'invoice_paid',$_POST['invoice-paid']);
		update_post_meta($post_id, 'quote_approved',$_POST['quote_approved']);
		update_post_meta($post_id, 'detail_title', $_POST['detail-title']);
	
		/*$temp_description = serialize($_POST['detail-description']);
		$temp_description = addslashes($temp_description);*/
		update_post_meta($post_id, 'detail_description', $_POST['detail-description']);
		
		update_post_meta($post_id, 'detail_type', $_POST['detail-type']);
		update_post_meta($post_id, 'detail_rate', $_POST['detail-rate']);
		update_post_meta($post_id, 'detail_duration', $_POST['detail-duration']);
		update_post_meta($post_id, 'detail_subtotal', $_POST['detail-subtotal']);
	}
	
	/**
	 * Invoice Template Redirect
	 *
	 * @author Sawyer Hollenshead
	 * @since 1.0.0
	 * 
	 **/
	function invoice_template_redirect()
	{
		// define invoice url variables
		global $wp, $post;
		
		$post_type = get_query_var('post_type');
		
		$email = (isset($_GET['email'])) ? $_GET['email'] : false;
		$paid = (isset($_GET['diap'])) ? $_GET['diap'] : false;
		$approved = (isset($_GET['approved'])) ? $_GET['approved'] : false;
		
		if($post_type == 'invoice')
		{
			if($paid == 'yes')
			{
				update_post_meta($post->ID, 'invoice_paid', date('d/m/Y'));
			} 
			
			if($approved == 'yes'){
				$approved_template = get_stylesheet_directory().'/invoice/approved.php';
				if(!file_exists($approved_template)){$approved_template = $this->theme_path.'/template/approved.php';}
				
				update_post_meta($post->ID, 'quote_approved', date('d/m/Y'));
				ob_start();
					include($approved_template);
					$message = ob_get_contents();
				ob_end_clean();
				include($this->theme_path.'/admin/approved-email.php');
			} 
			else if($approved == 'reset'){
				delete_post_meta($post->ID, 'quote_approved');
			}
			
			$this->invoice_security();
			if($email == 'send')
			{
				$email_template = get_stylesheet_directory().'/invoice/email.php';
				if(!file_exists($email_template)){$email_template = $this->theme_path.'/template/email.php';}
				
				// get html email and store as variable for sending
				ob_start();
					include($email_template);
					$message = ob_get_contents();
				ob_end_clean();
				include($this->theme_path.'/admin/client-email.php');
			}
		}
	}
	
	/**
	 * Invoice Security
	 *
	 * @since 1.0.0
	 * 
	 **/
	 function invoice_security()
	 {
		if (post_password_required()) 
		{ 
            ?>
       			<!DOCTYPE html>
				<html <?php language_attributes(); ?>>
				<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>" />
				
				<title><?php title_tag(); ?></title>
				
				<link rel="profile" href="http://gmpg.org/xfn/11" />
				<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
				
				<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
				<link rel='stylesheet' type='text/css' href="<?php echo home_url(); ?>/?shaken-custom-content=css" media="screen" />
	            </head>
	            <body>
	                    <form method="post" action="<?php echo home_url(); ?>/wp-pass.php" id="password">
	                    <h1><?php _e('This','sh_invoice'); ?> <?php sh_invoice_type(); ?> <?php _e('is password protected','sh_invoice'); ?>.</h1>
	                    <input type="text" id="pwbox-531" name="post_password" value="<?php _e('Password','sh_invoice'); ?>" onfocus="if(this.value == '<?php _e('Password','sh_invoice'); ?>') {this.value = '';this.type='password'}" onblur="if (this.value == '') {this.value = '<?php _e('Password','sh_invoice'); ?>'; this.type='text'}"/>
	                    <input type="submit" value="Submit" name="Submit" class="btn" />
	                    </form>
	         	</body>
	         	</html>
            <?php
            die;
    	}
	 }
function country_array(){

      $this->countries['CA'] = array('name'=>'Canada','currency'=>array('code'=>'CAD','format'=>'$#')); 
		$this->countries['US'] = array('name'=>'USA','currency'=>array('code'=>'USD','format'=>'$#')); 
		$this->countries['GB'] = array('name'=>'United Kingdom','currency'=>array('code'=>'GBP','format'=>'£#')); 
		$this->countries['DZ'] = array('name'=>'Algeria','currency'=>array('code'=>'DZD','format'=>'# د.ج')); 
		$this->countries['AR'] = array('name'=>'Argentina','currency'=>array('code'=>'ARS','format'=>'$#'));
		$this->countries['AW'] = array('name'=>'Aruba','currency'=>array('code'=>'AWG','format'=>'ƒ#'));
		$this->countries['AU'] = array('name'=>'Australia','currency'=>array('code'=>'AUD','format'=>'$#'));
		$this->countries['AT'] = array('name'=>'Austria','currency'=>array('code'=>'EUR','format'=>'€#'));
		$this->countries['BB'] = array('name'=>'Barbados','currency'=>array('code'=>'BBD','format'=>'$#'));
		$this->countries['BS'] = array('name'=>'Bahamas','currency'=>array('code'=>'BSD','format'=>'$#'));
		$this->countries['BH'] = array('name'=>'Bahrain','currency'=>array('code'=>'BHD','format'=>'ب.د #'));
		$this->countries['BE'] = array('name'=>'Belgium','currency'=>array('code'=>'EUR','format'=>'# €'));
		$this->countries['BR'] = array('name'=>'Brazil','currency'=>array('code'=>'BRL','format'=>'R$#'));
		$this->countries['BG'] = array('name'=>'Bulgaria','currency'=>array('code'=>'BGN','format'=>'# лв.'));
		$this->countries['CL'] = array('name'=>'Chile','currency'=>array('code'=>'CLP','format'=>'$#'));
		$this->countries['CN'] = array('name'=>'China','currency'=>array('code'=>'CNY','format'=>'¥#'));
		$this->countries['CO'] = array('name'=>'Colombia','currency'=>array('code'=>'COP','format'=>'$#'));
		$this->countries['CR'] = array('name'=>'Costa Rica','currency'=>array('code'=>'CRC','format'=>'₡#'));
		$this->countries['HR'] = array('name'=>'Croatia','currency'=>array('code'=>'HRK','format'=>'# kn'));
		$this->countries['CY'] = array('name'=>'Cyprus','currency'=>array('code'=>'CYP','format'=>'£#'));
		$this->countries['CZ'] = array('name'=>'Czech Republic','currency'=>array('code'=>'CZK','format'=>'# Kč'));
		$this->countries['DK'] = array('name'=>'Denmark','currency'=>array('code'=>'DKK','format'=>'# kr')); 
		$this->countries['DO'] = array('name'=>'Dominican Republic','currency'=>array('code'=>'DOP','format'=>'$#')); 
		$this->countries['EC'] = array('name'=>'Ecuador','currency'=>array('code'=>'ESC','format'=>'$#')); 
		$this->countries['EG'] = array('name'=>'Egypt','currency'=>array('code'=>'EGP','format'=>'£#'));
		$this->countries['EE'] = array('name'=>'Estonia','currency'=>array('code'=>'EEK','format'=>'# EEK'));
		$this->countries['FI'] = array('name'=>'Finland','currency'=>array('code'=>'EUR','format'=>'€#'));
		$this->countries['FR'] = array('name'=>'France','currency'=>array('code'=>'EUR','format'=>'€#'));
		$this->countries['DE'] = array('name'=>'Germany','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['GR'] = array('name'=>'Greece','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['GP'] = array('name'=>'Guadeloupe','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['GT'] = array('name'=>'Guatemala','currency'=>array('code'=>'GTQ','format'=>'Q#')); 
		$this->countries['HK'] = array('name'=>'Hong Kong','currency'=>array('code'=>'HKD','format'=>'$#')); 
		$this->countries['HU'] = array('name'=>'Hungary','currency'=>array('code'=>'HUF','format'=>'# Ft')); 
		$this->countries['IS'] = array('name'=>'Iceland','currency'=>array('code'=>'ISK','format'=>'# kr.')); 
		$this->countries['IN'] = array('name'=>'India','currency'=>array('code'=>'INR','format'=>'₨#')); 
		$this->countries['ID'] = array('name'=>'Indonesia','currency'=>array('code'=>'IDR','format'=>'Rp #')); 
		$this->countries['IE'] = array('name'=>'Ireland','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['IL'] = array('name'=>'Israel','currency'=>array('code'=>'ILS','format'=>'₪ #')); 
		$this->countries['IT'] = array('name'=>'Italy','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['JM'] = array('name'=>'Jamaica','currency'=>array('code'=>'JMD','format'=>'$#')); 
		$this->countries['JP'] = array('name'=>'Japan','currency'=>array('code'=>'JPY','format'=>'¥#')); 
		$this->countries['LV'] = array('name'=>'Latvia','currency'=>array('code'=>'LVL','format'=>'# Ls')); 
		$this->countries['LT'] = array('name'=>'Lithuania','currency'=>array('code'=>'LTL','format'=>'# Lt')); 
		$this->countries['LU'] = array('name'=>'Luxembourg','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['MY'] = array('name'=>'Malaysia','currency'=>array('code'=>'MYR','format'=>'RM#')); 
		$this->countries['MT'] = array('name'=>'Malta','currency'=>array('code'=>'MTL','format'=>'€#')); 
		$this->countries['MX'] = array('name'=>'Mexico','currency'=>array('code'=>'MXN','format'=>'$#')); 
		$this->countries['NL'] = array('name'=>'Netherlands','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['NZ'] = array('name'=>'New Zealand','currency'=>array('code'=>'NZD','format'=>'$#')); 
		$this->countries['NG'] = array('name'=>'Nigeria','currency'=>array('code'=>'NGN','format'=>'₦#'));
		$this->countries['NO'] = array('name'=>'Norway','currency'=>array('code'=>'NOK','format'=>'kr #')); 
		$this->countries['PK'] = array('name'=>'Pakistan','currency'=>array('code'=>'PKR','format'=>'₨#')); 
		$this->countries['PE'] = array('name'=>'Peru','currency'=>array('code'=>'PEN','format'=>'S/. #')); 
		$this->countries['PH'] = array('name'=>'Philippines','currency'=>array('code'=>'PHP','format'=>'Php #')); 
		$this->countries['PL'] = array('name'=>'Poland','currency'=>array('code'=>'PLZ','format'=>'# zł')); 
		$this->countries['PT'] = array('name'=>'Portugal','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['PR'] = array('name'=>'Puerto Rico','currency'=>array('code'=>'USD','format'=>'$#')); 
		$this->countries['RO'] = array('name'=>'Romania','currency'=>array('code'=>'ROL','format'=>'# lei'));
		$this->countries['RU'] = array('name'=>'Russia','currency'=>array('code'=>'RUB','format'=>'# руб')); 
		$this->countries['SG'] = array('name'=>'Singapore','currency'=>array('code'=>'SGD','format'=>'$#')); 
		$this->countries['SK'] = array('name'=>'Slovakia','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['SI'] = array('name'=>'Slovenia','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['ZA'] = array('name'=>'South Africa','currency'=>array('code'=>'ZAR','format'=>'R#')); 
		$this->countries['KR'] = array('name'=>'South Korea','currency'=>array('code'=>'KRW','format'=>'₩#')); 
		$this->countries['ES'] = array('name'=>'Spain','currency'=>array('code'=>'EUR','format'=>'€#')); 
		$this->countries['VC'] = array('name'=>'St. Vincent','currency'=>array('code'=>'XCD','format'=>'$#')); 
		$this->countries['SE'] = array('name'=>'Sweden','currency'=>array('code'=>'SEK','format'=>'# kr')); 
		$this->countries['CH'] = array('name'=>'Switzerland','currency'=>array('code'=>'CHF','format'=>"# CHF")); 
		$this->countries['TW'] = array('name'=>'Taiwan','currency'=>array('code'=>'TWD','format'=>'NT$#')); 
		$this->countries['TH'] = array('name'=>'Thailand','currency'=>array('code'=>'THB','format'=>'#฿')); 
		$this->countries['TT'] = array('name'=>'Trinidad and Tobago','currency'=>array('code'=>'TTD','format'=>'TT$#')); 
		$this->countries['TR'] = array('name'=>'Turkey','currency'=>array('code'=>'TRL','format'=>'# TL')); 
		$this->countries['UA'] = array('name'=>'Ukraine','currency'=>array('code'=>'UAH','format'=>'# ₴')); 
		$this->countries['AE'] = array('name'=>'United Arab Emirates','currency'=>array('code'=>'AED','format'=>'Dhs. #')); 
		$this->countries['UY'] = array('name'=>'Uruguay','currency'=>array('code'=>'UYP','format'=>'$#')); 
		$this->countries['VE'] = array('name'=>'Venezuela','currency'=>array('code'=>'VUB','format'=>'Bs. #')); 

}
function shaken_invoice_ajaxSubmission() {
   
	      $term_id = $_REQUEST['term_id'];
	      $action = get_term_meta($term_id, 'client_type', true);
	      echo $action;
      	exit;			
			
		}
}

?>