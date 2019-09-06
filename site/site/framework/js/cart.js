// JavaScript Document
$(document).ready(function (){
	$('#cart_s_same').click(function() {
		if ($('#cart_s_same').is(':checked'))
		{
			$('#cart_s_contact').val($('#cart_b_contact').val());
			$('#cart_s_address1').val($('#cart_b_address1').val());
			$('#cart_s_address2').val($('#cart_b_address2').val());
			$('#cart_s_city').val($('#cart_b_city').val());
			$('#cart_s_state').val($('#cart_b_state').val());
			$('#cart_s_zip').val($('#cart_b_zip').val());
			$('#cart_s_country').val($('#cart_b_country').val());
			$('#cart_s_phone').val($('#cart_b_phone').val());
		}
	});
	
	$('img[id^=pay_cardtype_]').bind('enable', function () {
		if ($(this).attr('src').indexOf('_gray.png') >= 0)
		{
			var src = $(this).attr('src').replace('_gray.png', '.png');
			$(this).attr('src', src);
		}
	});
	
	$('img[id^=pay_cardtype_]').bind('disable', function () {
		if ($(this).attr('src').indexOf('_gray.png') < 0)
		{
			var src = $(this).attr('src').match(/[^\.]+/) + '_gray.png';
			$(this).attr('src', src);
		}
	});
	
	$('#pay_new_cardnum').keyup(function() {
		var num = $('#pay_new_cardnum').val().replace(/[^\d]/g,'');
		
		if (num.match(/^5[1-5]\d{14}$/))
		{
			$('#pay_cardtype_VS').trigger('disable');
			$('#pay_cardtype_MC').trigger('enable');
			$('#pay_cardtype_AX').trigger('disable');
			$('#pay_cardtype_DI').trigger('disable');
			$('#pay_new_cardtype').val('MC');
		}
		else if (num.match(/^4\d{15}/) || num.match(/^4\d{12}/))
		{
			$('#pay_cardtype_VS').trigger('enable');
			$('#pay_cardtype_MC').trigger('disable');
			$('#pay_cardtype_AX').trigger('disable');
			$('#pay_cardtype_DI').trigger('disable');
			$('#pay_new_cardtype').val('VS');
		}
		else if (num.match(/^3[47]\d{13}/))
		{
			$('#pay_cardtype_VS').trigger('disable');
			$('#pay_cardtype_MC').trigger('disable');
			$('#pay_cardtype_AX').trigger('enable');
			$('#pay_cardtype_DI').trigger('disable');
			$('#pay_new_cardtype').val('AX');
		}
		else if (num.match(/^6011\d{12}/))
		{
			$('#pay_cardtype_VS').trigger('disable');
			$('#pay_cardtype_MC').trigger('disable');
			$('#pay_cardtype_AX').trigger('disable');
			$('#pay_cardtype_DI').trigger('enable');
			$('#pay_new_cardtype').val('DI');
		}
		else
		{
			$('#pay_cardtype_VS').trigger('enable');
			$('#pay_cardtype_MC').trigger('enable');
			$('#pay_cardtype_AX').trigger('enable');
			$('#pay_cardtype_DI').trigger('enable');
			$('#pay_new_cardtype').val('');
		}
	});
});

function scCheckPlaceOrder()
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/cart_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			action: "placeorder", 
		},
		success: function(data) {
			popWindow({
				content: data.Body,
				width: 600,
				modal: true
			});
			
			setTimeout(function() {
				$('#form_place_order').submit();
			}, 100);
		}
	});
}

