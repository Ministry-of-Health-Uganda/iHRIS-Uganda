

var Stub = {
    functions: [],
    toggle: [],



    addFunction: function(id,state,f) {
	if (Stub.functions[id] == undefined ) {
	    Stub.functions[id] = {};
	}
	if (Stub.functions[id][state] == undefined ) {
	    Stub.functions[id][state] = [];
	}
	Stub.functions[id][state][Stub.functions[id][state].length] = f;
    },

    callFunctions: function(id,state) {
	if (!$defined(Stub.functions[id])) {
	    return;
	}
	if (!$defined(Stub.functions[id][state])) {
	    return;
	}
	Stub.functions[id][state].each (function(fn) {
	    if ($type(fn) === 'string') {
		eval(fn);
	    } else if ($type(fn) === 'function') {
		$try(fn);
	    }
	});
    },

    urlencode: function (str) {
	return escape(str).replace(/\+/g,'%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
    },


    updateByIdOnEvent: function (event, id_action,id_replace,request,id_content,form_id,js_ids,remove_event,remove_event_on_request) {	
	if (Browser.loaded) {
	    this.updateWorker(event,remove_event, id_action,id_replace,request,id_content,form_id,js_ids,remove_event_on_request, false);
	} else {
	    window.addEvent('load',function(e) {
		this.updateWorker(event,remove_event, id_action,id_replace,request,id_content,form_id,js_ids,remove_event_on_request,false);
	    }.bind(this));
	}
    },

    toggleByIdOnEvent: function (event, id_action,id_replace,request,id_content,form_id,js_ids, remove_event, start_opened) {	
	if (Browser.loaded) {
	    this.updateWorker(event,remove_event, id_action,id_replace,request,id_content,form_id,js_ids,false,true, start_opened);
	} else {
	    window.addEvent('load',function(e) {
		this.updateWorker(event,remove_event, id_action,id_replace,request,id_content,form_id,js_ids,false,true,start_opened);
	    }.bind(this));
	}	
    },



    updateWorker: function (event,remove_event, id_action,id_replace,request,id_content,form_id,js_ids,remove_event_on_request, toggle, start_opened) {
	var actionObj = document.id(id_action);
	if (!actionObj) { 
	    return;
	}
	if (remove_event) {
	    actionObj.removeEvents(event);
	}
	if (toggle) {
	    var replace = document.id(id_replace);
	    if (!replace) {
		return;
	    }
	    if (start_opened) {
		Stub.toggle[id_action] = 'on'; //the state that we are
	    } else {
		Stub.toggle[id_action] = 'off'; //the state that we are
	    }
	    actionObj.addEvent(
		event,
		function (e) {
		    if (!replace) {
			return;
		    }
		    e = new Event(e).stop();
		    if (Stub.toggle[id_action] === 'off') { //not shown, so show it
			Stub.toggle[id_action] = 'on';  
			this.updateById(id_action,id_replace,request,id_content,form_id,js_ids,false, toggle);
		    } else { //clear out what was there.
			Stub.toggle[id_action] = 'off';
			//i should do this .. replace.empty(); -- but, 
			//this calls the GC which can take a inordinate long time.
			$A(replace.childNodes).each(function (child) {			    
			    replace.removeChild(child);
			});
		    }
		    Stub.callFunctions(id_action,Stub.toggle[id_action]);
		}.bind(this));	    
	    
	} else {
	    actionObj.addEvent(
		event,
		function (e) {
		    e = new Event(e).stop();
		    this.updateById(id_action,id_replace,request,id_content,form_id,js_ids,remove_event_on_request, toggle);
		}.bind(this));	    
	}
	var style = actionObj.getStyle('cursor');
	this.addFunction(id_action,'request',function () {
	    actionObj.setStyle('cursor','wait');
	});
	this.addFunction(id_action,'complete',function () {
	    actionObj.setStyle('cursor',style);
	});
    },

    toQueryString: function(form){
	var l=[];
	form.getElements("input, select, textarea",true).each( 
	    function(m) {
		if (!m.name || m.disabled || m.type=="submit" || m.type=="reset" || m.type=="file" ) { 
		    return;
		}
		var n=
		    (m.tagName.toLowerCase()=="select") 
		    ? Element.getSelected(m).map(
			function(o){
			    return o.value;
			})
		    : ( (m.type=="radio"||m.type=="checkbox") && !m.checked ) ?null:m.value;
		$splat(n).each(function(o){
		    if (typeof o!="undefined")  {
			l.push(encodeURIComponent(m.name)+"="+encodeURIComponent(o));
		    }});
	    });
	return l.join("&");
    },


    updateById: function(id_action,id_replace,request,id_content,form_id,js_ids,remove_event_on_request) {
	var replace = document.id(id_replace);
	if (!replace) { //id was not available.  exit so we don't get a javascript error
	    return;
	}
	var action = document.id(id_action);
	if (!action) {
	    return;
	}
	if (form_id) {
	    form = document.id(form_id);
	    if (form) {
		if (request.indexOf('?') > -1) {
		    request += '&' ;
		} else {
		    request += '?' ;
		}
		request += this.toQueryString(form);
	    }
	}	
	var url = 'index.php/stub/id?request=' + Stub.urlencode(request) +  '&content=' + Stub.urlencode(id_content) ;
	if (js_ids) {
	    url += '&keep_javascripts=' + js_ids;
	}
	//we can changes this to Request.HTML if the following is fixed
	//http://mootools.lighthouseapp.com/projects/2706/tickets/524-add-contenttype-for-requesthtml
	
	new Request.HTML(
		 {
		     url : url,
		     update: replace,
		     evalScripts:true,
		     onComplete: function() {
			 replace.removeClass('stub-ajax-loading');
			 Stub.callFunctions(id_action,'complete');
		     },

		     onRequest: function() { 
			 //replace.empty()
			 replace.addClass('stub-ajax-loading');
			 if (remove_event_on_request) {
			     action.removeEvents(remove_event_on_request);
			     action.addEvent(remove_event_on_request,function (e) {
				 e = new Event(e).stop();				 
			     });
			 }
			 Stub.callFunctions(id_action,'request');
		     }
		 }
	).get();
    }
	
    

  
};

