var removed_list = Array();
var old_styles = Array();
var old_text = Array();

function toggleFacility( node, facility, name ) {
    removed_list[facility] = node;
    old_text[facility] = node.get('html');
    node.set({'html':'Removed'});
    old_styles[facility] = node.getStyle('color');
    node.setStyle('color', 'red');
    if ( !$('display_'+facility) ) {
        var addit = new Element('div', {id:'display_'+facility,html:name+' - '});
        var removelink = new Element('a', {onclick:'removeFacility("'+facility+'");',html:'Remove'});
        removelink.inject(addit);
        addit.inject($('facility_list'));
    }
    if ( $('opt_'+facility) ) {
        $('opt_'+facility).setAttribute('selected','selected');
    } else {
        var addopt = new Option( name, facility );
        addopt.setAttribute('selected','selected');
        addopt.setAttribute('id','opt_'+facility);
        $('facility_select').add( addopt );
    }
}

function removeFacility( facility ) {
    if ( removed_list[facility] ) {
        removed_list[facility].set({'html':old_text[facility]});
        removed_list[facility].setStyle('color', old_styles[facility]);
    }
    $('opt_'+facility).removeAttribute('selected');
    $('display_'+facility).dispose();
}
