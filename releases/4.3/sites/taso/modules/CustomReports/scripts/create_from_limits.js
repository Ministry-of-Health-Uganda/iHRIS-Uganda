var createFormFromSearch = function(element,form) {
    element = $(element);
    var limit_form = $('limit_form');
    if (!element || !limit_form || !form)  {
        return false;
    }
    var href = element.get('href');
    var queryString = [];
    limit_form.getElements('input, select, textarea').each(function(el){
    	var type = el.type;
    	var name = el.get('name');
	if (!name) {
	    return;
	}
    	var s_pos = name.indexOf('+');
    	//limits:primary_form+surname:contains:value
    	if ( el.disabled 
    	    || type == 'submit' 
    	    || type == 'reset' 
    	    || type == 'file' 
    	    || type == 'image'
    	    || s_pos <= 2
    	    || name.substring(0,s_pos+1) != 'limits:primary_form+'
    	   ) {
    	    return;
    	}
    	name = name.substring(s_pos + 1);
    	s_pos = name.indexOf(':');
    	var e_pos = name.lastIndexOf(':');
    	var field = name.substring(0,s_pos);
    	var style = name.substring(s_pos+1,e_pos);
    	var data = name.substring(e_pos + 1);
    	if (data != 'value' || (style != 'equals' && style != 'lowerequals' && style != 'contains')) {
    	    return;
    	}
    	var value = null;
    	if (el.get('tag') == 'select') {
    	    value = el.getSelected().map(function(opt){
    		return document.id(opt).get('value');
    	    });
    	} else if ((type == 'radio' || type == 'checkbox') && !el.checked) {
    	    value = null;
    	} else {
    	    value = el.get('value');
    	}
	Array.from(value).each(function(val){
    	    if (typeof val != 'undefined') {
    		queryString.push(encodeURIComponent('form[' + form + '][0][0][fields][' + field + ']')  + '=' + encodeURIComponent(val));
    	    }
    	});
    });
    element.set('href',href +  '?' + queryString.join('&'));
    return false;
}

