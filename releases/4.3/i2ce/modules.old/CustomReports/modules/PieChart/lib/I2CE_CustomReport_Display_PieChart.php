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
*  I2CE_CustomReport_Display_PDF
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_CustomReport_Display_PieChart extends I2CE_CustomReport_Display{

    /***
     * The chart data
     * @var protected array $chart
     */
    protected $chart;


    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into
     * @param boolean $processResults Defaults to true meaning we run through the results
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display($contentNode,$processResults = true, $controls = null) {
        
        if ($this->page->request_exists('flash_data')) {
            return parent::display(false,true, $controls); 
        }  else {
            $parent_return = parent::display($contentNode,false,$controls); 
            //we want to do the reference to the flash content (this will call processResults below)
            // -- This doesn't seem to call processResults anymore so adding in the chart stuff here (?)
            
            // check if results_id is set, otherwise, leave the default report_results id
            
            if( array_key_exists('results_id', $this->defaultOptions) && $this->defaultOptions['results_id'] ){
                $report_results = 'report_results_' . $this->defaultOptions['results_id'];
                $this->template->setAttribute('id', $report_results, 'report_results' );
            }
            else{
                $report_results = 'report_results';
            }
            
            $i2ce_config = I2CE::getConfig();
            $license = '';
            $i2ce_config->setIfIsSet($license, 'modules/maani-charts/license');
            $this->template->addHeaderLink('swfobject.js');
            $flashDataURL = 'index.php/file/charts.swf?library_path=index.php/file/charts_library';
            if ($license) {
                $flashDataURL .= '&license=' . $license;
            }
            $save_req = "flash_data&" . file_get_contents( "php://input" ) . "&" . $_SERVER['QUERY_STRING'];
            $save_defs = array( 'flash_width', 'flash_height', 'label_size' );
            foreach( $save_defs as $def ) {
                if ( array_key_exists( $def, $this->defaultOptions ) ) {
                    $save_req .= "&$def=" . urlencode($this->defaultOptions[$def]);
                }
            }
            $req_key = md5($save_req);
            $_SESSION['req_query'][ $req_key ] = $save_req;
            $flashDataURL .='&php_source=' . 
                urlencode("index.php/CustomReports/show/{$this->view}/{$this->display}?req_query=" . $req_key );
            $js = "\tif(window.addEvent) { window.addEvent('domready', function() { swfobject.embedSWF('$flashDataURL', \n\t\t'$report_results', '{$this->defaultOptions['flash_width']}', " .
                " '{$this->defaultOptions['flash_height']}', '9.0.0' ,'expressInstall.swf',  \n\t\t" .
                " {}, \n\t\t{quality: 'high', bgcolor:'{$this->defaultOptions['flash_bgcolor']}',wmode:'opaque'}\n\t); } ); } ";        

            $this->template->addHeaderLink('mootools-core.js');
            $this->template->addHeaderText($js,'script','CustomReports_PieChart'); //add this to a new script node.

            return $parent_return;
 
        }
    }



    /**
     * Process results
     * @param array $results_data an array of results.  indices are 'results' and Buffered result and 'num_results' the
     * number of results.  (these values may be false on failure)
     * @param DOMNode $contentNode.  Default to null a node to append the results onto
     */
    protected function processResults($results_data,$contentNode=null) {
        if ($this->page->request_exists('flash_data')) {
            require_once(I2CE::getFileSearch()->search('MAANI_CHART_FILES','charts.php'));            
            if (substr($this->defaultOptions['displayFieldsType'],0,7) == 'one_row') {
                $this->preProcessResultsOneRow();
            } else {
                $this->preProcessResultsMultiRow();
            }
            parent::processResults($results_data,$contentNode);
            if ( !array_key_exists( 'num_results', $results_data ) || $results_data['num_results'] == 0 ) {
                // Nothing was found so just show a message instead of
                // the confusing default report.
                unset( $this->chart['chart_data'] );
                unset( $this->chart['draw']['print'] );
                $this->chart['chart_rect']['x'] = 1000;
                $this->chart['chart_rect']['y'] = 1000;
                $this->chart['legend_rect']['x'] = 1000;
                $this->chart['legend_rect']['y'] = 1000;
                $message = "Error!";
                I2CE::getConfig()->setIfIsSet( $message, 
                        "/modules/CustomReports/displays/PieChart/error_message" );
                $msg_arr = explode( "\n", wordwrap( $message, 40 ) );
                $start_y = 25;
                foreach( $msg_arr as $num => $msg_line ) {
                    $this->chart['draw']['error'.$num] = array(
                            'color' => '000000', 'size' => 25, 'type' => 'text', 
                            'text' => $msg_line,
                            'height' => 100, 'width' => 500,
                            'x' => 25, 'y' => $start_y,
                        );
                    $start_y += 25;
                }
                $this->SendChartData();
            }
            if (substr($this->defaultOptions['displayFieldsType'],0,7) == 'one_row') {
                $this->postProcessResultsOneRow();
            } else {
                $this->postProcessResultsMultiRow();
            }
            $this->setupHeightWidth();
            $this->SendChartData();
        } else {
            // I don't think this ever gets called anymore?  Can it just be removed?
            I2CE::raiseError( "I don't think this should be called this way anymore.  It's being added to display() instead." );
            //just make a reference to the chart
            $i2ce_config = I2CE::getConfig();
            $license = '';
            $license = $i2ce_config->setIfIsSet($license, 'modules/maani-charts/license');
            //$results_data will be false b/c we called parent::display($contentNode,false) in display() above
            if (!$contentNode instanceof DOMNode) {
                return false;
            }
            $this->template->addHeaderLink('swfobject.js');
            $flashDataURL = 'index.php/file/charts.swf?library_path=index.php/file/charts_library';
            if ($license) {
                $flashDataURL .= '&license=' . $license;
            }
            $save_req = "flash_data&" . file_get_contents( "php://input" ) . "&" . $_SERVER['QUERY_STRING'];
            $req_key = md5($save_req);
            $_SESSION['req_query'][ $req_key ] = $save_req;
            $flashDataURL .='&php_source=' . 
                urlencode("index.php/CustomReports/show/{$this->view}/{$this->display}?req_query=" . $req_key );
            $js = "\tif(window.addEvent) { window.addEvent('domready', function() { swfobject.embedSWF('$flashDataURL', \n\t\t'$report_results', '{$this->defaultOptions['flash_width']}', " .
                " '{$this->defaultOptions['flash_height']}', '9.0.0' ,'expressInstall.swf',  \n\t\t" .
                " {}, \n\t\t{quality: 'high', bgcolor:'{$this->defaultOptions['flash_bgcolor']}',wmode:'opaque'}\n\t); } ); } ";        

            $this->template->addHeaderLink('mootools-core.js');
            $this->template->addHeaderText($js,'script',true); //add this to a new script node.
            return true;
        }
    }


    /**
     * Calls the Chart, SendChartData() function and exits    
     */
    protected function SendChartData() {
        $err = array();
        while (ob_get_length() !== false) {
            if ( !($out = ob_get_clean())) {
                continue;
            }
            $err[] = $out;
        }
        if (count($err) > 0) {
            I2CE::raiseError("Suppresed error(s) while generating flash:\n" . implode("\n",$err));
        }        
        SendChartData( $this->chart);
        exit();
    }
    /**
     * Set up the height or width of the chart rectangle based no the size of the legend for pie
     * and the relevant axis for column/bar/line.
     */
    protected function setupHeightWidth() {
        //"pie", "bar", "column", "stacked column", "scatter", 
        if ( !array_key_exists( 'chart_type', $this->chart ) 
                || !array_key_exists( 'chart_data', $this->chart )
                || !array_key_exists( 0, $this->chart['chart_data'] ) ) {
            return;
        }
        $label_size = $this->chart['legend_label']['size'];
        if ( array_key_exists( 'label_size', $this->defaultOptions ) ) {
            $label_size = $this->defaultOptions['label_size'];
        }
        $this->chart['legend_label']['size'] = $label_size;
        $this->chart['draw']['print']['x'] = $this->defaultOptions['flash_width'] - 45;
        $max_length = max( array_map( "strlen", array_slice( $this->chart['chart_data'][0], 1 ) ) );
        if ( in_array( $this->chart['chart_type'], array( "column", "stacked column" ) ) ) {
            $this->chart['chart_rect']['height'] = max( 100, 
                    $this->defaultOptions['flash_height'] - 100 - round( $max_length*3.3 ) );
        } elseif( $this->chart['chart_type'] == "bar" ) {
            $x = min( round( $this->defaultOptions['flash_width'] / 2 ),
                    $max_length * 4.6 );
            $this->chart['chart_rect']['x'] = $x;
            $this->chart['chart_rect']['width'] = $this->defaultOptions['flash_width'] - 50 - $x;
        } elseif( $this->chart['chart_type'] == "pie" ) {
            $x = min( round( $this->defaultOptions['flash_width'] / 2 ),
                    $max_length * (8*($label_size/12)) );
            $this->chart['chart_rect']['x'] = $x;
            $this->chart['chart_rect']['width'] = $this->defaultOptions['flash_width'] - 25 - $x;
        }
    }

    protected function postProcessResultsOneRow() {
        $style = $this->defaultOptions['style'];
        $options = array('collate'=>false,'sort_values'=>'none');
        foreach ($options as $key=>$val) {
            if (is_array($this->defaultOptions['global_chart_options']) && array_key_exists($key,$this->defaultOptions['global_chart_options'])) {
                $options[$key] = $this->defaultOptions['global_chart_options'][$key];
            }
            if (array_key_exists($key,$this->defaultOptions['styles'][$style])){
                $options[$key] = $this->defaultOptions['styles'][$style][$key];
            }
            if ($this->page->request_exists($key)) {
                $options[$key] = $this->page->request($key);
            }
        }
        $type  = $this->defaultOptions['displayFieldsType'];
        $collate = $options['collate'];
        if ($collate && is_numeric($collate)) {
            $collate = intval($collate);
            if ($collate < 1) {
                $collate = false;
            }
        } else {
            $collate =false;
        }
        $sort = $options['sort_values'];
        if (is_int($collate) && count($this->chart['chart_data'][0]) > $collate) {
            if ($sort == 'increasing') {
                array_multisort($this->chart['chart_data'][1],$this->chart['chart_data'][0]); //sort the chart data on the second row, the values.
            } else if ($sort == 'decreasing') {
                array_multisort($this->chart['chart_data'][1],SORT_DESC,$this->chart['chart_data'][0]); //sort the chart data on the second row, the values.
            }
            //array_multisort($this->chart['chart_data'][1],$this->chart['chart_data'][0]); //sort the chart data on the second row, the values.
            if ($type != 'one_row_numeric') {
                $collate--;
                $sum = array_sum(array_slice($this->chart['chart_data'][1],$collate));
            }
            $this->chart['chart_data'][0] = array_slice($this->chart['chart_data'][0],0,$collate);
            $this->chart['chart_data'][1] = array_slice($this->chart['chart_data'][1],0,$collate);
            if ($type != 'one_row_numeric') {
                $this->chart['chart_data'][0][$collate] = 'Other';
                $this->chart['chart_data'][1][] = $sum;
            }
        } else {
            if ($sort == 'increasing') {
                array_multisort($this->chart['chart_data'][1],$this->chart['chart_data'][0]); //sort the chart data on the second row, the values.
            } else if ($sort == 'decreasing') {
                array_multisort($this->chart['chart_data'][1],SORT_DESC,$this->chart['chart_data'][0]); //sort the chart data on the second row, the values.
            }
        }
        /* 
         * Moving this to the conditional below instead.
         * This corrects some issues with charts.  I'm not sure if it introduces new problems or not.
        if ($this->reverseOrder) {
            array_unshift($this->chart['chart_data'],array('',''));
        } else {
            array_unshift($this->chart['chart_data'][0],'');        
        }
        */
        $displayedFields = $this->getDisplayFieldsData();
        if ($type == 'one_row_total') {
            $header1 = $displayedFields[$this->displayedChartFields[0]]['header'];
            $header2 = $displayedFields[$this->displayedChartFields[1]]['header'];
        } else { 
            $header1 = $displayedFields[$this->displayedChartFields[1]]['header'];
            $header2 = $displayedFields[$this->displayedChartFields[0]]['header'];
        }
        if ($this->reverseOrder) {
            array_unshift($this->chart['chart_data'],array('',''));
            $this->chart['chart_data'][0][1] = $header1;
        } else {
            //array_unshift($this->chart['chart_data'][1],$header);
            // I'm not sure if this is right, but it fixes a couple instances so I'm trying it
            array_unshift($this->chart['chart_data'][0],$header1);
            array_unshift($this->chart['chart_data'][1],$header2);
        }
        foreach( $this->chart['chart_data'][0] as &$head ) {
            $head = wordwrap( $head, 47 );
        }
    }

    protected function preProcessResultsOneRow() {
        $this->chart['chart_data'] = array();
        $this->reverseOrder = $this->defaultOptions['reverseOrder'];
    }
    protected function preProcessResultsMultiRow() {
        $this->chart['chart_data'] = array();
        $this->chart['chart_data'][0] = array('');
        $this->reverseOrder = $this->defaultOptions['reverseOrder'];
    }

    protected function postProcessResultsMultiRow() {
        $x= count($this->displayedChartFieldsIndex[0]); //columns
        $y= count($this->displayedChartFieldsIndex[1]); //rows
        $chart = &$this->chart['chart_data'];
        for ($j=0; $j<=$y; $j++) {
            for ($i=0; $i <= $x; $i++) {
                if (!array_key_exists($i,$chart[$j])) {
                    $chart[$j][$i] = 0;
                }
            }
            ksort($chart[$j]);
        }
    }

    /**
     * An array with values the name of the columns used for this display
     * @var protected array $displayedChartFields
     */
    protected $displayedChartFields;

    /**
     * An array (or an array or arrays) with keys the values of the charted fields and  values the index row (row and col) they are in the $chart['chart_data'] array
     * @var protected array $displayedChartFields
     */
    protected $displayedChartFieldsIndices;

    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        $mapped_row = $this->mapResults($row);
        if  (count ($this->displayedChartFields) == 2) {
            $index = $mapped_row[$this->displayedChartFields[0]];
            if (is_null($index) || (is_string($index) && strlen($index)==0)) {
                $index = 'Unknown';
            }
            if (!array_key_exists($index,$this->displayedChartFieldsIndex)) {
                $num = count($this->displayedChartFieldsIndex);
                $this->displayedChartFieldsIndex[$index] = $num;
                if ($this->reverseOrder) {
                    $this->chart['chart_data'][$num+1][0] = $index;
                } else {
                    $this->chart['chart_data'][0][$num] = $index;
                }
            }
            $index = $this->displayedChartFieldsIndex[$index];
            // 'one_row_total': //has one field and count
            //'one_row_count': //has two fields, the second one is an aggregate (either count or count_distinct)
            //'one_row_numeric': //has two fields, the second one is numeric.  second one possibly has aggregate
            $val = $mapped_row[$this->displayedChartFields[1]];
            if (is_null($val) || (is_string($val) && strlen($val) == 0) ) {
                $val = 0;
            }
            if ($this->reverseOrder) {
                $this->chart['chart_data'][$index+1][1] = $val;
            } else {
                $this->chart['chart_data'][1][$index] = $val;
            }
        } else {
            $indices = array($mapped_row[$this->displayedChartFields[0]], $mapped_row[$this->displayedChartFields[1]]);
            foreach ($indices as $i=>$index) {
                if (is_null($index) || (is_string($index) && strlen($index)==0)) {
                    $index = 'Unknown';
                }
                if (!array_key_exists($index,$this->displayedChartFieldsIndex[$i])) {
                    $num = count($this->displayedChartFieldsIndex[$i]) + 1;
                    $this->displayedChartFieldsIndex[$i][$index] = $num;
                    if ($i == 0) {
                        if ($this->reverseOrder) {
                            $this->chart['chart_data'][($i+1)%2][$num] = $index;
                        } else {
                            $this->chart['chart_data'][$i][$num] = $index;
                        }
                    } else {
                        if ($this->reverseOrder) {
                            $this->chart['chart_data'][($i+1)%2][$num] = $index;
                        } else {
                            $this->chart['chart_data'][$num][0] = $index;
                        }                        

                    }
                }   
                $indices[$i] = $this->displayedChartFieldsIndex[$i][$index];
            }
            $val = $mapped_row[$this->displayedChartFields[2]]; 
            if (is_null($val) || (is_string($val) && strlen($val) == 0) ) {
                $val = 0;
            }
            if ($this->reverseOrder) {
                $this->chart['chart_data'][$indices[0]][$indices[1]] =  $val;
            } else {
                $this->chart['chart_data'][$indices[1]][$indices[0]] =  $val;
            }
        }
        return true;
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
            'flash_width'=>590,
            'flash_height'=>350, 
            'flash_bgcolor'=>'#e0e0e0',
            'reverseOrder'=>false,
            'style'=>null,
            'styles'=>array(),
            'displayFieldsType'=>null,
            'displayFieldsTypes'=>array(),
            'displayFields'=>array(),
            );
        foreach ($make_exist as $key=>$val) {
            if (!array_key_exists($key,$options)) {
                $options[$key] = $val;
            }
        }
        $defaultOptions = parent::getDefaultOptions($get,$options);
        $this->defaultOptions = $defaultOptions;
        $this->findNumericFields();
        $this->makeChartStylesSane($defaultOptions);
        if (count($defaultOptions['styles']) == 0) { //no valid styles.
            I2CE::raiseError("No Valid Styles For Pie/Chart");
            return false;
        }
        if (array_key_exists('flash_data',$get)) {
            if (!$this->ensureValidStyleAndType($defaultOptions)) {
                I2CE::raiseError("No Valid Style and Type can be chosen For Pie/Chart");
                return false;
            }
            if (!$this->setupFlashChart($defaultOptions)) {
                I2CE::raiseError("Could not set up flash chart options");
                return false;
            }
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
     * Ensures check we have a valid style and type  set or we can fall back onto one.
     * @param array &$defaultOptions.  The default options we are scanning
     * @returns boolean.  True on success
     */
    protected function ensureValidStyleAndType(&$defaultOptions) {
        $style_options = array_keys($defaultOptions['styles']);
        array_unshift($style_options,$defaultOptions['style']);
        do {
            $valid_style = false;
            $style = array_shift($style_options); 
            if (!$style) { //bad style
                continue;
            }
            if (!array_key_exists($style,$defaultOptions['styles'])) { //no style data set                
                continue;
            }
            //we checked above that there were chart_type_options set.  make sure the type we have is valid. if not, try to get a valid one,
            //now we check that the type is set.
            $type_options =  $defaultOptions['styles'][$style]['chart_type_options'];
            $indx = array_search($defaultOptions['displayFieldsType'], $type_options);
            if ($indx) {
                unset($type_options[$indx]);
                array_unshift($type_options,$defaultOptions['displayFieldsType']);
            }
            do {
                $valid_type = false;
                $type = array_shift($type_options);
                if (!$type) {
                    continue;
                }
                if (!array_key_exists($type,$defaultOptions['displayFieldsTypes'])) {
                    continue;
                }
                if (!is_array($defaultOptions['displayFieldsTypes'][$type])) {
                    continue;
                }
                if ( count($defaultOptions['displayFieldsTypes'][$type]) == 0) {
                    continue;
                }
                $valid_type = $type;
            } while (!$valid_type && count($type_options) > 0);
            if (!$valid_type) {
                continue;
            }
            $valid_style = $style;
        } while  (!$valid_style && count($style_options) > 0);
        if (!$valid_style) {//no valid style was found.
            return false;
        }
        $defaultOptions['style'] = $valid_style;
        $defaultOptions['displayFieldsType'] = $valid_type;
        return true;
    }





    protected function makeChartStylesSane (&$defaultOptions) {
        foreach ($defaultOptions['styles'] as $style=>$data) {
            if (!is_array($data)) {
                unset($defaultOptions['styles'][$style]);
                continue;
            }
            if (!array_key_exists('chart_type_options',$data)) {
                unset($defaultOptions['styles'][$style]);
                continue;
            }
            if (!is_array($data['chart_type_options'])) {
                unset($defaultOptions['styles'][$style]);
                continue;
            }

            if (!array_key_exists('chart_options',$data) || !is_array($data['chart_options'])) {
                $defaultOptions['styles'][$style]['chart_options'] = array();
            }
            $chart_types = array();
            foreach ($data['chart_type_options'] as $chart_type) {
                if (substr($chart_type,-8) == '_numeric') {
                    if (count($this->numeric) > 0) {
                        $chart_types[] = $chart_type;
                    } else {
                        continue;
                    }
                } else {
                    $chart_types[] = $chart_type;
                }
            }
            if (array_key_exists('chart_type_excludes',$data) && is_array($data['chart_type_excludes'])) { //this allow us to exclude types from a chart on a per view basis
                $chart_types = array_diff($chart_types,$data['chart_type_excludes']);
            }
            if (count($chart_types) == 0) {
                unset($defaultOptions['styles'][$style]);
                continue;
            }
            //we are happy with this style.
            $defaultOptions['styles'][$style]['chart_type_options'] = $chart_types;
        }
    }


    
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


        
        foreach ($styles as $s=>$data) {
            $chart_types = $data['chart_type_options']; //these have already been made sane. 
            foreach ($chart_types as $k=>$v) {
                $chart_types[$k] = "'$v'";
            }
            $js .=  "\tcustom_report_chart_types['$s'] = new Array(" . implode(',',$chart_types) . ");\n";
            $attr = array('value'=>$s);
            if ($style === $s) {
                $attr['selected']='selected';
            }
            $header = $s;
            if (array_key_exists('display_name',$data) && $data['display_name']) {
                $header = $data['display_name'];
            }
            $selectNode->appendChild( $this->template->createElement('option',$attr,$header));
        }

        $js .="
    if (window.addEvent) {
       window.addEvent('domready',function(e) {
           chart_styles = $('chart_styles');
	   if (!chart_styles) {
   	      return;
	   }
	   custom_report_update_chart_types(chart_styles.options[chart_styles.selectedIndex].value);
";
        
        if ($this->defaultOptions['displayFieldsType']) {    
            $js .="           
           var types = $('displayFieldTypes');
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
                 custom_report_show_chart_type('" . $this->defaultOptions['displayFieldsType'] . "');
            }
"; 
        }
        $js .= "
       });
    }
