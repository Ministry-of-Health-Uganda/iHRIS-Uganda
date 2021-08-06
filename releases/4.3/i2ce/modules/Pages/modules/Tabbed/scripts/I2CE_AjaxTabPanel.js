/*
--- adopted from TabPane Class 
license: MIT-style
authors: akaIDIOT, litlfred@ibiblio.org
version: 0.1
*/

var I2CE_AjaxTabPanel = new Class({
    
    Implements: [Events, Options],

    options: {
        tabSelector: '.tab_link',
        contentSelectorClass: 'tab_content',
	loadingClass: 'stub-ajax-loading',
        activeClass: 'active',
	responseFilter: '#siteContent',
	responseFilters: {}, //key is a tab id, value is the filter we apply to the requested page (e.g. #siteContent)
    },

    container: null,
    showNow: false,

    initialize: function(container, options) {
        this.setOptions(options);

        this.container = document.id(container);
	if (!this.container) {
	    return;
	}
        this.container.getElements('.' + this.options.contentSelectorClass).setStyle('display', 'none');

        this.container.addEvent('click:relay(' + this.options.tabSelector + ')', function(event, tab) {
            this.showTab(this.getTabID(tab));
        }.bind(this));

        this.container.getElements(this.options.tabSelector).addClass(this.options.activeClass);
        this.container.getElements('.' + this.options.contentSelectorClass).setStyle('display', 'block');
    },

    showTab: function(id) {
	if (!this.container) {
	    return;	    
	}
        var content = this.container.getElement('#tab_content_' + id);
        var tab = this.container.getElement('#tab_link_' + id);
        if (!tab ||  !content) {
	    return;
	}
	if (tab.hasClass('disabled_tab')) {
	    return;
	}
	if (content.get('tag') == 'a') {
	    //try to ajax load it
	    this.requestTab(tab,content);
	}  else {
	    this.makeVisible(tab,content);
	}
    },

    getTabID: function(tab) {
	var id = tab.get('id');
	if (!id) {
	    return false;
	}
	if ( ! id.substring(0,9) == 'tab_link_') {
	    return false;
	}
	return id.substring(9);
    },

    requestTab: function(tab,content) {
	var url = content.get('href');
	var id = this.getTabID(tab);
	if (!id) {
	    return false;
	}
	var new_content = new Element(
	    'span',
	    {'id':'tab_content_' + id,
	     'class': this.options.contentSelectorClass + ' ' + this.options.loadingClass
	    });
	content.getChildren().each(function(elem) {
	    new_content.adopt(elem);
	});
        new_content.replaces( content);
	var loadingClass = this.options.loadingClass;
	var filter = this.options.responseFilter;
	if (this.options.responseFilters[id]) {
	    filter = this.options.responseFilters[id];
	}
	new Request.HTML(
	    {
		url : url,
		onSuccess: function() {
		    var ajax_content = this.response.elements.filter(filter);
		    if (!ajax_content) {
			content.appendText("Error Loading Tab");			
			return;
		    }
		    new_content.empty();
		    Browser.exec(this.response.javascript);		    
		    new_content.adopt(ajax_content);
		    var top = new_content.getCoordinates().top;
		    var getLowest =  function(elem) {
			var bottom = elem.getCoordinates().bottom;
			elem.getChildren().each(function(child) {
			    bottom = Math.max(bottom,getLowest(child));
			});
			return bottom;
		    };
		    var bottom = getLowest(new_content);
		    var height = Math.max(30,bottom - top);
		    new_content.setStyle( 'height',height);
		    new_content.removeClass(loadingClass);		    
		}
		
	    }
		
	).get();
	this.makeVisible(tab,new_content);
    },
    


    makeVisible: function(tab,content)  {
	if (!this.container) {
	    return;
	}

        this.container.getElements(this.options.tabSelector).removeClass(this.options.activeClass);
        this.container.getElements('.' + this.options.contentSelectorClass).setStyle('display', 'none');
        tab.addClass(this.options.activeClass);
        content.setStyle('display', 'block');
	this.fixHeight(content);
	this.fixHeight(tab);
        this.fireEvent('change', this.getTabID(tab));
    },

    fixHeight: function(elem) {
	elem = $(elem);
	if (elem == undefined) {
	    return;
	}
	var top = elem.getSize().y;
	var getLargest =  function(elem) {
	    var height = elem.getSize().y;
	    elem.getChildren().each(function(child) {
		height = Math.max(height,getLargest(child));
	    });
	    return height;
	};
	var height = Math.max(30,getLargest(elem) + 10);
	elem.setStyle( 'height',height);
    }






});


