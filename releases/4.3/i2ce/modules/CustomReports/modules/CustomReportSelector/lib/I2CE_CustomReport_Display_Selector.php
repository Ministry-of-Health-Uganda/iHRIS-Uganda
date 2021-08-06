<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
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
* @package i2ce
* @subpackage customreprots
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.11
* @since v4.0.11
* @filesource 
*/ 
/** 
* Class I2CE_CustomReport_Display_Selector
* 
* @access public
*/


class I2CE_CustomReport_Display_Selector extends I2CE_CustomReport_Display_Default{
    /**
     * @var protected I2CE_FormFactory $ff
     */
    protected $ff;
    /*
     *@var protected string $print_f.  The printf string used for returning the display value
     */
    protected $printf;
    /*
     *@var protected string $selectid.  The base id used for the selector
     */
    protected $selctorid;

    /*
     *@var protected string $reportForm.  The form we are select from our reprot
     */
    protected $reportFrom;

    /** 
     *@var protected array printf_args.  The array field names which are arguments to the printf
     */
    protected $printf_args;

    /** 
     *@var protected string style.  The style to use for the display values.
     */
    protected $style;




    /**
     * @var boolean set if the page is allows multiple selection.
     */
    protected $multi_select;
    /**
     * Get the report results prefix for the DOM
     * @return string
     */
    protected function getReportPrefix() {
        return $this->selectid . ':';
    }

    /**
     * The constuctor
     * @param I2CE_Page $page
     * @param string $view
     * @throws Excecption on error
     */
    public function __construct($page,$view) {
        parent::__construct($page,$view);
        $this->ff = I2CE_FormFactory::instance();
        $this->selectid = $page->request('select_id');
        $this->printf = $page->request('select_printf');
        if ( $page->request_exists( 'multi_select' ) ) {
            $this->multi_select = $page->request('multi_select');
        } else {
            $this->multi_select = false;
        }
        if ( $page->request_exists( 'select_style' ) ) {
            $this->style = $page->request('select_style');
        } else {
            $this->style = 'default';
        }


        $this->printf_args = explode(",",$page->request('select_printfargs'));
        if (! ( $this->reportForm = $page->request('select_reportform'))) {
            $this->reportForm = 'primary_form';
        }
        $page->getTemplate()->addHeaderLink('reportSelector.css');
    }

    /**
     *Get the query fields for the jumper
     * @returns array
     */
    protected function getJumperQryFields() {
        $qry_fields = parent::getJumperQryFields();
        $qry_fields['select_printf'] = $this->printf;
        $qry_fields['select_printfargs'] = implode(",",$this->printf_args);
        $qry_fields['select_reportform'] = $this->reportForm;
        $qry_fields['select_id'] = $this->selectid;
        $qry_fields['select_style'] = $this->style;
        return $qry_fields;
    }


