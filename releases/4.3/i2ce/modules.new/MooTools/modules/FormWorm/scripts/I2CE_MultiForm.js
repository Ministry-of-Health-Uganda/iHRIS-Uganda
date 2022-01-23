
var I2CE_MultiForm = new Class({
    Implements: Options, 
    submits: new Array(),
    form_id: null,
    form: null,
    given_action: null,
    given_method: null,
    


    initialize: function(form_id,options ) {
	this.form_id = form_id;
	this.form =document.id(form_id);	
	if (!this.form) {
	    return false;
	}
	this.given_action = this.form.getProperty('action');	
	this.given_method = this.form.getProperty('method');
	if (!this.given_method) {
	    this.given_method = 'get';
	}
	this.setOptions(options);
	this.scanForSubmits();
	return true;
    },

    scanForSubmits: function(node) {
	if (!$defined(node)) {
	    node = this.form;
	} else {
	    node = document.id(node);
	}
	if (!node) {
	    return;
	}
	//node.getElements('textarea, select, input').each(
	node.getElements('input, span.multiformsubmit').each(function(input) {
	    if ( (input.get('tag') == 'span' && input.get('id')) || (input.get('tag') == 'input' && input.getProperty('type').toLowerCase() == 'submit')) {
		this.addSubmitNode(input);
	    }
	}.bind(this));
    },


    

    addSubmitNode: function(submit) {	
	var id = submit.getProperty('id');
	if (id) {
	    if (this.submits[id]) {
		if (this.submits[id] === submit) {
		    //already dealt with this guy
		    return;
		} else {
		    //the id refers to a different element.  we need to redo it.
		    this.submits[id] = null;
		    if (this.options_menu[id]) {
			this.options_menu[id] = null;
		    }
		}
	    }		
	}
	//if we make it here, we are good to go for a form submission
	if (id) {
	    this.submits[id] = submit;
	}
	submit.removeEvents('click');
	var submitClicked = function(event) {
	    this.submitClickedEvent(event,submit);
	}.bindWithEvent(this);
	submit.addEvent('click', submitClicked);
    },


    getSendData: function(submit) {
	return this.form.toQueryString();
    },
    

    prepareForSubmit: function (submit) {
	var classVals = {action:this.given_action,method:this.given_method};
	submit.loadClassValues(classVals);
	this.setFormAction(classVals.action,classVals.method);	
    },

    

    
    setFormAction: function(action,method) {
	if (action) {
	    this.form.setProperty('action',action);
	} else {
	    this.form.setProperty(this.given_action);
	}
	if (method) {
	    this.form.setProperty('method',method);
	} else {
	    this.form.setProperty(this.given_method);
	}
    },



    canSubmit:function (submit,id) {
	return true;
    },
    

    submitClickedEvent: function(event, submit) {
	event = new Event(event);				
	var id = submit.getProperty('id');
	if (!this.canSubmit(submit,id)) {
	    event.stop();
	    return false;
	}
	var target;
	var classVals = {ajaxTargetId:null,ajaxTargetName:null};
	submit.loadClassValues(classVals);
	target = document.id(classVals.ajaxTargetID);
	if (!target) {
	    target = classVals.ajaxTargetName;
	    if (target) {
		target = this.form.getElementsByName(target);
		if (target.length >= 1) {
		    target = target.item(0);
		} else {
		    target = false;
		}
	    }
	}
	this.prepareForSubmit(submit);
	if (!target) {
	    //no ajax target.  we can go ahead and submit this form	    
        var curVal;
        for( i in this.form ) {
            if ( this.form[i] && this.form[i].value ) {
                curVal = this.form[i].value;
            }
            //if ( this.form[i] && this.form[i].type && this.form[i].type == "submit" ) {
                // Do nothing here, but Chrome needs it for some reason
                // or it fails to work in some cases.
            //}
        }
	    this.form.submit();
	    return true;
	}
	//there is an ajax target
	event.stop();
	var url = classVals.action;
	var method = classVals.method;
	var send = this.getSendData();
	var req = new Request.HTML( {
	    url: url,
	    method:method,
	    data: send,
	    update: target,
	    link: 'ignore'
	});
	var pointerStyle = submit.getStyle('cursor');			    
	req.addEvent(
	    'request',
	    function() {
		//replace.empty()
		if (id) {
		    this.submits[id]= null;
		}
		target.addClass('ajax-loading');
		if (this.options.ajaxLoadingPointer) {
		    submit.setStyle('cursor',this.options.ajaxLoadingPointer);
		}
	    }.bindWithEvent(this));
	req.addEvent(
	    'complete',
	    function() {
		this.scanForSubmits();
		target.removeClass('ajax-loading');
		if (this.options.ajaxLoadingPointer) {
		    submit.setStyle('cursor',pointerStyle);
		}
	    }.bindWithEvent(this));
	req.send();
	this.afterRequest(submit);
	var onclick = submit.getProperty('onClick');
	if (onclick) {
	    if (! eval(onclick)) {
		event.stop();
		return false;
	    } else {
		return true;
	    }
	} else {
	    return true;
	}
    },

    
    afterRequest: function(submit) {
	
    }


});


