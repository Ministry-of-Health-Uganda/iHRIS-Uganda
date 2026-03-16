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


class I2CE_CustomReport_Display_PDF extends I2CE_CustomReport_Display{

    protected function canView() {
        return true;
    }


    protected $resultsTable;

    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into
     * @param boolean $processResults Defaults to true meaning we run through the results
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @return boolean. true on success
     */
    public function display($contentNode,$processResults=true, $controls = null) {
        $this->saveDefaultView();
        $pdf = $this->getPDF( $contentNode, $processResults, $controls );
        if ( !$pdf instanceof I2CE_PDF ) {
            return $pdf;
        }
        $inline = 1;
        I2CE::getConfig()->setIfIsSet($inline,"/modules/CustomReports/displays/PDF/display_options/inline");
        $title = '';
        if ($this->defaultOptions['header']['title_prefix']) {
            $title = $this->defaultOptions['header']['title_prefix'] . ': ';
        }
        $title.= $this->config->display_name;
        if ($inline) {
            $pdf->Output($title,'I');
        } else {
            $pdf->Output($title,'D');
        }

        exit; // we want to make sure there is no further output or that the $this->page->display() method is not called
    }

    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into
     * @param boolean $processResults Defaults to true meaning we run through the results
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @return mixed I2CE_PDF on success, boolean otherwise
     */
     public function getPDF($contentNode,$processResults=true, $controls = null) {
        I2CE::longExecution( array( "max_execution_time" => 1800 ) );
        $this->resultsTable = array();
        if ($this->defaultOptions['table']['has_header']) {
            $formfields = $this->getDisplayFieldsData();
            $headers = array('#');
            foreach ($formfields as $formfield=>$data) {
                if (!$data) {
                    continue;
                }
                $headers[$formfield] = $data['header'];
            }
            $this->resultsTable[] = $headers;
        }
        $data = $this->getResults();
        if (!$this->processResults($data,$contentNode)) {
            I2CE::raiseError("Could not get results");
            return false;
        }        
        $encoding = new I2CE_Encoding('ASCII');
        //make sure we have somethin in our options
        $pdf = new I2CE_PDF($encoding,
                                  $this->defaultOptions['paper_orientation'], 
                                  $this->defaultOptions['unit_of_measure'],
                                  $this->defaultOptions['paper_size']); //portrait, we are doing measurements in inches, letter paper
        //setup the main page display style
        $this->addFont($this->defaultOptions['main']['font'],$pdf);
        $pdf->SetTopMargin($this->defaultOptions['main']['top_margin']);
        $pdf->SetCompression($this->defaultOptions['compression']);
        $pdf->SetLineSpacing($this->defaultOptions['line_spacing']);


        //setup the header
        $pdf->setPrintHeader($this->defaultOptions['header']['show']);
        $pdf->setPrintFooter(false);
        $this->addFont($this->defaultOptions['header']['font'],$pdf);
        $pdf->setHeaderFont($this->defaultOptions['header']['font']['name'],
                                  $this->defaultOptions['header']['font']['style'],
                                  $this->defaultOptions['header']['font']['size']);
        $title = '';
        if ($this->defaultOptions['header']['title_prefix']) {
            $title = $this->defaultOptions['header']['title_prefix'] . ': ';
        }
        $title.= $this->config->display_name;
        $text = '';
        if ($this->defaultOptions['header']['text_prefix']) {
            if ($this->config->description) {
                $text = $this->defaultOptions['header']['text_prefix'] . ': ' . $this->config->description;
            } else {
                $text = $this->defaultOptions['header']['text_prefix'];
            }
        } else {
            if ($this->config->description) {
                $text= $this->config->description;
            } else {
                $text = '';
            }
        }        
        $user = new I2CE_User();
        $name = $user->firstname . ' ' . $user->lastname;
        $time = strftime("%c");
        $desc = "This report was printed by $name on $time.\n";
        $limitsDesc = $this->getReportLimitsDescription();
        if ( strlen( $limitsDesc ) > 0 ) {
            $desc .= "Report Limited by: " . $limitsDesc . "\n";
        }
        if ( array_key_exists( 'message', $this->defaultOptions['header'] ) ) {
            $desc .= $this->defaultOptions['header']['message'] . "\n";
        }
        $pdf->setHeaderData($this->defaultOptions['header']['logo']['file'],
                            $this->defaultOptions['header']['logo']['width'],
                            $title,$text,$desc);
        
        $pdf->setHeaderMargin($this->defaultOptions['header']['margin']);
                
        // load our hyphenation dictionary                      
        $hyphen = new I2CE_Hyphen($encoding);
        $hyphen->LoadHyphenDictionary($this->defaultOptions['hyphenation_file']);
        $pdf->SetHyphenationDictionary($hyphen);


        //setup table style

        $pdf->SetTableHeaderFillColor($this->defaultOptions['table']['header']['fill_color']);
        $pdf->SetTableHeaderTextColor($this->defaultOptions['table']['header']['text_color']);
        $pdf->SetTableDataFillColor($this->defaultOptions['table']['data']['fill_color']);
        $pdf->SetTableDataTextColor($this->defaultOptions['table']['data']['text_color']);
        $pdf->SetMinTableCellWidth($this->defaultOptions['table']['min_cell_width']);
        $pdf->SetTableFramingColor($this->defaultOptions['table']['framing_color']);
        $pdf->SetTableColSpacing($this->defaultOptions['table']['column_spacing']);
        if (strtolower($this->defaultOptions['table']['width_style']) == 'explicit') {
            if (!empty($this->defaultOptions['table']['explicit_widths'])) {
                $pdf->SetTableWidths($this->defaultOptions['table']['explicit_widths']);
            } else {
                //fall back to a safe option
                $pdf->SetAutoTableWidthStyle('ALL');
            }                                   
        } else {
            $pdf->SetAutoTableWidthStyle($this->defaultOptions['table']['width_style']);
        }


        //get on with displaying the report
        $this->setFont($this->defaultOptions['main']['font'],$pdf);
        $pdf->AddPage();
        if ($this->defaultOptions['table']['has_header']) {
            if ($this->defaultOptions['table']['use_running_header'])  {
                $table_header_options = 2;
            } else {
                $table_header_options = 1;
            }
        } else {
            $table_header_options = 0;
        }
        $pdf->MakeTable($this->resultsTable,
                        $this->defaultOptions['table']['border'],
                        $this->defaultOptions['table']['max_width'], 
                        $this->defaultOptions['table']['data']['justification'],
                        $table_header_options,
                        $this->defaultOptions['table']['header']['justification'],1);
        $pdf->Close();
        $title = addslashes(str_replace(array(' ',"\n","\t") , array('_',' ','_'),$this->config->display_name) . '.pdf');
        if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Errors:\n" . print_r($errors,true));
        }
        return $pdf;
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
        array_unshift($mapped_row, '' . ($row_num+1));
        $this->resultsTable[] = $mapped_row;
        return true;
    }
   



    /**
     * adds the font so we can use it... ideally should move this to I2CE_PDF, but it is a mess there.
     */
    protected function addFont($font,$pdf) {
        $type = strtolower($font['type']);
        switch ($type) {
        case 'ttf':
            $pdf->AddFontByTTFFile($font['name'],$font['style'],$font['file']);
            break;
        case 'core':
            //don't do anything
            break;
        default:
            //unrecognized type
            I2CE::raiseError("Unknown font type ($type) for PDF file", E_USER_ERROR);
            break;
        }
    }


    /**
     * sets the font... ideally should move this to I2CE_PDF, but it is a mess there.
     */
    protected function SetFont($font,$pdf) {
        $pdf->SetFont($font['name'],$font['style'],$font['size']);
    }   






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
