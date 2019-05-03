// remap jQuery to $
(function($){})(window.jQuery);


/* trigger when page is ready */
$(document).ready(function (){

	/* Default Text for Input Fields */
		
	 $(".defaultText").focus(function(srcc)
	{
	    if ($(this).val() == $(this)[0].title)
	    {
	        $(this).removeClass("defaultTextActive");
	        $(this).val("");
	    }
	});
	
	$(".defaultText").blur(function()
	{
	    if ($(this).val() == "")
	    {
	        $(this).addClass("defaultTextActive");
	        $(this).val($(this)[0].title);
	    }
	});
	
	$(".defaultText").blur(); 
	
	/* End Input Field Thingy */
	
});


$(window).load(function() {
  var txts = document.getElementsByTagName('TEXTAREA') 

  for(var i = 0, l = txts.length; i < l; i++) {
    if(/^[0-9]+$/.test(txts[i].getAttribute("maxlength"))) { 
      var func = function() { 
        var len = parseInt(this.getAttribute("maxlength"), 10); 

        if(this.value.length > len) { 
          this.value = this.value.substr(0, len); 
          return false; 
        } 
      }

      txts[i].onkeyup = func;
      txts[i].onblur = func;
    } 
  } 	
});

/* optional triggers

$(window).resize(function() {
	
});

*/

jQuery.fn.onEnter = function(callback)
{
    this.keyup(function(e)
        {
            if(e.keyCode == 13)
            {
                e.preventDefault();
                if (typeof callback == 'function')
                    callback.apply(this);
            }
        }
    );
    return this;
}


//////////////////////////////////////////////////////
// CUSTOM FUNCTIONS
//////////////////////////////////////////////////////

