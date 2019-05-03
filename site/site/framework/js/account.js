// JavaScript Document
$(document).ready(function (){
	$("#info_birthday").datepicker({
		changeMonth: true,
		changeYear: true,
		yearRange: "c-100:c"
	});
});

function acctGetStates(country, state)
{
	$.ajax({ 
		type: "POST", 
		url: "/framework/ajax/account_ajax.php", 
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
				$('#label_state').css('display', 'none');
				$('#label_istate').css('display', '');
			}
			else
			{
				$('#label_istate').css('display', 'none');
				$('#label_state').css('display', '');
				
				$('#info_state').empty();
				$.each(data.Data, function(i, info) {
					$('#info_state').append($('<option>').text(info.text).attr('value', info.value).attr('selected', info.selected));
				});
			}
		}
	});
}

function acctSavePassword(id)
{
	var pwd = $('#login_password').val();
	var pwdc = $('#login_passwordc').val();
	
	$.ajax({ 
		type: "POST", 
		url: "/framework/ajax/account_ajax.php", 
		cache: false, 
		dataType: 'json', 
		data: { 
			"action": "savepassword", 
			"id": id,
			"pwd": pwd,
			"pwdc": pwdc
		}, 
		success: function(data) {
			if (data.Error != "")
			{
				$('#signin-error').html(data.Error);
			}
			else
			{
				popWindow({
					content: data.Body,
					width: 600,
					modal: false,
					unpop: function() {
						location.href = '/profile/login';
					}
				});
			}
		}
	});
}

function acctResetPassword()
{
	var email = $('#login_username').val();
	
	$.ajax({ 
		type: "POST", 
		url: "/framework/ajax/account_ajax.php", 
		cache: false, 
		dataType: 'json', 
		data: { 
			"action": "resetpassword", 
			"email": email
		}, 
		success: function(data) {
			if (data.Error != "")
			{
				$('#signin-error').html(data.Error);
			}
			else
			{
				popWindow({
					content: data.Body,
					width: 600,
					modal: false
				});
			}
		}
	});
}

function acctDeleteCard(id)
{
	if (confirm('Are you sure you want to delete this card?'))
	{
		$.ajax({ 
			type: "POST", 
			url: "/framework/ajax/account_ajax.php", 
			cache: false, 
			dataType: 'json', 
			data: { 
				"action": "deletecard", 
				"id": id 
			}, 
			success: function(data) {
				location.reload();
			}
		});
	}
}

function acctSaveNewCard()
{
	var name = $('#pay_new_cardname').val();
	var type = $('#pay_new_cardtype').val();
	var num = $('#pay_new_cardnum').val();
	var cvv = $('#pay_new_cvvnum').val();
	var month = $('#pay_new_expmonth').val();
	var year = $('#pay_new_expyear').val();
	
	$.ajax({
		type: "POST",
		url: "/framework/ajax/account_ajax.php",
		cache: false,
		dataType: 'json',
		data: {
			"action": "savenewcard", 
			"cardname": name,
			"cardtype": type,
			"cardnum": num,
			"cvvnum": cvv,
			"expmonth": month,
			"expyear": year
		},
		success: function(data) {
			if (data.Error != "")
			{
				$('#signin-error').html(data.Error);
			}
			else
			{
				$('#signin-error').html("");
				location.reload();
			}
		}
	});
}

function acctAddNewCard()
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/account_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			"action": "newcard"
		},
		success: function(data) {
			popWindow({
				content: data.Body,
				width: 600,
				modal: false,
				onpop: function() {
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
				}
			});
		}
	});
}

function acctChangeAvatar()
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/account_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			action: "changeavatar" 
		},
		success: function(data) {
			popWindow({
				content: data.Body,
				width: 600,
				modal: false
			});
		}
	});
}
