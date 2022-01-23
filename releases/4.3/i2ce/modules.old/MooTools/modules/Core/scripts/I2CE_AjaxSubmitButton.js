//note: requires mootools-more
function I2CE_AjaxSubmitButton(node,name,value,source_filter,target) {
    //walkup the nodes until we find a form element to submit from
    var formNode = false;
    var inputNode = false;
    node = $(node);
    if (!$defined(node)) {
 	return false;
    }
    if (node.hasClass('button_disabled')) {
	return false;
    }
    formNode = node.getParent('form');

    if (!$defined(formNode)) {
	return false;
    }

    var given_action = formNode.getProperty('action');	
    var given_method = formNode.getProperty('method');
    var classVals = {action:given_action,method:given_method};
    node.loadClassValues(classVals);
    formNode.setProperty('action',classVals.action);
    formNode.setProperty('method',classVals.method);

    inputNode = formNode.getElement('input[name=' + name + ']');
    if (!$defined(inputNode)) {
	inputNode = new Element('input', {
	    'type':'hidden',
	    'name':name
	});
	formNode.appendChild(inputNode);
    }
    inputNode.set('value',value);
    //formNode.submit();
    //var request = new Form.Request(formNode,target,{'filter':source_filter});
    var content = $(target);
    if (!content) {
	return false;
    }
    
    content.addClass('stub-ajax-loading');
    content.empty();
    node.addClass('button_disabled');
    var enable = function() {node.removeClass('button_disabled');};
    enable.delay(5000); //renable button after 5 seconds

    var request = new Form.Request(
	formNode,null,
	{
	    'update': null,
	    'append': null,
	    'link': 'cancel',
	    'onComplete': function() {
		node.removeClass('button_disabled');
	    },
	    'onFailure': function() {
		node.removeClass('button_disabled');
	    },

	    'onSuccess': function() {
		node.removeClass('button_disabled');
		var content = $(target);
		if (!content) {
		    return;
		}
		content.empty();
		var ajax_content = this.request.response.elements.filter(source_filter);		    
		if (!ajax_content) {
		    content.appendText("Error Loading");			
		    return;
		}
		$exec(this.request.response.javascript);

		ajax_content.inject(content);
		content.removeClass('stub-ajax-loading');		    
	    }
	}    
    );
    request.send();
    return true;
}