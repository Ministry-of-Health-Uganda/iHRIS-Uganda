var I2CE_WindowSelect = new Class({
    Extends: I2CE_ToggableWindow,    
    options: {
	windowFloats:true,
	windowDraggable: true,
	toggleButtonHideClass: 'selector_value_hide',
	reportview: false, //the reporview we are slecting from
	reportform: false, //the form in the report we are selecting
	printf: false, //the display string for the form we are selecting
	printfargs: false, //the array of fields the form we are displaying
	select_id: false, //the base id of the selecot
	style: 'default', //the style of the display
	clear_value: 'Select Value', //the text to display when clearing
	clear_hidden_value: '', //the hidden value set  when clearing 
	allow_clear: true, //whether or not we allow clearing the value
	contentid: 'report_results_with_limits' //the id of the DOMElement we are pulling in for updates,
    },
    content: false,
    reportLoaded: false,

    initialize: function(element,toggle_class, options ) {
	this.parent(element,toggle_class,options);
	this.content = document.id(this.options.select_id + ':content');
	if (!this.options.reportform || !this.options.reportview || !this.options.printf || !this.options.printfargs || !this.content   ) {
	    this.window = false;
	    return false;
	}

    },
    
    clear: function() {
	if (!this.options.allow_clear) {
	    return false;
	}
	var disp = document.id(this.options.select_id + ':display');
	var value = document.id(this.options.select_id + ':value');
        if (!disp ||  !value) {
	    return false;
	}
	disp.textContent = this.options.clear_value;
	value.set("value",this.options.clear_hidden_value);
	return true;
    },
    
    loadReport:function() {
	if (this.reportLoaded || !this.content) {
	    return true;
	}
	this.reportLoaded = true;
	var request = 'CustomReports/show/' + this.options.reportview  
	    + '/Selector?select_printf=' + Stub.urlencode(this.options.printf)
	    + '&select_printfargs=' + Stub.urlencode(this.options.printfargs)
	    + '&select_reportform=' + Stub.urlencode(this.options.reportform)
        + '&select_style=' + Stub.urlencode(this.options.style)
	    + '&select_id=' + Stub.urlencode(this.options.select_id) ;
	    
	var url = 'index.php/stub/id?request=' + Stub.urlencode(request) +  '&content=' + this.options.contentid+ '&keep_javascripts=' + Stub.urlencode('stub_events,formworm,treeselect,ajax_list');
	new Request.HTML(
	    {
		url :  url,
		update: this.content,
		evalScripts:true,
		onComplete: function() {
		    this.content.removeClass('stub-ajax-loading');
		}.bind(this),

		onRequest: function() { 
		    //replace.empty()
		    this.content.addClass('stub-ajax-loading');
		}.bind(this)
	    }
	).get();
	return true;
    },
    
    show:function() {
	if (!this.window) {
	    return false;
	}
	this.loadReport();
	this.parent();
    }
   
});
