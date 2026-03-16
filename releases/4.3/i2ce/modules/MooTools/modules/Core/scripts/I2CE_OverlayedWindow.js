var I2CE_OverlayedWindow = new Class({
    Extends: I2CE_Window,
    overlay: null,
    load: null,
    options: {
	overlayId: 'I2CE_Overlay',
	overlayLoadingImage: 'index.php/file/loading.gif',
	overlayOpacity: 0.6,
	overlayClass:  'overlayedWindow'
    },

    
    show: function() {
	this.addOverlay();
	if (this.overlay.get('opacity') != this.options.overlayOpacity) {
	    this.appear(this.overlay,'short',this.options.overlayOpacity);
	}
	if (this.load) {
	    this.repositionLoad();
	    this.appear(this.load);
	}
	this.parent();
	if (this.load) {
	    this.disappear(this.load);
	}
	return true;
    },

    hide:function() {
	window.removeEvent('scroll',this.repositionEvent);
	window.removeEvent('resize',this.resizeEvent);
	document.removeEvent('keyup',this.keyUp);
	this.parent();
	if (this.load) {
	    this.disappear(this.load);
	}
	if (this.overlay) {
	    this.disappear(this.overlay,'short');
	}
	return true;
    },


    repositionEvent: function(ev) {
	this.parent(ev);
        this.positionLoad();
    },


    resizeEvent:function(ev) {
	parent.resizeEvent(ev);
	this.sizeOverlay();
	this.positionLoad();
    },
    
    

    sizeOverlay: function() {
	if (!this.overlay) {
	    return;
	}
	this.overlay.setStyles({
	    "height": window.getScrollSize().y + 'px',
	    "width": window.getScrollSize().x + 'px'
	});	
    },
   



    positionLoad:function() {
	if (!this.load) {
	    return;
	}
	this.load.setStyles({
            left: (window.getScrollLeft() + (window.getWidth() - 56) / 2) + 'px',
            top: (window.getScrollTop() + ((window.getHeight() - 20) / 2)) + 'px'
        });
    },
    


    createElem: function(id,   zindex , htmlclass, styles,  content,parent) {
	if ($type(parent) != 'element') {
	    parent  = document.body;
	}	
	var elem = parent.getElementById(id);
	if (elem) {
	    return elem;
	}
	elem = new Element('div').set('id', id);
	if (content) {
	    elem.set('html', content);
	}
	elem.setOpacity(0).setStyle('display','none').setStyle('visibility','hidden');
	elem.set('z-index', this.options.windowZIndex + zindex);
	if (htmlclass) {
	    elem.addClass(htmlclass);
	}
	if (styles) {
	    $each(styles,function(val,style) {
		if (val!== false) {
		    elem.setStyle(style,val);
		}
	    });
	}
	elem.inject(parent);
	return elem;
    },




    addOverlay: function() {
	if (!this.overlay) {
	    this.overlay = this.createElem(
		this.options.overlayId,
		0,
		this.options.overlayClass,
		{'position':'absolute',
		 'top':'0px',
		 'left':'0px'});
	    this.sizeOverlay();
	    this.overlay.addEvent('click', this.hide);
	}
	if (this.options.overlayLoadingImage) {
	    if (!this.load) {
		this.load = this.createElem(
		    this.options.overlayId + '_loader',
		    1,
		    false, 
		    {'position':'absolute'}, 
		    "<img src='" + this.options.overlayLoadingImage + "' />",
		    this.overlay);	
		this.positionLoad();
	    }
	}	
	return true;
    },

	

    


    appear:function(element,duration,to) {
	if (!$type(duration)) {
	    duration = 0;
	}
	if (!$type(to)) {
	    to = 1;
	}
	if (!$type(element) == 'elment') {
	    return;
	}
	if (element.getOpacity() == to) {
	    return;
	}
	if (duration == 0) {
	    element.setOpacity(to);
	    element.setStyle('display','block');
	    element.setStyle('visibility','visible');
	} else {
	    element.setStyle('display','block');
	    element.setStyle('visibility','visible');
	    new Fx.Tween(element,{
		property: 'opacity',
		duration: duration	    
	    }).start(to);
	}
	
    },


    

    disappear:function(element,duration) {
	if (!$type(duration)) {
	    duration = 0;
	}
	if ($type(element) == 'element') {
	    return;
	}
	if (element.getProperty('display') === 'none') {
	    return;
	}
	if (element.getOpacity() == 0 ) {
	    return;
	}
	if (duration == 0) {
	    element.setStyle('display','none');
	    element.setStyle('visibility','hidden');
	    element.setOpacity(0);
	} else {
	    new Fx.Tween(element,{
	    property: 'opacity',
		duration: duration
	    }).start(0).chain(
		function() { 
		    element.setStyle('display','none');
		    element.setStyle('visibility','hidden');
		}
	    );
	}
    }



});

    