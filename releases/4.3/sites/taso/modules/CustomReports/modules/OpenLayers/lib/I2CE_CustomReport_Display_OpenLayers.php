<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
*  I2CE_CustomReport_Display_OpenLayers
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1.11
* @access public
*/


class I2CE_CustomReport_Display_OpenLayers extends I2CE_CustomReport_Display{

    /**
     * Process the default options to make sure checkbox non-set values are there.
     * @param array $get
     * @param array $options
     */
    protected function getDefaultOptions( $get, $options=array() ) {
        if ( array_key_exists( 'layers', $get ) && is_array( $get['layers'] ) ) {
            foreach( $get['layers'] as $layer => &$data ) {
                if ( !array_key_exists( 'enable', $data ) ) {
                    $data['enable'] = 'off';
                }
                if ( !array_key_exists( 'style', $data ) ) {
                    $data['style'] = 'off';
                }
            }
        }

        return parent::getDefaultOptions($get,$options);
    }

    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into
     * @param boolean $processResults Defaults to true meaning we run through the results
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display($contentNode,$processResults = true, $controls = null) {
            $parent_return = parent::display($contentNode,false,$controls); 
            //we want to do the reference to the flash content (this will call processResults below)
            // -- This doesn't seem to call processResults anymore so adding in the chart stuff here (?)
            
            if ( array_key_exists( 'results_id', $this->defaultOptions ) && $this->defaultOptions['results_id'] ) {
                $results_id = $this->defaultOptions['results_id'];
                $report_results = 'report_results_' . $results_id;
                $report_map = "report_results_{$results_id}_map";
                $report_style = "report_results_{$results_id}_style_edit";
                $report_display = "report_results_{$results_id}_display";
                $this->template->setAttribute( 'id', $report_results, 'report_results', null, $contentNode );
                $this->template->setAttribute( 'id', $report_map, 'report_results_map', null, $contentNode );
                $this->template->setAttribute( 'id', $report_style, 'report_results_style_edit', null, $contentNode );
                $this->template->setAttribute( 'id', $report_display, 'report_results_display', null, $contentNode );
            } else {
                $report_results = 'report_results';
                $report_map = 'report_results_map';
                $report_style = 'report_results_style_edit';
                $report_display = 'report_results_display';
            }

            $this->template->setAttribute( 'style', 'width: ' . $this->defaultOptions['width'] 
                    . 'px; height: ' . $this->defaultOptions['height'] . 'px;', $report_map, null, $contentNode );
            $this->template->setAttribute( 'style', 'width: ' . $this->defaultOptions['width']
                    . 'px;', $report_display, null, $contentNode );

            $this->template->setDisplayData( 'openlayers_style_details_vector_button', false, $contentNode );

            $save_req = "export_style=RAWJSON&" . file_get_contents( "php://input" ) . "&" . $_SERVER['QUERY_STRING'];
            $save_defs = array( 'width', 'height' );
            foreach( $save_defs as $def ) {
                if ( array_key_exists( $def, $this->defaultOptions ) ) {
                    $save_req .= "&$def=" . urlencode($this->defaultOptions[$def]);
                }
            }
            $save_req = str_replace( '&save_options_as_default_view=1', '', $save_req );
            $req_key = md5($save_req);
            $_SESSION['req_query'][ $req_key ] = $save_req;
 
            $dataURL = "index.php/CustomReports/show/{$this->view}/Export?req_query=" . $req_key;


            $display_layers = array();
            $layers = array();
            if ( array_key_exists( 'layers', $this->defaultOptions ) && is_array( $this->defaultOptions['layers'] ) ) {
                $ol_config = I2CE::getConfig()->modules->OpenLayers;
                $layers = $this->defaultOptions['layers'];
                foreach( $layers as $layer => $layer_data ) {
                    $defaults = array();
                    if ( array_key_exists( 'openlayers', $this->defaultOptions ) && is_array( $this->defaultOptions['openlayers'] )
                            && array_key_exists( 'styles', $this->defaultOptions['openlayers'] )
                            && is_array( $this->defaultOptions['openlayers']['styles'] )
                            && array_key_exists( $layer, $this->defaultOptions['openlayers']['styles'] )
                            && is_array( $this->defaultOptions['openlayers']['styles'][$layer] ) ) {
                        $defaults = $this->defaultOptions['openlayers']['styles'][$layer];
                    }

                    if ( array_key_exists('enable', $layer_data) && $layer_data['enable'] == 'on' ) {
                        $order = 1;
                        $ol_config->setIfIsSet( $order, "default/layers/$layer/_order" );
                        $display_layers[$layer] = $order;
                        //$style_node = $this->template->appendFileById( "customReports_display_OpenLayers_layer_style.html", "div", $report_style, false, $contentNode );
                        $style_node = $this->template->appendFileById( "customReports_display_OpenLayers_layer_style.html", "div", 'report_results_style_layer', false, $contentNode );
                        $style_node->setAttribute( "id", "openlayers_layer_style_$layer" );
                        $display_name = $layer;
                        $ol_config->setIfIsSet( $display_name, "default/layers/$layer/_display" );
                        $this->template->setDisplayDataImmediate( "openlayers_layer_style_layer", $display_name, $style_node );
                        if ( array_key_exists('style', $layer_data) && $layer_data['style'] == 'on' ) {
                            $this->template->setDisplayDataImmediate( 'openlayers_style_details', true, $style_node );

                            $form_names = array( 'layer_id' => false, 'report_id' => false, 'threshold' => false, 
                                    'red' => true, 'green' => true );
                            foreach( $form_names as $name => $set_default ) {
                                $this->template->setAttribute( 'id', "{$name}_$layer", $name, null, $style_node );
                                $this->template->setAttribute( 'name', "openlayers:styles:$layer:$name", "{$name}_$layer", null, $style_node );
                                if ( $set_default && array_key_exists( $name, $defaults ) ) {
                                    $this->template->setAttribute( 'value', $defaults[$name], "{$name}_$layer", null, $style_node );
                                }
                            }

                            $layer_class = 'Vector';
                            $ol_config->setIfIsSet( $layer_class, "default/layers/$layer/_class" );
                            if ( $layer_class == 'Vector' ) {
                                $this->template->setDisplayDataImmediate( 'openlayers_style_details_vector', true, $style_node );
                                $this->template->setDisplayDataImmediate( 'openlayers_style_details_vector_button', true, $contentNode );
                            } else {
                                $this->template->setDisplayDataImmediate( 'openlayers_style_details_vector', false, $style_node );
                            }
                        } else {
                            $this->template->setDisplayDataImmediate( 'openlayers_style_details', false, $style_node );
                        }
                        $this->template->setAttribute( 'id', "layer_display_$layer", 'layer_display', null, $style_node );
                        $this->template->setAttribute( 'name', "openlayers:styles:$layer:layer_display[]", "layer_display_$layer", null, $style_node );
                    }
                }
                //$button = $this->template->appendFileById( "customReports_display_OpenLayers_layer_style_submit.html", "div", $report_style, false, $contentNode );
            } else {
                $this->template->userMessage("Unable to view report.", true);
                I2CE::raiseError("Invalid map data for report.");
                return true;
            }

            $maps = array( '_height' => $this->defaultOptions['height'], '_width' => $this->defaultOptions['width'],
                    'map' => array( 'target' => $report_map, 'layers' => array( '_order' => $display_layers ) ) );

            $ol_mod = I2CE_ModuleFactory::instance()->getClass('OpenLayers');
            $ol_mod->addMapDefaults($maps);

            $map_data = $ol_mod->processOptions( 'maps', $maps );
            if ( count( $map_data ) != 2 || !is_array( $map_data[1] ) ) {
                $this->template->userMessage("Unable to view report.", true);
                I2CE::raiseError("Invalid map data for report.");
                return true;
            }

            $js = <<<EOF
var report_data = {};
var style_lookup = {};
var feature_display = {};
var setMapStyle = function() {};

EOF;

            

            foreach( $map_data[1] as $prepend ) {
                $js .= "$prepend\n";
            }
            $js .= "window.addEvent('domready', function() {\n";
            $js .= $map_data[0] . "\n";

            $layer_load = '';
            $js .= <<<EOF

var raw_req = new Request.JSON( { url : '$dataURL',
        onSuccess: function( results ) {
        report_data = results;
        for( i in report_data.headers ) {
EOF;
            $has_defaults = false;
            foreach( $display_layers as $layer => $order ) {
                $defaults = array();
                if ( array_key_exists( 'openlayers', $this->defaultOptions ) && is_array( $this->defaultOptions['openlayers'] )
                        && array_key_exists( 'styles', $this->defaultOptions['openlayers'] )
                        && is_array( $this->defaultOptions['openlayers']['styles'] )
                        && array_key_exists( $layer, $this->defaultOptions['openlayers']['styles'] )
                        && is_array( $this->defaultOptions['openlayers']['styles'][$layer] ) ) {
                    $defaults = $this->defaultOptions['openlayers']['styles'][$layer];
                }

                $layer_load .= <<<EOF
            $layer.once('change', function() {
                window.addEvent('domready', function() {
                        var props = $layer.getSource().getFeatures().pick().getKeys();
                        props.each( function( item ) {
                            if ( item != 'geometry' ) {
                                if ( $('layer_id_$layer') ) {
                                    var opt = new Option( item, item );

EOF;
                if ( array_key_exists( 'layer_id', $defaults ) ) {
                    $has_defaults = true;
                    $layer_load .="     if( item == '" . $defaults['layer_id'] . "' ) opt.selected = true;\n";
                }
                $layer_load .= <<<EOF
                                    $('layer_id_$layer').add( opt );
                                    $('layer_id_$layer').disabled = false;
                                }
                                if ( $('layer_display_$layer') ) {
                                    var opt = new Option( item, item );

EOF;
                if ( array_key_exists( 'layer_display', $defaults ) ) {
                    $has_defaults = true;
                    $layer_load .="     if( " . json_encode(array_values($defaults['layer_display'])) . ".indexOf(item) != -1 ) opt.selected = true;\n";
                }
                $layer_load .= <<<EOF
                                    $('layer_display_$layer').add( opt );
                                    $('layer_display_$layer').disabled = false;
                                    $('openlayers_layer_style_$layer').show();
                                }
                            }
                        } );
                    });
                } );
EOF;
                $js .= <<<EOF
            if ( $('report_id_$layer') ) {
                var opt1 = new Option( results.headers[i], i );
                var opt2 = new Option( results.headers[i], i );

EOF;
                if ( array_key_exists( 'report_id', $defaults ) ) {
                    $has_defaults = true;
                    $js .="     if( i == '" . $defaults['report_id'] . "' ) opt1.selected = true;\n";
                }
                if ( array_key_exists( 'threshold', $defaults ) ) {
                    $has_defaults = true;
                    $js .="     if( i == '" . $defaults['threshold'] . "' ) opt2.selected = true;\n";
                }
                $js .= <<<EOF
                $('report_id_$layer').add( opt1 );
                if ( $('threshold_$layer') ) $('threshold_$layer').add( opt2 );
                $('report_id_$layer').disabled = false;
                if ( $('threshold_$layer') ) $('threshold_$layer').disabled = false;
            }
EOF;
            }
        
            $callStyle = '';
            if ( $has_defaults ) {
                $callStyle = "setMapStyle();";
            }

        $js .= <<<EOF
        }
        $callStyle
        },
        onError: function( text, error ) {
            console.log("Error: "+text);
        },
} ).get();

EOF;
            //$js .= $layer_load;


            $page_js = I2CE::getFileSearch()->search( 'SCRIPTS', 'openlayers_post_inline_report_view_' . $this->view . '.js' );
            if ( $page_js ) {
                $js .= file_get_contents( $page_js );
            }


            $js .= <<<EOF
setMapStyle = function() {
    var i, ii;
    for( i = 0, ii = report_data.data.length; i < ii; i++ ) {

EOF;
            $setStyle_js = "";
            foreach( $layers as $layer => $layer_data ) {
                if ( !array_key_exists( 'style', $layer_data ) || $layer_data['style'] != 'on' ) {
                    continue;
                }
                $poly_style = "{ style: null }";
                if ( array_key_exists( 'style', $maps['map']['layers'][$layer] ) ) {
                    $style_arr = $maps['map']['layers'][$layer]['style'];
                    $style_arr['fill']['color'] = array( 'func' => 'opaque' );
                    $style_data = $ol_mod->processOptions( 'style', $style_arr );
                    $poly_style = "{ " . $style_data[0] . " }";
                }

                $layer_class = 'Vector';
                $ol_config->setIfIsSet( $layer_class, "default/layers/$layer/_class" );
                if ( $layer_class == 'Vector' ) {

                    $setStyle_js .= <<<EOF
    $layer.setStyle( function( feature, resolution ) {
        var use_color = 'black';
        var opaque = 'rgba(0,0,0,0.5)';
        var red = parseFloat($('red_$layer').value);
        var green = parseFloat($('green_$layer').value);
        var name = feature.get( $('layer_id_$layer').value );
        if ( name && style_lookup['$layer'][name] ) {
            var val = parseFloat(style_lookup['$layer'][name]);
            if ( red <= green ) {
                if ( val <= red ) {
                    use_color = 'red';
                    opaque = 'rgba(255,0,0,0.5)';
                } else if ( val < green ) {
                    use_color = 'yellow';
                    opaque = 'rgba(255,255,0,0.5)';
                } else {
                    use_color = 'green'
                    opaque = 'rgba(0,255,0,0.5)';
                }
            } else {
                if ( val <= green ) {
                    use_color = 'green';
                    opaque = 'rgba(0,255,0,0.5)';
                } else if ( val < red ) {
                    use_color = 'yellow';
                    opaque = 'rgba(255,255,0,0.5)';
                } else {
                    use_color = 'red'
                    opaque = 'rgba(255,0,0,0.5)';
                }
            }
        }
        var type = feature.getGeometry().getType();
        if ( type == 'Point' ) {
            return [ new ol.style.Style({ image : new ol.style.Circle({ radius: 5, fill : new ol.style.Fill({color:use_color}) }) }) ];
        } else if ( type == 'Polygon' ) {
            var new_style = $poly_style;
            return [ new_style.style ];
        }
    } );

EOF;
                }
                $js .= <<<EOF
    if ( $('layer_id_$layer') && !$('layer_id_$layer').disabled ) {
        if ( !style_lookup['$layer'] ) style_lookup['$layer'] = {};

        if ( $('threshold_$layer') ) {
            style_lookup['$layer'][ report_data.data[i][ $('report_id_$layer').value ] ] = report_data.data[i][ $('threshold_$layer').value ];
        }
        if ( !feature_display['$layer'] ) feature_display['$layer'] = {};
        feature_display['$layer'][ report_data.data[i][ $('report_id_$layer').value ] ] = '';
        for( j in report_data.headers ) {
            feature_display['$layer'][ report_data.data[i][ $('report_id_$layer').value ] ] += report_data.headers[j] + ": " + report_data.data[i][j] + "<br />";
        }
    }

EOF;
            
            }
            $js .= <<<EOF
    }

    $setStyle_js
};

    map.on('click', function(evt) {
            var pixel = evt.pixel;
            var output = [];
            map.forEachFeatureAtPixel(pixel, function( feature, layer ) {
                var layer_var;
                switch( layer ) {

EOF;
            foreach( $display_layers as $layer => $order ) {
                $js .= "case $layer : layer_var = '$layer'; break;\n";
            }
            $js .= <<<EOF
                }
                if ( layer_var ) {
                    var layer_output = '';
                    if ( $('layer_display_'+layer_var) ) {
                        $('layer_display_'+layer_var).getSelected().each( function( opt ) {
                                if ( feature.get(opt.value) ) {
                                    layer_output += opt.value +": "+feature.get(opt.value)+"<br />";
                                }
                            });
                    }
                    if ( $('layer_id_'+layer_var ) ) {
                        if ( $('layer_id_'+layer_var) && !$('layer_id_'+layer_var).disabled 
                            && feature_display[layer_var] && feature.get( $('layer_id_'+layer_var).value )
                            && feature_display[layer_var][ feature.get( $('layer_id_'+layer_var).value ) ] ) {
                            layer_output += feature_display[layer_var][feature.get( $('layer_id_'+layer_var).value )];
                        }
                    }
                    if ( layer_output != '' ) output.push( layer_output );
                }
                } );
            if ( !output || output.length == 0 ) output = "&nbsp;";
            else output = output.join("<hr />");
            $('report_results_display').innerHTML = output;
            });


EOF;
            $js .= "\n});\n$layer_load\n";

            $this->template->addHeaderLink('mootools-core.js');
            $this->template->addHeaderLink('mootools-more.js');
            $this->template->addHeaderLink('ol.js');
            $this->template->addHeaderLink('ol.css');
            $this->template->addHeaderLink('i2ce_ol.css');
            $this->template->addHeaderLink('openlayers_base.js');
            $this->template->addHeaderText($js,'script','openlayers_report'); //add this to a new script node.

            return $parent_return;
    }

