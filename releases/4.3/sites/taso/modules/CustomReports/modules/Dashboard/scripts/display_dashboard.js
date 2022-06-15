function ajaxLoadReports( node, reportView, reportTitle ) {
    node = $(node);
    if (!node) {
        return false;
    }
    node.removeEvents('click');
    node = node.parentNode.parentNode.parentNode;

    var url = Stub.urlencode('CustomReports/show/' +  reportView);
    var req = new Request.HTML({
        method: 'get',
	url : 'index.php/stub/id?request=' + url +  '&content=report' ,
	evalScripts:true,
        onRequest: function() { 
	    node.set('text', 'Loading '+ reportTitle);     
	    node.addClass('stub-ajax-loading');
	},
        update: node,
        onComplete: function(response) { 
	    node.removeClass('stub-ajax-loading'); 
	    node.replace(reposnse);     
	}
    }).send();
}   