<?php
/**
 * @copyright © 2014 Intrahealth International, Inc.
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
*  I2CE_CustomReport_Display_Datasource
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.2
* @access public
*/


class I2CE_CustomReport_Display_Datasource extends I2CE_CustomReport_Display{

    /***
     * @var array The report data
     */
    protected $data;

    /**
     * @var array An array with values the name of the columns used for this display
     */
    protected $displayedFields;

    /**
     * @var array The selected fields for the visualization
     */
    protected $visualizedFields;



    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into
     * @param boolean $processResults Defaults to true meaning we run through the results
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display($contentNode,$processResults = true, $controls = null) {
        // Since this is just data we don't use the content node.
        return parent::display(false,true, $controls); 
    }



    /**
     * Process results
     * @param array $results_data an array of results.  indices are 'results' and Buffered result and 'num_results' the
     * number of results.  (these values may be false on failure)
     * @param DOMNode $contentNode.  Default to null a node to append the results onto
     */
    protected function processResults($results_data,$contentNode=null) {
        $this->preProcessResults();
        parent::processResults($results_data,$contentNode);
        $this->postProcessResults();
        if ( count( $this->data['table']['rows'] ) < 1 ) {
            $this->sendError();
        } else {
            $this->sendData();
        }
    }

    /**
     * Send an error message to the visualization
     * @param string $msg_key
     */
    protected function sendError( $msg_key = 'error_message' ) {
        unset( $this->data['table'] );
        $this->data['status'] = 'error';
        $message = 'An unknown error has occurred.';
        I2CE::getConfig()->setIfIsSet( $message, "/modules/CustomReports/displays/Visualization/$msg_key" );
        $this->data['errors'] = array( array( 'reason' => 'other', 'message' => $message ) );
        $this->sendData();
     }

    /**
     * Output the data and exit
     */
    protected function sendData() {
        if ( array_key_exists( 'table', $this->data ) ) {
            $sig = md5(serialize($this->data['table']));
            if ( array_key_exists('sig', $this->data) ) {
                if ( $sig == $this->data['sig'] ) {
                    $this->data['status'] = 'error';
                    $this->data['errors'] = array( array( 'reason' => 'not_modified', 'message' => 'Not modified', 'detailed_message' => 'It was the same data.' ) );
                    $this->data['reason'] = 'not_modified';
                    unset( $this->data['table'] );
                }
            }
            $this->data['sig'] = $sig;
        }

        header('Content-type: application/json');
        echo json_encode( $this->data );
        exit();
    }

    /**
     * @var boolean Set to true if there are three columns instead of two.
     */
    protected $multi_row;

    /**
     * @var array Holding location for multi row data.
     */
    protected $holding;

