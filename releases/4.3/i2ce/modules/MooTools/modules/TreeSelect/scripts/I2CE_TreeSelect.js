

var I2CE_Tree = new Class({
    Extends: I2CE_ToggableWindow,
    options: {
	treeChildClass: 'I2CE_Tree',
	toggleButtonShowClass: 'tree_button_show', //'toggle_button_show'.  if is is a string then this is the class that any toggle buttons are set to when 
	//the  window is shown
	toggleButtonHideClass: 'tree_button_hide', //defaults to 'toggle_button_hide'.  if is is a string then this is the class that any toggle  buttons are set to when 	
	windowHideClass: 'tree_closed', 
	windowShowClass:'tree_open',
	delay_index:false,
	
    },
    treeParent: false,
    treeRoot: false,
    toggle_buttons:false,
    alertLoaded: false,
    alertStartLoaded: false,
    createdChildTree:false,
    childTrees: [],
    loaded: false,


    post_load: function() {
	return true;
    },

    initialize: function(node,options) {
	var toggle_class = false;
	if ($type(node) === 'string') {
	    toggle_class = node + '_toggle';
	} else if ($type(node) === 'element' && node.getProperty && node.getProperty('id')) {
	    toggle_class = node.getProperty('id') + '_toggle';
	} 
	this.parent(node,false,options);
	this.toggle_buttons = false;
	if (toggle_class) {
	    this.toggle_buttons = $$(getElementsByClassName(toggle_class));
	    this.setToggleButtons(this.toggle_buttons);
	}
	if (!this.window) {
	    return false;
	}
	this.treeRoot = this;

	if (this.options.delay_index == false) {
	    this.loaded = true;
	    if (this.isVisible()) {
		this.createChildTrees();
	    }
	    this.post_load();
	    return true;
	} else {
	    var url = "index.php/treedata?delay_index=" + this.options.delay_index;	   
	    var treeDataRequest = new Request.HTML({
		url: url,
		method: 'get',
		onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
		    if (responseTree.length > 0) {
			this.loaded = true;
			this.window.adopt(responseTree.item(0).getChildren());
			if (this.isVisible()) {
			    this.createChildTrees();
			}
			if (this.toggle_buttons) {
			    this.toggle_buttons.each(function(e) {
				e.removeClass('selector-ajax-loading');
			    });
			}
			this.post_load();
			if (this.alertLoaded) {
			    this.alertLoaded.call();
			}
		    }
		}.bind(this)
	    });
	    if (this.toggle_buttons) {
		this.toggle_buttons.each(function(e) {
		    e.addClass('selector-ajax-loading');
		});
	    }
	    if (this.alertStartLoad) {
		this.alertStartLoad.call();
	    }
	    treeDataRequest.send();
	}
    },

    


    setToggleButtons: function(buttons) {
    	this.parent(buttons);
    	this.toggle_buttons.each(
    	    function(toggle) {
    		toggle.addEvent(this.options.toggleEvent,function(e) { this.scroll.call(this,e);}.bind(this));
    	    },this);   	
    },
    


    createChildTrees: function() {
	if (this.createdChildTree === true) {
	    return;
	}
	this.createdChildTree = true;
	var childOptions = Object.clone(this.options);
	childOptions.delay_index = false; //make sure that delayed data only gets loaded on the tree's root node.
	this.window.getChildren('.tree_expandable').each(
	    function(expander) {
		expander.getChildren('.tree_children').each( //allow multiple children to be assigned to theses toggles. why not?
		    function(child) {
			var childTree = this.createChildTree(expander,child,childOptions);
			if (childTree) {
			    this.childTrees.push(childTree);
			}
		    },this);
	    },this);
    },

    createChildTree: function(expander,child,childOptions,toggles) {	
	var childTree = eval('new ' + this.options.treeChildClass + '(child,childOptions)');
	if (!childTree) {
	    return false;
	}
	childTree.treeRoot = this.treeRoot;
	childTree.treeParent = this;
	childTree.setToggleButtons(expander.getChildren('.tree_toggle'));
	return childTree;
    },

    show: function() {
	this.createChildTrees();
	this.parent();
    },


    scrollFx: false,

    scrollRoot: function(element) {
    	if (!this.scrollFx ) {
    	    this.scrollFx = new Fx.Scroll(this.window);
    	}
    	this.scrollFx.toElement(element);
    },

    scroll: function() {
    	if (!this.treeRoot) {
    	    return;
    	}
    	this.treeRoot.scrollRoot(this.window);
    }

});



