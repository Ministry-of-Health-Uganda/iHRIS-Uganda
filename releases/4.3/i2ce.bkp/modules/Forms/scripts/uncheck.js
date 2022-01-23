function uncheck( id ) {
    document.getElementById( id ).checked = false;
}
function check( obj, id ) {
    if ( obj.checked ) {
        document.getElementById( id ).value = "";
    }
}
