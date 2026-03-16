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
*  I2CE_CustomReport_Display_Visualization
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.2
* @access public
*/


class I2CE_CustomReport_Display_Visualization extends I2CE_CustomReport_Display{


    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into
     * @param boolean $processResults Defaults to true meaning we run through the results
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display($contentNode,$processResults = true, $controls = null) {
            $parent_return = parent::display($contentNode,false,$controls); 
            
            $i2ce_config = I2CE::getConfig();
            $this->template->addHeaderLink( 'https://www.google.com/jsapi', array( 'type' => "text/javascript", 'ext' => 'js' ), false );
            $this->template->addHeaderText("        google.load('visualization', '1.0', {'packages':['corechart']});\n", 'script', 'visualization_wrapper' );
            $this->template->addHeaderLink( 'QueryWrapper.js' );
            $save_req = "flash_data&" . file_get_contents( "php://input" ) . "&" . $_SERVER['QUERY_STRING'];
            $req_key = md5($save_req);
            $_SESSION['req_query'][ $req_key ] = $save_req;

            $vis_class = $this->defaultOptions['styles'][$this->defaultOptions['style']]['visualization_class'];

            $options = array( 'height' => 350, 'width' => 590 );
            if ( array_key_exists( 'global_options', $this->defaultOptions ) 
                    && is_array( $this->defaultOptions['global_options'] ) ) {
                foreach( $this->defaultOptions['global_options'] as $key => $val ) {
                    if ( is_numeric($val) ) {
                        $options[$key] = $val+0;
                    } elseif ( is_array($val) ) {
                        // Make sure arrays stay as JSON arrays
                        ksort($val);
                        $options[$key] = $val;
                    } else {
                        $options[$key] = $val;
                    }
                }
            }
            if ( array_key_exists( 'visualization_options', $this->defaultOptions['styles'][$this->defaultOptions['style']] ) 
                    && is_array( $this->defaultOptions['styles'][$this->defaultOptions['style']]['visualization_options'] ) ) {
                foreach( $this->defaultOptions['styles'][$this->defaultOptions['style']]['visualization_options'] as $opt => $val ) {
                    if ( is_numeric( $val ) ) {
                        $options[$opt] = $val+0;
                    } else {
                        $options[$opt] = $val;
                    }
                }
            }
            if ( array_key_exists( 'height', $this->defaultOptions ) ) {
                $options['height'] = $this->defaultOptions['height']+0;
            }
            if ( array_key_exists( 'width', $this->defaultOptions ) ) {
                $options['width'] = $this->defaultOptions['width']+0;
            }
            $report_results = 'report_results';
            if ( array_key_exists('results_id', $this->defaultOptions) ) {
                $report_results = 'report_results_'.$this->defaultOptions['results_id'];
                $this->template->setAttribute('id', $report_results, 'report_results', null, $contentNode );
            }

            $this->template->setAttribute('onclick', "queryWrapper_$report_results.resize( 0, 0, true );", 'vis_button_smaller', null, $contentNode );
            $this->template->setAttribute('onclick', "queryWrapper_$report_results.resize();", 'vis_button_bigger', null, $contentNode );

            $dataSourceURL = "index.php/CustomReports/show/{$this->view}/Datasource?req_query=$req_key";
            //$js = "        google.load('visualization', '1.0', {'packages':['corechart']});\n"
            $js = "        google.setOnLoadCallback(drawChart_$report_results);\n"
                . "        var dataSourceURL_$report_results = '$dataSourceURL';\n"
                . "        var query_$report_results;\n"
                . "        var queryWrapper_$report_results;\n"
                . "        function drawChart_$report_results() {\n"
                . "          var container = document.getElementById('$report_results');\n"
                . "          var chart = new google.visualization.$vis_class(container);\n"
                . "          query_$report_results && query_$report_results.abort();\n"
                . "          query_$report_results = new google.visualization.Query(dataSourceURL_$report_results);\n"
                . "          queryWrapper_$report_results = new QueryWrapper( query_$report_results, chart, " . json_encode( $options ) . ", container );\n"
                . "          queryWrapper_$report_results.sendAndDraw();\n"
                . "        }\n";

            $this->template->addHeaderText($js,'script','visualization_wrapper'); //add this to a new script node.

            return $parent_return;
 
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
            'width'=>590,
            'height'=>350, 
            'bgcolor'=>'#ffffff',
            'reverseOrder'=>false,
            'style'=>null,
            'styles'=>array(),
            'displayFieldsType'=>null,
            'displayFieldsTypes'=>array(),
            'displayFields'=>array(),
            'style' => 'Pie',
            );
        foreach ($make_exist as $key=>$val) {
            if (!array_key_exists($key,$options)) {
                $options[$key] = $val;
            }
        }
        $defaultOptions = parent::getDefaultOptions($get,$options);
        $this->defaultOptions = $defaultOptions;
        $this->findNumericFields();
        $this->makeVisualizationStylesSane($defaultOptions);
        if (count($defaultOptions['styles']) == 0) { //no valid styles.
            I2CE::raiseError("No Valid Styles For Visualization");
            return false;
        }
        //get rid of limit stuff
        foreach (array('limit_page','limit_per_page','limit_offset','limit_amount') as $key) {
            $defaultOptions[$key] = false;
        }
        return  $defaultOptions;
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


