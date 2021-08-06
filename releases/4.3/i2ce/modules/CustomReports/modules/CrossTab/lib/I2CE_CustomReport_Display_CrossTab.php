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
*  I2CE_CustomReport_Display_CrossTab
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1
* @access public
*/


class I2CE_CustomReport_Display_CrossTab extends I2CE_CustomReport_Display_Default {

    /**
     * The constant to signify the left side of the cross tab.
     */
    const CROSSTAB_LEFT = 1;
    /**
     * The constant to signify the top side of the cross tab.
     */
    const CROSSTAB_TOP = 2;
    /**
     * The constant to signify the neither side of the cross tab.
     */
    const CROSSTAB_NONE = 0;

    /**
     * @var array The data
     */
    protected $data;
    /**
     * @var array The header details and order of display
     */
    protected $headers;

    protected function tooMuchData($contentNode) {
        if (! ($noDataNode = $this->template->appendFileByNode( "customReports_toomuch.html",'div',$contentNode )) instanceof DOMNode) {
            return;
        }
    }

    /**
     * Return true if this is an export.
     * @return boolean
     */
    public function isExport() {
        if ( array_key_exists( 'CrossTab_export', $this->defaultOptions ) 
                && $this->defaultOptions['CrossTab_export'] ) {
            return true;
        }
        return false;
    }

    /**
     * Return true to display Percentages
     * @return boolean
     */
    public function isPercentage() {
        if ( array_key_exists( 'CrossTab_percentage', $this->defaultOptions ) 
                && $this->defaultOptions['CrossTab_percentage'] ) {
            return true;
        }
        return false;
    }


    /**
     * Generate the export data for this crosstab and return it.
     * @param DOM_Node $contentNode
     * @param boolean $return_array Set to true to return the array, false returns a string
     * @return mixed
     */
    public function generateExport( $contentNode, $return_array=true ) {
        if ( $this->isExport() ) {
            if ( parent::display( $contentNode ) ) {
                $table = $this->template->getElementById( "report_table_table" );
                $rows = $table->getElementsByTagName( "tr" );
                $csv = array();
                for( $i = 0; $i < $rows->length; $i++ ) {
                    $tr = $rows->item($i);
                    if ( !array_key_exists( $i, $csv ) || !is_array( $csv[$i] ) ) {
                        $csv[$i] = array();
                    }
                    for( $cell = $tr->firstChild; $cell !== null; $cell = $cell->nextSibling ) {
                        if ( $cell == null ) {
                            break;
                        }
                        if ( !$cell instanceof DOMElement ) {
                            continue;
                        }
                        if ( $cell->tagName != "th" && $cell->tagName != "td" ) {
                            continue;
                        }
                        $text = $cell->textContent;
                        $csv[$i][] = $text;
                        $rowspan = 1;
                        $colspan = 1;
                        if ( $cell->hasAttribute("rowspan") ) {
                            $rowspan = $cell->getAttribute("rowspan");
                        }
                        if ( $cell->hasAttribute("colspan") ) {
                            $colspan = $cell->getAttribute("colspan");
                        }
                        $addcols = $colspan;
                        while( --$addcols ) {
                            $csv[$i][] = '';
                        }
                        $next=$i;
                        while( --$rowspan ) {
                            $next++;
                            if ( !array_key_exists( $next, $csv ) || !is_array( $csv[$next] ) ) {
                                $csv[$next] = array();
                            }
                            $csv[$next][] = '';
                            $addcols = $colspan;
                            while( --$addcols ) {
                                $csv[$next][] = '';
                            }
                        }
                    }
                }
                if ( $return_array ) {
                    return $csv;
                } else {
                    $out = fopen( "php://temp", 'r+' );
                    foreach( $csv as $line ) {
                        fputcsv( $out, $line );
                    }
                    rewind($out);
                    $output = '';
                    while ( !feof($out) ) {
                        $output .= fgets($out);
                    }
                    fclose( $out );
                    return $output;
                }
            } else {
                I2CE::raiseError("Failed to call parent::display when calling generateExport");
                return '';
            }
        } else {
            I2CE::raiseError( "Tried to call generateExport when export isn't set for this report.");
            return '';
        }
    }

