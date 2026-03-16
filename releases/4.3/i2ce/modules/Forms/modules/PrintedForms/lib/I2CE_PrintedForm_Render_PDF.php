<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package I2Ce
* @subpackage I2Ce
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.5
* @since v4.0.5
* @filesource 
*/ 
/** 
* Class I2CE_PrintedForm_Render_PDF
* 
* @access public
*/


class I2CE_PrintedForm_Render_PDF extends I2CE_PrintedForm_Render {
    /**
     * @var protected I2CE_PDF $pdf The PDF we are rendering
     */
    protected $pdf;
    /**
      *Abstract method to retreive/display the contents of the rendered forms
     * @param boolean $as_string.  Defaults to false 
     * @returns mixed.  If {$as_string} is false the it is a  boolean true on sucess.  If $as_string is true, then it is a string on success, false on failure
     */
    public function display($as_string = false) {
        if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Got errors:\n$errors");
        }
        $title = addslashes(str_replace(array(' ',"\n","\t") , array('_',' ','_'),$this->std_form) . '.pdf');
        if (!$as_string) {
            $this->pdf->Output($title,'D');
            exit; // we want to make sure there is no further output or that the $this->page->display() method is not called
        } else {
            return $this->pdf->Output($title,'S');
        }
    }


    /**
     * business  method to render the forms
     * @param array $textProps
     * @returns boolean true on sucess.
     */
    protected function _render($textProps) {
        $encoding = new I2CE_Encoding($this->layoutOptions['encoding']);
        $this->pdf = new I2CE_PDF($encoding,$this->layoutOptions['orientation'], 'mm',$this->layoutOptions['size']);
        $this->pdf->SetMargins($this->layoutOptions['horiz_pad'],$this->layoutOptions['vert_pad']);
        $this->pdf->SetAutoPageBreak(false,$this->layoutOptions['vert_pad']);
        $this->pdf->SetCellPadding(0);
        $this->pdf->setCompression(false);
        $hyphen = new I2CE_Hyphen($encoding);
        $hyphen->LoadHyphenDictionary($this->layoutOptions['hyphenation_file']);
        $this->pdf->SetHyphenationDictionary($hyphen);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        return parent::_render($textProps);
    }

    /**
     * Sets the text properties for the pdf
     */
    protected function setTextProps($textProps) {
        $red = hexdec(substr($textProps['color'],1,2));
        $green = hexdec(substr($textProps['color'],3,2));
        $blue = hexdec(substr($textProps['color'],5,2));
        $this->pdf->SetTextColor($red, $green, $blue); 
        $this->pdf->SetTableDataTextColor($red, $green, $blue); 
        if ($textProps['bg_color'] !== 'none') {
            $red = hexdec(substr($textProps['bg_color'],1,2));
            $green = hexdec(substr($textProps['bg_color'],3,2));
            $blue = hexdec(substr($textProps['bg_color'],5,2));       
            $this->pdf->SetTableDataFillColor($red, $green, $blue); 
            $this->pdf->SetFillColor($red, $green, $blue); 
        } else {
            $this->pdf->SetTableDataFillColor(false); 
            //$this->pdf->SetFillColor(255,255,255); 
        }
        $this->pdf->SetFont($textProps['font'], $textProps['style'] ,$textProps['size']);
    }

    /**
     * Add a page to the rendered document
     * @param array $textProps
     */
    protected function addPage($textProps) {
        $this->setTextProps($textProps);
        $this->pdf->addPage();        
        if ($this->layoutOptions['border'] == 0) {
            return;
        }
        for ($r = 0; $r < $this->layoutOptions['cols']; $r++) {
            $left_x =  $this->layoutOptions['horiz_pad'] + $r * ($this->layoutOptions['form_width'] + 2*$this->layoutOptions['horiz_pad_border']  + $this->layoutOptions['border']);
            $right_x = $left_x + $this->layoutOptions['form_width'] + 0*$this->layoutOptions['horiz_pad_border']  + $this->layoutOptions['border'];
            for ($c = 0; $c < $this->layoutOptions['rows']; $c++) {
                $top_y = $this->layoutOptions['vert_pad'] + $c * ($this->layoutOptions['form_height'] + 2*$this->layoutOptions['vert_pad_border']  + $this->layoutOptions['border'] );
                $bottom_y = $top_y + $this->layoutOptions['form_height'] + $this->layoutOptions['vert_pad_border']  + $this->layoutOptions['border'];
		$this->pdf->Line($left_x, $top_y, $left_x, $bottom_y, array('color'=>array(0,0,0)));
		$this->pdf->Line($right_x, $top_y, $right_x, $bottom_y, array('color'=>array(0,0,0)));
		$this->pdf->Line($left_x, $top_y, $right_x, $top_y, array('color'=>array(0,0,0)));
		$this->pdf->Line($left_x, $bottom_y, $right_x, $bottom_y, array('color'=>array(0,0,0)));
            }
        }
    }
    

    /**
     * Abstract business method to render a text element from the elements tree
     * @param int $left_x
     * @param int $top_y
     * @param array $formData of I2CE_Form
     * @param array $textProps
     * @param I2CE_MagicDataNode $elementConfig The node defining the element
     * @returns boolean. True on success
     */
    protected function processElement_text($left_x,$top_y,$formData,$textProps, $elementConfig) {
        // ===Definition for type: Text=== (taken from  http://open.intrahealth.org/mediawiki/Printed_Forms#Magic_Data_Details )
        //The text element is just certain text to be placed in the document.  It should consist of the following nodes:
        //*printf:  Optional scalar node. The a printf string to be placed here.  Defaults to ''.  Example: "%s, %s has registation number %s"
        //*printf_args:  Optional scalar node.  A comma separted list of the report form fields to substitute into the printf.  E.g. "person+surname,person+fisrtname,registation+number"     
        //*horiz_min:  Required numeric scalar node. If the alignment is 'L' it is the left most coordinate to place this text.  If the alignment is 'R' it is the right-most cooridnate of the text
        //*horiz_max: Optional numeric scalar node.  If not set and the allignment is 'J' then the alignment reverts to 'L'.    If set and allignment if 'L' is the right-most coordinate.  If set and alignment is 'R' then it is the left-most coordinate.  If set and alignment is 'J' then the this is the right-most coordinate and ''horiz-min'' is the left-most coodinate.
        //*vert_max: Optional numeric scalar node.  The  bottom most coordinate to place this text.
        //*vert_min:  Required numeric value.   The  bottom most coordinate to place this text.
        $this->setTextProps($textProps);
        $horiz_min = false;
        if (!$elementConfig->setIfIsSet($horiz_min,"horiz_min")) {
            I2CE::raiseError("horiz_min not set");
            return true; //error silently
        }
        $horiz_min = (int) $horiz_min;
        $horiz_min = max(0,$horiz_min);
        if ($horiz_min >= $this->layoutOptions['form_width'] ) {
            I2CE::raiseError("Element does not fit in form");
        }
        $vert_min = false;
        if (!$elementConfig->setIfIsSet($vert_min,"vert_min")) {
            I2CE::raiseError("vert_min not set");
            return true; //error silently
        }
        $horiz_max = false;
        $vert_min = max(0,$vert_min);
        if ($vert_min >= $this->layoutOptions['form_height'] ) {
            I2CE::raiseError("Element does not fit in form");
            return true; //error silently
        }
        $elementConfig->setIfIsSet($horiz_max,"horiz_max");
        $vert_max = false;
        $elementConfig->setIfIsSet($vert_max, "vert_max");
        if ($horiz_max == false) {
            $horiz_max = $this->layoutOptions['form_width'];
        } 
        $horiz_max  = min($horiz_max, $this->layoutOptions['form_width']);
        if ($vert_max == false) {
            $vert_max = $this->layoutOptions['form_height'];
        }
        $horiz_max = min($horiz_max, $this->layoutOptions['form_width'] );
        $vert_max = min($vert_max, $this->layoutOptions['form_height'] );
        $printf = '';
        $printf_args = array();
        $elementConfig->setIfIsSet($printf,"printf");
        $elementConfig->setIfIsSet($printf_args,"printf_args",true);
        $text = $this->processTextString($printf, $printf_args, $formData);
        $this->pdf->SetXY(($left_x + $horiz_min),($top_y + $vert_min));
        if ($textProps['bg_color'] == 'none') {
            $fill = 0;
        }else {
            $fill = 1;
        }
        $this->pdf->MakeTable(array(array($text)),0,$horiz_max - $horiz_min, $textProps['alignment'],false);
        return true;
    }
    
    /**
     * Abstract business method to render a text element from the elements tree
     * @param int $left_x
     * @param int $top_y
     * @param array $formData of I2CE_Form
     * @param array $textProps
     * @param I2CE_MagicDataNode $elementConfig The node defining the element
     * @returns boolean. True on success
     */
     protected function processElement_image($left_x,$top_y,$formData,$textProps, $elementConfig) {
         $image = '';
         if (!$elementConfig->setIfIsSet($image,"image") || !$image) {
             I2CE::raiseError("No image set");
             return true; //error silently
         }
         $image_file = false;
         if (substr($image,0,7) == 'form://') {
             $image = substr($image,7);
             if (strlen($image) == 0) {
                 I2CE::raiseError("No image from form set");
                 return true;
             }             
             list($namedform,$field) = array_pad(explode('+',$image,2),2,'');
             if (!$namedform || !$field) {
                 I2CE::raiseError("Image form $image is not in relationship");
                 return true;
             }
             if (!array_key_exists($namedform,$formData)) {
                 I2CE::raiseError("Form $namedform is not in relationship");
                 return true;
             }
             if ( !($fieldObj = $formData[$namedform]->getField($field)) instanceof I2CE_FormField_IMAGE) {
                 I2CE::raiseError("Field $field of form $namedform is not an image");
                 return true;
             }             
             if (!$fieldObj->isValid()) {
                 return true;
             }
             $image_content = $fieldObj->getBinaryData();
             if (strlen($image_content) == 0) {
                 //no data
                 return true;
             }
             $image_file = $image .'@'. $this->getCurrentId()  . '.' . $fieldObj->getExtension();
             $this->pdf->addImageContent($image_content,$image_file);
         } else {
             $image_file = I2CE::getFileSearch()->search('PDF_IMAGES',$image);
             if (!($image_file)) {
                 $msg = "Header image ($image) not found" .
                     "\nSearch Path is:\n" 
                     . print_r(I2CE::getFileSearch()->getSearchPath('PDF_IMAGES'), true); 
                 I2CE::raiseError($msg);
                 return true; //error silently'
             }
        }         
        $horiz_min = false;
        if (!$elementConfig->setIfIsSet($horiz_min,"horiz_min")) {
            I2CE::raiseError("horiz_min not set");
            return true; //error silently
        }
        $horiz_min = (int) $horiz_min;
        $horiz_min = max(0,$horiz_min);
        if ($horiz_min >= $this->layoutOptions['form_width'] ) {
            I2CE::raiseError("Element does not fit in form");
        }
        $vert_min = false;
        if (!$elementConfig->setIfIsSet($vert_min,"vert_min")) {
            I2CE::raiseError("vert_min not set");
            return true; //error silently
        }
        $horiz_max = false;
        $vert_min = max(0,$vert_min);
        if ($vert_min >= $this->layoutOptions['form_height'] ) {
            I2CE::raiseError("Element does not fit in form");
            return true; //error silently
        }
        $elementConfig->setIfIsSet($horiz_max,"horiz_max");
        $vert_max = false;
        $elementConfig->setIfIsSet($vert_max, "vert_max");
        if ($horiz_max == false) {
            $w = 0;
        }  else {
            $w  = min($horiz_max, $this->layoutOptions['form_width']) - $horiz_min;
        }
        if ($vert_max == false) {
            $h = 0;
        } else {            
            $h = min($vert_max, $this->layoutOptions['form_height'] ) - $vert_min;
        }
        $k = $this->pdf->getScaleFactor();
        $this->pdf->SetXY(($left_x + $horiz_min),($top_y + $vert_min));
        $this->pdf->Image($image_file, $left_x + $horiz_min, $top_y + $vert_min, $w,$h);
        return true;
     }
    
    public  function getFileName() {
        return $this->$std_form .'.pdf';
    }

    public  function getMimeType() {
        return 'application/pdf';
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
