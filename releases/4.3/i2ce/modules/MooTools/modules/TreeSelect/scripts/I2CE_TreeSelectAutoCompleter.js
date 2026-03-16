
I2CE_TreeSelectAutoCompleter = new Class({
    Extends: Autocompleter,
    options: { //autocomplete options described at http://www.clientcide.com/docs/3rdParty/Autocompleter
	selectMode: 'type-ahead',
	matchBegin: true,	
	matchInsensitive: true,	
	forceSelect: true,
	cache: false,
	delay: 200,
	noSelectedClass: 'error', //the class to add to the visible input when nothing is selected
	clearValueOnEdit: true,  //clears  the hidden input  value when typing in visible
	checkValidOnFirstFocus: true, //defaults to true in which case if the set value of the input does not match exactly the displayed input it cleared
	checkValidOnLoad: false	
    },

    treeSelect: false,
    firstFocus: true,


    initialize: function (treeSelect,   options) {
	if (!treeSelect || !treeSelect.matchDisplayedValue || !treeSelect.setHidden || !treeSelect.getVisibleElement ) {
	    return;
	}
	treeSelect.alertLoaded = this.treeDataLoaded.bind(this);
	var visible = treeSelect.getVisibleElement();
	if (!visible) {
	    return;
	}
	this.options.zIndex = treeSelect.options.windowZIndex + 555;	
	this.parent(visible,options);
	this.treeSelect = treeSelect;	
	if (this.treeSelect.loaded) {
	    this.setHiddenFromDisplayValue(!this.options.checkValidOnLoad);
	}
	//this.element = visible by the parent
	if (this.options.checkValidOnFirstFocus) {
	    this.element.addEvent('focus', function() {
		if (!this.firstFocus) {
		    return;
		}
		this.firstFocus = false;
		this.setHiddenFromDisplayValue(false);
	    }.bindWithEvent(this));
	}
    },
    
    treeDataLoaded: function() {
	if (!this.options) {
	    return false;
	}
	this.setHiddenFromDisplayValue(!this.options.checkValidOnLoad);
    },


    setHiddenFromDisplayValue: function(allow_invalid) {	 
	if (!this.treeSelect.loaded) {
	    //wait until it is loaded
	    return;
	}
	var match = this.treeSelect.getVisible();
	if ($type(match) !== 'string') {
	    this.treeSelect.setHidden('');
	    if (allow_invalid) {
		return;
	    }
	    this.treeSelect.setVisible('');
	    this.observer.value = '';
	    this.opted = '';
	    return;
	}
	match = '^' + match.escapeRegExp() + '$';
	var params = '';
	if (this.options.matchInsensitive) {
	    params = 'i';
	}
	match = this.treeSelect.matchDisplayedValue(match, 1, params);	    
	var key = match.getKeys();
	if (key.length == 1) {
	    key = key[0];		
	    this.treeSelect.setHidden(match.get(key));		    
	    this.treeSelect.setVisible(key);
	    this.observer.value = key;
	    this.opted = key;
	} else { //getLength() == 0;		
	    this.treeSelect.setHidden('');
	    if (allow_invalid) {
		return;
	    }
	    this.treeSelect.setVisible('');
	    this.observer.value = '';
	    this.opted = '';
	    return;
	}
    },


    query: function() {
	var match = this.queryValue.escapeRegExp();
	if (this.options.matchBegin) {
	    match = '^'+ match;
	}
	var params = '';
	if (this.options.matchInsensitive) {
	    params = 'i';
	}
	this.updatePairs(this.treeSelect.matchDisplayedValue(match, this.options.maxChoices, params));	    
    },
    
    prefetch: function() {
	if (this.options.clearValueOnEdit) {
	    this.treeSelect.setHidden('');
	}
	if (this.options.noSelectedClass) {
	    this.treeSelect.getVisibleElement().addClass(this.options.noSelectedClass);
	}
	this.parent();
    },

    
    setSelection: function(finish) {
	if (this.options.noSelectedClass) {
	    this.treeSelect.getVisibleElement().removeClass(this.options.noSelectedClass);
	}
	this.parent(finish);
    },
   
    updatePairs: function(pairs) {
	this.choices.empty();
	var type = pairs && $type(pairs);
	if (type !==  'hash'  ||  !pairs.getLength()) {
	    (this.options.emptyChoices || this.hideChoices).call(this);
	} else {
	    pairs.each(
		function(value,display){
		    var choice = new Element('li', {'html': this.markQueryValue(display)});		    
		    choice.inputValue = display;		    
		    choice.addEvent('click',function() { this.treeSelect.setHidden(value);}.bind(this));
		    document.addEvent(
			'keydown' , 
			function(e) {
			    if (!e || !e.key || e.shift) {
				return  true;
			    }
			    if (!this.visible || !this.selected) {
				return true;
			    }				
			    if (this.element.value != this.opted) {
				return true;
			    }				
			    if (this.element.value != display) {
				return true;
			    }				
			    if (e.key=='enter' ) {
				this.treeSelect.setHidden(value);
			    }
			    return true;
			}.bind(this));			
		    this.addChoiceEvents(choice).inject(this.choices);
		}, this);
	    this.showChoices();
	}
    }



});