    /*
    protected function makeChartStylesSane (&$defaultOptions) {
        foreach ($defaultOptions['styles'] as $style=>$data) {
            if (!is_array($data)) {
                unset($defaultOptions['styles'][$style]);
                continue;
            }
            if (!array_key_exists('visualization_type_options',$data)) {
                unset($defaultOptions['styles'][$style]);
                continue;
            }
            if (!is_array($data['visualization_type_options'])) {
                unset($defaultOptions['styles'][$style]);
                continue;
            }

            if (!array_key_exists('visulization_options',$data) || !is_array($data['visulization_options'])) {
                $defaultOptions['styles'][$style]['visulization_options'] = array();
            }
            $visualization_types = array();
            foreach ($data['visualization_type_options'] as $visualization_type) {
                if (substr($visualization_type,-8) == '_numeric') {
                    if (count($this->numeric) > 0) {
                        $visualization_types[] = $visualization_type;
                    } else {
                        continue;
                    }
                } else {
                    $visualization_types[] = $visualization_type;
                }
            }
            if (array_key_exists('visualization_type_excludes',$data) && is_array($data['visualization_type_excludes'])) { //this allow us to exclude types from a chart on a per view basis
                $visualization_types = array_diff($visualization_types,$data['visualization_type_excludes']);
            }
            if (count($visualization_types) == 0) {
                unset($defaultOptions['styles'][$style]);
                continue;
            }
            //we are happy with this style.
            $defaultOptions['styles'][$style]['visualization_type_options'] = $visualization_types;
        }
    }
    */


    /**
     * Adds any controls for this display to the content node.
     * @param DOMNode $contentNode 
     * @returns boolean;
     */
    protected function displayReportControl($contentNode) {
        parent::displayReportControl($contentNode);
        $js = '';

        $style = $this->defaultOptions['style'];

        $selectNode = $this->template->getElementByName('style',0,$contentNode);
        if (!$selectNode instanceof DOMNode) {
            I2CE::raiseError("Could not find style node");
            return false;
        }               
        
        $styles = $this->defaultOptions['styles'];

        $allowed_types = array( 0 => array(),
                1 => array( 'one_row_total' ),
                2 => array( 'one_row_count', 'one_row_total', 'one_row_numeric', 'multi_row_total' ),
                );
        
        $avail_fields = array_filter( $this->getReportViewDisplayedFields( false, array( '' ) ), 'is_array' );
        foreach ($styles as $s=>$data) {
            $visualization_types = $data['visualization_type_options']; //these have already been made sane. 
            //I2CE::raiseMessage("avail fields are " . print_r($avail_fields,true));
            $num_fields = count($avail_fields);
            if ( array_key_exists( $num_fields, $allowed_types ) ) {
                $visualization_types = array_intersect( $visualization_types, $allowed_types[$num_fields] );
            }

            foreach ($visualization_types as $k=>$v) {
                $visualization_types[$k] = "'$v'";
            }
            $js .=  "\tcustom_report_visualization_types['$s'] = new Array(" . implode(',',$visualization_types) . ");\n";
            $attr = array('value'=>$s);
            if ($style === $s) {
                $attr['selected']='selected';
            }
            $header = $s;
            if (array_key_exists('display_name',$data) && $data['display_name']) {
                $header = $data['display_name'];
            }
            $selectNode->appendChild( $this->template->createElement('option',$attr,$header));
            //I2CE::raiseMessage("adding $s $header style " . print_r($visualization_types,true));
        }

        $js .="
    if (window.addEvent) {
       window.addEvent('domready',function(e) {
           visualization_styles = $('visualization_styles');
	   if (!visualization_styles) {
   	      return;
	   }
	   custom_report_update_visualization_types(visualization_styles.options[visualization_styles.selectedIndex].value);
";
        
        if ($this->defaultOptions['displayFieldsType']) {    
            $js .="           
           var types = $('displayVisualizationFieldTypes');
           if (types) {
                  var selected = -1;
		  \$each(types.options, function(option,i){
                      if (option && option.value == '" . $this->defaultOptions['displayFieldsType']   . "') {
                         selected = i;
                      }
                  });
                  if (selected > -1)  {
                        types.selectedIndex = selected;
                  } 
                 custom_report_show_visualization_type('" . $this->defaultOptions['displayFieldsType'] . "');
            }
"; 
        }
        $js .= "
       });
    }
";
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderText($js,'script','script_visualization_type_options');
        
        $numeric = $this->findNumericFields();
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
                $options = array('value'=>$reportformfield);
                if (($selected === false) || ($selected == $reportformfield)) { //make the first one selected or the current one selected
                    $selected = $reportformfield;
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
        if ( array_key_exists( 'isStacked', $this->defaultOptions['global_options'] ) 
                && $this->defaultOptions['global_options']['isStacked'] ) {
            $this->template->setDisplayDataImmediate( "global_options:isStacked", 1 );
        }
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
