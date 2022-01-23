function hideDiv( id, anchor ) {
    if (!document.getElementById(id)) {
	return false;
    }
    if ( anchor.className == 'hide' ) {
	document.getElementById(id).style.display = 'none'; 
	anchor.title = 'Expand';
	hideText[id] = anchor.innerHTML;
	if (expandText[id] == undefined) {
	    expandText[id] = 'Expand';
	}
	anchor.innerHTML = expandText[id];
	anchor.className = 'expand';
    } else {
	document.getElementById(id).style.display = 'inline';
	anchor.title = 'Hide';
	expandText[id] = anchor.innerHTML;
	if (hideText[id] == undefined) {
	    hideText[id] = 'Hide';
	}
	anchor.innerHTML = hideText[id];
	anchor.className = 'hide';
    }
    return false;
}
var prevAnchor = false;
var hideText = [];
var expandText = [];


function ajaxLoadDiv( toggle,node, url,filter ) {
    toggle = $(toggle);
    node = $(node);
    if (!node) {
        return false;
    }
    if (toggle.hasClass('clicker_show')) {
	toggle.removeClass('clicker_show');
	toggle.addClass('clicker_hide');
	node.setStyle('display','none');
    } else {
	toggle.removeClass('clicker_hide');
	toggle.addClass('clicker_show');
	node.setStyle('display','block');
	var req = new Request.HTML({
            method: 'get',
	    url : url,
	    evalScripts:true,
            onRequest: function() { 
		node.set('text', 'Loading');     
		node.addClass('stub-ajax-loading');
	    },
	    onSuccess: function() {
		node.removeClass('stub-ajax-loading'); 
		node.empty();
		if (filter) {
		    node.adopt(this.response.elements.filter(filter))
		} else {
		    node.adopt(this.response.elements);     
		}
		if (this.options.evalScripts) Browser.exec(this.response.javascript);
	    }
	}).send();
    }
}   
