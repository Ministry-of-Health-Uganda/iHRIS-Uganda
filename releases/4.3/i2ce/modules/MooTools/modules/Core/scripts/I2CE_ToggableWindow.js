var I2CE_ToggableWindow = new Class({
    Extends: I2CE_Window,
    toggle_buttons: null, //toggle buttons
    options: {
	toggleEvent: 'click', //the event which triggers the toggable window.  defaults to 'click'
	//when we show the class. To set the class, both windowShowClass and windowHideClass need to be strings (possibly empty)
	toggleButtonShowClass: 'toggle_button_show', //'toggle_button_show'.  if is is a string then this is the class that any toggle buttons are set to when 
	//the  window is shown
	toggleButtonHideClass: 'toggle_button_hide' //defaults to 'toggle_button_hide'.  if is is a string then this is the class that any toggle  buttons are set to when 
	//the window is hidden
    },

    
    
    initialize: function(element,toggle_class, options ) {
	this.parent(element,options);
	if (!this.window) {
	    return false;
	}	
	this.toggleEvent = this.toggleEvent.bindWithEvent(this);
	if (toggle_class) {
	    //this.setToggleButtons($$(document.getElementsByClassName(toggle_class)));
	    this.setToggleButtons($$(getElementsByClassName(toggle_class)));
	}
	return true;
    },


    setToggleButtons: function(buttons) {
	this.toggle_buttons = buttons;	
	var opened = this.isVisible();
	this.toggle_buttons.each(
	    function(toggle) {
		toggle.removeEvents(this.options.toggleEvent);
		toggle.addEvent(this.options.toggleEvent,this.toggleEvent);
		this.toggleButton(toggle,opened);
	    },this);   
	
    },

    toggleButtons:function(open) {
	if ($type(this.toggle_buttons) !== 'array') {
	    return;
	}
	this.toggle_buttons.each(function(button) { this.toggleButton(button,open);}, this);
    },

    toggleButton:function(button,open) {
	if (!button) {
	    return;
	}
	if (open) {  //windowVisible is false
	    if ($type(this.options.toggleButtonShowClass) == 'string') {
		button.removeClass(this.options.toggleButtonShowClass);
	    }
	    if ($type(this.options.toggleButtonHideClass) == 'string') {
		button.addClass(this.options.toggleButtonHideClass);
	    }
	} else {
	    if ($type(this.options.toggleButtonShowClass) == 'string') {
		button.addClass(this.options.toggleButtonShowClass);
	    }
	    if ($type(this.options.toggleButtonHideClass) == 'string') {
		button.removeClass(this.options.toggleButtonHideClass);
	    }
	}
    },
    
    hide: function() {
	this.parent();
	this.toggleButtons(false);	
	return true;
    },

    show: function() {
	this.parent();
	this.toggleButtons(true);	
	return true;
    },

    toggleEvent: function() {
	if (this.isVisible()) { //make it hidden	    
	    this.hide();
	} else { //show the  window
	    this.show();
	}
	return true;
    }
    

});