var I2CE_MultiOptionForm = new Class({
    Extends: I2CE_MultiForm,
    options_menu: new Object(),  //option menus -- indexed by ids of the optionmenu
    options: {
	optionsMenuPreserve: false, //defaults to false. if true we preserver all input/textarea/etc elements on from submission, otherwise we delete input/textarea/etc
	//which are not contained in the options menu for the submit  we are doing (or any options menu if the submit is not a part of an options menu)
	ajaxLoadingPointer: 'wait' //the class to set the pointer when over an input button that is ajaxy
    },




    prepareForSubmit: function (submit) {
	this.parent(submit);
	var id = submit.getProperty('id');	
	if (!this.options.optionsMenuPreserve) {
	    //if the submit has the class 'only_options_menu' we only enable submit what is in the submit's option menu
	    this.enableMenuOptions(id,submit.hasClass('only_options_menu')); 
	}   
    },


    afterRequest: function(submit) {
	this.form.getElements('input, textarea, select').each(function(input) {
	    this.inputEnable(input);
	},this);
    },


    enableMenuOptions:function(id,optionsMenuOnly) {
	$each(this.options_menu,function(menu,menu_id) {
	    if (menu_id == id || menu.hasClass('preserveOptions')) {
		//we enable anything that was disabled before
		//if the id does not exists or is not for an options menu, this 
		//will not match so everything in an options menu will be disabled
		menu.getElements('input, textarea, select').each(function(input) {
		    this.inputEnable(input);
		},this);
	    } else {
		//we disable everything
		menu.getElements('input, textarea, select').each(function(input) {
		    this.inputDisable(input);
		},this);
	    }}, this);
	if (optionsMenuOnly) {
	    //if the id does not exists or is not for an options menu, you presumable do not have 'only_options_menu' class set
	    //in this case optionsMenuOnly is false so you do not disable the free input
	    this.scanForInputs().each(function(input) { //get the "freee" inputs which are not tied to an options menu
		this.inputDisable(input);
	    },this);
	} else {
	    this.scanForInputs().each(function(input) { //get the "freee" inputs which are not tied to an options menu
		this.inputEnable(input);
	    },this);
	}
    },
    
    addSubmitNode: function(submit) {
	this.parent(submit);
	var id = submit.getProperty('id');
	if (!id) {
	    return;
	}
	var menu = document.id(id + '_options_menu');
	if (!menu) {
	    return;
	}
	//add this as an options menu.
	//you may have several submits referring to the same options menu.
	//this is ok.
	this.options_menu[id] = menu; 
	//now see if it should be toggable
	new I2CE_ToggableWindow(menu,id+'_options_toggle'); //second argument is toggle class
    },


    inputEnable:function(input)  {
	//we add the multioption_disabled property as a flag that this input was disabled by
	//multiform and not at the user's request
	if (!input.getProperty('multioption_disabled')) {
	    return;
	}
	input.removeProperty('disabled');
	input.removeProperty('multioption_disabled');
    },


    inputDisable:function(input)  {
	//we add the multioption_disabled property as a flag that this input was disabled by
	//multiform and not at the user's request
	if (input.getProperty('disabled') || !input.getProperty('name')) {
	    return;
	}
	input.setProperty('disabled',true);
	input.setProperty('multioption_disabled',true);
    },





    inputIsEnabled:function(input) {
	return input.getProperty('multiform_disabled');
    },
    

    //If which is set and there is no options menu for which no inputs are returned.
    //If which is set and there is an  options menu, only that options menu input are returned
    //If which is not set only inputs which are not in an options menu are returned.
    scanForInputs:function(which) {
	var fields;
	if (which ) {
	    if (!this.options_menu[which]) {
		return new Array();
	    } 
	    return  this.options_menu[which].getElements('textarea, select, input');
	} else {
	    fields = new Array();
	    this.form.getElements('textarea, select, input').each(
		function(node) { //find loners -- i,e, inputs that don't lie within an options menu
		    var pNode = node;
		    var which = false;
		    var match;		
		    do {
			which = pNode.getProperty('id');
			if (which) {
			    match =  (/^(.*)_options_menu$/).exec(which);
			    if (!match || !match[1] || !this.options_menu[match[1]]) { //this is not an options menu so continue up the DOM
				which = false;
			    } else {
				which = match[1];
			    }
			}
			pNode = pNode.getParent();
		    } while (pNode && ( pNode.get("tag") != "form" ) && !which);
		    if (pNode && !which) {
			fields.push(node);
		    }		
		}, this);
	    return fields;
	}
    }



});