    /**
     * Set up the data before processing results
     */
    protected function preProcessResults() {
        $this->displayedFields = $this->getDisplayFieldsData();
        $visualizedCount = count( $this->visualizedFields );
        if ( $visualizedCount == 2 ) { 
            $this->multi_row = false;
            $visFirst = true;
            foreach( $this->visualizedFields as $field ) {
                $data = $this->displayedFields[$field];
                if ( $data && is_array( $data ) && array_key_exists( 'header', $data ) ) {
                    $objs = $this->getFormFieldObjects( $field );
                    $type = 'string';
                    if ( $objs[1] && $objs[1] instanceof I2CE_FormField ) {
                        $i2ce_type = $objs[1]->getTypeString();
                        if ( $i2ce_type == 'integer' || $i2ce_type == 'float' ) {
                            $type = 'number';
                        }
                    } elseif ( $field == 'total' ) {
                        $type = 'number';
                    }
                    $this->data['table']['cols'][] = array( 'id' => $field, 'label' => $data['header'], 'type' => $type );
                    if ( $visFirst ) {
                        foreach( array( 'hAxis', 'vAxis' ) as $type ) {
                            $opts = $this->defaultOptions['styles'][ $this->defaultOptions['style'] ]['visualization_options'];
                            if ( array_key_exists( $type, $opts ) && is_array( $opts[$type] )
                                    && array_key_exists( 'title', $opts[$type] ) ) {
                                if ( $opts[$type]['title'] ) {
                                    $this->data['table']['p'][$type] = array( 'title' => $data['header'] );
                                } else {
                                    $this->data['table']['p'][$type] = array( 'title' => '' );
                                }
                            }
                        }
                        $visFirst = false;
                    }
                } 
            }
            $this->reverseOrder = $this->defaultOptions['reverseOrder'];
        } elseif ( $visualizedCount == 3 ) { 
            $this->multi_row = true;
            $this->holding = array( 'cols' => array(), 'rows' => array(), 'data' => array() );
            $field = $this->visualizedFields[0];
            $data = $this->displayedFields[ $field ];
            if ( $data && is_array( $data ) && array_key_exists( 'header', $data ) ) {
                $this->data['table']['cols'][] = array( 'id' => $field, 'label' => $data['header'], 'type' => 'string' );
                            $opts = $this->defaultOptions['styles'][ $this->defaultOptions['style'] ]['visualization_options'];
                foreach( array( 'hAxis', 'vAxis' ) as $type ) {
                    if ( array_key_exists( $type, $opts ) && is_array( $opts[$type] )
                            && array_key_exists( 'title', $opts[$type] ) ) {
                        if ( $opts[$type]['title'] ) {
                            $this->data['table']['p'][$type] = array( 'title' => $data['header'] );
                        } else {
                            $this->data['table']['p'][$type] = array( 'title' => '' );
                        }
                    }
                }
            }
        } else {
            $this->sendError( 'invalid_fields' );
        }
    }

    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        $mapped_row = $this->mapResults($row);
        if ( $this->multi_row ) {
            $primary = $mapped_row[ $this->visualizedFields[0] ];
            $secondary = $mapped_row[ $this->visualizedFields[1] ];
            $number = $mapped_row[ $this->visualizedFields[2] ];

            $this->holding['cols'][$primary] = true;
            $this->holding['rows'][$secondary] = true;
            if ( !array_key_exists( $primary, $this->holding['data'] ) ) {
                $this->holding['data'][$primary] = array();
            }
            $this->holding['data'][$primary][$secondary] = $number;

            /*
            //$current['c'][0] = array( "v" => $primary, "f" => null );
            if ( !array_key_exists( $secondary, $this->fieldIndices['cols'] ) ) {
                $this->fieldIndices['cols'][ $secondary ] = $this->currentIndices['cols']++;
                $col_idx = $this->fieldIndices['cols'][$secondary];
                $this->data['table']['cols'][$col_idx] = array( 'id' => $col_idx, 'label' => $secondary, 'type' => 'number' );
            } else {
                $col_idx = $this->fieldIndices['cols'][$secondary];
            }
            if ( !array_key_exists( $primary, $this->fieldIndices['rows'] ) ) {
                //$current = array( "c" => array() );
                $this->fieldIndices['rows'][ $primary ] = $this->currentIndices['rows']++;
                $row_idx = $this->fieldIndices['rows'][$primary];
                $this->data['table']['rows'][$row_idx] = array( "c" => array( array( "v" => $primary, "f" => null ) ) );
            } else {
                $row_idx = $this->fieldIndices['rows'][$primary];
            }
            $this->data['table']['rows'][$row_idx]["c"][$col_idx] = array( "v" => $number+0, "f" => null );
            */

        } else {
            $current = array( "c" => array() );
            foreach( $this->data['table']['cols'] as $col ) {
                if ( $col['type'] == 'number' ) {
                    $current['c'][] = array( "v" => $mapped_row[$col['id']]+0, "f" => null );
                } else {
                    $current['c'][] = array( "v" => $mapped_row[$col['id']], "f" => null );
                }
            }
            $this->data['table']['rows'][] = $current;
        }
        return true;
    }
    
    /**
     * Post process any results
     */
    protected function postProcessResults() {
        if ( $this->multi_row ) {
            foreach( $this->holding['rows'] as $secondary => $tmp ) {
                $this->data['table']['cols'][] = array( 'id' => $secondary, 'label' => $secondary, 'type' => 'number' );
            }
            foreach( $this->holding['cols'] as $primary => $tmp ) {
                $current = array( "c" => array(
                            array( "v" => $primary, "f" => null )
                            ) );
                foreach( $this->holding['rows'] as $secondary => $tmp ) {
                    if ( array_key_exists( $secondary, $this->holding['data'][$primary] ) ) {
                        $data = $this->holding['data'][$primary][$secondary]+0;
                    } else {
                        $data = 0;
                    }
                    $current['c'][] = array( "v" => $data, "f" => null );
                }
                $this->data['table']['rows'][] = $current;
            }
        }
    }


    /**
     * returns an array of the default display options.  Default options are read and overriddenb
     * in the following order:
     * /modules/CustomReports/displays/$display/display_options
     * /modules/CustomReports/relationships/display_options/$display
     * /modules/CustomReports/reports/display_options/$display
     * /modules/CustomReports/reportViews/$view/display_options/$display
     * Finally any options that have a key in $get are replaced by that value
     * @param array $get
     * @param array $options. Default to the empty array.  The options that we want to be already set before we start goinng through
     * @returns array
     */
    protected function getDefaultOptions($get,$options = array()) {
        ; //make sure we get these values from the get variables
        $make_exist = array(
            'reverseOrder'=>false,
            'displayFieldsType'=>null,
            'displayFieldsTypes'=>array(),
            'displayFields'=>array(),
            'limit_page' => 1,
            'limit_per_page' => false,
            'limit_offset' => 0,
            'limit_amount' => false,
            'limit_paginated' => false,
            );
        foreach ($make_exist as $key=>$val) {
            if (!array_key_exists($key,$options)) {
                $options[$key] = $val;
            }
        }
        $t_options = array();
        if ( I2CE::getConfig()->setIfIsSet( $t_options, "/modules/CustomReports/displays/Visualization/display_options", true ) ) {
            I2CE_Util::merge_recursive( $options, $t_options );
        }
        $t_options = array();
        if ( $this->config->setIfIsSet( $t_options, "display_options/Visualization", true ) ) {
            I2CE_Util::merge_recursive( $options, $t_options );
        }
        $defaultOptions = parent::getDefaultOptions($get,$options);
        $this->defaultOptions = $defaultOptions;
        $this->findNumericFields();
        $this->makeVisualizationStylesSane( $defaultOptions );
        if ( count( $defaultOptions['styles'] ) == 0 ) {
            I2CE::raiseError( "No valid styles for Visualization!" );
            return false;
        }
        if ( !$this->ensureValidStyleAndType( $defaultOptions ) ) {
            I2CE::raiseError( "No valid style and type can be chosen for Visualization" );
            return false;
        }
        if ( !$this->setupVisualization( $defaultOptions ) ) {
            I2CE::raiseError( "Could not set up visualization options!" );
            return false;
        }
        // Currently don't allow saving datasource as default view since Visualization passes this through
        unset( $defaultOptions['save_options_as_default_view'] );
        return  $defaultOptions;
    }

    /**
     * Make sure any required data is set on the style before allowing it.
     * @param array &$defaultOptions pointer to the default options
     */
    protected function makeVisualizationStylesSane( &$defaultOptions ) {
        foreach( $defaultOptions['styles'] as $style => $data ) {
            if ( !is_array( $data ) || !array_key_exists( 'visualization_type_options', $data )
                    || !is_array( $data['visualization_type_options'] ) ) {
                continue;
            }
            if ( !array_key_exists( 'visualization_options', $data ) || !is_array( $data['visualization_options'] ) ) {
                $defaultOptions['styles'][$style]['visualization_options'] = array();
            }
            $vis_types = array();
            foreach( $data['visualization_type_options'] as $vis_type ) {
                if ( substr( $vis_type, -8 ) == '_numeric' ) {
                    if ( count( $this->numeric ) > 0 ) {
                        $vis_types[] = $vis_type;
                    } else {
                        continue;
                    }
                } else {
                    $vis_types[] = $vis_type;
                }
            }
            if( count($vis_types) == 0 ) {
                unset( $defaultOptions['styles'][$style] );
                continue;
            }
            $defaultOptions['styles'][$style]['visualization_type_options'] = $vis_types;
        }
    }

    /**
     * Ensures we have a valid style and type set or fall back to one.
     * @param array &$defaultOptions
     * @return boolean
     */
    protected function ensureValidStyleAndType( &$defaultOptions ) {
        $style_options = array_keys( $defaultOptions['styles'] );
        array_unshift( $style_options, $defaultOptions['style'] );
        do {
            $valid_style = false;
            $style = array_shift( $style_options );
            if ( !$style || !array_key_exists( $style, $defaultOptions['styles'] ) ) {
                continue;
            }
            $type_options = $defaultOptions['styles'][$style]['visualization_type_options'];
            $idx = array_search( $defaultOptions['displayFieldsType'], $type_options );
            if ( $idx ) {
                unset( $type_options[$idx] );
                array_unshift( $type_options, $defaultOptions['displayFieldsType'] );
            }
            do {
                $valid_type = false;
                $type = array_shift( $type_options );
                if ( !$type || !array_key_exists( $type, $defaultOptions['displayFieldsTypes'] )
                        || !is_array( $defaultOptions['displayFieldsTypes'][$type] )
                        || count( $defaultOptions['displayFieldsTypes'][$type] ) == 0 ) {
                    continue;
                }
                $valid_type = $type;
            } while (!$valid_type && count( $type_options ) > 0 );
            if ( !$valid_type ) {
                continue;
            }
            $valid_style = $style;
        } while( !$valid_style && count( $style_options ) > 0 );
        if ( !$valid_style ) {
            return false;
        }
        $defaultOptions['style'] = $valid_style;
        $defaultOptions['displayFieldsType'] = $valid_type;
        return true;
    }

    /**
     * Sets up the visualization options 
     * @param array &$defaultOptions
     * @return boolean
     */
    protected function setupVisualization( &$defaultOptions ) {
        $type = $defaultOptions['displayFieldsType'];
        $displayFields = $defaultOptions['displayFields'];
        I2CE_Util::merge_recursive( $defaultOptions['displayFields'], $defaultOptions['displayFieldsTypes'][$type] );
        $style = $defaultOptions['style'];

        $tqx = array();
        if ( $this->page->request_exists('tqx') ) {
            $tqx_req = $this->page->request('tqx');
            $pairs = explode( ';', $tqx_req );
            foreach( $pairs as $pair ) {
                list($key,$val) = explode( ':', $pair, 2 );
                $tqx[$key] = $val;
            }
        }
        $this->data = array( 
                'version' => '0.6',
                'reqId' => (array_key_exists('reqId', $tqx) ? $tqx['reqId'] : ''),
                'sig' => (array_key_exists('sig', $tqx) ? $tqx['sig'] : ''),
                'status' => 'ok',
                'table' => array( 'cols' => array(), 'rows' => array(), 'p' => array() ) 
                );
        $this->data['table']['p']['title']= $this->config->display_name;
        $this->data['table']['p']['description']= $this->config->description;
        $this->data['table']['p']['limit_text'] = $this->getReportLimitsDescription();

        $this->visualizedFields = array();
        foreach( $defaultOptions['displayFields'] as $index => $data ) {
            if ( !is_numeric( $index ) ) {
                continue;
            }
            if ( array_key_exists( 'aggregate', $data ) && $data['aggregate'] ) {
                $this->visualizedFields[ intval($index) ] = $data['formfield'] . '+' . $data['aggregate'];
            } else {
                $this->visualizedFields[ intval($index) ] = $data['formfield'];
            }
        }

        return true;
    }


    /**
     * Abstract method that each display is resposbile for implementing.  Checks to see
     * if it can display the given view.
     * @returns boolean
     */
    protected function canView() {
        return true;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