function gMessageBox(title, message)
{
	$.ajax({
		type: "POST",
		url: "/framework/ajax/site_ajax.php",
		cache: false,
		dataType: 'json',
		data: { 
			"action": "messagebox", 
			"title": title,
			"message": message
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

function ccNumbersOnly(myfield, e, xtra)
{
	var key;
	var keychar;
	var charlist = "0123456789";
	
	if (xtra)
		charlist += xtra;
	
	if (window.event)
		key = window.event.keyCode;
	else if (e)
		key = e.which;
	else

	return true;
	keychar = String.fromCharCode(key);
	
	// control keys
	if ((key==null) || (key==0) || (key==8) || (key==9) || (key==13) || (key==27) )
	return true;
	// numbers
	else if (((charlist).indexOf(keychar) > -1))
		return true;
	else
		return false;
}



//////////////////////////////////////////////////////
// STRING FUNCTIONS
//////////////////////////////////////////////////////

function toTitleCase(e,p){if (!p) e=e.toLowerCase();var t=/^(a|an|and|as|at|but|by|en|for|if|in|is|of|on|or|the|to|vs?\.?|via)$/i;return e.replace(/([^\W_]+[^\s-]*) */g,function(e,n,r,i){return r>0&&r+n.length!==i.length&&n.search(t)>-1&&i.charAt(r-2)!==":"&&i.charAt(r-1).search(/[^\s-]/)<0?e.toLowerCase():n.substr(1).search(/[A-Z]|\../)>-1?e:e.charAt(0).toUpperCase()+e.substr(1)})};

function cleanPaste(e)
{
	setTimeout(function(){
		s = $(e).val();
		// smart single quotes and apostrophe
		s = s.replace(/[\u2018|\u2019|\u201A]/g, "\'");
		// smart double quotes
		s = s.replace(/[\u201C|\u201D|\u201E]/g, "\"");
		// ellipsis
		s = s.replace(/\u2026/g, "...");
		// dashes
		s = s.replace(/[\u2013|\u2014]/g, "--");
		// circumflex
		s = s.replace(/\u02C6/g, "^");
		// open angle bracket
		s = s.replace(/\u2039/g, "<");
		// close angle bracket
		s = s.replace(/\u203A/g, ">");
		// spaces
		s = s.replace(/[\u02DC|\u00A0]/g, " ");
		
		$(e).val(s);
		
	}, 0);
}

Array.prototype.remove = function() {
    var what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

//////////////////////////////////////////////////////
// COLOR FUNCTIONS
//////////////////////////////////////////////////////

function d2h(d) {return d.toString(16);}
function h2d(h) {return parseInt(h,16);}

function darkenColor(color, percent) {

	color = color.replace("#", "");

	var red = h2d(color.substring(0,2));
	var green = h2d(color.substring(2,4));
	var blue = h2d(color.substring(4,6));

	red = d2h(parseInt(red - ((red / 100) * percent)));
	green = d2h(parseInt(green - ((green / 100) * percent)));
	blue = d2h(parseInt(blue - ((blue / 100) * percent)));

	if (red.length < 2) red = "0" + red;
	if (green.length < 2) green = "0" + green;
	if (blue.length < 2) blue = "0" + blue;

	newcolor = "#" + red + green + blue;

	if (newcolor.length == 7) {
		return newcolor;
	} else {
	    alert("error in color darken: " + newcolor);
	}
}

function lightenColor(color, percent) {

	color = color.replace("#", "");

	var red = h2d(color.substring(0,2));
	var green = h2d(color.substring(2,4));
	var blue = h2d(color.substring(4,6));

	red = d2h(parseInt(red + ((red / 100) * percent)));
	green = d2h(parseInt(green + ((green / 100) * percent)));
	blue = d2h(parseInt(blue + ((blue / 100) * percent)));

	if (red.length < 2) red = "0" + red;
	if (green.length < 2) green = "0" + green;
	if (blue.length < 2) blue = "0" + blue;

	newcolor = "#" + red + green + blue;

	if (newcolor.length == 7) {
		return newcolor;
	} else {
	    alert("error in color lighten: " + newcolor);
	}
}

//////////////////////////////////////////////////////
// IMAGE FUNCTIONS
//////////////////////////////////////////////////////

function fitWidth(oItem, oContainer, offset) {

		var c_width = oContainer.outerWidth() - offset;
		var c_height = oContainer.outerHeight() - offset;

		var i_width = oItem.outerWidth();
		var i_height = oItem.outerHeight();
		
		var i_ratio = i_width / i_height;
		var c_ratio = c_width / c_height;
		
		var newwidth = (i_ratio > c_ratio ? c_width : c_height * i_ratio);

		return newwidth;

}
		
//////////////////////////////////////////////////////
// MODAL WINDOW
//////////////////////////////////////////////////////

function popWindow(options) {

	// create default options
	var defaults = {
		selectID: 'popwindow',
		content: '',
		width: 400,
		maximize: false,
		title: '',
		overlay: true,
		overlaycolor: '#FFF',
		modal: false,
		className: '',
		onpop: function() {  },
		unpop: function() {  }
	}
	
	// merge provided options with default options
	var options = $.extend({}, defaults, options);
	
	// construct the popwindow (with display:none)
	html =	'<div id="' + options.selectID + '" class="popwindow ' + options.className + '" style="position:fixed; z-index:10001; width:' + options.width + 'px; display:none;">';
	if (options.title != '') html +=	'<div class="poptitle"><div onclick="unpopWindow();" class="popclose">&#10006;</div>' + options.title + '</div>';
	html += '<div class="popcontent">' + options.content + '</div>';
	html +=	'</div>';
	
	// apply the background overlay
	if (options.overlay) putOverlay({ selectID: options.selectID, overlaycolor: options.overlaycolor, modal: options.modal });

	// now that the id string has been assigned, add the id selector hash for future reference use
	options.selectID = '#' + options.selectID;
	
	// add the popwindow to the DOM
	$('body').append($(html));
	
	// set up the stuff to do once the popup has popped up
	var popped = function () {
		options.onpop();
	}
	
	// bind the unpop function to the popwindow for later use
	$(options.selectID).bind('unpop', options.unpop);
	
	// set a zero timeout to stop things getting ahead of themselves
	setTimeout( function() {
	
		// get document dimensions
		var docx = $(document).width();
		var docy = $(document).height();
		
		// get window (viewport) dimensions
		var winx = $(window).width();
		var winy = $(window).height();
		
		// determine the center of the viewport
		var xcenter = (docx / 2);
		var ycenter = (winy / 2);
	
		if (options.maximize) {
			
			$(options.selectID).css('width', '');
			$(options.selectID).children('.popcontent').css('padding', '0');
			
			var tmpwidth = $(options.selectID).width();
						
			var newwidth = fitWidth($(options.selectID), $(window), 150);
			
			if (newwidth > tmpwidth) newwidth = tmpwidth;
			
			$(options.selectID).css('width', newwidth);

		}

		// get the popwindow dimensions
		var elx = $(options.selectID).outerWidth();
		var ely = $(options.selectID).outerHeight();
		
		// calculate the center position to place the popwindow
		var offsetx = xcenter - (elx / 2);
		var offsety = ycenter - (ely / 2);
		
		// adjust position of popwindow to center
		$(options.selectID).css('top', offsety).css('left', offsetx);

		// fade in the popwindow
		$(options.selectID).fadeIn('fast', popped);
	
	}, 0);
	
}

function putOverlay(options) {
	
	// create the default options
	var defaults = {
		selectID: 'popwindow',
		overlaycolor: '#FFF',
		modal: false
	}
	
	// merge provided options with default options
	var options = $.extend({}, defaults, options);
	
	// get the document dimensions
	var x = $(document).width();
	var y = $(document).height();
	
	// hide any embedded objects
	$('embed').css('visibility', 'hidden');
	
	// construct the overlay div
	overlay = $('<div id="overlay_' + options.selectID + '" style="position:absolute; z-index:10000; top:0; left:0; background:' + options.overlaycolor + '; opacity:0.6; height:' + y + 'px; width:' + x + 'px;"></div>');
	
	// add the overlay div to the DOM
	$('body').append(overlay);
	
	// if not modal then allow the overlay to be clicked to close the window
	if (!options.modal) $('#overlay_' + options.selectID).click(unpopWindow);
	
}

function unpopWindow(options) {
	
	// create the default options
	var defaults = {
		selectID: 'popwindow'
	}

	// merge provided options with default options
	var options = $.extend({}, defaults, options);
	
	// add the id selector hash for future reference use
	var popwindow = '#' + options.selectID;
	var overlay = '#overlay_' + options.selectID;
	
	// trigger the unpop function
	if ($(popwindow)) $(popwindow).trigger('unpop');
	
	// remove the popwindow
	if ($(popwindow)) $(popwindow).remove();
	
	// remove the overlay
	if ($(overlay)) $(overlay).remove();
	
	// show any embedded objects that were previously hidden
	$('embed').css('visibility', 'visible');
	
}

//////////////////////////////////////////////////////
// PURR - simple, custom notification popup
//////////////////////////////////////////////////////

var purrnum = 0;

function purr(ptext, pstyle) {
	
	purrnum += 1;
	if (!pstyle) pstyle = 'display:none; position:fixed; top:0; z-index:999999; background:#000; border:2px solid #FFF; border-top:0; color:#FFF; font-size:12px; font-weight:bold; padding:5px 10px; box-shadow:0px 2px 5px #444;';
	
	// create the notification div
	$('<div id="purrBox' + purrnum + '" style="' + pstyle + '">' + ptext + '</div>').appendTo(document.body);
	var pel = $('#purrBox'+purrnum);
	
	// adjust div to horizontal center based on actual width
	pel.css('left', (($(window).width() / 2) - (pel.width()/ 2)));
	
	// animate in and out, then remove the div from the dom
	pel.slideDown('normal').delay(2000).slideUp('fast', function() { $(this).remove() });
	
}


//////////////////////////////////////////////////////
// END OF CUSTOM FUNCTIONS
//////////////////////////////////////////////////////






