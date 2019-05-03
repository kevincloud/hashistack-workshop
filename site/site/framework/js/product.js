// JavaScript Document

	
$(document).ready(function (){

	$('#description-tab').click(function() {
		
		$('#description-tab').addClass('selected');
		$('#description-content').addClass('selected');
		$('#excerpt-tab').removeClass('selected');
		$('#excerpt-content').removeClass('selected');
		$('#reviews-tab').removeClass('selected');
		$('#reviews-content').removeClass('selected');
		$('#video-tab').removeClass('selected');
		$('#video-content').removeClass('selected');
		
	});
	
	$('#excerpt-tab').click(function() {
		
		$('#description-tab').removeClass('selected');
		$('#description-content').removeClass('selected');
		$('#excerpt-tab').addClass('selected');
		$('#excerpt-content').addClass('selected');
		$('#reviews-tab').removeClass('selected');
		$('#reviews-content').removeClass('selected');
		$('#video-tab').removeClass('selected');
		$('#video-content').removeClass('selected');

		setTimeout(function() {
			var isbn = $('#canvasisbn').val();
			var viewer = new google.books.DefaultViewer(document.getElementById('viewerCanvas'));
			viewer.load('ISBN:'+isbn, gb_notfound);
		}, 250);
	});
	
	$('#reviews-tab').click(function() {
		
		$('#description-tab').removeClass('selected');
		$('#description-content').removeClass('selected');
		$('#excerpt-tab').removeClass('selected');
		$('#excerpt-content').removeClass('selected');
		$('#reviews-tab').addClass('selected');
		$('#reviews-content').addClass('selected');
		$('#video-tab').removeClass('selected');
		$('#video-content').removeClass('selected');
		
	});
	
	$('#video-tab').click(function() {
		
		$('#description-tab').removeClass('selected');
		$('#description-content').removeClass('selected');
		$('#excerpt-tab').removeClass('selected');
		$('#excerpt-content').removeClass('selected');
		$('#reviews-tab').removeClass('selected');
		$('#reviews-content').removeClass('selected');
		$('#video-tab').addClass('selected');
		$('#video-content').addClass('selected');
		
	});
	
	/*
	setTimeout(function() {
		var x = $('#__GBS_Button0').html();
		
		if (x == '')
			$('#excerpt-content').html('<div style="text-align:left;">Sorry, no excerpt is available for this book.</div>');
	}, 250);
	*/
});



function gb_notfound()
{
	$('#excerpt-content').html('<div style=\"text-align:left;\">Sorry, no excerpt is available for this item.</div>');
}

function gb_initialize() {
}

function itmReviewSubmit(pid)
{
	var name = $('#review_name').val();
	var rating = $('#review_rating').val();
	var summary = $('#review_summary').val();
	var text = $('#review_text').val();
	var words = text.split(' ');
	var msg = '';
	
	words.remove('');
	
	$('#review_error').html('');
	
	if (text == '' || words.length < 25)
		msg = 'Please enter at least 25 words for your review. You only have '+words.length+'.';
	
	if (summary == '')
		msg = 'Please enter a summary.';
	
	if (rating < 1)
		msg = 'You must select a rating for your review.';
	
	if (name.length < 5)
		msg = 'Please enter at least 5 characters for your display name.';
	
	if (msg != '')
	{
		$('#review_error').html(msg);
		return;
	}

	$.ajax({
		type: "POST",
		url: "/framework/ajax/product_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			'action': "savereview", 
			'pid': pid,
			'name': name,
			'rating': rating,
			'summary': summary,
			'text': text
		},
		success: function(data) {
			if (data.Error != '')
			{
				$('#review_error').html(data.Error);
			}
			else
			{
				unpopWindow();
				setTimeout(function() {
					popWindow({
						content: data.Body,
						width: 600,
						modal: false
					});
				}, 500);
			}
		}
	});
}

function itmReviewProduct(pid)
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/product_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			'action': "writereview", 
			'pid': pid
		},
		success: function(data) {
			popWindow({
				content: data.Body,
				width: 600,
				modal: true,
				onpop: function () {
					$('#review_stars').mouseleave(function() {
						var id = Number($('#review_rating').val());
						var i;
						
						if (id > 0)
						{
							for (i = 1; i <= id; i++)
							{
								$('#star_'+i).attr('src', '/framework/img/star-full.png');
							}
							if (id < 5)
							{
								for (i = id + 1; i <= 5; i++)
								{
									$('#star_'+i).attr('src', '/framework/img/star-empty.png');
								}
							}
						}
						else
						{
							for (i = 1; i <= 5; i++)
							{
								$('#star_'+i).attr('src', '/framework/img/star-empty.png');
							}
						}
					});
					
					$('#review_stars > img').each(function(index) {
						$(this).mouseover(function() {
							var id = Number($(this).attr('id').replace('star_', ''));
							var i;
							
							for (i = id; i >= 1; i--)
							{
								$('#star_'+i).attr('src', '/framework/img/star-full.png');
							}
							
							if (id < 5)
							{
								for (i = id + 1; i <= 5; i++)
								{
									$('#star_'+i).attr('src', '/framework/img/star-empty.png');
								}
							}
						});
						$(this).click(function() {
							var id = Number($(this).attr('id').replace('star_', ''));
							
							$('#review_rating').val(id);
							$('#star_text').html(' ('+id+' star'+(id == 1 ? '' : 's')+')');
						});
					});
				}
			});
		}
	});
}




