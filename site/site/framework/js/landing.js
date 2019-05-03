// JavaScript Document

	
$(document).ready(function (){
	
});



function itmCloseDescription()
{
	$('#more_info').toggle();
	$('#more_info_data').html('');
}

function itmSetupLabel() {
	if ($('.label_check input').length) {
		$('.label_check').each(function(){ 
			$(this).removeClass('c_on');
		});
		$('.label_check input:checked').each(function(){ 
			$(this).parent('label').addClass('c_on');
		});
		
		$.ajax({
			type: "POST",
			url: "/framework/ajax/landing_ajax.php",
			cache: false,
			dataType: 'json',
			data: { 
				'action': 'cartcount'
			},
			success: function(data) {
				var numcheck = $('.label_check input:checked').length;
				var left = (data.Data.CartCount * 2) - numcheck;
				if (left > 30) left = 30;
				var label = '';
				
				if (numcheck >= (data.Data.CartCount * 2))
				{
					$('.label_check input:not(:checked)').each(function(){ 
						$(this).parent('label').addClass('c_ex');
						var elbox = $(this).parent('label').parent('div');
						elbox.removeClass('label_check_box');
						elbox.addClass('label_check_box_off');
						$(this).attr('disabled', true);
					});
				}
				else
				{
					$('.label_check input:not(:checked)').each(function(){ 
						$(this).parent('label').removeClass('c_ex');
						var elbox = $(this).parent('label').parent('div');
						elbox.removeClass('label_check_box_off');
						elbox.addClass('label_check_box');
						$(this).removeAttr("disabled");
					});
				}
				switch (left)
				{
					case 0:
						label = 'All selections made';
						break;
					case 1:
						label = '1 selection remaining';
						break;
					default:
						label = left+' selections remaining';
						break;
				}
				$('#selections-left').html(label);
			}
		});
	};
};

function itmMoreEInfo(pid)
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/landing_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			'action': 'getdescr',
			'pid': pid
		},
		success: function(data) {
			$('#more_info_data').html(data.Data.Description);
			$('#more_info_title').html('Description - ' + data.Data.Title);
			$('#more_info').toggle();
		}
	});
}

function itmCheckState()
{
	var books = '';
	$('.label_check input:checked').each(function(){ 
		books += ','+$(this).val();
	});
	
	if (books != '') books = books.substr(1);
	
	$.ajax({
		type: "POST",
		url: "/framework/ajax/landing_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			'action': 'checkstate',
			'books': books
		},
		success: function(data) {
			if (data.Data.Incomplete)
			{
				if (!confirm('You still have more FREE titles to select. Are you sure you want to check out now?'))
					return;
			}
			$('#proceed-button').removeClass('green');
			$('#proceed-button').addClass('disabled');
			$('#proceed-button').attr("disabled", true);
			if (data.Data.LoggedIn)
			{
				location.href='/special/cart/billing';
			}
			else
			{
				alert('Sorry, but it appears your session has expired!');
				location.reload();
			}
		}
	});
}

function itmDisplayEbooks(pageid)
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/landing_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			'action': 'ebooks',
			'pageid': pageid
		},
		success: function(data) {
			if (data.Error == 'notitles')
			{
				alert('First, select the titles you\'d like to purchase.');
			}
			else if (data.Error == 'login')
			{
				popWindow({
					content: data.Body,
					width: 800,
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
			else
			{
				popWindow({
					content: data.Body,
					width: 800,
					modal: true
				});
				
				$('.label_check').click(function(){
					itmSetupLabel();
				});
				itmSetupLabel();
			}
		}
	});
}

function itmAddToCart(pid)
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/landing_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			'action': 'addtocart', 
			'pid': pid
		},
		success: function(data) {
			$('#landing-cart-data').html(data.Body);
		}
	});
}

function itmUpdateCart()
{
	var cartdata = 'action=updatecart';
	$('input[id^="cart_item"]').each(function(index) {
		cartdata += '&'+this.id+'='+this.value;
	});
	
	$.ajax({
		type: "POST",
		url: "/framework/ajax/landing_ajax.php",
		cache: false,
		dataType: 'json',
		data: cartdata,
		success: function(data) {
			$('#landing-cart-data').html(data.Body);
			$('<div id="updated-text" style="display:none;">Your cart has been updated.</div>').appendTo($('#updated-notify'));
			var nelem = $('#updated-text');
			nelem.slideDown('normal').delay(2000).slideUp('fast', function() { $(this).remove() });
		}
	});
}

function itmMoreInfo(pid)
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/landing_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			'action': 'moreinfo', 
			'pid': pid
		},
		success: function(data) {
			popWindow({
				content: data.Body,
				width: 600,
				modal: true
			});
		}
	});
}