    /**
     * Adds any report display controls that can be added for this view.
     * @param DOMNode $conentNode
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean $true on success
     */
    protected function displayReportControls($contentNode, $controls=null) {
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('I2CE_ClassValues.js');
        $this->template->addHeaderLink('I2CE_SubmitButton.js');
        $this->template->addHeaderLink("mootools-more.js");
        $this->template->addHeaderLink("getElementsByClassName-1.0.1.js");
        $this->template->addHeaderLink("I2CE_ClassValues.js");       
        $this->template->addHeaderLink("I2CE_Window.js");       
        $this->template->addHeaderLink("I2CE_ToggableWindow.js");       
        $this->template->addHeaderLink("I2CE_TreeSelect.js");       
        $this->template->addHeaderLink('Observer.js');
        $this->template->addHeaderLink('Autocompleter.js');
        $this->template->addHeaderLink('Autocompleter.css');
        $this->template->addHeaderLink('I2CE_TreeSelectAutoCompleter.js');
        $this->template->addHeaderLink("Tree.css");       
        $controlNode = $this->template->createElement('span',array('class'=>"CustomReport_control",'id'=>"CustomReport_controls_Selector"));            
        $contentNode->appendChild($controlNode);
        $this->displayReportControl($controlNode);
        $action =  $this->getBasePage() . "/" . $this->view . "/" . $this->display;
        $flds = $this->getJumperQryFields();
        foreach ($flds as $key=>&$fld) {
            if ( substr($key, 0, 7) == 'limits:' ) {
                $fld = '';
            } else {
                $fld = $key . '=' . urlencode($fld);
            }
        }
        unset($fld);
        $limit_form = $this->selectid . ":content";
        $this->template->setAttribute( "id", 
                $this->getReportPrefix() . "report_results_with_limits",
                "report_results_with_limits" );
        $this->template->setAttribute( "id", 
                $this->getReportPrefix() . "Selector_submit",
                "Selector_submit" );
        $this->template->setAttribute( "id", 
                $this->getReportPrefix() . 'report_results',
                "report_results" );

        $action .= '?' . implode("&", $flds);
        $this->template->addAjaxUpdate($this->getReportPrefix() . 'report_results_with_limits',$this->getReportPrefix().'Selector_submit','click',$action,$this->getReportPrefix().'report_results_with_limits',array('stub_events','formworm','treeselect','ajax_list'),$limit_form,true);
        return true;
    }

    
    protected function getPivots() {
        //we don't want to allow pivoting
        return array();
    }

    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into. If null, we do not do any of the DOM processing stuff, do
     * not call the report display controls, limits etc. It will however still call processResults with a DOMNode of null
     * @param boolean $processResults Defaults to true meaning we run through the results.  If false, we do not process results.
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display($contentNode,$processResults = true, $controls = null) {
        if (!$this->selectid || !$this->printf || !is_array($this->printf_args) || count($this->printf_args) == 0) {
            return false;
        }
        return parent::display($contentNode,$processResults,$controls);
    }

    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        if ($this->formfields == null) {
            $this->formfields = $this->getDisplayFieldsData();
        }
        $mapped_row = $this->mapResults($row);
        $rowNode = $this->template->appendFileByNode( "customReports_Selector_table_data_row.html", "tr",$contentNode);
        if (!$rowNode instanceof DOMNode) {
            I2CE::raiseError("Could not add row to table");
            return false;
        }
        $this->template->setDisplayDataImmediate( "row_count", $row_num+1 ,$rowNode);                       
        foreach ($this->formfields as $formfield =>$data) {
            list($formname,$fieldname) = explode('+',$formfield,2);
            if (!$data) {
                continue;
            }
            if (!$this->isMapped($formfield)) {
                list($form,$field,$agg) = array_pad(explode('+',$formfield,3),3,'');
                $formid = $form . '+id';
            }
            $cellNode = $this->template->appendFileByNode( "customReports_Selector_table_data_cell.html", "td", $rowNode );
            if (!$cellNode instanceof DOMNode) {
                I2CE::raiseError("Could not add data cell to table");
                return false;
            }
            $clickNode = $this->template->getElementById('select_link',$cellNode);
            if (!$clickNode  instanceof DOMElement) {
                I2CE::raiseError("Could get selector in  cell ");
                return false;
            }            
            $clickNode->removeAttribute('id');
            if ($formname == $this->reportForm && array_key_exists($this->reportForm . '+id', $mapped_row)) {
                //$formObj = $this->ff->createForm($mapped_row['primary_form+id']);
                $formObj = $this->ff->createForm($mapped_row[$this->reportForm. '+id']);
                if (!$formObj instanceof I2CE_Form) {
                    continue;
                }
                $formObj->populate();
                $vals = array();
                foreach ($this->printf_args as $field) {
                    $fieldObj = $formObj->getField($field);
                    if ($fieldObj instanceof I2CE_FormField) {
                        $vals[] = $fieldObj->getDisplayValue(null, $this->style);
                    } else {
                        $vals[] = '';
                    }
                }
                $disp =     @vsprintf($this->printf , $vals );
                if (!$this->multi_select) {
                    $js = 'var disp = document.id("' .addslashes($this->selectid . ":display") . '");  ';
                    $js .= 'var value = document.id("'. addslashes($this->selectid . ":value") . '");';
                    $js .= 'if (disp && value) { '
                        .'disp.textContent = "' . addslashes($disp) . '"; value.set("value","' . addslashes($mapped_row[$this->reportForm . '+id']) . '");' 
                        . 'reportselectors["' . addslashes($this->selectid) . '"].hide();'
                        .'} ';
                } else {
                    list($rform,$rid) = array_pad(explode("|",$mapped_row[$this->reportForm . '+id'],2),2,'');
                    if ($rform && $rid != '0') {
                        $js  = 'var disp = document.id("' .addslashes($this->selectid . ":display") . '");  '; 
                        $js .= 'var value = document.id("'. addslashes($this->selectid . ":value") . '");';
                        $js .= 'if (!disp && value) { return false;}';
                        $js .= 'var val = JSON.decode(value.get("value")); if (!val) {val = {};} ';                        
                        $js .= 'var key =false;';
                        $js .= 'Object.each(val , function(e,k) {if  (("' . addslashes($rform) . '" == e[0]) && ("' . addslashes($rid) . '" == e[1])) {key = k;}});';
                        $js .= "var d;";
                        $js .= 'if (key === false) {'; //need to add it
                        $js .= ' d = disp.textContent.replace(/^Select Value,*/,"");';
                        $js .= ' if (d) { d +=  ",' . addslashes($disp) . '"; } else {d = "' . addslashes($disp) . '";} ';
                        $js .= ' var next = Math.max.apply(null,Object.keys(val));';
                        $js .= ' if (next>= 0) { next++;} else { next = 0}' ;
                        $js .= ' val[next] = {0:"' . addslashes($rform) . '" , 1:"' . addslashes($rid) . '"}';
                        $js .= '}else{ ';//need to remove it
                        $js .= ' delete val[key]; ';
                        $js .= ' var re = new RegExp("' . addslashes($disp) . ',?");';
                        $js .= ' d = disp.textContent.replace(re,"");';
                        $js .= '}';
                        $js .= 'd=d.replace(/^[\s,]+|[,\s]+$/g,""); if (!d) {d="Select Value";}';
                        $js .= ' disp.textContent = d;';
                        $js .= 'value.set("value",JSON.encode(val));' ;
                        $js .= ' return true;';
                        
                    }
                }
                $clickNode->setAttribute('onClick', $js);
                $this->template->addClass($clickNode, 'reportSelection');
                $this->template->addClass($clickNode, $this->selectid . '_toggle');
            }
            $this->template->setDisplayDataImmediate( "report_data",  $mapped_row[$formfield],$cellNode );
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