//a validating multi-form
var I2CE_FormWorm = new Class({
    Extends: I2CE_MultiOptionForm,
    options: {
	focusOnError: true, //set to true to focus an invalid  field on form submission
	stopOnFirst:false, //set to true if only stop validating when an error has been reached
	showAlert:true, //set to true to show an alert message on form submission if there is an invalid field
	alertMessage: 'There is a problem with your request',  //the alert message to show on an invalid field
	checkOnBlur:true, //validate a field on blur
	errorMsgPlacement :'before', //can be false,'before','after'.   It desribes where to put the error the error message div for
    	                             // an invalid field -- dont place one, immediately before, immediately after
	invalidFieldClass: 'invalidSubmission', //the class attribute assigned to a field that is invalid
	errorMsgClass: 'invalidSubmissionMessage' //the class attribute assigned to the error message div
    },


    scanForSubmits: function(node) {
	this.parent(node);
	if (!this.options.checkOnBlur) {
	    return;
	}
	if (!$defined(node)) {
	    node = this.form;
	} else {
	    node = document.id(node);
	}
	if (!node) {
	    return;
	}
	node.getElements('textarea, select, input').each(this.addBlurCheck,this);
    },


    canSubmit:function (submit,id) {
	if (!this.parent(submit,id)) {
	    return false;
	}
	return this.validate(id, submit.hasClass('only_options_menu'));
    },

    addBlurCheck:function(field) {
	var name = field.getProperty('name');
	if (!name) {
	    return;	    
	}
	var validate = {validate:null,validate_data:{}};
	field.loadClassValues(validate);		
	var validates = validate.validate;	
	if ($type(validates) == 'string') {
	    validates = [validates];
	}
	if (!validates || $type(validates) !=='array' || validates.length == 0) {
	    return;
	}
	var validate_data = validate.validate_data;
	if (!$type(validate_data) == 'object' ) {
	    validate_data = {};
	}
	field.removeEvents('blur');
	var blurFunc = 	function(event) {
	    event = new Event(event);
	    advice = document.id(name + '_submission_error_message');
	    if (advice) {
		advice.dispose();
	    }
	    fieldBox = document.id(name+'_field_container');
	    if (!fieldBox) {
		fieldBox = field;
	    }			
	    fieldBox.removeClass(this.options.invalidFieldClass);
	}.bind(this).bindWithEvent(this);
	field.addEvent('blur',blurFunc);
	validates.each(
	    function(validate) {
		if (!validate) {
		    return;
		}
		var type;
		var validCheck;
		var data = [];
		if (validate_data[validate]) {
		    data = validate_data[validate];
		}
		if (field.get('tag') == 'select' && field.getProperty('multiple')) {
		    type = 'array';
		} else {
		    type = 'string';		
		}
		if ( !I2CE_Validator.hasValidateFunction(type,validate)) {
		    return;
		}
		field.addEvent(
		    'blur',
		    function(event) {		
			event = new Event(event);
			msg = I2CE_Validator.getInputErrors(field.get('value'),type,validate,data);
			if (msg) {
			    this.processErrors(name,field,msg);
			}
		    }.bindWithEvent(this));			
	    }.bind(this));
    },
    

    getValidators:function(inputs) {
	var fields = new Array();
	inputs.each(
	    function(input) {
		var name = input.getProperty('name');
		var classVals = {validate:'',validate_data:{}};
		input.loadClassValues(classVals);
		var type;
		var data;
		if (!name) {
		    return;	    
		}
		if ($type(classVals.validate) == 'string') {
		    classVals.validate = [classVals.validate];
		}
		if (  ($type(classVals.validate) != 'array') || (classVals.validate.length == 0  )) {
		    return; 
		}
		classVals.validate.each(function(validate) {	    
		    if (!validate) {
			return;
		    }
		    if (input.get('tag')== 'select' && input.getProperty('multiple')) {
			type = 'array';
		    } else {
			type = 'string';		
		    }
		    if ( !I2CE_Validator.hasValidateFunction(type,validate)) {
			return;
		    }
		    data = [];
		    data['name'] = name;		
		    data['input']=input;
		    data['validate']=validate;
		    data['type']=type;
		    data['data']= new Array();
		    if (classVals.validate_data[type]) {
			data['data'] = validate_data[type];
		    }
		    fields[fields.length] = data;
		});
	    },this);
	return fields;
    },

    
    validate:function(id,optionsMenuOnly) {
	var error_fields = [];
	var processErrors;
	var processFields;
	var msgs = [];
	inputs = [];
	if (id && this.options_menu[id]) {
	    //if there is an id and it associated to  an option menu, make sure everything is valid
	    inputs = this.options_menu[id].getElements('input, textarea, select');
	}
	if (!optionsMenuOnly) {
	    //if this is not an options_menu_only, then
	    //get the "free" inputs which are not in an options menu
	    inputs = inputs.combine(this.scanForInputs());
	}
	this.getValidators(inputs).each(
	    function(data) {
		if ( this.options.stopOnFirst && msgs.length> 0 ) {
		    return; //this is bad i know -- its an expensive 'break'
		}
		msg = I2CE_Validator.getInputErrors(data['input'].get('value'),data['type'],data['validate'],data['data']);
		if (msg) {
		    error_fields[error_fields.length] = data;
		    msgs[msgs.length] = msg; 
		} else {
		    fieldBox = document.id(data['name']+'_field_container');
		    if (!fieldBox) {
			fieldBox = data['input'];
		    }
		    fieldBox.removeClass(this.options.errorMsgClass);
		    advice = document.id(data['name'] + '_submission_error_message');
		    if (advice) {
			advice.dispose();
		    }
		}
	    }, this);
	if (msgs.length == 0) {
	    return true;
	} else {
	    if (this.options.showAlert) {
		msg_string = '';
		alert(this.options.alertMessage);
	    }
	    error_fields.each(
		function(data,i) {
		    this.processErrors(data['name'],data['input'],msgs[i]);
		},this);
	    return false;
	}
    },
					    

    processErrors:function(name,field,msg) {
	fieldBox = document.id(name+'_field_container');
	if (!fieldBox) {
	    fieldBox = field;
	}
	fieldBox.addClass(this.options.invalidFieldClass);
	if (this.options.errorMsgPlacement) {
	    advice = document.id(name + '_submission_error_message');
	    if (!advice) {
		advice = new Element('div'); 
		advice.addClass(this.options.errorMsgClass);
		advice.setProperty('id',name+'_submission_error_message');
		switch(this.options.errorMsgPlacement) {
		case 'after':
		    advice.injectAfter(field);
		    break;
		default: // 'before':
		    advice.injectBefore(field);
		    break;
		}
	    }
	    if ($type(msg) == 'collection') {
		$each(msg, function(m) {
		    m.injectInside(advice);
		});
	    } else {
		advice.set('html',msg);
	    }
	}
    }

});





