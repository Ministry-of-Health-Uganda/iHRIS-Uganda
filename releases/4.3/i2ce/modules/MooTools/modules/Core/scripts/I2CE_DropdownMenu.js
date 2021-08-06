
var I2CE_DropdownMenu = new Class({
    
    date: false,
    menu: false,
    toggle:false,
    clicked: false,
    timer:false,
    timer_delay: 200, //time in miliseconds

    doToggle: function() {
	if (!this.menu) {
	    return false;
	}
	if (this.menu.getStyle('display') == 'none') {
	    this.doShow();
	} else { 
	    this.doHide();
	} 
	return false;
    },


    doShow: function()  {
	this.menu.setStyle('display','inline-block');
	this.menu.addClass('toggler-show');
    },

    doHide: function() {
	this.menu.setStyle('display','none');
	this.menu.addClass('toggler-hider');
    },

    stopTimer: function () {
	if (this.timer) {
	    clearTimeout(this.timer);
	}
	this.timer = false;
    },
    
    doClick: function(event) {
	event.stop();
	if (this.clicked) {
	    this.clicked = false;
	    this.doHide();
	} else {
	    this.clicked = true;
	    this.doShow();
	}
    },

    doLeave: function() {
	if (!this.clicked) {
	    this.clicked = false;
	    this.doHide();
	}
    },


    startTimer: function () {
	if (this.timer) {
	    this.timer = false;
	}
	this.timer = this.doShow.bind(this).delay(this.timer_delay);
    },


    initialize: function(element) {
	element = $(element);

	if (!element) {
	    return this;
	}
	element.getChildren().each(function(el){
	    if(el.hasClass('dropdown-menu')){
	    	this.menu = el;
	    } else if (el.hasClass('dropdown-toggle')){
		this.toggle = el;
	    }
	}.bind(this));
	if (!this.menu  || !this.toggle) {
	    return this;
	}
	this.toggle.addEvent( 'click', this.doClick.bind(this));
	element.addEvent('mouseleave',this.doLeave.bind(this));
	element.addEvent('mouseleave',this.stopTimer.bind(this));
	element.addEvent('mouseenter',this.startTimer.bind(this));
	return this;
    }
    

    
});