function scGetStates(country, atype, state)
{
	$.ajax({ 
		type: "POST", 
		url: "/framework/ajax/cart_ajax.php", 
		cache: false, 
		dataType: 'json', 
		data: { 
			"action": "getstates", 
			"country": country,
			"state": state
		}, 
		success: function(data) {
			if (data.Data == "")
			{
				$('#label_'+atype.toLowerCase()+'_state').css('display', 'none');
				$('#label_'+atype.toLowerCase()+'_istate').css('display', '');
			}
			else
			{
				$('#label_'+atype.toLowerCase()+'_istate').css('display', 'none');
				$('#label_'+atype.toLowerCase()+'_state').css('display', '');
				
				$('#cart_'+atype.toLowerCase()+'_state').empty();
				$.each(data.Data, function(i, info) {
					$('#cart_'+atype.toLowerCase()+'_state').append($('<option>').text(info.text).attr('value', info.value).attr('selected', info.selected));
				});
			}
		}
	});
}

function scCheckNewAccount(noform)
{
	var v_email = $('#cart_new_email').val();
	var v_firstname = $('#cart_new_firstname').val();
	var v_lastname = $('#cart_new_lastname').val();
	var v_password = $('#cart_new_password').val();
	var v_passwordc = $('#cart_new_passwordc').val();
	
	if ($.trim(v_email) == '')
	{
		$('#signin-error').html('Please enter an e-mail address.');
		return;
	}
	
	if ($.trim(v_firstname) == '')
	{
		$('#signin-error').html('Please enter your first name.');
		return;
	}
	
	if ($.trim(v_lastname) == '')
	{
		$('#signin-error').html('Please enter your last name.');
		return;
	}
	
	if (v_password == '')
	{
		$('#signin-error').html('Please enter password.');
		return;
	}
	
	if (v_passwordc == '')
	{
		$('#signin-error').html('Please confirm your password.');
		return;
	}
	
	if (v_password != v_passwordc)
	{
		$('#signin-error').html('Your passwords don\'t match.');
		return;
	}
	
	$.ajax({
		type: "POST",
		url: "/framework/ajax/cart_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			action: 'newaccount', 
			email: v_email,
			firstname: v_firstname,
			lastname: v_lastname,
			password: v_password,
			passwordc: v_passwordc
		},
		success: function(data) {
			if (data.Data.Authenticated)
			{
				if (noform)
				{
					unpopWindow();
					
					setTimeout(function () {
						$('#free-books-button').click();
					}, 500);
				}
				else
				{
					$('#cart_process').val('checkout');
					$('#checkout_form').submit();
				}
			}
			else
			{
				$('#signin-error').html(data.Error);
			}
		}
	});
}

function scCheckLogin(noform)
{
	var v_username = $('#cart_login_email').val();
	var v_password = $('#cart_login_password').val();
	
	if ($.trim(v_username) == '')
	{
		$('#signin-error').html('Please enter your e-mail address.');
		return;
	}
	
	if (v_password == '')
	{
		$('#signin-error').html('Please enter your password.');
		return;
	}
	
	$.ajax({
		type: "POST",
		url: "/framework/ajax/cart_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			action: 'authlogin', 
			username: v_username,
			password: v_password
		},
		success: function(data) {
			if (data.Data.Authenticated)
			{
				if (noform)
				{
					unpopWindow();
					
					setTimeout(function () {
						$('#free-books-button').click();
					}, 500);
				}
				else
				{
					$('#cart_process').val('checkout');
					$('#checkout_form').submit();
				}
			}
			else
			{
				$('#signin-error').html(data.Error);
			}
		}
	});
}

function scActivateButton()
{
	$('#checkout_first').removeAttr('disabled');
	$('#checkout_first').addClass('green');
	$('#checkout_first').removeClass('disabled');
}

function scCheckCheckout()
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/cart_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			action: "loggedin", 
		},
		success: function(data) {
			if (data.Data.LoggedIn)
			{
				$('#cart_process').val('checkout');
				$('#checkout_form').submit();
			}
			else
			{
				popWindow({
					content: data.Body,
					width: 400,
					modal: true,
					onpop: function() {
						$("input[id^='cart_login_']").onEnter( function()
							{
								$('#checkout_login').click();
							}
						);
						$("input[id^='cart_new_']").onEnter( function()
							{
								$('#checkout_newaccount').click();
							}
						);
					}
				});
			}
		}
	});
}




