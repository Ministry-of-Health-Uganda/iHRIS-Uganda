
var I2CE_Window = new Class({
    Implements: [Options,Events],
    window: null,
    drag: null,
    options: {
	windowShow: false, //a function to call on the  window when you want to show it.  if false, it will set the display style to block.
	//the function should take one argument, the window element
	windowHide: false,  //a function to call on the  window when you want to show it.  if false, it will set the display style to none
	//the function should take one argument, the window element
	windowCheck: false,  //a function to call on The  window when see if it is 'hidden' or 'shown'  if false, it will checkthe display style state
	//to work with the default (false) windowHide and windowShow
	//the function should take one argument, the window wlement. It should also return true (if 'visible') or false (if not 'visible')
	windowDraggable: false, //make the  window draggable.  only applies if windowHide/Show/Check are false
	windowDragButtonClass: 'window_drag', //the class of an element to idenfity a dragging handle for a floating draggable window.  Defaults to 'window_drag'. only matches elements contained within the window
	windowDragContainer: false, //the container to limit dragging to. Defaults to false meaning there is no containment
	windowFloats: false, //make the  window a floater (if no function is specifed)  otherwise affects the style.visible propert.
	//if false, then the  window is not draggable.  You can also make a window not floatable by setting the class not_floats
	windowShowClass: false, //defaults to false.  if set to a string, it is the class we set a  window to if the window is not a floater  nor a function
	//when we show the class.  To set the class, both windowShowClass and windowHideClass need to be strings (possibly empty)
	windowHideClass: false, //defaults to false.  if set to a string, it is the class we set window to if the window is not a floater nor a function
	windowPositionVert: 'upper_viewable', //where to place the  windowmake the  window vertically.  only applies if windowShow is false
	//valid options  'upper_viewable' (defaul) , 'lower_viewable', 'center_viewable', 'upper', 'lower' 'center' 'mouse_above' 'mouse_below'
	//or none of these which means we dont do anything 
	windowPositionHoriz: 'center_viewable', //where to place the  windowmake the  window vertically.  only applies if windowShow is false
	//valid options  'center_viewable' (default), 'left_viewable', 'right_viewable' , 'left', 'right' 'center' or none of these which means we dont do anything 
	windowZIndex: 50000, //the default z-index set for a floating window
	windowRepositions: false, //set to true to reposition a floating  window on a scroll/pgup event
	windowRepositionMorphDuration: 50,
	windowLeftPad: 10,
	windowRightPad: 10,
	windowTopPad: 10,
	windowBottomPad: 10,
	//windowHideOnKeys: [],  //For exmaple, 27=escape, 18=CTRL-W
	windowHideOnKeys: [], //Defaults to []  //For exmaple, 27=escape, 18=CTRL-W
	windowHideButtonClass: 'window_hide' //applies to floating windows and defaults to 'window_hide'.  
	//If set, it is a class we check within the window element to attach a hide on click event
    },

   

   
    getElement: function() {
	return this.window;
    },


    initialize: function(element,options ) {
	this.window = document.id(element);
	if (!this.window) {
	    return;
	}
	this.setOptions(options);	
	if (this.window.loadClassValues) {
	    this.window.loadClassValues(this.options);
	}

	this.keyUpEvent = this.keyUpEvent.bindWithEvent(this);
	this.hideEvent = this.hideEvent.bindWithEvent(this);
	this.showEvent = this.showEvent.bindWithEvent(this);
	this.scrollEvent = this.scrollEvent.bindWithEvent(this);
	this.resizeEvent = this.resizeEvent.bindWithEvent(this);
	if (this.options.windowHideButtonClass) {
	    //var elements = $$(this.window.getElementsByClassName( this.options.windowHideButtonClass)).each(
	    var elements = $$(getElementsByClassName( this.options.windowHideButtonClass, null, this.window )).each(
		function(button) {
		    button.addEvent('click',this.hideEvent);	
		},this);
	}
	if (this.options.windowHideOnKeys) {
	    if ($type(this.options.windowHideOnKeys) == 'string') {
		this.options.windowHideOnKeys = this.options.windowHideOnKeys.split(/,\s*/);
	    }
	    if ($type(this.options.windowHideOnKeys) == 'array' && this.options.windowHideOnKeys.length > 0) {
		document.addEvent('keyup',this.keyUpEvent);
	    }
	}
	if (this.floats()) {
	    if (this.options.windowRepositions) {
		window.addEvent('scroll',this.scrollEvent);
		window.addEvent('resize',this.resizeEvent);
	    }
	}
	if (this.draggable()) {
	    var dragOptions;
	    if (this.options.windowDragButtonClass) {
		//var handle  = $$(this.window.getElementsByClassName( this.options.windowDragButtonClass));
		var handle  = getElementsByClassName( this.options.windowDragButtonClass, null, this.window );
		if (handle) {
		    dragOptions = {
			container: this.options.windowDragContainer,
			handle: handle
		    };
		    this.drag = new Drag.Move(this.window, dragOptions);
		}
	    } else {
		dragOptions = {
		    container: this.options.windowDragContainer
		};
		this.drag = new Drag.Move(this.window, dragOptions);
	    }
	}
    },
    


    draggable: function() {
	if (this.floats() !== true) {
	    return false;
	}
	return this.options.windowDraggable;
    },
    

    floats: function() {
	if (!this.window) {
	    return null;
	}
	if ($type(this.options.windowShow) == 'function') {
	    return false;
	} 
	return this.options.windowFloats;
    },




    scrollEvent: function(ev) {
	var styles = this.getDesiredPosition(ev);
	this.window.set('morph', { duration: this.options.windowRepositionMorphDuration});
	this.window.morph(styles);
	return false;
    },


    resizeEvent: function(ev) {
	var styles = this.getDesiredPosition(ev);
	this.window.set('morph', { duration: this.options.windowRepositionMorphDuration});
	this.window.morph(styles);
	return false;
    },



    keyUpEvent: function(event) {
	if ($type(this.options.windowHideOnKeys) != 'array') {
	    return false;
	}
	if (!this.options.windowHideOnKeys.contains(event.code)) {
	    return false;
	}
	event = new Event(event);
	event.stop();
	this.hide();
	return false;
    },


    hideEvent:function(ev) {
	ev = new Event(ev);
	ev.stop();
	this.hide();
	return false;
    },


    showEvent:function(ev) {
	ev = new Event(ev);
	ev.stop();
	this.show();
	return false;
    },

    

    
    show: function() {
	if (!this.window) {
	    return null;
	}
	if (this.isVisible() === true) {
	    return true;
	}
	if ($type(this.options.windowShow) == 'function') {
	    this.windowShow(this.window);
	} else if (this.floats()) {	    
	    var styles = {};
	    if (this.window.getStyle('display') == 'none') {
		styles['display'] = 'block';
		styles['position'] = 'absolute';	    
		styles['visibility'] = 'hidden';
		this.window.setStyles(styles);
	    }
	    styles = this.getDesiredPosition();
	    styles['position'] = 'absolute';	    
	    styles['display'] = 'block';
	    styles['visibility'] = 'visible';
	    styles['z-index']=  this.options.windowZIndex;
	    this.window.setStyles(styles);
	} else {
	    var hide_class = false;
	    var show_class = false;
	    if ($type(this.options.windowShowClass) == 'string') {
		show_class = this.options.windowShowClass;
	    } 
	    if ($type(this.options.windowHideClass) == 'string') {
		hide_class = this.options.windowHideClass;
	    } 
	    if (hide_class !== false && show_class !== false) {
		this.window.removeClass(hide_class);
		this.window.addClass(show_class);
	    } else {		
		//try to guess a reasonable display style based on the element's tagname based on http://www.w3.org/TR/CSS21/visuren.html#propdef-display
		var style;
		switch (this.window.get('tag')) {
		case 'li':
		    style='list-item';
		    break;
		case 'thead':
		    style='table-header-group';
		    break;
		case 'col':
		    style= 'table-column';
		    break;
		case 'colgroup':
		    style= 'table-column-group';
		    break;
		case 'tfoot':
		    style='table-footer-group';
		    break;
		case 'caption':
		    style='table-caption';
		    break;
		case 'tr':
		    style='table-row';
		    break;
		case 'td':
		case 'th':
		    style='table-cell';
		    break;
		case 'table':
		    style='table-cell';
		    break;
		default:
		    style = 'inline';
		    break;
		}
		this.window.setStyles({'display':style,'visibility':'visible'});
	    }
	}	
	this.fireEvent('windowShow',this.window);
	return true;
    },


			    
    getDesiredPosition: function(event) {	
	var width  = this.window.getSize().x;
	var height = this.window.getSize().y;
	var left  = this.window.getPosition().x;
	var top = this.window.getPosition().y;
	switch (this.options.windowPositionVert) {
	case 'mouse_above':
	    if (event) {
		event = new Event(event);		
		top = event.page.y -height - this.options.windowTopPad;
	    } else {
		top = window.getScroll().y  + this.options.windowTopPad;
	    }
	    break;
	case 'mouse_below':
	    if (event) {
		event = new Event(event);		
		top = event.page.y + this.options.windowBottomPad;
	    } else {
		top = window.getScroll().y  + this.options.windowBottomPad;
	    }
	    break;
	case 'upper_viewable':
	    top = window.getScroll().y + this.options.windowTopPad;
	    break;
	case 'upper':
	    top = this.options.windowTopPad;
	    break;
	case 'lower_viewable':
	    top = window.getScroll().y + window.getScrollSize().y - this.options.windowBottomPad;
	    break;
	case 'lower':
	    top = window.getSize().y - this.options.windowBottomPad - height;
	    break;
	case 'center_viewable':
	    top = window.getScroll().y + (((window.getScrollSize().y - height)/2) | 0) ;  //the bitwise or with zero will cast the float back to a int
	    break;
	case 'center':
	    top = (((window.getSize().y - height)/2) | 0);
	    break;
	default:
	    //do nothing;
	    break;
	}
	switch (this.options.windowPositionHoriz) {
	case 'center':
	    left = (((window.getSize().x - width)/2)|0);
	    break;
	case 'center_viewable':
	    left = window.getScroll().x + (((window.getScrollSize().x - width)/2)|0);  //the bitwise or with zero will cast the float back to a int
	    break;
	case 'left':
	    left = this.options.windowLeftPad;
	    break;
	case 'left_viewable':
	    left = this.options.windowLeftPad + window.getScroll().x;
	    break;
	case 'right':
	    right = window.getSize().x - width - this.options.windowRightPad;
	    break;
	case 'right_viewable':
	    right = window.getScroll().x + window.getScrollSize().x - width  - this.options.windowRightPad;
	    break;
	default:
	    //do nothing
	    break;
	}
	return {'left':left , 'top' : top, 'visibility':'visible'};
    },


    hide: function() {
	if (!this.window) {
	    return null;
	}
	if (this.isVisible() === false ) {
	    return true;
	}
	if ($type(this.options.windowHide) == 'function') {
	    this.options.windowHide(this.window);
	} else {
	    var hide_class = false;
	    var show_class = false;
	    if ($type(this.options.windowShowClass) == 'string') {
		show_class = this.options.windowShowClass;
	    } 
	    if ($type(this.options.windowHideClass) == 'string') {
		hide_class = this.options.windowHideClass;
	    } 
	    if (hide_class !== false && show_class !== false) {
		this.window.removeClass(show_class);
		this.window.addClass(hide_class);
	    } else {		    
		this.window.style.display = "none";
	    }
	}
	this.fireEvent('windowHide',this.window);
	return true;
    },
    
    isVisible: function() {
	if (!this.window) {
	    return null;
	}
	if ($type(this.options.windowCheck) == 'function') {
	    return this.options.windowCheck(this.window);
	} else {
	    if ($type(this.options.windowShowClass) == 'string') {
		return this.window.hasClass(this.options.windowShowClass);
	    } else {
		return  !(this.window.style.display === "none" || 
			  ( $type(this.window.style.display)==='string' && this.window.style.display.length === 0));
	    }
	}
    }


});








