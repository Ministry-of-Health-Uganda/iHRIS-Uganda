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
*  I2CE_CustomReport_Display_Default -- the default HTML display of a report view
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_CustomReport_Display_Default extends I2CE_CustomReport_Display{
    

    protected function canView() {
        return true;
    }


    protected function noData($contentNode) {
        if (! ($noDataNode = $this->template->appendFileByNode( "customReports_notfound.html",'div',$contentNode )) instanceof DOMNode) {
            return;
        }
        $link = '';
        if (!$this->config->setIfIsSet($link,'create_link') || !$link) {
            return;
        }
        $createNode = $this->template->addFile( "customReports_notfound_create.html", 'div', $noDataNode );
        if ( !$createNode instanceof DOMNode ) {
            return;
        }
        if (! ($linkNode = $this->template->getElementByName('create_form_link',0)) instanceof DOMNode) {
            return;
        }
        $this->template->addHeaderLink('create_from_limits.js');
        $rel = $this->reportObj->getFormRelationship();
        $form = $rel->getPrimaryForm();
        if (! ($formObj = I2CE_FormFactory::instance()->createContainer($form)) instanceof I2CE_Form) {
            return;
        }

        $form_name = $formObj->getDisplayName();
        $js = 'createFormFromSearch(this,"' . $form  . '");';
        $linkNode->setAttribute('onClick',$js);
        $this->template->setDisplayData('has_create_data',1,$createNode);        
        $this->template->setDisplayData('create_form_link',$link,$createNode);
        $this->template->setDisplayData('create_form_name',$form_name,$createNode);
    }


    /**
     * Process results
     * @param array $results_data an array of results.  indices are 'results' and Buffered result and 'num_results' the
     * number of results.  (these values may be false on failure)
     * @param DOMNode $contentNode.  Default to null a node to append the results onto
     */
    protected function processResults($results_data,$contentNode=null) {
        $this->pivots = $this->getPivots();
        //add int the header fields to the table
        if ( !$results_data['num_results'] ) {
            $this->noData($contentNode);
            return true;
        }
        $tableContainerNode = $this->template->appendFileByNode( "customReports_table.html",'div',$contentNode ); //add the report table shell
        if (!$tableContainerNode instanceof DOMNode) {
            I2CE::raiseError("Could not add table container template");
            return false;
        }
        $this->template->setAttribute( "id", 
                $this->getReportPrefix() . 'report_pager_display',
                "report_pager_display" );
        $this->doJumper($results_data['num_results'],$tableContainerNode);
        $reportBodyNode= $this->template->getElementById('report_body',$tableContainerNode);
        if (!$reportBodyNode instanceof DOMNode) {
            I2CE::raiseError("Could not find report body");
            return false;
        }
        $this->doHeaderRow($tableContainerNode);
        $this->template->setDisplayDataImmediate('num_results',$results_data['num_results'],$tableContainerNode);
        return parent::processResults($results_data,$reportBodyNode);
    }


    /**
     * @var protected array $formfields The array of formfields to display for this report.
     * Keys are the formfields 
     */
    protected $formfields = null;

    /**
     * Get the page root to use for this page.
     * @return string
     */
    protected function getPageRoot() {
        return "CustomReports/show/{$this->view}/{$this->display}";
    }




    protected function doHeaderRow($contentNode) {
        $page_root =  $this->getPageRoot();
        $postfix = '?';
        $qry_fields = $this->page->request();
        if (array_key_exists('sort_order',$this->defaultOptions)) {
            if ($this->defaultOptions['sort_order'] == 'none') {
                $sortFields = array();
            } else {
                $sortFields = explode(',',$this->defaultOptions['sort_order']);
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
        if ($this->formfields == null) {
            $this->formfields = $this->getDisplayFieldsData();
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
                if ( !array_key_exists($s,$this->formfields)) {
                    continue;
                }
                if (!$this->formfields[$s]) {
                    continue;
                }
                $sort_descriptions[] = $this->formfields[$s]['header'] . ' (Decreasing)';                
                $t_sortFields[$s] = true;
                $sort_order[$s] = '-';
            } else {
                if ( !array_key_exists($s,$this->formfields)) {
                    continue;
                }
                if (!$this->formfields[$s]) {
                    continue;
                }
                $sort_descriptions[] = $this->formfields[$s]['header'] . ' (Increasing)';                
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
        foreach ($this->formfields as $formfield=>$data) {
            if (!$data) {
                continue;
            }
            $head = $this->template->appendFileByName( "customReports_table_head_cell.html", "th", "report_header",0,$contentNode );
            if (!$head instanceof DOMNode) {
                I2CE::raiseError("Could not add head cell to table");
                return false;
            }
            if ( count($sortFields) > 0 && $formfield == $sortFields[count($sortFields) -1]) {
                $this->template->setNodeAttribute( "class", "selected", $head );
            }else {
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
                                                  $data['header'] );
            if ($this->page->hasAjax()) {
                $this->page->addAjaxUpdate($this->getReportPrefix().'report_results', 
                                           "report_column_header_$formfield", 
                                           'click', 
                                           $link,
                                           $this->getReportPrefix().'report_results',
                                           array( 'stub_events','formworm','treeselect','ajax_list') );
            }
        }
    }

    /**
     * Display the results jumper
     * @param mixed $num_results  Either boolean (false) if we don't have the total number of results  or an integer the number of results
     * @return boolean true on sucess
     */
    protected function doJumper($num_results,$contentNode) {
        $pager_id = $this->getReportPrefix() . 'report_pager_display';
        if (($num_results === false) || ( ((int) $num_results) < 1) ||
            ($this->page->request_exists('limit_paginated') && !$this->page->request('limit_paginated'))) {            
            $this->template->removeNodeById($pager_id,$contentNode);            
            return;
        }
        //take care of the jumper
        $total_pages = 1;
        $per_page = (int) ($this->defaultOptions['limit_per_page']);
        if ($per_page <1) {
            //check it is not bad, if so make it something reasonable -- in fact make it the default per page in I2CE_CustomReport_Display
            $per_page = 100;
        }
        $page = (int) $this->defaultOptions['limit_page'];
        //$page = (int) $this->page->request('limit_page');
        if ($page < 1) {
            $page = 1;
        }
        if (I2CE::PDO()->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY)) {
            $num_rows = $num_results;
            $total_pages = ceil( $num_rows / $per_page );
            if ( $page > $total_pages ) {
                $page = $total_pages ;
            }
        }
        if ($total_pages == 1) {
            $this->template->removeNodeById($pager_id,$contentNode);            
            return;
        }        
        $url = $this->getPageRoot();
        $qry_fields = $this->getJumperQryFields();
        $q = array();
        foreach ($qry_fields as $i=>$v) {
            $q[urlencode($i)  ] = $v;
        }
        $this->page->makeScalingJumper($this->getReportPrefix().'report',$page,$total_pages,$url,$q,'limit_page');            
    }


    /**
     *Get the query fields for the jumper
     * @returns array
     */
    protected function getJumperQryFields() {
        $qry_fields = $this->page->request();
        foreach (array('limit_page') as $key) {
            unset($qry_fields[$key]);
        }
        $qry_fields = I2CE_Page::flattenRequestVars($qry_fields);        
        return $qry_fields;
    }

    

    
    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        //echo "<pre>";var_dump($row); die();
        if ($this->formfields == null) {
            $this->formfields = $this->getDisplayFieldsData();
        }
        $mapped_row = $this->mapResults($row);
        $rowNode = $this->template->appendFileByNode( "customReports_table_data_row.html", "tr",$contentNode);
        if (!$rowNode instanceof DOMNode) {
            I2CE::raiseError("Could not add row to table");
            return false;
        }
        $this->template->setDisplayDataImmediate( "row_count", $row_num+1 ,$rowNode);                       
        foreach ($this->formfields as $formfield =>$data) {
            if (!$data) {
                continue;
            }
            $lformfield = strtolower($formfield);
            $pivots = array();
            if (array_key_exists($formfield,$this->pivots) && is_array($this->pivots[$formfield]) && count($this->pivots[$formfield]) > 0) {
                foreach ($this->pivots[$formfield] as $p) {
                    //$p = $this->pivots[$formfield];
                    $p['val'] = $row->$lformfield;
                    $pivots[] = $p;
                }
            }
            list($form,$field,$agg) = array_pad(explode('+',$formfield,3),3,'');
            $formid = $form . '+id';
            $lformid = strtolower($formid);
            if ($formid != $formfield && !$this->isMapped($formfield)) {
                if (array_key_exists($formid,$this->pivots) && array_key_exists($lformid,$row)) {                    
                    foreach ($this->pivots[$formid] as $p) {
                        //$p = $this->pivots[$formid];                        
                        $p['val'] = $row->$lformid;
                        $pivots[] = $p;
                    }
                }
            }
            if ($data['link']) {
                $type = 'link';
                if ( $data['link_type'] ) {
                    $type = $data['link_type'];
                }
                $cellNode =$this->template->appendFileByNode( "customReports_table_${type}_cell.html", "td", $rowNode );
                $link_append = '';
                if ($data['link_append']) {
                    list($mergekey,$mergereport,$formfieldagg) = array_pad(explode(':',$formfield,3),-3,'');
                    if ($mergekey) {
                        $data['link_append'] = $mergekey . ':' . $mergereport . ':' . $data['link_append'];
                    }
                    $llinkappend = strtolower($data['link_append']);
                    $link_append = $row->$llinkappend;
                }
                //$link_id = substr( $formfield, 0, strpos( $formfield, "+" ) ) . "+id";
                $this->template->setDisplayDataImmediate( "report_link", $data['link'] . $link_append,$cellNode );
                $reportLinkNode = $this->template->getElementByName("report_link", -1);
                if ( array_key_exists( 'target', $data ) ) {
                    $reportLinkNode->setAttribute('target',$data['target']);
                }
                if (!$cellNode instanceof DOMNode) {
                    I2CE::raiseError("Could not add linked data cell to table");
                    return false;
                }
            }  else {
                $cellNode = $this->template->appendFileByNode( "customReports_table_data_cell.html", "td", $rowNode );
                if (!$cellNode instanceof DOMNode) {
                    I2CE::raiseError("Could not add data cell to table");
                    return false;
                }
            }
            if ( count($pivots) > 0 &&  ($pivotsNode = $this->template->appendFileByNode( "customReports_pivot.html", "span", $cellNode )) instanceof DOMNode) {
                if (($pivotList = $this->template->getElementById("pivot_list", $pivotsNode)) instanceof DOMElement &&
                    ($pivotMenu = $this->template->getElementById("pivot_menu", $pivotsNode)) instanceof DOMElement) {
                    
                    $id = 'pivot_list:' .$row_num . ':' . $formfield;                                        
                    $js = "var node = \$('$id'); if (node) {if (node.getStyle('display') == 'none') { node.setStyle('display','block');} else {node.setStyle('display','none'); }}";
                    $pivotList->setAttribute('id',$id);
                    $pivotMenu->setAttribute('onClick',$js);
                    foreach ($pivots as $pivot_data) {
                        $link = 'CustomReports/show/' . $pivot_data['relatedView'] . "?" .  urlencode("limits:" . $pivot_data['pivotReportForm'] . '+' .$pivot_data['pivotField']  . ':' . $pivot_data['pivotLimit'] . ':value') . '=' . urlencode($pivot_data['val']);
                        if ( !($pivotNode =$this->template->appendFileByNode("customReports_pivot_each.html","li",$pivotList)) instanceof DOMNode) {
                            continue;
                        }
                        $this->template->setDisplayData('view_name',$pivot_data['view_name'], $pivotNode);
                        $this->template->setDisplayData('view_link',$link, $pivotNode);

                    }
                }
            }
            if (!array_key_exists($lformfield,$mapped_row)) {
                continue;
            }

            $this->template->setDisplayDataImmediate( "report_data",  $mapped_row[$lformfield],$cellNode );
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