    /**
     * Display the report.
     * @return boolean
     */
    public function display( $contentNode, $processResults = true, $controls = null ) {
        if ( $this->isExport() ) {
            $csv = $this->generateExport( $contentNode );
            $filename = addslashes(str_replace(array(' ',"\n","\t") , array('_',' ','_'    ),$this->config->display_name)) . '_' . date("d_m_Y") . ".csv";
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                header("Content-disposition: attachment; filename=\"$filename\"");
                if (preg_match('/\s+MSIE\s+\d\.\d;/',$_SERVER['HTTP_USER_AGENT'])) {
                    header("Content-type: application/vnd.ms-excel");
                } else{
                    header("Content-type: text/csv; charset=UTF-8");
                }
                flush();
            }
            $out = fopen( "php://output", 'w' );
            foreach( $csv as $line ) {
                fputcsv( $out, $line );
            }
            flush();
            exit;
        } else {
            return parent::display( $contentNode, $processResults, $controls );
        }
    }


    /**
     * Process results
     * @param array $results_data an array of results.  indices are 'results' and Buffered result and 'num_results' the
     * number of results.  (these values may be false on failure)
     * @param DOMNode $contentNode.  Default to null a node to append the results onto
     * @return boolean
     */
    protected function processResults($results_data,$contentNode=null) {
        if ( !$results_data['num_results'] ) {
            $this->noData($contentNode);
            return true;
        }
        $too_much = 1000;
        I2CE::getConfig()->setIfIsSet($too_much,"/modules/CustomReports/displays/CrossTab/too_much");
        if ( $results_data['num_results'] > $too_much ) {
            $this->tooMuchData($contentNode);
            return true;
        }

        $this->template->addHeaderLink("customReports_display_Default.css" );
        $tableContainerNode = $this->template->appendFileByNode( "customReports_display_CrossTab_table.html", "div", $contentNode );
        if ( !$tableContainerNode instanceof DOMNode ) {
            I2CE::raiseError( "Could not add table container template" );
            return false;
        }

        $reportBodyNode = $this->template->getElementById( 'report_body', $tableContainerNode );
        if ( !$reportBodyNode instanceof DOMNode ) {
            I2CE::raiseError( "Could not find report body" );
            return false;
        }
        $this->data = array();
        $left = array();
        $top = array();
        $this->headers = array();
        $displayOrder = ''; 
        if ( array_key_exists( 'display_order', $this->defaultOptions ) && $this->defaultOptions['display_order'] ) { 
            $displayOrder = $this->defaultOptions['display_order'];
        } else {
            $this->config->setIfIsSet( $displayOrder, 'display_order' );
        }
        $displayOrderArr = explode( ',', $displayOrder );
        foreach( $this->defaultOptions['displayFieldsTab'] as $reportfield => $side ) {
            if ( !in_array( $reportfield, $displayOrderArr ) ) { 
                $displayOrderArr[] = $reportfield;
            }   
            if ( $side == self::CROSSTAB_LEFT ) {
                $left[] = $reportfield;
            } elseif ( $side == self::CROSSTAB_TOP ) {
                $top[] = $reportfield;
            }
        }
        $left = array_intersect( $displayOrderArr, $left );
        $top = array_intersect( $displayOrderArr, $top );

        $total = 'Total';
        I2CE::getConfig()->setIfIsSet( $total, "/modules/CustomReports/text/headers/count" );
        $num_results = 0;
        try {
            while( $row = $results_data['results']->fetch() ) {
                $mapped_row = $this->mapResults($row);
                $left_arr = array();
                foreach( $left as $left_field ) {
                    $left_arr[$left_field] = $mapped_row[$left_field];
                    $this->headers[ $left_field ] = true;
                }
                $left_key = json_encode($left_arr);
                if ( !array_key_exists( $left_key, $this->data ) ) {
                    $this->data[$left_key] = array();
                    $num_results++;
                }
                if ( !array_key_exists( 'total', $this->data ) ) {
                    $this->data['total'] = array();
                    $num_results++;
                }
                $data_row = &$this->data[$left_key];
                foreach( $top as $top_field ) {
                    if ( !array_key_exists( $top_field, $data_row ) ) {
                        $data_row[$top_field] = array();
                    }
                    if ( !array_key_exists( $top_field, $this->headers ) ) {
                        $this->headers[$top_field] = array();
                    }
                    $this->headers[$top_field][$mapped_row[$top_field]] = true;
                    if ( !array_key_exists( $mapped_row[$top_field], $data_row[$top_field] ) ) {
                        $data_row[$top_field][ $mapped_row[$top_field] ] = $mapped_row['total'];
                    } else {
                        $data_row[$top_field][ $mapped_row[$top_field] ] += $mapped_row['total'];
                    }
                    if ( !array_key_exists( $total, $data_row[$top_field] ) ) {
                        $data_row[$top_field][ $total ] = $mapped_row['total'];
                    } else {
                        $data_row[$top_field][ $total ] += $mapped_row['total'];
                    }

                    if ( !array_key_exists( $top_field, $this->data['total'] ) ) {
                        $this->data['total'][$top_field] = array();
                    }
                    if ( !array_key_exists( $mapped_row[$top_field], $this->data['total'][$top_field] ) ) {
                        $this->data['total'][$top_field][ $mapped_row[$top_field] ] = $mapped_row['total'];
                    } else {
                        $this->data['total'][$top_field][ $mapped_row[$top_field] ] += $mapped_row['total'];
                    }
                    if ( !array_key_exists( $total, $this->data['total'][$top_field] ) ) {
                        $this->data['total'][$top_field][ $total ] = $mapped_row['total'];
                    } else {
                        $this->data['total'][$top_field][ $total ] += $mapped_row['total'];
                    }
                }
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error getting cross tab results: " );
            return false;
        }
        // Now add the total columns to the end of the header.
        foreach( $top as $top_field ) {
            ksort( $this->headers[$top_field] );
            $this->headers[$top_field][$total] = true;
        }

        $this->doCrossTabHeaders( $tableContainerNode );
        $this->template->setDisplayDataImmediate( 'num_results', $num_results, $tableContainerNode );
        $row_num = 1;
        foreach( $this->data as $left => $rights ) {
            if ( $left == 'total' ) {
                continue;
            }
            $row = array_merge( json_decode( $left, true ), $rights );
            if ( !$this->processResultRow( $row, $row_num, $reportBodyNode ) ) {
                return false;
            }
            $row_num++;
        }
        $row = array_merge( array( 'total' => $total ), $this->data['total'] );
        if ( !$this->processResultRow( $row, $row_num, $reportBodyNode ) ) {
            return false;
        }
        return true;
    }

    /**
     * Process a result row
     * @param array $row
     * @param int $row_num
     * @param DOMNode $contentNode
     * @return boolean
     */
    protected function processResultRow( $row, $row_num, $contentNode = null ) {
        $rowNode = $this->template->appendFileByNode( "customReports_table_data_row.html", "tr", $contentNode );
        if ( !$rowNode instanceof DOMNode ) {
            I2CE::raiseError( "Could not add row to table." );
            return false;
        }
        $total_head = 'Total';
        I2CE::getConfig()->setIfIsSet( $total_head, "/modules/CustomReports/text/headers/count" );
        $this->template->setDisplayDataImmediate( "row_count", $row_num, $rowNode );
        $total_row = false;
        if ( array_key_exists( 'total', $row ) ) {
            $total_row = true;
            $totalNode = $this->template->appendFileByNode( "customReports_table_data_cell.html", "td", $rowNode );
            if ( !$totalNode instanceof DOMNode ) {
                I2CE::raiseError( "Could not add data cell to table." );
                return false;
            }
            $this->template->setDisplayDataImmediate( "report_data", $row['total'], $totalNode );
            $rowNode->setAttribute("class", "crosstab_total_row");
            $left_cols = 0;
        }

        foreach( $this->headers as $formfield => $headinfo ) {
            if (!$headinfo) {
                continue;
            }
            if ( is_array($headinfo) ) {
                foreach( $headinfo as $topVal => $enabled ) {
                    if ( !$enabled ) {
                        continue;
                    }
                    $cellNode = $this->template->appendFileByNode( "customReports_table_data_cell.html", "td", $rowNode );
                    if ( !$cellNode instanceof DOMNode ) {
                        I2CE::raiseError( "Could not add data cell to table." );
                        return false;
                    }
                    $dispVal = 0;
                    if ( array_key_exists( $formfield, $row ) && array_key_exists( $topVal, $row[$formfield] ) ) {
                        $dispVal = $row[$formfield][$topVal];
                        if ( $this->isPercentage() && $topVal != $total_head && array_key_exists( $total_head, $row[$formfield] ) && $row[$formfield][$total_head] > 0 ) {
                            $dispVal .= " (" . sprintf("%.0f", $dispVal/$row[$formfield][$total_head]*100 ) . "%)";
                        }
                    }
                    $this->template->setDisplayDataImmediate( "report_data", $dispVal, $cellNode );
                }
            } elseif ( !$total_row ) {
                $cellNode = $this->template->appendFileByNode( "customReports_table_data_cell.html", "td", $rowNode );
                if ( !$cellNode instanceof DOMNode ) {
                    I2CE::raiseError( "Could not add data cell to table." );
                    return false;
                }
                $this->template->setDisplayDataImmediate( "report_data", $row[$formfield], $cellNode );
            } else {
                $left_cols++;
            }
        }
        if ( $total_row && $left_cols > 1 ) {
            $totalNode->setAttribute( "colspan", $left_cols );
        }
        return true;
    }



    /**
     * Display the headers for this report.
     * @param DOMNode $contentNode 
     */
    protected function doCrossTabHeaders( $contentNode ) {
        $page_root = $this->getPageRoot();
        $postfix = '?';
        $qry_fields = $this->page->request();
        if ( array_key_exists( 'sort_order', $this->defaultOptions ) ) {
            if ( $this->defaultOptions['sort_order'] == 'none' ) {
                $sortFields = array();
            } else {
                $sortFields = explode( ',', $this->defaultOptions['sort_order'] );
            }
            unset($qry_fields['sort_order']);
        } else {
            $sortFields = array();
        }
        if (count($qry_fields) > 0) {
            $flat = array();
            $qry_fields = I2CE_Util::flattenVariables($qry_fields,$flat);
            $fields = array();
            foreach ($flat as $key=>$val) {
                if (is_array($val)) {
                    foreach ($val as $v) {
                        array_push($fields, urlencode($key) . "[]=" . $v);
                    }   
                } else {
                array_push($fields, urlencode($key) . '=' . $val);
                }   
            }   
            $page_root .= '?' . implode('&',$fields);
            $postfix = '&';
        }   
        if ($this->page->hasAjax()) {
            $this->page->getTemplate()->addHeaderLink("mootools-core.js");
        }     
        $sort_order = array();
        if ($this->displayedFields == null) {
            $this->displayedFields = $this->getDisplayFieldsData();
        }   
        $sort_descriptions = array();
        $t_sortFields = array();
        $decreasing = 'Decreasing';
        $increasing = 'Increasing';
        I2CE::getConfig()->setIfIsSet($decreasing,"/modules/CustomReports/text/Decreasing");
        I2CE::getConfig()->setIfIsSet($increasing,"/modules/CustomReports/text/Increasing");
     
        foreach ($sortFields as $i=>$s) {
            if (strlen($s) == 0 ) { 
                continue;
            }   
            if ($s[0] == '-') {
                $s = substr($s,1);
                if ( !array_key_exists($s,$this->displayedFields)) {
                    continue;
                }
                if (!$this->displayedFields[$s]) {
                    continue;
                }
                $sort_descriptions[] = $this->displayedFields[$s]['header'] . ' (Decreasing)';        
                $t_sortFields[$s] = true;
                $sort_order[$s] = '-';
            } else {
                if ( !array_key_exists($s,$this->displayedFields)) {
                    continue;
                }
                if (!$this->displayedFields[$s]) {
                    continue;
                }
                $sort_descriptions[] = $this->displayedFields[$s]['header'] . ' (Increasing)';        
                $t_sortFields[$s] = true;
                $sort_order[$s] = '';
            }
        }
        $sortFields = array_keys($t_sortFields);
        $sort_description = '';
        if (count($sort_descriptions) > 0) {
            $sort_description = implode(',',$sort_descriptions);
        }
        $this->template->setDisplayDataImmediate('sort_description',$sort_description,$contentNode);

        $color_grad = 0;
        if (count($sortFields) > 0) {
            $color_grad = (int) floor((0xFF - 0xCC) /(count($sortFields)));
        }
        foreach( $this->headers as $formfield => $headinfo ) {
            if (!$headinfo) {
                continue;
            }
            $show_sort = false;
            if ( is_array($headinfo) ) {
                $header = $this->displayedFields[$formfield]['header'];
                $head_count = count( $headinfo );
                $head = $this->template->appendFileByName( "customReports_table_head_cell.html", "th", "report_header_one",0,$contentNode );
                if (!$head instanceof DOMNode) {
                    I2CE::raiseError("Could not add head cell to table");
                    return false;
                }
                $head->setAttribute("colspan", $head_count);
                $this->template->appendElementByNode( $head, "span",
                                                    array(
                                                        'id'=>"report_column_header_$formfield"
                                                        ),
                                                    $header );
                $idx = 0;
                foreach( $headinfo as $header => $enabled ) {
                    if ( !$enabled ) {
                        continue;
                    }
                    if ( $header == '' ) {
                        $header = '??';
                    }
                    $head = $this->template->appendFileByName( "customReports_table_head_cell.html", "th", "report_header_two",0,$contentNode );
                    if (!$head instanceof DOMNode) {
                        I2CE::raiseError("Could not add head cell to table");
                        return false;
                    }
                    $this->template->appendElementByNode( $head, "span",
                                                        array(
                                                            'id'=>"report_column_header_${formfield}_$idx"
                                                            ),
                                                        $header );
                    $idx++;
                }
            } else {
                $header = $this->displayedFields[$formfield]['header'];
                $head = $this->template->appendFileByName( "customReports_table_head_cell.html", "th", "report_header_one",0,$contentNode );
                if (!$head instanceof DOMNode) {
                    I2CE::raiseError("Could not add head cell to table");
                    return false;
                }
                $head->setAttribute( "rowspan", "2" );
                if ( count($sortFields) > 0 && $formfield == $sortFields[count($sortFields) -1]) {
                    $this->template->setNodeAttribute( "class", "selected", $head );
                } else {
                    $position = array_search($formfield,$sortFields);
                    if ($position !== false) {
                        $this->template->setNodeAttribute( "class", "secondary_selected", $head );
                        $bgcolor = dechex ((count($sortFields) - $position-1) * $color_grad + 0xCC);
                        $bgcolor = hexdec( $bgcolor . $bgcolor. $bgcolor) - 0x0219;
                        $bgcolor = '#' . dechex($bgcolor);
                        $this->template->setNodeAttribute( "style", "background-color:$bgcolor", $head );
                    }
                }
                $t_sortFields =$sortFields;
                $t_sort_order = $sort_order;
                $key = array_search($formfield,$t_sortFields);
                if ($key !== false) { //the field is already in the sort order
                    unset($t_sortFields[$key]);
                    if ($key == count($sortFields) - 1) { //the field was the first on the list
                        //so switch it's sort order
                        if  ($t_sort_order[$formfield] == '') {
                            //make increasing decreasing
                            $t_sort_order[$formfield] = '-';
                            $t_sortFields[]  = $formfield; //put the field at the end of the list            
                        } else {
                            //remove decreasing from the list (don't need to do anything)
                            unset($t_sort_order[$formfield]);
                        }
                    } else {
                        //sort it by ascending
                        $t_sort_order[$formfield] = '';
                        $t_sortFields[]  = $formfield; //put the field at the end of the list            
                    }
                } else {
                    $t_sort_order[$formfield] = '';
                    $t_sortFields[]  = $formfield; //put the field at the end of the list            
                }
                foreach ($t_sortFields as $i=>$s) {
                    $t_sortFields[$i] = $t_sort_order[$s] . $s;
                }
                if (count($t_sortFields) > 0) {
                    $link = $page_root . $postfix . 'sort_order=' . urlencode(implode(',',$t_sortFields));
                } else {
                    $link = $page_root . $postfix . 'sort_order=none';
                }
                $this->template->appendElementByNode( $head, "a",
                                                    array(
                                                        "href" => $link,
                                                        'id'=>"report_column_header_$formfield"
                                                        ),
                                                    $header );
                if ($this->page->hasAjax()) {
                    $this->page->addAjaxUpdate($this->getReportPrefix().'report_results',
                                            "report_column_header_$formfield",
                                            'click',
                                            $link,
                                            $this->getReportPrefix().'report_results');
                }
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
        $defaultOptions = parent::getDefaultOptions($get,$options);
        //$this->defaultOptions = $defaultOptions;
        foreach (array('limit_page','limit_per_page','limit_offset','limit_amount') as $key) {
            $defaultOptions[$key] = false;
        }
        $defaultOptions['total'] = 1;
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
     * Gets the data about the fields we are going to display.
     */
    public function getDisplayFieldsData() {
        if ( is_array( $this->displayedFields ) ) {
            return $this->displayedFields;
        }
        if ( !array_key_exists( 'displayFieldsTab', $this->defaultOptions ) || !is_array( $this->defaultOptions['displayFieldsTab'] ) ) {
            I2CE::raiseError( "No displayFieldsTab for Cross Tab report." );
            return array();
        }
        $fieldData = parent::getDisplayFieldsData();
        foreach( $fieldData as $reportformfield => $data ) {
            if ( $reportformfield == 'total' || ( array_key_exists( $reportformfield, $this->defaultOptions['displayFieldsTab'] )
                    && $this->defaultOptions['displayFieldsTab'][$reportformfield] != self::CROSSTAB_NONE ) ) {
                continue;
            }
            unset( $fieldData[$reportformfield] );
        }
        $this->displayedFields = $fieldData;
        return $this->displayedFields;
    }


    /**
     * Adds any controls for this display to the content node.
     * @param DOMNode $contentNode 
     * @returns boolean;
     */
    protected function displayReportControl($contentNode) {
        parent::displayReportControl($contentNode);

        $avail_fields = $this->getReportViewDisplayedFields( false, array( '' ) );

        $js = "function validateCrossTabOptions() {\n\tvar ct_values = {'".self::CROSSTAB_LEFT."':0, '".self::CROSSTAB_TOP."':0};\n";

        $field_table = $this->template->getElementById( "form_field_list", $contentNode );
        if ( !$field_table instanceof DOMNode ) {
            I2CE::raiseError( "Unable to find form_field_list id in DOM" );
            return false;
        }

        $field_count = 0;
        foreach( $avail_fields as $reportformfield => $data ) {
            if ( !$data || !is_array( $data ) ) {
                continue;
            }
            $current_value = null;
            if ( array_key_exists( 'displayFieldsTab', $this->defaultOptions) && array_key_exists( $reportformfield, $this->defaultOptions['displayFieldsTab'] ) ) {
                $current_value = $this->defaultOptions['displayFieldsTab'][$reportformfield];
            }
            $field_count++;
            $tr = $this->template->createElement( "tr", array( "class" => "even" ) );
            $attr = array( "type" => "radio", "name" => "displayFieldsTab:$reportformfield", "onchange" => "validateCrossTabOptions();" );

            $td = $this->template->createElement( "td", array(), $data['header'] );
            $tr->appendChild( $td );
            $td_left = $this->template->createElement( "td", array( "style" => "text-align: center; vertical-align: middle;" ) );
            $attr["value"] = self::CROSSTAB_LEFT;
            if ( ($current_value === null ? $field_count == 1 : $current_value == self::CROSSTAB_LEFT ) ) {
                $attr["checked"] = "checked";
            }
            $radio_left = $this->template->createElement( "input", $attr );
            $td_left->appendChild( $radio_left );
            $tr->appendChild( $td_left );
            $td_top = $this->template->createElement( "td", array( "style" => "text-align: center; vertical-align: middle;" ) );
            unset( $attr["checked"] );
            $attr["value"] = self::CROSSTAB_TOP;
            if ( ($current_value === null ? $field_count == 2 : $current_value == self::CROSSTAB_TOP ) ) {
                $attr["checked"] = "checked";
            }
            $radio_top = $this->template->createElement( "input", $attr );
            $td_top->appendChild( $radio_top );
            $tr->appendChild( $td_top );
            $td_none = $this->template->createElement( "td", array( "style" => "text-align: center; vertical-align: middle;" ) );
            $attr["value"] = self::CROSSTAB_NONE;
            unset( $attr["checked"] );
            if ( ($current_value === null ? $field_count > 2 : $current_value == self::CROSSTAB_NONE ) ) {
                $attr["checked"] = "checked";
            }
            $radio_none = $this->template->createElement( "input", $attr );
            $td_none->appendChild( $radio_none );
            $tr->appendChild( $td_none );

            $field_table->appendChild( $tr );
            $js .= "\tct_values[ $('limit_form').getElement('input[name=displayFieldsTab:$reportformfield]:checked').value ]++;\n";
        }
        $js .= "\tif ( ct_values['".self::CROSSTAB_LEFT."'] < 1 || ct_values['".self::CROSSTAB_TOP."'] < 1 ) {\n\t\t$('CrossTab_submit').hide();\n\t\t$('CrossTab_error').show();\n\t} else {\n\t\t$('CrossTab_error').hide();\n\t\t$('CrossTab_submit').show();\n\t}\n}\n";
        //$js .= "\tfor ( i in ct_values ) { alert( i+' '+ct_values[i] ); }\n\treturn true;\n}\n";

        $this->template->addHeaderLink( 'mootools-core.js' );
        $this->template->addHeaderText($js, 'script', true );
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
