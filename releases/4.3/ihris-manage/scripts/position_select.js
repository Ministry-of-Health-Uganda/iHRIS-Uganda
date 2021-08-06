/*
function updatePosition( field, top_value, secondary_default ) {
	updateStackMenu( field, top_value, secondary_default );
}
*/
function updateButtons( field, idx ) {
    if ( field == "id" ) {
	    submit_add = document.getElementById( "submit_add" );
	    submit_view = document.getElementById( "submit_view" );
	    status_discontinue = document.getElementById( "status_discontinue" );
	    status_open = document.getElementById( "status_open" );
        if ( idx == 0 ) {
            showAndEnable( submit_add );
            hideAndDisable( submit_view );
            hideAndDisable( status_open );
            hideAndDisable( status_discontinue );
        } else {
            showAndEnable( submit_view );
            hideAndDisable( submit_add );
            select = document.getElementById( "position_select_status" );
            statusValue = select.options[select.selectedIndex].value;
            if ( statusValue == 1 ) {
                showAndEnable ( status_discontinue );
                hideAndDisable ( status_open );
            } else if ( statusValue == 3 ) {
                showAndEnable ( status_open );
                hideAndDisable ( status_discontinue );
            } else {
                hideAndDisable ( status_open );
                hideAndDisable ( status_discontinue );
            }
        }
    }
}

function hideAndDisable( button ) {
    if ( button ) {
        button.disabled = true;
        button.style.display = "none";
        button.style.visibility = "hidden";
    }
}
function showAndEnable( button ) {
    if ( button ) {
        button.disabled = false;
        button.style.display = "inline";
        button.style.visibility = "visible";
    }
}