    /**
     * Abstract method that each display is resposbile for implementing.  Checks to see
     * if it can display the given view.
     * @returns boolean
     */
    protected function canView() {
        return true;
    }


    /**
     * Adds any controls for this display to the content node.
     * @param DOMNode $contentNode 
     * @returns boolean;
     */
    protected function displayReportControl($contentNode) {
        parent::displayReportControl($contentNode);
        $js = '';

        $avail_fields = $this->getReportViewDisplayedFields( false, array( '' ) );
        $numeric = $this->findNumericFields();
        if ( $this->defaultOptions['total'] ) {
            $total = 'Total';
            I2CE::getConfig()->setIfIsSet($total, "/modules/CustomReports/text/headers/count" );
            $numeric['total'] = array( 'header' => $total );
        }


        $ol_config = I2CE::getConfig()->modules->OpenLayers;

        if ( $ol_config->is_parent('default/layers') ) {
            $layers = $ol_config->default->layers->getAsArray();
            foreach( $layers as $layer => $layer_data ) {
                $layer_node = $this->template->appendFileById( "customReports_display_control_OpenLayers_layer.html", "div", "openlayers_layers", false, $contentNode );
                $display = $layer;
                if ( array_key_exists( '_display', $layer_data ) ) {
                    $display = $layer_data['_display'];
                }
                $this->template->setDisplayDataImmediate( "openlayers_layer_name", $display, $layer_node );
                $defaults = array();
                if ( array_key_exists( 'layers', $this->defaultOptions ) && is_array( $this->defaultOptions['layers'] )
                        && array_key_exists( $layer, $this->defaultOptions['layers'] ) 
                        && is_array( $this->defaultOptions['layers'][$layer] ) ) {
                    $defaults = $this->defaultOptions['layers'][$layer];
                }
                $renames = array( "layers_enable" => "enable", "layers_style" => "style", "layers_exist" => "exist" );
                $checkbox = array( "enable" => true, "style" => true );
                foreach( $renames as $id => $new_id ) {
                    $this->template->setAttribute( "id", "layers:$layer:$new_id", $id, null, $layer_node );
                    $this->template->setAttribute( "name", "layers:$layer:$new_id", "layers:$layer:$new_id", null, $layer_node );
                    if ( array_key_exists( $new_id, $defaults ) && $defaults[$new_id] == 'on' ) {
                        if ( array_key_exists( $new_id, $checkbox ) && $checkbox[$new_id] ) {
                            $this->template->setAttribute("checked", "checked", "layers:$layer:$new_id", null, $layer_node );
                            $this->template->setAttribute( "id", "layers:$layer:{$new_id}_for", "layers_{$new_id}_for", null, $layer_node );
                            $this->template->setAttribute( "for", "layers:$layer:$new_id", "layers:$layer:{$new_id}_for", null, $layer_node );
                        } else {
                            $this->template->setAttribute("value", $defaults[$new_id], "layers:$layer:$new_id", null, $layer_node );
                        }
                    }
                }
            }

        }

        /*
        $selects = $this->template->query('.//select[@class="reportformfield" or @class="reportformfield_numeric"]',$contentNode);
        for ($i=0; $i < $selects->length; $i++) {
            $selectNode = $selects->item($i);
            $keys = explode(':',$selectNode->getAttribute('name'));
            //Example:  <select name="displayFieldsTypes:one_row_total:0:formfield" class='reportformfield'>
            $selected = $this->defaultOptions;
            while (count($keys) > 0) {
                $key = array_shift($keys);
                if (!is_array($selected) || !array_key_exists($key,$selected)) {
                    array_unshift($keys,$key);
                    break;
                }
                $selected = $selected[$key];
            }
            if (!is_scalar($selected) || count($keys) > 0 || (is_string($selected) && strlen($selected) == 0)) {
                $selected = false;
            }
            if ($selectNode->getAttribute('class') == 'reportformfield') {
                $rffs = $avail_fields;
            } else {
                $rffs = $numeric;
            }
            if ($selected && !array_key_exists($selected,$rffs)) {
                $selected = false;
            }
            foreach ($rffs as $reportformfield=>$data) {
                if ( !$data ) {
                    continue;
                }
                $options = array('value'=>$reportformfield);
                if (($selected == $reportformfield)) { //make the first one selected or the current one selected
                    $options['selected'] = 'selected';
                }
                $selectNode->appendChild($this->template->createElement('option',$options,$data['header']));
            }
        }

        $selects = $this->template->query('.//select[@class="reportformfield" or @class="reportformfield_numeric" or @class="reportformfield_aggregate"]',$contentNode);
        for ($i=0; $i < $selects->length; $i++) {
            $selectNode = $selects->item($i);
            $selected = $this->defaultOptions;
            $keys = explode(':',$selectNode->getAttribute('name'));
            while (count($keys) > 0) {
                $key = array_shift($keys);
                if (!is_array($selected) || !array_key_exists($key,$selected)) {
                    array_unshift($keys,$key);
                    break;
                }
                $selected = $selected[$key];
            }
            if (!is_scalar($selected) || count($keys) > 0 || (is_string($selected) && strlen($selected) == 0)) {
                $selected = false;
            }
            if ( $selected === false ) {
                continue;
            }
            $options = $this->template->query(".//option[@value='$selected']",$selectNode);
            if ($options->length == 1) {
                $selected_options = $this->template->query( ".//option[@selected]", $selectNode );
                //remove any existing selections
                for( $j = 0; $j < $selected_options->length; $j++ ) {
                    $selected_options->item($j)->removeAttribute('selected');
                }
                $options->item(0)->setAttribute('selected','selected');
            }           
        }
        */
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
