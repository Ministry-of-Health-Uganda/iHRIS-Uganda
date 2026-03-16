/**
* © Copyright 2008 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License 
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @access public
*
* based on Smoothbox v20080623 by Boris Popoff (http://gueschla.com) MIT License
* Based on Cody Lindley's Thickbox, MIT License
*/



var MessageBox= new Class({
    Implements: Options,
    options: {
	title: "Information:",
	messageBoxLinkClass : 'MessageBox',
	draggable: true,
	zindex: 50000,
	loadingImage: 'index.php/file/loading.gif',
	elementPrefix: 'MessageBox',
	repositionMorphDuration: 50,
	overlayClass: 'MessageBoxOverlay',
	windowClass: 'MessageBoxWindow',
	windowCloseButtonClass: 'MessageBoxClose',
	windowCaptionClass: 'MessageBoxCaption',
	windowContentClass: 'MessageBoxContent',
	imageMetaClass: false,
	imagePrevClass: 'button',
	imageNextClass: 'button',
	imageContentClass : false,
	window: {width: false, height: false},
	closeText: 'Close',
	failureText: 'Unable to load',
	imagePrevText: 'Back',
	imageNextText: 'More',
	overlayOpacity: 0.6
    },
    drag: false,
    overlay : false,
    load: false,
    caption: false,
    content: false,
    closeWindowButton: false,
    imageControls: false,
    imageMeta: false, 
    imageContent: false, 
    prev: false,
    next:false,
    id:false,

    initialize: function (id, options) {
	this.id = id;
	this.setOptions(options);		
	this.setup = this.setup.bindWithEvent(this);
	this.hideWindow = this.hideWindow.bindWithEvent(this);
	this.showLinkEvent = this.showLinkEvent.bindWithEvent(this);
	this.keyUpEvent = this.keyUpEvent.bindWithEvent(this);
	this.resizeEvent = this.resizeEvent.bindWithEvent(this);
	this.repositionEvent = this.repositionEvent.bindWithEvent(this);
	if (Browser.loaded) {
	    this.setup();
	} else {
	    window.addEvent('domready', this.setup);
	}
    },



    setup: function() {
	if (this.options.messageBoxLinkClass) {
	    $$("#" + this.id + " ." + this.options.messageBoxLinkClass).each(function(link){  this.addLink(link);}, this);
	}
    },



    showLinkEvent: function(event) {
	event.preventDefault();
	if (!event.target) {
	    return false;
	}
	this.show(event.target);
	return false;
    },

    addLink: function(link) {
	if ($type(link) === 'string') {
	    link = document.id(link);
	}
	if (link && $type(link) === 'element') {
	    link.addEvent('click',this.showLinkEvent);
	}
    },



    ensureElem: function(tag,name,  invisible,   options, htmlclass, styles,  content,parent) {
	if (parent == undefined) {
	    parent  = document.body;
	}	
	parent = $(parent);
	var elem = parent.getChildren(this.options.elementPrefix + '_' + name)[0];
	var inject = false;
	if (!elem) {
	    elem = new Element(tag).set('id', this.options.elementPrefix + '_' + name);
	    if (content) {
		elem.set('html', content);
	    }
	    inject = true;
	}
	if (invisible) {
	    elem.setOpacity(0).setStyle('display','none').setStyle('visibility','hidden');
	}
	if (options) {
	    $each(options, function(opt,i) {
		if (opt !== false) {
		    elem.set(i,opt);
		}
	    });
	}
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
	if (inject) {
	    elem.inject(parent);
	}
	return elem;

    },


    ensureOverlays:function() {
	if (!Browser.loaded) {
	    return;
	}
	if (!this.load) {
	    this.load = this.ensureElem('div','Load',true,{'z-index':this.options.zindex + 1},false, {'position':'absolute'}, "<img src='" + this.options.loadingImage + "' />");
	}
	if (!this.overlay) {
	    this.overlay = this.ensureElem(
		'div','Overlay',true, {'z-index':this.options.zindex},this.options.overlayClass,
		{'position':'absolute',
		 'top':'0px',
		 'left':'0px'});
	    this.overlay.addEvent('click', this.hideWindow);
        }
	if (!this.window) {
	    this.window = this.ensureElem(
		'div','Window',true,
		{'z-index':this.options.zindex + 2}, 
		this.options.windowClass, 
		{'width': this.options.window.width, 'height':this.options.height, 'position':'absolute'});
	}
	if (!this.caption) {
	    //ensureElem: function(tag,name,  invisible,   options, htmlclass, styles,  content,parent) {
	    //first see if there is a caption div already defiend
	    var main = $(this.id);
	    if (main) {
		this.caption = main.getElement('.MessageBoxCaption');
	    }
	    if (this.caption) {
		this.caption.setStyle('display','block').setStyle('visibility','visible');
		this.caption.inject(this.window);
	    } else {
		this.caption = this.ensureElem('div','windowTitle',false,{},this.options.windowCaptionClass,{},this.options.title,this.window);
	    }
	    if (this.window && this.options.draggable) {
		this.drag = new Drag.Move(this.window, {handle:this.caption, container:document.body});  
	    }
	}
	if (!this.content) {
	    this.content = this.ensureElem('div','windowContent',false,{},this.options.windowContentClass,{}, false,this.window);
	}
	if (!this.closeWindowButton) {
	    this.closeWindowButton = this.ensureElem(
		'a','closeWindowButton',false,
		{'title':this.optionsCloseText,'href':this.options.closeText,'html':this.options.closeText},
		this.options.windowCloseButtonClass, {} , false, this.window);
	    this.closeWindowButton.addEvent('click',this.hideWindow);
	}
	if (!this.imageControls) {
	    this.imageControls = this.ensureElem('div','imageControls',false,{},false,{},false,this.window);
	}
	if (!this.imageContent) {
	    this.imageContent = this.ensureElem('div','imageContent',false,{},this.options.imageContentClass,{},false,this.imageControls);
	}
	if (!this.imageMeta ) {
	    this.imageMeta = this.ensureElem('div','imageMeta',false,{},this.options.imageMetaClass,{},false,this.imageControls);
	}
	if (!this.prev) {
	    this.prev =  this.ensureElem(
		'a','imagePrev',false,
		{'html':this.options.imagePrevText,'href':this.options.imagePrevText},
		this.options.imagePrevClass,{},false,this.imageControls);
	    this.prev.setStyle('display','inline');
	    this.prev.addEvent('click',function(e) {
		e = new Event(e);
		e.stop();
	    });
	}
	if (!this.next) {
	    this.next =  this.ensureElem(
		'a','imageNext',false,
		{'html':this.options.imageNextText,'href':this.options.imageNextText},
		this.options.imageNextClass,{},false,this.imageControls);
	    this.next.setStyle('display','inline');
	    this.next.addEvent('click',function(e) {
		e = new Event(e);
		e.stop();
	    });
	}

    },


    resizeEvent:function(ev) {
	ev = new Event(ev);
	ev.stop();
	this.resizeOverlay();
	this.reposition();
    },
    

    resizeOverlay: function() {
	if (!this.overlay) {
	    return;
	}
	this.overlay.setStyles({
	    "height": window.getScrollSize().y + 'px',
	    "width": window.getScrollSize().x + 'px'
	});	
    },
   

    repositionEvent: function(ev) {
	if (ev) {
	    ev = new Event(ev);
	    ev.stop();
	}
        this.repositionWindow(true);
        this.repositionLoad();
    },

    repositionWindow: function(update){
	if (!this.window || !this.overlay) {
	    return false;
	}
	if (this.drag && update) {
	    return false;
	}
	this.window.set('morph', {	    
	    duration: this.options.repositionMorphDuration
	});
	this.window.morph({
	    left: (window.getScrollLeft() + (window.getSize().x - this.window.getSize().x)/2) +  'px',
	    top: (window.getScrollTop() + (window.getSize().y - this.window.getSize().y)/2) +  'px'
	});	

	return false;
    },


    repositionLoad:function() {
	if (!this.load) {
	    return false;
	}
	this.load.setStyles({
            left: (window.getScrollLeft() + (window.getWidth() - 56) / 2) + 'px',
            top: (window.getScrollTop() + ((window.getHeight() - 20) / 2)) + 'px'
        });
	return true;
    },
    



    setWindowProperties:function(params) {
	if (!this.window) {
	    return;
	}
	['width','height'].each(function(key) {
	    if (params.has(key)) {
		this.window.set(key,this.params.get(key));
	    } else if (this.options.window[key]) {
		this.window.set(key,this.options.window[key]);
	    }
	}.bind(this));
    },
    

    hideImageControls: function() {
	return;
	if (this.imageControls) {
	    this.disappear(this.imageControls);
	}
	this.showNextImage = false;
	this.showPrevImage = false;
    },
    
    showImageControls: function(target) {
	var rel;
	var url;
	if (!target) {
	    return;

	}
	if (!this.imageControls) {
	    return;
	}
        var prev = false, next = false;
	var foundState = false;
	// find the prev and next images if any
	var elements =	$$("#" + this.id + " ." + this.options.messageBoxLinkClass);
	elements.sort(function(a, b){
	    if (a.id == b.id) return 0;
	    return (a.id > b.id) ? 1 : -1;
	});
	elements.each(function(el){
            if (el === target) {
		foundState = true;
	    } else  if (foundState) {
		if (!next) {
		    next = el;
		}
	    } else {
		prev = el;
	    }
	});           

        this.appear(this.imageControls);
	if (this.imageMeta) {
	    this.imageMeta.set('html','');
	}
	if (this.prev) {
	    this.prev.removeEvents('click');
	    if ( prev) {
		this.showPrevImage = function() {
		    this.showImageControls(prev);
		}.bind(this);
		this.prev.addEvent( 'click',  function(e) { e.stop(); this.showImageControls(prev);}.bind(this));
		this.appear(this.prev,0,1,'inline');
	    } else {
		this.showPrevImage = false;
		this.disappear(this.prev);
	    }
	}
	if (this.next) {
	    this.next.removeEvents('click');
	    if ( next) {
		this.showNextImage = function() {
		    this.showImageControls(next);
		}.bind(this);  
		this.next.addEvent( 'click',  function(e) { e.stop(); this.showImageControls(next);}.bind(this));
		this.appear(this.next,0,1,'inline');
	    } else {
		this.showNextImage = false;
		this.disappear(this.next);
	    }
	}
	if (target.get('tag') == 'a') {
	    url = target.href;
	    target.blur();
	    if (url.length === 0) {
		return;
	    }
	    this.showImage(url);
	} else {
	    this.content.empty();	    
	    target.clone().removeClass(this.options.messageBoxLinkClass).setStyle("display","block").inject(this.content);
	    this.showOverlay(false);
	    this.showWindow();
	}	

    },


    showImage:function (url) {
	if (!url ) {
	    return;
	}
	var imageLoad = function(image) {
	    if ( !this.imageContent || !this.window) {
		return;
	    }
	    var x = window.getScrollWidth() - 150;
	    var y = window.getScrollHeight() - 150;
	    var imageWidth = image.width;
	    var imageHeight = image.height;
	    if (imageWidth > x) {
		imageHeight = imageHeight * (x / imageWidth);
		imageWidth = x;
		if (imageHeight > y) {
		    imageWidth = imageWidth * (y / imageHeight);
		    imageHeight = y;
		}
	    } else {
		if (imageHeight > y) {
		    imageWidth = imageWidth * (y / imageHeight);
		    imageHeight = y;
		    if (imageWidth > x) {
			imageHeight = imageHeight * (x / imageWidth);
			imageWidth = x;
		    }
		}
	    }	    
	    this.imageContent.empty();		    	    
	    this.window.set('morph', {	    
		duration: this.options.repositionMorphDuration
	    });
	    this.window.morph({
		width: imageWidth + 30, 
		height: imageHeight + 60
	    });
	    image.inject(this.imageContent);
	    if (this.load) {
		this.disappear(this.load);
	    }
	}.bind(this);

        new Asset.image(url, {onload:function() { imageLoad(this);}});		    
	
    },
	
    show: function(target) {
	if (Browser.loaded) {
	    this.showWorker(target);
	} else {	    
	    window.addEvent('domready', function() {this.showWorker(target);}.bindWithEvent(this));
	}
    },
    
    showWorker: function (target) {
	target = $(this.id + "_" + target);
	if (!target) 	{
	    return;
	}
	this.ensureOverlays();
	if (!this.window || !this.content) {
	    return;
	}
	//this.hideImageControls();
	var caption;
	var rel;
	var url;
	if (!target) {
	    return;
	}
	if (target.get('tag') == 'a') {
	    caption = target.title || target.name || '';
	    url = target.href;
	    rel = target.rel;
	    if (this.caption) {
		this.caption.set('html',caption);
	    }
	    if (url.length === 0) {
		return;
	    }
	    imageURL = /\.(jpe?g|png|gif|bmp)/gi;	
	    var vars = this.parseQuery(url);

	    var baseURL = url.match(/(.+)?/)[1] || url;
	    var update = false;
	    if (baseURL.match(imageURL)) {
		//image request
		this.showImageControls(target);
		update = this.imageContent;
	    } else {
		//ajax request
		update = this.content;
	    }
	    if (!update) {
		return;
	    }
	    var req = baseURL;
	    if (vars.request.length > 0) {
		req += '?' + vars.request.toQueryString();
	    }	
	    this.showOverlay(true);
	    this.setWindowProperties(req.params);
	    new Request(
		{		    
		    url : req,
		    update: update,
		    evalScripts:false,
		    content: 'text/html',
		    onFailure: function() {
			this.window.set('html',this.options.failureText);
		    }.bind(this),
		    onComplete: function() {
			this.showWindow();
			if (this.load) {
			    this.disppear(this.load,0);
			}
		    }.bind(this)
		}).get();
	} else {
	    this.showOverlay(false);
            this.showWindow();
	    this.showImageControls(target);
	}
    },



	
    parseQuery:function (url){
	var vars = {request: $H({}) , params: $H({})};
	var query = url.match(/\?(.+)/);
	if (!query || $type(query) !== 'array' || query.length <  2) {
	    return vars;
	}
	query = query[1];
	if (!query || $type(query) !== 'string' ) { 
            return vars;
	}
	var pairs = query.match(/^\??(.*)$/)[1].split('&'); 
	pairs.each(function(pair) { 
	    pair = pair.split('=',2); 
	    if (!pair || pair.length != 2) {
		return;
	    }
	    pair[0] = decodeURIComponent(pair[0]); 
	    pair[1] = decodeURIComponent(pair[1]); 
	    if (pair[0].length < (this.options.elementPrefix.length +1 ) || 
		pair[0].substr(0,this.options.elementPrefix.length + 1) !== (this.options.elementPrefix + ':')) {
		vars.request.set(pair[0] ,pair[1]);
	    } else {
		vars.params.set(pair[0].substr(this.options.elementPrefix.length+1) ,pair[1]);
	    }
	}.bind(this));
	return vars;
    },


	
    showOverlay:function (showLoad) {
	this.ensureOverlays();
	this.resizeOverlay();
	window.addEvent('scroll',this.repositionEvent);
	window.addEvent('resize',this.resizeEvent);
	document.addEvent('keyUp',this.keyUpEvent);
	if (this.overlay && this.overlay.get('opacity') != this.options.overlayOpacity) {
	    this.appear(this.overlay,'short',this.options.overlayOpacity);
	}
	if (showLoad && this.load && this.overlay.get('opacity') != 1) {
	    this.repositionLoad();
	    this.appear(this.load);
	}	
    },

    showWindow:function () {
	if (this.window && this.window.get('opacity') != 1 ) {
	    this.appear(this.window,'short');
	}
	this.window.setStyle('z-index', this.options.zindex +2);
	this.load.setStyle('z-index', this.options.zindex +1);
	this.overlay.setStyle('z-index', this.options.zindex );

	this.repositionWindow(false);
    },


    keyUpEvent: function(event) {
	event = new Event(event);
        switch (event.code) {
        case 27:
	    event.stop();
	    this.hideWindow();	    
	    break;
	case 190:
	    if ($type(this.showNextImage) === 'function') {
		event.stop();
		this.showNextImage();
	    }
	    break;
	case 188:
	    if ($type(this.showPrevImage) === 'function') {
		event.stop();
		this.showPrevImage();
	    }
	    break;	    
	default:
	    break;
        }	
    },


    hideWindow:function(ev){	
	if (ev) {
	    ev = new Event(ev);
	    ev.stop();
	}
	window.removeEvent('scroll',this.repositionEvent);
	window.removeEvent('resize',this.resizeEvent);
	document.removeEvent('keyup',this.keyUp);
	if (this.window) {
	    this.disappear(this.window);
	}
	if (this.load) {
	    this.disappear(this.load);
	}
	if (this.overlay) {
	    this.disappear(this.overlay,'short');
	}
	this.window.setStyle('z-index', -this.options.zindex -2);
	this.load.setStyle('z-index', -this.options.zindex -1);
	this.overlay.setStyle('z-index', -this.options.zindex );

    },



    appear:function(element,duration,to,style) {
	if (duration == undefined) {
	    duration = 0;
	}
	if (style == undefined) {
	    style = 'block';
	}
	if (to == undefined) {
	    to = 1;
	}
	var fxappear;
	if (!element) {
	    return;
	}
	if (element.getOpacity() == to) {
	    return;
	}
	if (duration == 0) {
	    element.setOpacity(to);
	    element.setStyle('display',style);
	    element.setStyle('visibility','visible');
	} else {
	    element.setStyle('display',style);
	    element.setStyle('visibility','visible');
	    new Fx.Tween(element,{
		property: 'opacity',
		duration: duration	    
	    }).start(to);
	}
	
    },

    disappear:function(element,duration) {
	if (duration == undefined) {
	    duration = 0;
	}
	var fxDisappear;
	if (!element) {
	    return;
	}
	if (element.getProperty('display') === 'none') {
	    return;
	}
	if (element.getOpacity() == 0 ) {
	    return;
	}
	if (duration == 0) {
	    //element.setStyle('display','none');
	    element.setStyle('visibility','hidden');
	    element.setOpacity(0);
	} else {
	    new Fx.Tween(element,{
	    property: 'opacity',
		duration: duration
	    }).start(0).chain(
		function() { 
		    //element.setStyle('display','none');
		    element.setStyle('visibility','hidden');
		}
	    );
	}

    }

    
    

});


