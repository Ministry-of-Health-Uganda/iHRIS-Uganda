function I2CE_SubmitButton(node,name,value) {
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
    node.addClass('button_disabled');
    var enable = function() {node.removeClass('button_disabled');};
    enable.delay(5000); //renable button after 5 seconds
    //node.removeAttribute('onclick');
    //node.removeEvents('click');
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
    formNode.submit();
    return true;
}