var I2CE_TreeSelect = new Class({ 
    Extends: I2CE_Tree,
    options: {
	treeSelectCheckTitle: true,
	treeChildClass: 'I2CE_TreeSelect'
    },


    post_load: function() {
	this.getSelectors().each(
	    function(selectable) {
		selectable.addEvent(
		    'click',
		    function() {
			this.select(this.getSelectorValues(selectable));
		    }.bind(this));
	    }, this);
	return true;
    },

    
    getSelectors: function () {
	var selectors  = this.window.getChildren('.treeselect_selectable');
 	this.window.getChildren('.tree_expandable').each(
 	    function(expander) {
 		selectors.combine(expander.getChildren('.treeselect_selectable'));
 	    });
	return selectors;
    },

    createChildTree: function(expander, child,childOptions,toggles) {	
	var childTree = this.parent(expander, child,childOptions,toggles);
	if (!childTree) {
	    return false;
	}
	return childTree;
    },

    
    select: function (values) {
	//this is meant to be overriden by a child class
	return true;
    },

    getSelectorValues: function(selector) {
	if (!selector || !selector.loadClassValues) {
	    return false;
	}
	var values = {
	    'treeselect_value':'',
	    'treeselect_display': selector.get('html')
	};
	selector.loadClassValues(values);
	if ($type(values.treeselect_value) === 'string') {
	    values.treeselect_value = values.treeselect_value.trim();
	}
	if ($type(values.treeselect_display) === 'string') {
	    values.treeselect_display = values.treeselect_display.trim();
	}
	if (this.options.treeSelectCheckTitle) {
	    var title = selector.getProperty('title');
	    if (title) {
		values.treeselect_display = title;
	    }
	}
	return {value:values.treeselect_value, display:values.treeselect_display};
    },

    matchDisplayedValue: function(regexp, max, params) {
	var matches  = new Hash({});
	if ($type(max) !== 'number' || max === 0) {
	    return matches;
	}
	this.getSelectors().each(
	    function(selector) {
		if (max > 0 && matches.getLength() >= max) {
		    return ;
		}
		var values = this.getSelectorValues(selector);
		values.display = '' + values.display; //cast things to a string		
		if (values.display.test(regexp,params)) {
		    matches.set(values.display,values.value);
		}			

	    },this);
	//at this point, if max > 0 then matches.getLength() < max
	//max == 0 cannot happen
	//if max < 0 there are no conditions.
	this.createChildTrees();
	for (var i=0; i < this.childTrees.length; i++) {
	    if (this.childTrees[i].matchDisplayedValue) {
		//if max < 0, then we get all mathces and then still max - matches.getLength() < 0
		//max == 0  cannot happen
		//if max > 0 then we have found matches.getLengh() matches above, so we need to get at most max-matches.getLength() more from this child
		// note:  max - matches.getLength() > 0 at this point (see above)
		matches.combine(this.childTrees[i].matchDisplayedValue(regexp,max - matches.getLength(), params));
		if (max > 0  && matches.getLength() >= max) {
		    return matches;
		}
	    }
	}
	return matches;
    }

});


var InputSelect = new Class({
    hiddenInput: false,
    visibleInput: false,
    
    getHiddenElement: function() {
	    return this.hiddenInput;
    },

    getVisibleElement: function() {
	    return this.visibleInput;
    },


    getHidden: function() {
	    if (!this.hiddenInput) {
	        return false;
	    }
	    return this.hiddenInput.get('value');
    },

    getVisible: function() {
	    if (!this.visibleInput) {
	        return false;
	    }
	    return this.visibleInput.get('value');
    },

    setHidden: function(val) {
	    if (!this.hiddenInput) {
	        return false;
	    }
	    return this.hiddenInput.set('value',val);
    },

    setVisible: function(val) {
	    if (!this.visibleInput) {
	        return false;
	    }
	    return this.visibleInput.set('value',val);
    }

});




var I2CE_TreeInputSelect = new Class({ 
    Extends: I2CE_TreeSelect,
    Implements: InputSelect,
    options:{
	    inputSelectHidden: false,
	    inputSelectVisible: false,
	    treeChildClass: 'I2CE_TreeInputSelect'
    },

    initialize: function(node,options) {
	    this.parent(node,options);	
	    if (!this.window) {
	        return false;
	    }
	    this.visibleInput = document.id(this.options.inputSelectVisible);
	    this.hiddenInput = document.id(this.options.inputSelectHidden);
	    return true;
    },


    select: function(values) {
	    this.setHidden(values.value);
	    this.setVisible(values.display);
    }
});
