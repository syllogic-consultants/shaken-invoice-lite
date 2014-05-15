<?php 
class Shaken_Options
{
	var $name;
	var $dir;
	var $theme_dir;
	var $theme_path;

	/**
	 * Options Constructor
	 *
	 * @since 2.0.0
	 * 
	 * @param object: sh_invoice to find parent variables.
	 **/
	function __construct($parent)
	{
		$this->name = $parent->name;					// Plugin Name
		$this->plugin_dir = $parent->dir;				// Plugin directory
		$this->plugin_path = $parent->path;				// Plugin Absolute Path
		$this->dir = plugins_url('/',__FILE__);			// This directory
		return true;	
	}
	
	/**
	 * Options Admin Page
	 *
	 * @since 2.0.0
	 * 
	 **/
	function admin_page()
	{
		if( !current_user_can( 'manage_options' ) ){
			wp_die( __( 'Insufficient permissions', 'shaken' ) );
		}
		?>
	
	<?php if($_GET['settings-updated'] == 'true' ){ ?>	
		<div id="message" class="updated">
			<p><?php _e( 'Settings updated', 'shaken' ); ?></p>
		</div>
	<?php } ?>
	
	<div class="wrap" id="sh_invoice-options"> 
        <div class="sh_invoice-heading">
            <div class="icon32" id="icon-themes"><br></div>
            <h2><?php _e('Invoice Options','sh_invoice'); ?></h2>
        </div>
        
        <div id="poststuff">
        <form method="post" action="options.php" >
            <?php wp_nonce_field('update-options'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label><?php _e('Currency','sh_invoice'); ?></label><span><?php _e('This is used throughout the theme','sh_invoice'); ?></span></th>
                    <td>
                    <select name="sh_invoice_currency">
                    <option value="Select a Currency"><?php _e('Select a Currency','sh_invoice'); ?></option>
                    <?php foreach(sh_invoice_get_countries() as $key => $value): ?>
                    	<option value="<?php echo $key; ?>" <?php if(sh_invoice_get_currency() == $key){echo 'selected="selected"'; } ?> >
						<?php echo $value['name']; ?> (<?php echo $value['currency']['code']; ?>)
                        </option>
                    <?php endforeach; ?>
                    </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Tax','sh_invoice'); ?></label><span><?php _e('Enter Tax Amount (5% = .05)','sh_invoice'); ?></span></th>
                    <td><input name="sh_invoice_tax" value="<?php sh_invoice_tax(); ?>" type="text" size="4" maxlength="5"> </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Send Invoice','sh_invoice'); ?></label><span><?php _e('Select invoice recipients','sh_invoice'); ?></span></th>
                    <td><input name="sh_invoice_emailrecipients" type="radio" value="client" <?php if(sh_invoice_get_emailrecipients() == 'client'){echo'checked="checked"';} ?>>
                        <?php _e('Send Invoice to Client Only','sh_invoice'); ?> <br />
                        <input name="sh_invoice_emailrecipients" type="radio" value="both" <?php if(sh_invoice_get_emailrecipients() == 'both'){echo'checked="checked"';} ?>>
                        <?php _e('Send Invoice to Client &amp; Me (<a href="profile.php">see Profile</a>)','sh_invoice'); ?></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">
                    	<label><?php _e('E-mail Content','sh_invoice'); ?></label>
                    	<span><?php _e('This is what will display above the invoice/quote link when you e-mail it through the Admin screen','sh_invoice'); ?></span>
                    </th>
                    <td><textarea name="sh_invoice_email_content" cols="40" value="" rows="5"><?php if( get_option('sh_invoice_email_content') ){ echo get_option('sh_invoice_email_content'); } ?></textarea></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><label><?php _e('Permalinks','sh_invoice'); ?></label><span><?php _e('Encoded is more secure','sh_invoice'); ?></span></th>
                    <td><input name="sh_invoice_permalink" type="radio" value="encoded" <?php if(sh_invoice_get_permalink() == 'encoded'){echo'checked="checked"';} ?>>
                        <?php _e('Encoded','sh_invoice'); ?><br />
                        <input name="sh_invoice_permalink" type="radio" value="standard" <?php if(sh_invoice_get_permalink() == 'standard'){echo'checked="checked"';} ?>>
                        <?php _e('Standard','sh_invoice'); ?></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Content Editor','sh_invoice'); ?></label><span><?php _e('Add content to your invoice','sh_invoice'); ?></span></th>
                    <td><input name="sh_invoice_content_editor" type="radio" value="enabled" <?php if(sh_invoice_get_content_editor() == 'enabled'){echo'checked="checked"';} ?>>
                        <?php _e('Enabled','sh_invoice'); ?> <br />
                        <input name="sh_invoice_content_editor" type="radio" value="disabled" <?php if(sh_invoice_get_content_editor() == 'disabled'){echo'checked="checked"';} ?>>				
                        <?php _e('Disabled','sh_invoice'); ?> 
                        </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><label><?php _e('Payment Gateway','sh_invoice'); ?></label><span><?php _e('Let clients pay invoice\'s online','sh_invoice'); ?></span></th>
                    <td>
                    
                    <select name="sh_invoice_payment_gateway" style="float:left; margin-right:10px;">
	                    <option value="None">None</option>                    
	                    
	                    <?php foreach($this->get_payment_gateways() as $gateway): ?>
	                    	<option value="<?php echo $gateway; ?>" <?php if(sh_get_payment_gateway() == $gateway){echo 'selected="selected"'; } ?> ><?php echo $gateway; ?></option>
	                    <?php endforeach; ?>
                    </select>
                    <div class="none" style="float:left; margin-right:10px;">
                    </div>
                    
                    <div class="account" style="float:left; margin-right:10px;">
                    	<input name="sh_invoice_payment_gateway_account" value="<?php sh_invoice_payment_gateway_account(); ?>" type="text" size="30"><br />
                    	<label for="sh_invoice_payment_gateway_account"><?php _e('Enter your account ID.','sh_invoice'); ?></label>
                    </div>
                    
                    </td>
                </tr>
                
                <tr valign="top" class="sandbox-row">
                    <th scope="row"><label><?php _e('Sandbox Mode','sh_invoice'); ?></label><span><?php _e('Enable to test payments','sh_invoice'); ?></span></th>
                    <td><input name="sh_invoice_sandbox_mode" type="radio" value="enabled" <?php if( get_option('sh_invoice_sandbox_mode') == 'enabled'){echo'checked="checked"';} ?>>
                        <?php _e('Enabled','sh_invoice'); ?> <br />
                        <input name="sh_invoice_sandbox_mode" type="radio" value="disabled" <?php if( get_option('sh_invoice_sandbox_mode') != 'enabled'){echo'checked="checked"';} ?>>				
                        <?php _e('Disabled','sh_invoice'); ?> 
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">
                    	<label><?php _e('Company Name','sh_invoice'); ?></label>
                    	<span><?php _e('Displayed on the invoice and in the automated e-mail (if you decide to send it)','sh_invoice'); ?></span>
                    </th>
                    <td><input name="sh_invoice_company" value="<?php sh_invoice_option('sh_invoice_company'); ?>" type="text" size="30"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Address','sh_invoice'); ?></label></th>
                    <td><textarea name="sh_invoice_company_address" cols="40" value="" rows="5"><?php sh_invoice_option('sh_invoice_company_address'); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Phone','sh_invoice'); ?></label></th>
                    <td><input name="sh_invoice_company_phone" value="<?php sh_invoice_option('sh_invoice_company_phone'); ?>" type="text" size="30"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Email','sh_invoice'); ?></label><span><?php _e('Also appears as "sent from" in emails.','sh_invoice'); ?></span></th>
                    <td><input name="sh_invoice_email" value="<?php sh_invoice_email(); ?>" type="text" size="30"></td>
                </tr>
            </table>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="sh_invoice_email_content, sh_invoice_sandbox_mode, sh_invoice_currency, sh_invoice_tax, sh_invoice_emailrecipients, sh_invoice_permalink, sh_invoice_content_editor, sh_invoice_email, sh_invoice_payment_gateway, sh_invoice_payment_gateway_account, sh_invoice_company, sh_invoice_company_address, sh_invoice_company_phone" />
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
		</div>
	</div>
    <script type="text/javascript">
		jQuery(document).ready(function($){
			var payment_gateway_select = $('select[name=sh_invoice_payment_gateway]');
			
			$('.sandbox-row').hide();
			
			function sh_invoice_payment_gateway_switch()
			{
				if(payment_gateway_select.attr('value') == 'None')
				{
					payment_gateway_select.siblings('.none').show();
					payment_gateway_select.siblings('.account').hide();	
					$('sandbox-row').hide();
				}
				else
				{
					gateway = payment_gateway_select.attr('value');
					
					if( gateway == 'Google Checkout' ){
						label_text = 'Enter your '+gateway+' Merchant ID';
						$('.sandbox-row').show();
					} else{
						label_text = 'Enter your '+gateway+' account e-mail';
						$('.sandbox-row').hide();
					}
					payment_gateway_select.siblings('.none').hide();
					payment_gateway_select.siblings('.account').find('label').html(label_text);
					payment_gateway_select.siblings('.account').show();	
				}
			}
			payment_gateway_select.change(function(){
				sh_invoice_payment_gateway_switch();
			});
			sh_invoice_payment_gateway_switch();

		});
	</script>
	<?php
	}
	
	/**
	 * Get Payment Gateways
	 *
	 * @since 2.0.1
	 *
	 **/
	function get_payment_gateways()
	{
		$plugins = array();
		$gateways_path = $this->plugin_path.'gateways/';
		
		$files = array_diff(scandir($gateways_path), array('.', '..')); 
		if($files)
		{
			foreach($files as $file)
			{
				if(is_dir($gateways_path.$file)){ break; }							// cancel out the folders
				
				$file_contents = file_get_contents($gateways_path.$file);			// 1. Reads file
				
				preg_match( '|@gateway (.*)$|mi', $file_contents, $matches);			// 2. Finds Tempalte Name, stores in $matches
				
				if(!empty($matches[1]))
				{
					$plugins[] = $matches[1]; 											// 3. Adds array ([name] => array(path, dir)) 
				}
				
			}
		}
		return $plugins;
	}
	
}
?>