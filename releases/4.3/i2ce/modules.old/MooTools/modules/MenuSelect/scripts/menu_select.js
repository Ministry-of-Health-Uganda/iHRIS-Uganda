if (!menu_entries) {
    var menu_entries = new Array();
}
function updateStackMenu( field, top_value, secondary_default ) {
    var menu_options = menu_entries[ field ];
    var secondary = document.getElementById( field );
    secondary.options.length = 0;
    if ( top_value == "" ) {
        secondary.options[0] = new Option( secondary_default, "" );
    } else {
        secondary.options[0] = new Option( "Select One", "" );
	var idx = 1;
	var list = menu_options[top_value];
	if (list) { //there may not be any sub options defined
	    list.each(function(name,i) {
		secondary.options[idx] = new Option( name[1],name[0]);
		idx++;
            });
	}
    }
    if ( secondary["onchange"] ) {
	    secondary.onchange();
    }
}


