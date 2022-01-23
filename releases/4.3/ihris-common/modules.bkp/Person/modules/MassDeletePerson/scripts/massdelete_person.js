var removed_list = Array();
var old_styles = Array();
var old_text = Array();

function togglePerson( node, person, surname, firstname ) {
    removed_list[person] = node;
    old_text[person] = node.get('html');
    node.set({'html':'Removed'});
    old_styles[person] = node.getStyle('color');
    node.setStyle('color', 'red');
    if ( !$('display_'+person) ) {
        var addit = new Element('div', {id:'display_'+person,html:surname+', '+firstname+' - '});
        var removelink = new Element('a', {onclick:'removePerson("'+person+'");',html:'Remove'});
        removelink.inject(addit);
        addit.inject($('search_list'));
    }
    if ( $('opt_'+person) ) {
        $('opt_'+person).setAttribute('selected','selected');
    } else {
        var addopt = new Option( surname+', '+firstname, person );
        addopt.setAttribute('selected','selected');
        addopt.setAttribute('id','opt_'+person);
        $('search_select').add( addopt );
    }
}

function removePerson( person ) {
    if ( removed_list[person] ) {
        removed_list[person].set({'html':old_text[person]});
        removed_list[person].setStyle('color', old_styles[person]);
    }
    $('opt_'+person).removeAttribute('selected');
    $('display_'+person).dispose();
}