";
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderText($js,'script','script_chart_type_options');
        
        $avail_fields = $this->getReportViewDisplayedFields( false, array( '' ) );
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
        return true;
    }


    /**
     * Sets up the flash chart options in $this->chart
     * @returns boolean.  True on success
     */
    protected function setupFlashChart(&$defaultOptions) {
        $type =   $defaultOptions['displayFieldsType'];
        $displayFields  = $defaultOptions['displayFields'];
        I2CE_Util::merge_recursive($defaultOptions['displayFields'], $defaultOptions['displayFieldsTypes'][$type]);
        $data = array();
        $data['title']['text']= $this->config->display_name;
        $data['elements']= array();
        $style = false;
        $style = $defaultOptions['style'];
        $this->chart = array();
        if (array_key_exists('global_chart_options',$defaultOptions) && is_array($defaultOptions['global_chart_options'])) {
            $this->chart = $defaultOptions['global_chart_options'];
        }
        I2CE_Util::merge_recursive($this->chart,$defaultOptions['styles'][$style]['chart_options']);
        $this->displayedChartFields = array();
        foreach ($defaultOptions['displayFields'] as $index=>$data) {
            if (!is_numeric($index)) {
                continue;
            }
            if (array_key_exists('aggregate',$data) && ($data['aggregate'])) {
                $this->displayedChartFields[intval($index)] = $data['formfield'] . '+' . $data['aggregate'];
            } else {
                $this->displayedChartFields[intval($index)] = $data['formfield'];
            }
        }
        if ( substr($type,0,7) == 'one_row') {
            $this->displayedChartFieldsIndex = array();
            if (count($this->displayedChartFields) != 2 || !array_key_exists(0,$this->displayedChartFields) || !array_key_exists(1,$this->displayedChartFields)) {
                I2CE::raiseError("Innappropriate index (!=2) for charted fields.  Slicing down to 2.  Fix this better!: " .implode(',',array_keys($this->displayedChartFields)));
                $this->displayedChartFields = array_slice( $this->displayedChartFields, 0, 2 );
            }
        } else if (substr($type,0,9) == 'multi_row') {
            $this->displayedChartFieldsIndex = array(0=>array(),1=>array());
            if (count($this->displayedChartFields) != 3 || !array_key_exists(0,$this->displayedChartFields) || 
                !array_key_exists(1,$this->displayedChartFields) || !array_key_exists(2,$this->displayedChartFields)) {
                I2CE::raiseError("Innappropriate index (!=3) for charted fields.  Slicing down to 3.  Fix this better! :" .implode(',',array_keys($this->displayedChartFields)));
                $this->displayedChartFields = array_slice( $this->displayedChartFields, 0, 3 );
            }
        } else {
            I2CE::raiseError("Dont know how to display fields with type: $type");
            return false;
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
