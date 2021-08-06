
var AjaxLink = new Class({
    
    initialize: function(element) {
	element = $(element);
	if (!element || element.hasClass('ajaxlink_init')) {
	    return this;
	}
	element.addClass('ajaxlink_init');
	var load = element.getElements('.ajaxlink_load').getLast();
	var content = element.getElements('.ajaxlink_content').getLast();
	var help = element.getElements('.ajaxlink_help').getLast();
	if (!load  || !content) {
	    return;
	}
	var url = load.get('href');
	var filter = '';
	var pos = url.indexOf('?');
	if (pos > 0) {
	    var query  = new Hash(url.substring(pos + 1).parseQueryString());
	    if (query.has('AJAXLINK_FILTER')) {
		filter = query.get('AJAXLINK_FILTER');
		query.erase('AJAXLINK_FILTER');
		url  =  url.substring(0,pos ) + '?' + query.toQueryString(); 
	    }
	} 

	content.setStyle('display','none');
	var contentSlider = new Fx.Slide(content);
	var helpSlider;
	if (help) {
	    helpSlider = new Fx.Slide(help);
	    helpSlider.slideIn();
	}

	contentSlider.slideOut();
	content.setStyle('display','block');
	content.addClass('ajaxlink_empty');
	load.addEvent(
	    'click',
	    function() { 
		if (content.hasClass('ajaxlink_empty')) {
		    content.removeClass('ajaxlink_empty');
		    if (helpSlider) {
			helpSlider.slideOut();
		    }		    
		    content.addClass('ajaxlink_loading');
		    new Request.HTML(
 			{
			    url: url,
			    onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				content.empty();
				content.removeClass('ajaxlink_loading');		    
				var ajax_content = this.response.elements;
				if (filter) {
				    ajax_content = ajax_content.filter(filter);
				}
				if (!ajax_content) {
				    content.addClass('ajaxlink_error');		    
				    content.addClass('alert-block');		    
				    return false;
				}		
				content.addClass('ajaxlink_loaded');		    
				ajax_content.inject(content);
				contentSlider.slideIn();				
				
			    }
			    
			}			
		    ).get();

		}else  if (content.hasClass('ajaxlink_loading')) {
		    //do nothing
		} else { //it is loaded or there was an error
		    content.removeClass('ajaxlink_loading');		    
		    content.addClass('ajaxlink_empty');
		    contentSlider.slideOut();
		    content.empty();
		    if (helpSlider) {
			helpSlider.slideIn();
		    }
		}
		return false;
	    }
	);

    
	

	return this;
    }
});


var ClickSlider = new Class({
    
    initialize: function(element) {
	element = $(element);
	if (!element || element.hasClass('clickslider_init')) {
	    return this;
	}
	element.addClass('clickslider_init');
	var click = element.getElements('.clickslider_click').getLast();
	var content = element.getElements('.clickslider_content');
	var contentSliders = [];
	content.each(function(el) {contentSliders.push(new Fx.Slide(el));});
	click.addEvent(
	    'click',
	    function() {
		alert('click');
		contentSliders.each(function(contentSlider) {
		    contentSlider.toggle();
		});
	    }
	);
	return this;
    }
});

var SliderMenu = new Class({
    
    initialize: function(element) {
	element = $(element);
	if (!element) {
	    return this;
	}
	var menu = false;
	var slide = false;
	element.getChildren().each(function(el){
	    if(el.hasClass('slider-menu')){
	    	menu = el;
	    } else if (el.hasClass('slider-slide')){
		slide = el;
	    }
	});
	if (!menu || !slide ) {
	    return;
	}
	var menuSlider = new Fx.Slide(menu);
	menuSlider.slideOut();
	slide.addEvent('mouseleave',function() { menuSlider.slideOut();});
	slide.addEvent('mouseenter',function() { menuSlider.slideIn();});
	return this;
    }
});
