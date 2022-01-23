var I2CE_Ajax_List_Selectors = new Object();
I2CE_Ajax_List_Selectors.elements = Array();
I2CE_Ajax_List_Selectors.text = Array();
I2CE_Ajax_List_Selectors.requests = Array();
function setupAjaxList( element, formfield, form, count, 
        blank_text, load_text, no_results_text, default_text, no_suffix, style ) {
    //if ( !blank_text ) blank_text = 'Select One';
    if ( !load_text ) load_text = 'Loading';
    if ( !no_results_text ) no_results_text = 'No results';
    I2CE_Ajax_List_Selectors.elements[element] = count;
    I2CE_Ajax_List_Selectors.text[element] = Array(
            blank_text, load_text, no_results_text, default_text, style
            );
    populateAjaxList( null, element, form+'_0', formfield, form, '', '', no_suffix );

}

function populateAjaxList( caller, element_base, element_suffix, formfield, form, 
        limit_field, limit_value, no_suffix ) {
    if ( !limit_field ) limit_field = '';
    if ( !limit_value ) limit_value = '';
    var element;

    if ( no_suffix ) element = element_base;
    else element = element_base+'_'+element_suffix;

    var select = $(element);
    if ( !select ) {
        alert('no element for '+element);
        return;
    }
    var name_node = $('name_'+element);
    var name_text = '';
    if ( name_node ) name_text = name_node.get('text');

    if ( limit_field != '' && limit_value == '' ) {
        //alert('clearing '+element);
        // Just clear out the following one.
                // To call onChange for the next level
                //select.selectedIndex = 0;
        if ( select.isVisible() ) {
            if ( select.selectedIndex > 0 ) {
                select.selectedIndex = 0;
                select.fireEvent('change');
            }
            select.empty();
            select.disabled = true;
            select.hide();
        }
    } else {
    
        var style = I2CE_Ajax_List_Selectors.text[element_base][4];
        var request_url = "index.php/web-services/lists/"+form+(formfield==''?'':"/"+formfield+(style?"/"+style:''))+(limit_field==''?'':"?"+limit_field+"="+limit_value);
        if ( I2CE_Ajax_List_Selectors.requests[element] &&
                I2CE_Ajax_List_Selectors.requests[element].isRunning() ) {
                    // If there is already a request running, then cancel it before starting a new one.
                    I2CE_Ajax_List_Selectors.requests[element].cancel();
        }
        select.empty();
        curr_val = null;
        if ( caller && limit_value != '' ) {
            curr_val = caller.options[caller.selectedIndex].text;
        }
        select.add( new Option( I2CE_Ajax_List_Selectors.text[element_base][1]+': '+name_text+(curr_val?' ('+curr_val+')':''), '' ) );
        select.show('block');
        //alert("trying " +request_url+" for "+element);
        
        I2CE_Ajax_List_Selectors.requests[element] = new Request.JSON( {url: request_url,
            onSuccess: function( results ) {
                //var select = $(element);
                select.empty();
                if ( results.length == 0 ) {
                    select.disabled = true;
                    select.add( new Option( I2CE_Ajax_List_Selectors.text[element_base][2]+': '+name_text+(curr_val?' ('+curr_val+')':''), '' ) );
                    select.selectedIndex = 0;
                    select.fireEvent('change');
                } else {
                    var default_node = $('default_'+element);
                    var default_val = '';
                    if ( default_node ) default_val = default_node.get('text');
                    select.disabled = false;
                    multiple = false;
                    mult_val = Array();
                    if ( I2CE_Ajax_List_Selectors.text[element_base][0] != '' ) {
                        select.add( new Option( I2CE_Ajax_List_Selectors.text[element_base][0]+': '+name_text+(curr_val?' ('+curr_val+')':''), '' ) );
                    } else {
                        multiple = true;
                        default_val = default_val.split(',');
                    }
                    for( var key in results.data ) {
                        var opt = new Option( results.data[key], key );
                        if ( ( multiple ? default_val.indexOf(key) != -1 : key == default_val ) ) opt.selected = true;
                        select.add( opt );
                    }
                    select.fireEvent('change');
                }
            },
            onError: function(text,error) {
            },
        }).get();
    }
}

function selectAjaxList( caller, element, count ) {
    if ( caller.options[caller.selectedIndex].value != '' ) {
        $(element).set( 'value', caller.options[caller.selectedIndex].value );
        $(element+'_display').set( 'text', caller.options[caller.selectedIndex].text );
        $(element+'_clear').show('inline');
        if ( count ) {
            for( i = 1; i <= I2CE_Ajax_List_Selectors.elements[element]; i++ ) {
                if ( i != count ) {
                    if ( $(element+'_'+i) ) $(element+'_'+i).selectedIndex = 0;
                }
            }
        }
    }
}

function resetAjaxList( element ) {
    $(element).set( 'value', '' );
    $(element+'_display').set( 'text', I2CE_Ajax_List_Selectors.text[element][3] );
    for( i = 1; i <= I2CE_Ajax_List_Selectors.elements[element]; i++ ) {
        if ( $(element+'_'+i) ) $(element+'_'+i).selectedIndex = 0;
    }
    $(element+'_clear').hide();
}
