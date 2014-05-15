/**
 * Shaken Invoice Admin Scripts
 *
 * @author Elliot Condon & Sawyer
 * @since 1.0.0
 *
 **/



jQuery(document).ready(function($)
{
	/**
	 * Invoice edit page (if #invoice_details exists)
	 *
	 * @author Elliot Condon
	 * @since 1.0.0
	 *
	 **/
var sh_invoice_default_tax = jQuery('#invoice_details input#invoice-tax').attr('value');
	if($('#invoice_details').size() > 0 )
	{
		/* 
		
		sh_invoice_rand
		-----------------------------------------*/
		function sh_invoice_rand(l,u) // lower bound and upper bound
		{
			 return Math.floor((Math.random() * (u-l+1))+l);
		}
		/* 
	
		sh_invoice_generate_permalink
		-----------------------------------------*/
		function sh_invoice_generate_permalink() {
			var length = 30;
			var characters = '0123456789abcdefghijklmnopqrstuvwxyz';
			var string = "";    
			for (var p = 0; p < length; p++) {
				string += characters[sh_invoice_rand(0, (characters.length-1))];
			}
			
			return string;
		}
		/* 
	
		sh_invoice_get_date_pretty
		-----------------------------------------*/
		function sh_invoice_get_date_pretty(date)
		{
			var months = new Array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
			return months[parseInt(date,16)];
		}
		/* 
	
		Generate Invoice Slug
		-----------------------------------------*/
		var sh_invoice_permalink = $('input#sh_invoice_hidden_permalink').attr('value');
		if(sh_invoice_permalink == 'encoded')
		{
			if($('#slugdiv input#post_name').attr('value').length < 30)
			{
				$('#slugdiv input#post_name').attr('value',sh_invoice_generate_permalink());
			}
		}
		else
		{
			$('#slugdiv input#post_name').attr('value','');	
		}
		/* 
	
		Enter Client Password
		-----------------------------------------*/
		var sh_invoice_client_password = $('input#sh_invoice_hidden_password').attr('value');
		if($('#visibility input#post_password').attr('value').length < 1)
		{
			$('#visibility input#post_password').attr('value',sh_invoice_client_password);
		}
		/* 
	
		Invoice Detail Functions
		-----------------------------------------*/
		$.fn.sh_invoice_detail_edit = function()
		{
			$(this).find('.front').css({'display':'none'});
			$(this).find('.back').css({'display':'inline'});	
		}
		$.fn.sh_invoice_detail_hide = function()
		{
			$(this).find('.front').css({'display':'inline'});
			$(this).find('.back').css({'display':'none'});	
		}
		
		$.fn.sh_invoice_detail_get_input = function()
		{
			var li = $(this);
			var result = '';
			if(li.find('input').size() > 0)
			{
				result = li.find('input').attr('value');
			}
			else if(li.find('select').size() > 0)
			{
				result = li.find('select').attr('value');
			}
			return  result;
		}
		
		$.fn.sh_invoice_detail_set_input = function(origInputValue)
		{
			var li = $(this);
			if(li.find('input').size() > 0)
			{
				li.find('input').attr('value', origInputValue);
			}
			else if(li.find('select').size() > 0)
			{
				li.find('select option').each(function(){
					if($(this).attr('value') == origInputValue){
						$(this).attr('selected','selected');
					}
				});
			}
		}
		
		/*-------------------------------------------------------------------------------
			Detail Normal
		-------------------------------------------------------------------------------*/
		$('#invoice_details li.normal-detail').each(function()
		{
			var li = $(this);
			var origInputValue;
			var inputType = 'input'; if(li.find('select').size > 0){inputType = 'option';}
			
			li.find('a.sh_invoice-edit').click(function(){
				origInputValue = li.sh_invoice_detail_get_input();
				li.sh_invoice_detail_edit();	
				return false;
			});	
			
			li.find('a.sh_invoice-ok').click(function(){
				var newInputValue = li.sh_invoice_detail_get_input();
				li.find('span').html(newInputValue);	
				li.sh_invoice_detail_hide();
				update_subtotal_numbers()
				return false;
			});
			
			li.find('a.sh_invoice-cancel').click(function(){
				li.find('span').html(origInputValue);	
				//li.sh_invoice_detail_set_input(origInputValue);
				li.sh_invoice_detail_hide();
				update_subtotal_numbers()
				return false;
			});
		});
		
		/*-------------------------------------------------------------------------------
			Detail Date
		-------------------------------------------------------------------------------*/
		$.fn.sh_invoice_detail_set_date = function(day, month, year)
		{
			$(this).find('select#mm option').each(function(){
				if($(this).attr('value') == month){
					$(this).attr('selected','selected');
				}
			});
			$(this).find('input#dd').attr('value', day);
			$(this).find('input#yyyy').attr('value', year);
		}
		
		$('#invoice_details li.date-detail').each(function()
		{
			var li = $(this);
			var hidden = li.find('input[type=hidden]');
			
			var currentTime = new Date();
			var month = currentTime.getMonth() + 1;
			var day = currentTime.getDate();
			var year = currentTime.getFullYear();
			
			if(!hidden.attr('value') == 'Not yet')
			{
				currentTime = li.find('input[type=hidden]').attr('value'); currentTime = currentTime.split('/');
				month = currentTime[1];
				day = currentTime[0];
				year = currentTime[2];
			}
			
			li.sh_invoice_detail_set_date(day, month, year);
			
			li.find('a.sh_invoice-edit').click(function(){
				li.sh_invoice_detail_edit();
				return false;
			});	
			
			li.find('a.sh_invoice-ok').click(function(){
				var newInputValue = li.find('input#dd').attr('value') +'/'+ li.find('select#mm').attr('value') +'/'+ li.find('input#yyyy').attr('value');
				var newInputValuePretty = li.find('select#mm option:selected').text() +' '+ li.find('input#dd').attr('value') +', '+ li.find('input#yyyy').attr('value');
				li.find('input[type=hidden]').attr('value', newInputValue);	
				li.find('span').html(newInputValuePretty);	
				li.sh_invoice_detail_hide();
				return false;
			});
			
			li.find('a.sh_invoice-clear').click(function(){
				/**/
				li.find('span').html('Not yet');	
				li.find('input[type=hidden]').attr('value', 'Not yet');	
				li.sh_invoice_detail_hide();
				return false;
			});
			
			li.find('a.sh_invoice-cancel').click(function(){
				if(hidden.attr('value') == 'Not yet')
				{
					hidden.attr('value', 'Not yet');	
					li.find('span').html('Not yet');	
				}
				else
				{
					currentTime = li.find('input[type=hidden]').attr('value'); currentTime = currentTime.split('/');
					month = currentTime[1];
					day = currentTime[0];
					year = currentTime[2];
					
					hidden.attr('value', day+'/'+month+'/'+year);	
					li.find('span').html(sh_invoice_get_date_pretty(month)+' '+day+', '+year);		
				}
				li.sh_invoice_detail_hide();
				return false;
			});
		});
		/* 
	
		Store Tax, Currency
		-----------------------------------------*/
		var sh_invoice_currenty = $('input#sh_invoice_hidden_currency').attr('value');
		
		/* 
		
		Update Sub total
		-----------------------------------------*/
		function update_subtotal(detail)
		{
			var rate = detail.find('input#detail-rate').attr('value'); rate = parseFloat(rate);
			var duration = detail.find('input#detail-duration').attr('value'); duration = parseFloat(duration);
			
			var subtotal = 0.00;
			if(detail.find('select :selected').attr('value') == 'Fixed')
			{
				subtotal = rate * duration; subtotal = subtotal.toFixed(2);
			}
			else
			{
				subtotal = rate * duration; subtotal = subtotal.toFixed(2);
				detail.find('span.hr').html('/hr');
			}
	
			detail.find('input#detail-subtotal').attr('value', subtotal);
			detail.find('#detail-subtotal').html(subtotal);
			
			update_subtotal_numbers();
		}
		
		function update_subtotal_numbers()
		{
			$('.detail-footer .invoice-subtotal').html(get_invoice_subtotal().toFixed(2));
			$('.detail-footer .invoice-tax').html(get_invoice_tax().toFixed(2));
			$('.detail-footer .invoice-total').html(get_invoice_total().toFixed(2));
		}
		
		function get_invoice_subtotal()
		{
			var temp_total = 0;
			$('input#detail-subtotal').each(function(){
				temp_total += parseFloat($(this).attr('value'));
			});	
			return temp_total;
		}
		
		function get_invoice_tax()
		{
			var sh_invoice_tax = $('#invoice_details input#invoice-tax').attr('value'); sh_invoice_tax = parseFloat(sh_invoice_tax);
			var temp_total = parseFloat(sh_invoice_tax * get_invoice_subtotal());
			return temp_total;
		}
		
		function get_invoice_total()
		{
			var temp_total = parseFloat(get_invoice_subtotal() + get_invoice_tax());
			return temp_total;
		}
				
		/* Init Sub total
		--------------------*/
		function initSubtotalUpdate()
		{
			$('.detail').each(function()
			{
				var detail = $(this);
				update_subtotal(detail);
				$(this).find('input#detail-rate').change(function(){update_subtotal(detail);}).bind("change keyup blur", function(){update_subtotal(detail);});
				$(this).find('input#detail-duration').change(function(){update_subtotal(detail);}).bind("change keyup blur", function(){update_subtotal(detail);});
				$(this).find('select#detail-type').change(function(){update_subtotal(detail);});
				$(this).find('a.delete').click(function(){detail.remove(); return false; });
				
			});
		}
		initSubtotalUpdate();
		
		/* add Detail Button
		---------------------*/
		$('a.add-detail').click(function()
		{

			$('.details .detail:last').clone().appendTo('.details');
			$('.details .detail:last input[type=text]').val('0');
			$('.details .detail:last .title input[type=text]').val('');
			$('.details .detail:last textarea').html('');
			initSubtotalUpdate();
			return false;
		});
		
		
		/* Sortable
		--------------------*/
		if($('div.details').length > 0)
		{
			$('div.details').sortable({
				accept : 'detail',
				opacity: 	0.5,
				fit :	false,
				placeholder: 'detail-placeholder'
			});
		}
	
	}// end if detail exists
		
	/**
	 * Edit Page Icons / Style
	 *
	 * @author Elliot Condon
	 * @since 1.0.0
	 *
	 **/
	var post_type = getUrlVars()["post_type"];
	var taxonomy = getUrlVars()["taxonomy"];
	
	if(taxonomy == 'client')
	{
		$('.icon32#icon-edit').addClass('sh_invoice-icon');
		$('.form-field:has( input[name=slug])').hide();
	}
	else if($('#wpbody-content h2').html() == 'Edit Invoice' || $('#wpbody-content h2').html() == 'Add New Invoice')
	{
		$('div.wrap').addClass('sh_invoice-edit');
	}
	else if(post_type == 'invoice')
	{
		$('.icon32#icon-edit').addClass('sh_invoice-icon');
	}
	
	function getUrlVars() {
		var vars = {};
		var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			vars[key] = value;
		});
		return vars;
	}

      var typecurrency;
      jQuery('#clientchecklist input:checkbox').click(function()
		{
            var current_symbol;
            var domestic_symbol;
            var domestic_tax;
            var international_symbol;
            var international_tax;
            var replace_symbol ;
           	var change_tax; 
            var path = jQuery('#ajaxURL').val();
            var n = jQuery('#clientchecklist input:checkbox').filter(':checked').length;
            if(n > 1 || n == 0){
			   if(n > 1){
                  alert("Please select only one client");
                }
                  jQuery('#clientchecklist input:checkbox').removeAttr("checked");

                 current_symbol=jQuery('#current_symbol').val();
                 domestic_symbol=jQuery('#domestic_symbol').val();
                 domestic_tax=jQuery('#domestic_tax').val();
                  replace_symbol = domestic_symbol;
           		 change_tax = domestic_tax;
                 if(current_symbol != replace_symbol){
                    jQuery('#current_symbol').val(replace_symbol);
                    replaceCurrency(current_symbol,domestic_symbol);
                 }
                 jQuery('#invoice_details input#invoice-tax').attr('value',domestic_tax);
                 jQuery("li.normal-detail:nth-child(3)").find('span').html(domestic_tax);  
                 update_subtotal_numbers();
           }
            else{
               
            	jQuery.ajax({
                
   	   	  		url: path,
      	  			 data: {
            		 			'term_id' : jQuery('#clientchecklist input:checkbox').filter(':checked').val(),
            					'action' : 'shaken_invoice_ajaxSubmission'
            					
        	  			},
        				success: function(respon) {
                        typecurrency = respon;
                         //alert(typecurrency);
                         current_symbol=jQuery('#current_symbol').val();
                         domestic_symbol=jQuery('#domestic_symbol').val();
                         domestic_tax=jQuery('#domestic_tax').val();
                        international_symbol=jQuery('#international_symbol').val();
                        international_tax=jQuery('#international_tax').val();
                       if(typecurrency == "Domestic"){
           		            replace_symbol = domestic_symbol;
           		            change_tax = domestic_tax;
                        }
                       else {
	             
            	            replace_symbol = international_symbol;
                       		change_tax = international_tax; 
                        
        				}        
                        if(current_symbol != replace_symbol){
                            jQuery('#current_symbol').val(replace_symbol)
                            replaceCurrency(current_symbol,replace_symbol);
                        }   
                        jQuery('#invoice_details input#invoice-tax').attr('value',change_tax);
                        jQuery("li.normal-detail:nth-child(3)").find('span').html(change_tax);
                        update_subtotal_numbers();
                   
                  },
        	 			error: function(respon, ajaxOptions, thrownError) {
        							alert(respon.status);
        							alert(thrownError);
        							alert(ajaxOptions);
        				}
     				});
                }
                
		  });
	
      function replaceCurrency(current_currency,replace_currency){

      
       $(".details .detail p").each(function() {
         var text1 = $(this).html();
         var temp;
         var subtotal;
         var string;
         temp = text1.replace(current_currency, replace_currency);
       
         $(this).html(temp);
        });
        jQuery(".detail-footer").html("");
        jQuery('.detail').each(function(){
		var detail = $(this);
        var rate = detail.find('input#detail-rate').attr('value'); rate = parseFloat(rate);
	    var duration = detail.find('input#detail-duration').attr('value'); duration = parseFloat(duration);
		subtotal = 0.00;
		subtotal = rate * duration; subtotal = subtotal.toFixed(2);
        });
		string ="<p><strong>Subtotal:</strong>"+replace_currency+"<span class='invoice-subtotal'>"+subtotal+"</span>&nbsp;&nbsp;&nbsp; ";
        string =string +"<strong>Tax:</strong>"+replace_currency+"<span class='invoice-tax'>"+get_invoice_tax()+"</span>&nbsp;&nbsp;&nbsp;";
        string =string +"<strong>Total:</strong>"+replace_currency+"<span class='invoice-total'>"+get_invoice_total()+"</span>&nbsp;&nbsp;&nbsp;";
        string =string +"<a class='add-detail button-primary' href='#' title='Add New Row'>Add New Row</a></p>";
      //  alert(string);
       jQuery(".detail-footer").html(string);
       jQuery('a.add-detail').click(function()
		        { 
                  
			        jQuery('.details .detail:last').clone().appendTo('.details');
			        jQuery('.details .detail:last input[type=text]').val('0');
			        jQuery('.details .detail:last .title input[type=text]').val('');
			        jQuery('.details .detail:last textarea').html('');
			        initSubtotalUpdate();
			        return false;
		        });    
    }
   
   
});
