function enablePositionNode( enable_id, disable_id ) {
    enable_node = document.getElementById( enable_id );
    disable_node = document.getElementById( disable_id );
    enable_tab = document.getElementById( enable_id+'_tab' );
    disable_tab = document.getElementById( disable_id+'_tab' );
    if ( enable_node && disable_node ) {
        enable_node.style.display = 'inline';
        if ( enable_tab ) enable_tab.className = 'active';
        disable_node.style.display = 'none';
        if ( disable_tab ) disable_tab.className = 'select';
    }
}
function setupPositionNode( enable_id, disable_id, hide_tab ) {
    enable_button = document.getElementById( enable_id+'_button' );
    disable_button = document.getElementById( disable_id+'_button' );
    if ( hide_tab != '' ) {
        tab_node = document.getElementById( hide_tab );
        if ( tab_node ) {
            tab_node.style.display = 'none';
        }
    }
    if ( enable_button && disable_button ) {
        enable_button.set('checked', true);
        disable_button.set('checked', false);
    }
    enablePositionNode( enable_id, disable_id );
}
