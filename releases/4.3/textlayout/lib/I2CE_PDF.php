<?php

/*
 * Class to handle generation of PDF files.  
 * In particular prints nicely formatted tables.
 *
 *  This class extends the class tcpdf which may be found at:
 *     <a href="http://tcpdf.sourceforge.net">http://tcpdf.sourceforge.net</a>
 *
 *
 * @package I2CE
 * @subpackage TextLayout
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007 IntraHealth International, Inc. 
 * This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software Foundation; either 
 * version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
 * received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
 * @version 0.1
 * @access public
 * @see tcpdf
 * @uses I2CE_TextTable
 * @uses I2CE_Encoding
 * @uses I2CE_TextTable
 * @uses I2CE_FontMetric
 * @uses I2CE_FontMetricMultiDirection
 * @uses I2CE_FontMetricAFM
 * @uses I2CE_FontMetricTTF
 */

 
class I2CE_PDF extends tcpdf{


    /**
     * @var If true prints header
     * @access protected
     */
    protected $print_header = true;
        
    /**
     * @var If true prints footer.
     * @access protected
     */
    protected $print_footer = true;
                
    /**
     * @var Header width (0 = full page width).
     * @access protected
     */
    protected $header_width = 0;
        
    /**
     * @var Header image logo.
     * @access protected
     */
    protected $header_logo = "";
        
    /**
     * @var Header image logo width in mm.
     * @access protected
     */
    protected $header_logo_width = 30;

    /**
     * @var Header font.
     * @access protected
     */
    protected $header_font;
        
    /**
     * @var Footer font.
     * @access protected
     */
    protected $footer_font;
                
    /**
     * @var String to print as title on document header.
     * @access protected
     */
    protected $header_title = "";
                
    /**
     * @var String to print on document header.
     * @access protected
     */
    protected $header_string = "";

    /**
     * @var String to print as a descroption under the logo in the document header
     * @access protected $header_desc
     */
    protected $header_desc = "";


    /**
     * @var protecterd I2CE_TextCell The wordwrapped text cell with the header desc 
     * @access protected $header_desc_cell
     */
    protected $header_desc_cell = false;


    /**
     * @var Minimum distance between header and top page margin.
     * @access protected
     */
    protected $header_margin;
        
    /**
     * @var Minimum distance between footer and bottom page margin.
     * @access protected
     */
    protected $footer_margin;
        
    /**
     * @var original left margin value
     * @access protected
     * @since 1.53.0.TC013
     */
    protected $original_lMargin;
        
    /**
     * @var original right margin value
     * @access protected
     * @since 1.53.0.TC013
     */
    protected $original_rMargin;

    /**
     * protected @var I2CE_Encoding enc
     */
    protected $enc;


    /**
     * protected @var I2CE_TextTable $text_table
     */
    protected $text_table;



    /**
     * protected @var array of I2CE_FontMetric font_metrics
     */
    protected $font_metric;

    /**
     * @var boolean $running_header True if we want the header of a table repeated on each page
     */
    protected $running_header  = true;

    /**
     * @var numeric $row_spacing  -- the spacing (in points) between succesive rows of text in  a table
     */
    protected $row_spacing;

    /**
     * @var array of I2CE_Encodings $adobe_standard_encodings
     */
    protected $adobe_standard_encodings;

    /**
     * This is the class constructor. 
     * It allows to set up the page format, the orientation and 
     * the measure unit used in all the methods (except for the font sizes).
     * @param I2CE_Encoding $enc charset encoding for all input strings
     * @param string $orientation page orientation. Possible values are (case insensitive):<ul><li>P or Portrait (default)</li><li>L or Landscape</li></ul>
     * @param string $unit User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul><br />A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.
     * @param mixed $format The format used for pages. It can be either one of the following values (case insensitive) or a custom format in the form of a two-element array containing the width and the height (expressed in the unit given by unit).<ul><li>4A0</li><li>2A0</li><li>A0</li><li>A1</li><li>A2</li><li>A3</li><li>A4 (default)</li><li>A5</li><li>A6</li><li>A7</li><li>A8</li><li>A9</li><li>A10</li><li>B0</li><li>B1</li><li>B2</li><li>B3</li><li>B4</li><li>B5</li><li>B6</li><li>B7</li><li>B8</li><li>B9</li><li>B10</li><li>C0</li><li>C1</li><li>C2</li><li>C3</li><li>C4</li><li>C5</li><li>C6</li><li>C7</li><li>C8</li><li>C9</li><li>C10</li><li>RA0</li><li>RA1</li><li>RA2</li><li>RA3</li><li>RA4</li><li>SRA0</li><li>SRA1</li><li>SRA2</li><li>SRA3</li><li>SRA4</li><li>LETTER</li><li>LEGAL</li><li>EXECUTIVE</li><li>FOLIO</li></ul>
     */
    public function __construct($encoding, $orientation='P', $unit = 'mm',$format='A4')  {
        parent::__construct($orientation, $unit,$format,$encoding->useMB(),$encoding->getEncodingType());
        $this->enc = $encoding;
        $this->font_metrics = array();
        $fm = null;
        $hyphen = null;
        $this->text_table = new I2CE_TextTable( $this->fwPt,$fm,$hyphen,$encoding);
        $this->SetLineSpacing(1);
    }


    public function Error($msg, $level = E_WARNING) {
        //Fatal error
        I2CE::raiseError($msg,$level);
    }
        
    /**
     * Add image content
     * @param string $content The image binary data
     * @param string $reference_name.  A "fake" file  name to reference the image content when calling {Image()}.  Should include the correct extension for the file
     */
    public function addImageContent($content,$reference_name) {
        $image = imagecreatefromstring($content);
        $pos = strrpos($reference_name,'.');
        if(empty($pos)) {
            $this->Error('Image reference_name has no extension and no type was specified: '.$reference_name);
        }
        $type = strtolower(substr($reference_name, $pos+1));
        //$mqr = get_magic_quotes_runtime();
        //set_magic_quotes_runtime(0);
        if($type == 'jpg' or $type == 'jpeg') {
            $jpeg_content = $content;
        } else {
            ob_start();
            if (!imagejpeg($image)) {
                $this->Error("Can't convert $reference_name to jpeg");
                return false;
            }
            $jpeg_content = ob_get_contents();
            ob_end_clean();
        }
        $colspace='DeviceRGB';
        $bpc=8;  		
        $info =  array('w'=>imagesx($image),'h'=>imagesy($image),'cs'=>$colspace,'bpc'=>$bpc,'f'=>'DCTDecode','data'=>$jpeg_content);			;
        $info['i']=count($this->images)+1;
        $this->images[$reference_name]=$info;
    }




    /**
     * get the fontkey associated to a fmaily and style
     * @param string $family
     * @param string $style
     * @param boolean  $modify_style
     * @returns string
     */
    protected function getFontKey(&$family,&$style, $modify_style = false) {
        if (!$modify_style) {
            $t_style = $style;
        }
        //normalize the font family and style
        if ($family == '') {
            $family = $this->FontFamily;
        }
        if(strpos($style,'U')!==false) {
            $this->underline=true;
            $style=str_replace('U','',$style);
        } else {
            $this->underline=false;
        }
        $family = strtolower($family);
        if(($family == 'arial')) {
            $family = 'helvetica';
        }
        if(($family=="symbol") || ($family=="zapfdingbats")) {
            $style='';
        }

        $style=strtoupper($style);
        $style=str_replace('U','',$style);
        if($style == 'IB') {
            $style = 'BI';
        }
        $ret =  $family.$style;
        if (!$modify_style) {
            $style = $t_style;
        }
        return $ret;
    }

        

    /**
     * Setup the array  the required fields for the FontDescriptor entry
     * The required fields are 'Ascent' 'Descent' 'CapHeight' 'Flags' 'FontBBox' 'ItalicAngle' 'StemV' 'MissingWidth'
     */
    protected function getDescription ($fm,$uses_adobe_standard_encoding) { 
        $desc = array();
        $fm->setDirection('H');
        $desc['Ascent'] = $fm->getAscender();
        $desc['Descent'] = $fm->getDescender();
        $fm->setGlobal();
        $bbox = $fm->getBoundingBox();
        $desc['FontBBox'] = 
            "[{$bbox[0]} {$bbox[1]} {$bbox[2]} {$bbox[3]}]";
        $T = mb_convert_encoding('T',$this->enc->getEncodingType(),'ASCII');
        $capheight = $fm->getFontCharacteristic('CapHeight'); 
        if ($capheight ===null) {
            $capheight = $fm->getCharacterHeight($T);
            if ($capheight === null) { 
                $capheight = $bbox[3];
            }
        }
        $desc['CapHeight'] = $capheight; 
        $desc['ItalicAngle'] =0;
        //$fm->getFontCharacteristic('ItalicAngle'); 
        $stemv =  $fm->getFontCharacteristic('StdVW'); 
        if ( $stemv === null) {
            //just do some bad guesses
            $stemv = 70;
            $weight =  $fm->getFontCharacteristic('Weight'); 
            if (isset($weight)) { 
                if (eregi('(bold|black)',$weight)) {
                    $stemv = 120;
                }
            }
        }
        $desc['StemV'] = $stemv;
        $flags = 0;
        if ($fm->isFixedWidth()) {
            $flags += 1;
        }
        if ($uses_adobe_standard_encoding) {
            //it has latin symbols
            $flags += 32;
        } else {
            //it has non-latin symbols
            $flags += 4;
        }
        if ($desc['ItalicAngle'] > 0) { //it's italic
            $flags += 64;
        }
        $desc['Flags'] = $flags; ///required
        $desc['Flags'] = 32; ///required
        return $desc;
    }


    /*
     *@param boolean $embed_file -- set to true to embed the TTF file
     */
    public function AddFontByTTFFile($family,$style,$ttf_file) {
        $fontkey = $this->getFontKey($family,$style);
        $fm = new I2CE_FontMetricTTF($this->enc,$ttf_file);
        $this->font_metrics[$fontkey] =&$fm;
        $fm->setGlobal();
        $this->fonts[$fontkey] = array();
        $this->fonts[$fontkey]['font_metric'] =&$fm;
        $this->fonts[$fontkey]['i'] = count($this->fonts);
        $this->fonts[$fontkey]['name'] = $fm->getFontCharacteristic('postscriptName');
        $this->fonts[$fontkey]['desc'] = $this->getDescription($fm,false);
        $underlinepos = $fm->getFontCharacteristic('UnderlinePosition');  
        if ($underlinepos !== null) {
            $underlinepos = -100; //not required by PDF but we dont want to break the underlining of tcpdf
        }
        $this->fonts[$fontkey]['up'] = $underlinepos; 
        $underlinethick = $fm->getFontCharacteristic('UnderlineThickness'); 
        if ($underlinethick === null) {
            $underlinethick = 50;//not required by PDF but we dont want to break the underlining of tcpdf
        }
        $this->fonts[$fontkey]['ut'] = $underlinethick; 
        //we are embedding the file
        $this->fonts[$fontkey]['type'] = 'TrueTypeEmbedded';
        $CIDtoGID = $fm->getCIDtoGIDmap(0,3); //unicode 2.0
        if (empty($CIDtoGID)) {
            $CIDtoGID = $fm->getCIDtoGIDmap(3,1); //microsoft unicode
        }
        if (empty($CIDtoGID)) {
            $CIDtoGID = $fm->getCIDtoGIDmap(0,0); //unicode default
        }
        if (empty($CIDtoGID)) {
            die ("Cannot find a good cmap for True Type font <{$ttf_file}>\n");
        }
        $cidtogid_string = str_pad('',65536*2,"\x00");
        foreach ($CIDtoGID as $cid=>$gid) {
            if (($cid >= 0) && ($cid <= 0xFFFF)) {
                $cidtogid_string[$cid*2] = chr($gid >> 8); //the upper 8 bits -- first byte is high order byte
                $cidtogid_string[$cid*2 + 1] = chr($gid & 0xFF); //the lowest 8 bits
            } else {
                I2CE::raiseError("Got CID out of range ($cid) for true type file ($ttf_file)", E_USER_NOTICE);
            }
        }
        $this->fonts[$fontkey]['compress'] = $this->compress;
        $ttf_file = $fm->getTTFFile(); 
        $this->FontFiles[$ttf_file]['compress'] = $this->compress;                              
        $this->FontFiles[$ttf_file]['type'] = 'truetypeembedded';
        if ($this->compress) {
            $cidtogid_string = gzcompress($cidtogid_string);
        }
        $this->fonts[$fontkey]['cidtogid'] = $cidtogid_string;
        $this->fonts[$fontkey]['file'] = $ttf_file; 
    }


    /**
     * Add a Core PDF Font 
     * @param string $family
     * @param string $style
     */
    protected function AddCoreFontMetrics($family,$style='') {
        $fontkey = $this->getFontKey($family,$style);
        $afm_file = $this->CoreFonts[$fontkey]. '.afm';
        switch ($this->FontFamily) { 
        case 'symbol': 
            $afm_encoding = $this->getAdobeStandardEncoding('adobe-symbol');
            $uses_adobe_standard_encoding = false;             
            break;
        case 'xapfdingbats':
            $afm_encoding = $this->getAdobeStandardEncoding('adobe-dingbats');
            $uses_adobe_standard_encoding = false;
            break;
        default:
            $afm_encoding = $this->getAdobeStandardEncoding('adobe-standard');
            $uses_adobe_standard_encoding = true;
            break;
        }
        $fm = new I2CE_FontMetricAFM($this->enc,$afm_encoding,$afm_file);  
        $this->font_metrics[$fontkey] = &$fm;                           
        $fm->setGlobal();
        $this->fonts[$fontkey] = array();
        $this->fonts[$fontkey]['font_metric'] =&$fm;
        $this->fonts[$fontkey]['i'] = count($this->fonts);
        $this->fonts[$fontkey]['name'] = $this->CoreFonts[$fontkey];
        $this->fonts[$fontkey]['type'] = 'core';
        $this->fonts[$fontkey]['desc'] = $this->getDescription($fm,$uses_adobe_standard_encoding);
        $underlinepos = $fm->getFontCharacteristic('UnderlinePosition');  
        if ($underlinepos !== null) {
            $underlinepos = -100; //not required by PDF but we dont want to break the underlining of tcpdf
        }
        $this->fonts[$fontkey]['up'] = $underlinepos; 
        $underlinethick = $fm->getFontCharacteristic('UnderlineThickness'); 
        if ($underlinethick === null) {
            $underlinethick = 50;//not required by PDF but we dont want to break the underlining of tcpdf
        }
        $this->fonts[$fontkey]['ut'] = $underlinethick; 
    }






    /**
     * Sets the font used to print character strings. It is mandatory to call this method at least once before printing text or the resulting document would not be valid.
     * The font can be either a standard one or a font added via the AddFont() method. Standard fonts use Windows encoding cp1252 (Western Europe).
     * The method can be called before the first page is created and the font is retained from page to page.
         If you just wish to change the current font size, it is simpler to call SetFontSize().
         * Note: for the standard fonts, the font metric files must be accessible. There are three possibilities for this:<ul><li>They are in the current directory (the one where the running script lies)</li><li>They are in one of the directories defined by the include_path parameter</li><li>They are in the directory defined by the FPDF_FONTPATH constant</li></ul><br />
         * Example for the last case (note the trailing slash):<br />
         * <pre>
         * define('FPDF_FONTPATH','/home/www/font/');
         * require('tcpdf.php');
         *
         * //Times regular 12
         * $pdf->SetFont('Times');
         * //Arial bold 14
         * $pdf->SetFont('Arial','B',14);
         * //Removes bold
         * $pdf->SetFont('');
         * //Times bold, italic and underlined 14
         * $pdf->SetFont('Times','BIU');
         * </pre><br />
         * If the file corresponding to the requested font is not found, the error "Could not include font metric file" is generated.
         * @param string $family Family font. It can be either a name defined by AddFont() or one of the standard families (case insensitive):<ul><li>Courier (fixed-width)</li><li>Helvetica or Arial (synonymous; sans serif)</li><li>Times (serif)</li><li>Symbol (symbolic)</li><li>ZapfDingbats (symbolic)</li></ul>It is also possible to pass an empty string. In that case, the current family is retained.
         * @param string $style Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li></ul>or any combination. The default value is regular. Bold and italic styles do not apply to Symbol and ZapfDingbats
         * @param float $size Font size in points. The default value is the current size. If no size has been specified since the beginning of the document, the value taken is 12
         * @since 1.0
         * @see AddFont(), SetFontSize(), Cell(), MultiCell(), Write()
         */
    public function SetFont($family, $style='', $size=0) {
        // save previous values
        $this->prevFontFamily = strtolower($this->FontFamily);
        $this->prevFontStyle = strtoupper($this->FontStyle);
        $fk_style = $style;
        $fontkey = $this->getFontKey($family,$fk_style,true);
        if (strlen($family) == 0) {
            I2CE::raiseError("No font family specified", E_USER_ERROR); 
        }
        //make sure we have the font metrics 
        if (!array_key_exists($fontkey, $this->font_metrics)) {
            if(isset($this->CoreFonts[$fontkey])) {
                $this->AddCoreFontMetrics($family,$fk_style);
            } else {
                I2CE::raiseError('Undefined font: '.$family.' '.$fk_style, E_USER_ERROR);                          
            }
        }             
        $fm = $this->font_metrics[$fontkey];
        //really should check that this is a font metric
        //Select it
        if ($size != 0) {
            $fm->setFontSize($size);
        } else {
            $fm->setFontSize($this->FontSizePt);
        }
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->CurrentFont = &$this->fonts[$fontkey];
        $this->SetFontSize($size);
        //select it for use with  tables
        if ($this->text_table instanceof I2CE_TextTable) {
            $this->text_table->setFontMetric($this->font_metrics[$fontkey]);
        }
        if ($this->header_desc_cell instanceof I2CE_TextCell) {
            $this->header_desc_cell->setFontMetric($this->font_metrics[$fontkey]);
        }
    }



    /**
     * Adds a new page to the document. If a page is already present, the Footer() method is called first to output the footer. Then the page is added, the current position set to the top-left corner according to the left and top margins, and Header() is called to display the header.
     * The font which was set before calling is automatically restored. There is no need to call SetFont() again if you want to continue with the same font. The same is true for colors and line width.
     * The origin of the coordinate system is at the top-left corner and increasing ordinates go downwards.
     * @param string $orientation Page orientation. Possible values are (case insensitive):<ul><li>P or Portrait</li><li>L or Landscape</li></ul> The default value is the one passed to the constructor.
     * @since 1.0
     * @see TCPDF(), Header(), Footer(), SetMargins()
     */
    public function AddPage($orientation='') {
        //Start a new page
        if($this->state==0) {
            $this->Open();
        }
        $family=$this->FontFamily;
        $style=$this->FontStyle.($this->underline ? 'U' : '');
        $size=$this->FontSizePt;
        $lw=$this->LineWidth;
        $dc=$this->DrawColor;
        $fc=$this->FillColor;
        $tc=$this->TextColor;
        $cf=$this->ColorFlag;
        if($this->page>0) {
            //Page footer
            $this->InFooter=true;
            $this->Footer();
            $this->InFooter=false;
            //Close page
            $this->_endpage();
        }
        //Start new page
        $this->_beginpage($orientation);
        //Set line cap style to square
        $this->_out('2 J');
        //Set line width
        $this->LineWidth=$lw;
        $this->_out(sprintf('%.2f w',$lw*$this->k));
        //Set font
        if(strlen($family) > 0) {
            $this->SetFont($family,$style,$size);
        }
        //Set colors
        $this->DrawColor=$dc;
        if($dc!='0 G') {
            $this->_out($dc);
        }
        $this->FillColor=$fc;
        if($fc!='0 g') {
            $this->_out($fc);
        }
        $this->TextColor=$tc;
        $this->ColorFlag=$cf;
        //Page header
        $this->Header();
        //Restore line width
        if($this->LineWidth!=$lw) {
            $this->LineWidth=$lw;
            $this->_out(sprintf('%.2f w',$lw*$this->k));
        }
        //Restore font
        if(strlen($family)> 0) {
            $this->SetFont($family,$style,$size);
        }
        //Restore colors
        if($this->DrawColor!=$dc) {
            $this->DrawColor=$dc;
            $this->_out($dc);
        }
        if($this->FillColor!=$fc) {
            $this->FillColor=$fc;
            $this->_out($fc);
        }
        $this->TextColor=$tc;
        $this->ColorFlag=$cf;
    }

    /*******************************
     * Functions to deal with the new font metric scheme
     ********************************/

    public function GetStringWidth ($s) {
        $fontkey = $this->getFontKey($this->FontFamily,$this->FontStyle);
        $fm = $this->font_metrics[$fontkey];
        if ($fm instanceof I2CE_FontMetricMultiDirection) {
            $fm->setDirection('H'); //UGLY!!!
        }
        return $this->font_metrics[$fontkey]->getStringWidth($s,true) / ($this->k*1000);
    }


    /**
     * Defines the size of the current font.
     * @param float $size The size (in points)
     * @since 1.0
     * @see SetFont()
     */
    public function SetFontSize($size) {
        if (strlen($this->FontFamily) == 0 ) {
            I2CE::raiseError ("Trying to set font size when no font has been selected", E_USER_ERROR);
            return; 
        } 
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        $fontkey = $this->getFontKey($this->FontFamily,$this->FontStyle);
        $fm = $this->font_metrics[$fontkey];
        $fm->setFontSize($size);
        if(($this->page > 0) ) {
            $this->_out(sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
            $this->_out("% Setting for ($fontkey)");
        }
        if ($fm instanceof I2CE_FontMetricMultiDirection) {
            $fm->setDirection('H'); //UGLY!!!
        }
    }


    /**
     * Format a text string
     * @access protected
     */
    protected function _textstring($s) {
        if($this->enc->useMB()) {
            //Convert string to UTF-16BE //-- place BOM
            //$s = $this->UTF8ToUTF16BE($s, true);
            $s = "\xFE\xFF" . mb_convert_encoding($s,'UTF16BE',$this->enc->getEcodingType());
        }
        return '('. $this->_escape($s).')';
    }
        
    /**
     * Format a text string
     * @access protected
     */
    function _escapetext($s) {
        if($this->enc->useMB()) {
            //$s = $this->UTF8ToUTF16BE($s, false); //-- no BOM
            //Convert string to UTF-16BE
            $s = mb_convert_encoding($s,'UTF16BE',$this->enc->getEcodingType());
                        
        }
        return $this->_escape($s);
    }


    /**
     * Set header margin.
     * (minimum distance between header and top page margin)
     * @param int $hm distance in user units
     */
    public function setHeaderMargin($hm=10) {
        $this->header_margin = $hm;
    }

        
    /**
     * Sets header width
     * @param int $w width in user units.  Value of 0 says to use all the avaiable width
     */
    public function setHeaderWidth($w=0) {
        $this->header_width = $w;
    }

    /**
     * Set a flag to print page header.
     * @param boolean $val set to true to print the page header (default), false otherwise. 
     */
    public function setPrintHeader($val=true) {
        $this->print_header = $val;
    }


    protected $header_count  =0;
    /**
     * This method is used to render the page header.
     */
    public function Header() {
        if (!$this->print_header) {
            return;
        }
        $this->header_count++;
        $this->SetAutoPageBreak(false);
        $this->_out ("% Beginning Header");
        if (!isset($this->original_lMargin)) {
            $this->original_lMargin = $this->lMargin;
        }
        if (!isset($this->original_rMargin)) {
            $this->original_rMargin = $this->rMargin;
        }
        //set current position
        $this->SetXY($this->original_lMargin, $this->header_margin*$this->k);
        if ($this->header_logo) {
            $this->Image($this->header_logo, $this->original_lMargin, $this->header_margin, $this->header_logo_width);
        }else {
            $this->img_rb_y = $this->GetY();
        }
        $header_x = $this->original_lMargin + ($this->header_logo_width * 1.05); //set left margin for text data cell
        $this->SetXY($header_x, $this->header_margin);
        if (isset($this->header_font)) {
            $header_font = $this->header_font;
        } else {
            //use something reasonable as a fall back
            $header_font = array('Arial','',14);
        }
        $cell_height = $header_font[2] / $this->k;
        if ($this->line_spacing > 0) {
            $cell_height = $cell_height * $this->line_spacing;
        }               
        // header title
        if (isset($this->header_title)) {
            $this->SetFont($header_font[0], 'B', $header_font[2] + 1);
            $this->Cell($this->header_width, $cell_height, $this->header_title, 0, 2, 'L'); 
        }
                
        // header string
        if ($this->header_string) {
            $this->SetFont($header_font[0], $header_font[1], $header_font[2]);
            $this->Cell($this->header_width, $cell_height, $this->header_string, 0, 2,'L');
        }
        // print an ending header line
        if (empty($this->header_width)) {
            //set style for cell border
            $prev_line_width = $this->LineWidth;
            $this->SetLineWidth($this->LineWidth*3);
            $this->SetDrawColor(0, 0, 0); //black
            $this->SetY(1.06*max($this->img_rb_y, $this->GetY()));
            $this->SetX($this->original_lMargin);
            $this->Cell(0, 0, '', 'T', 0, 'C'); 
            $this->SetLineWidth($prev_line_width);
        }               
        //restore position
        $this->SetXY($this->original_lMargin, $this->tMargin);
        if ($this->header_count == 1 && $this->header_desc) {
            if (!$this->header_desc_cell instanceof I2CE_TextCell && $this->hyphen) {
                $fm = null;
                $this->header_desc_cell = new I2CE_TextCell($fm, $this->hyphen,$this->enc, $this->algorithm);
                if ($this->header_width > 0) {
                    $width = $this->header_width;
                } else {
                    //get all available space
                    $width = $this->w - $this->rMargin - $this->x;
                }
                $this->header_desc_cell->setWidth($width *$this->k*1000);
            }
            $this->SetFont($header_font[0], $header_font[1], $header_font[2]/2);
            if ($this->header_desc_cell instanceof I2CE_TextCell) {
                $this->displayTextCell($this->header_desc_cell, $this->header_desc);
            } else {
                $this->Cell($this->header_width, $cell_height/2, $this->header_desc, 0, 2,'L');        
            }
        }
        $this->_out ("% Ending Header");
        $this->SetAutoPageBreak(true);
    }


    /**
     *Puts in a wordwrapped text cell
     *@param I2CE_TextCell $text_cell
     *@param string $text
     */
    protected function displayTextCell($text_cell, $text) {
        if (!$text_cell instanceof I2CE_TextCell) {
            return ;
        }        
        if( ! ($fm = $this->text_table->getFontMetric())  instanceof I2CE_FontMetric) {
            return;
        }
        if ($fm instanceof I2CE_FontMetricMultiDirection) {
            $fm->setDirection('H'); //UGLY!!!
        }
        $adj = ($fm->getDescender() + $fm->getLinegap())/(1000*$this->k);
        $line_height = $fm->getFontSize() / $this->k + $adj;
        if ($this->line_spacing > 0) {
            $line_height = $line_height * $this->line_spacing;
        }               
        $width = $text_cell->getWidth();
        $lines =  $text_cell->getLineBreaks($text);
        foreach($lines as $line) {
            $this->Cell($width, $line_height, $line, 0, 1,'L');        
        }
    }


    /*
     * Prints a cell (rectangular area) with optional borders, background color and character string. The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered. After the call, the current position moves to the right or to the next line. It is possible to put a link on the text.<br />
     * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
     * @param float $w Cell width. If 0, the cell extends up to the right margin.
     * @param float $h Cell height. Default value: 0.
     * @param string $txt String to print. Default value: empty string.
     * @param mixed $border Indicates if borders must be drawn around the cell. The value can be either a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul>or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul>
     * @param int $ln Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right</li><li>1: to the beginning of the next line</li><li>2: below</li></ul>
         Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
         * @param string $align Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
         * @param int $fill Indicates if the cell background must be painted (1) or transparent (0). Default value: 0.
         * @param mixed $link URL or identifier returned by AddLink().
         * @since 1.0
         * @see SetFont(), SetDrawColor(), SetFillColor(), SetTextColor(), SetLineWidth(), AddLink(), Ln(), MultiCell(), Write(), SetAutoPageBreak()
         */
     public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0) {
        //Output a cell
        $k=$this->k;
        if(($this->y + $h) > $this->PageBreakTrigger AND empty($this->InFooter) AND $this->AcceptPageBreak()) {
            //Automatic page break
            $x = $this->x;
            $ws = $this->ws;
            if($ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            } 
            $this->AddPage($this->CurOrientation);
            $this->x = $x;
            if($ws > 0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3f Tw',$ws * $k));
            }
        }
        if($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $s = '';
        if(($fill == 1) OR ($border == 1)) {
            if($fill == 1) {
                $op = ($border == 1) ? 'B' : 'f';
            }
            else {
                $op = 'S';
            }
            $s = sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
        }
        if(is_string($border)) {
            $x=$this->x;
            $y=$this->y;
            if(strpos($border,'L')!==false) {
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
            }
            if(strpos($border,'T')!==false) {
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
            }
            if(strpos($border,'R')!==false) {
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
            }
            if(strpos($border,'B')!==false) {
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
            }
        }
        if($txt != '') {
            $width = $this->GetStringWidth($txt);                               
            switch ($align) {
            case 'R':
                $dx = $w - $this->cMargin - $width;
                break;
            case 'C':
                $dx =  ($w - $width)/2;
                break;
            case 'J':
                $len = 0;
                if ($this->enc->useMB()) {
                    $len = mb_strlen($txt,$this->enc->getEncodingType());
                } else {
                    $len = strlen($txt);
                }
                $dx = $this->cMargin;
                $char_weight = 0.8;
                $num_words = count($this->text_table->getWords($txt));
                $char_spacing = $char_weight * $width / ($len-1);
                $word_spacing = (1-$char_weight) * width * $num_words;
                //                              $s .= sprintf( ' TC %.2f Tw %.2f ',$char_spacing,$word_spacing);
                break;
            default:
                $dx = $this->cMargin;
                break;
            }
            if($this->ColorFlag) {
                $s .= 'q '.$this->TextColor.' ';
            }
            $txt2 = $this->_escapetext($txt);
            $s.=sprintf('BT %.2f %.2f Td (%s) Tj ET', 
                        ($this->x + $dx) * $k, ($this->h - ($this->y + 0.5 * $h + 0.3 * $this->FontSize)) * $k, $txt2);
            if($this->underline) {
                $s.=' '.$this->_dounderline($this->x + $dx, $this->y + 0.5 * $h + 0.3 * $this->FontSize, $txt);
            }
            if ($align === 'J') {
                //reset the word and character spacing to zero
                //$s .= " Tc 0 Tw 0 ";
            }
            if($this->ColorFlag) {
                $s.=' Q';
            }
            if($link) {
                $this->Link($this->x + $dx, $this->y + 0.5 * $h - 0.5 * $this->FontSize, $width, $this->FontSize, $link);
            }
        }
        if($s) {
            $this->_out($s);
        }
        $this->lasth = $h;
        if($ln>0) {
            //Go to next line
            $this->y += $h;
            if($ln == 1) {
                $this->x = $this->lMargin;
            }
        } else {
            $this->x += $w;
        }
    }
    /*********************
     *Functions to deal with tables
     *********************/




    /**
     * Get the TextTable object
     * @returns TextTable
     */
    public function GetTextTable() {
        return $this->text_table;
    }

    /**
     * Set the minimum cell width of the table
     * @param numeric $width
     */
    public function SetMinTableCellWidth($width) {
        $this->text_table->setMinCellWidth($width*$this->k*1000);
    }

    /**
     * Set the spacing between columns of the table
     * @param numeric $spacing
     */
    public function SetTableColSpacing($spacing) {
        $this->text_table->setColSpacing($spacing*$this->k*1000);
    }

    /**
     * Set the spacing between rows of the table
     * @param numeric $spacing  -- number to multiply the text height by to get the spacing
     */
    public function SetLineSpacing($spacing) {
        $this->line_spacing = $spacing;
    }

    /**
     * Set header data.
     * @param string $ln header image logo
     * @param string $lw header image logo width in mm
     * @param string $ht string to print as title on document header
     * @param string $hs string to print on document header
     * @param string $hd a description of the report.
     */
    public function setHeaderData($logo="", $lw=0, $ht="", $hs="", $hd ='') {
        if ($logo) {
            $logo_file = I2CE::getFileSearch()->search('PDF_IMAGES',$logo);
            if (!($logo_file)) {
                $msg = "Header image ($logo) not found" .
                    "\nSearch Path is:\n" 
                    . print_r(I2CE::getFileSearch()->getSearchPath('PDF_IMAGES'), true); 
                I2CE::raiseError($msg);
                unset( $this->header_logo );
            }
        }
        $this->header_logo = $logo_file;
        $this->header_logo_width = $lw;
        $this->header_title = $ht;
        $this->header_string = $hs;
        $this->header_desc = $hd;
    }

    /**
     * @var protected I2CE_Hyphen $hyphen  A 
     */
    protected $hyphen = false;
     
    /**
     * set the hyphenation dictionary used for text tables
     * @param I2CE_Hyphen $hyphen
     */
    public function SetHyphenationDictionary($hyphen) {
        $this->hyphen = $hyphen;
        
        if ($this->text_table instanceof I2CE_TextTable) {
            $this->text_table->setHyphenationDictionary($hyphen);
        }
        if ($this->header_desc_cell instanceof I2CE_TextCell) {
            $this->header_desc_cell->setHyphenationDictionary($hyphen);
        }

    }
        
    /**
     *  Set a running header for tables that span multiple pages
     *  @param boolean $running_header  true to repeat the header 
     */
    public function SetRunningHeader($running_header) {
        $this->running_header = $running_header;
    }

    /**
     * Set the maximum width of the table width 
     * @param numeric $width in user units
     */
    public function SetMaxTableWidth($width) {
        //TextTable uses FontMetric where dimensions are given in thousandths of a point
        $this->text_table->setMaxTableWidth($width*$this->k*1000);
    }




    /**
     * @var int $min_cell_width  the minimum number of points we want minimially as the width of  a cell in a table
     */
    protected  $min_cell_width = 36;   //default is 1/2"

    /**
     * @var array of int $header_fill_color for the fill color
     * of the header row of a table 
     */
    protected $header_fill_color = array(255,0,0);

    /**
     * @var array of int $header_text_color for the text color
     * of the header row of a table 
     */
    protected $header_text_color = array(255,255,255);

    /**
     * @var array of int $header_fill_color for the fill color
     * of the header row of a table 
     */
    protected $data_fill_color = array(0,0,0);

    /**
     * @var array of int $table_framing_color  the color used to draw the frame for the table
     */
    protected $table_framing_color = array(128,20,20);

    /**
     * @var array of int $header_text_color for the text color
     * of the header row of a table 
     */
    protected $data_text_color = array(80,80,80);
    
    /*
     * @param String $algorithm 
     */
    protected $algorithm = 'Greedy';
    /**
     * Set the alogorithm used to determine word-wrapping
     * @param String $algorithm 
     */
    public function SetWordWrapAlgorithm($algorithm) {
        $this->algorithm = $algorithm;
        if ($this->text_table instanceof I2CE_TextTable) {
            $this->text_table->setWordWrapAlgorithm($algorithm);
        }
        if ($this->header_desc_cell instanceof I2CE_TextCell) {
            $this->header_desc_cell->setWordWrapAlgorithm($algorithm);
        }
    }

    /**
     * Set the alogorithm used to determine column widths
     * @param string $style.  Valid values are 'All', 'Header', and 'Explicit'
     */
    public function SetAutoTableWidthStyle($style) {
        $this->text_table->setWidthStyle($style);
    }

    /**
     * Set the widths of the columns used in the table explicitly
     * @parma array of number $widths -- the column widths
     * Note: setting table width will set the table width style to explicit
     */
    public function SetTableWitdths($widths) { 
        $this->text_table->setWidths($widths);
    }
        
    /**
     * Set the widths of the columns last used in the table 
     */
    public function GetTableWidths() {
        return $this->text_table->getWidths();
    }




    /**
     * Set the header of a table's fill color
     * @param array of $int -- the rgb values
     */
    public function SetTableHeaderFillColor ($color){
        $this->header_fill_color = $color;
    }


    /**
     * Set the header of a table's text color
     * @param array of $int -- the rgb values
     */
    public function SetTableHeaderTextColor ($color){
        $this->header_text_color = $color;
    }


    /**
     * Set the framing color for a table
     * @param array of $int -- the rgb values
     */
    public function SetTableFramingColor ($color){
        $this->table_framing_color = $color;
    }
        


    /**
     * Set the data fill color for a table
     * @param array of $int -- the rgb values
     */
    public function SetTableDataFillColor ($color){
        $this->data_fill_color = $color;
    }




    /**
     * Set the data text color for a table
     * @param array of $int -- the rgb values
     */
    public function SetTableDataTextColor ($color) {
        $this->data_text_color = $color;
    }



        
    /**
     *  Given a 2-dimensional table of string prints out a 'fancy' table
     *  @param float $w width of the table, 0 goes to right margin in user units
     *  @param float $h height of each row of text in user units
     *  @param array $text_table  2 dimensional array of text (indexing is [$row][$col]) .. Row has to be numeric sequential  starting at 0.  Column does not
     * @param numeric $max_table_width the maximum table width -- 0 fill the table to the right margin, null use the value as it
     * is already set, non null > 0 is the maximum table width
     * @param mixed $border Indicates if borders must be drawn around the cell block. The value can be:
     *        <ul><li>0: no border (default)</li><li>1: frame</li></ul>
     * @param int $ln Indicates where the current position should go after the call. 
     *        Possible values are:<ul><li>0: to the right</li><li>1: to the beginning of the next line [DEFAULT]</li><li>2: below</li></ul>
     *  @param int $header  : 0 no header, 1 has a header, 2 has a header and the header should repeat on a page break
     */
    public function MakeTable($text_table_data,$border=0, $max_table_width = 0, $align_data = 'C' , $header = 1, $align_header = 'L', $ln = 1) {
        $cols = array_keys($text_table_data); 
        $this->_out ("% Beginning Table");
        $prevx = $this->x;
        $prevy = $this->y;
        $prev_cmargin = $this->cMargin;
        $prev_lw = $this->LineWidth;
        $prev_fc = $this->FillColor;
        $prev_dc = $this->DrawColor;
        $prev_tc = $this->TextColor;
        $auto = $this->AutoPageBreak;
        if ($max_table_width !== null) {
            if ($max_table_width <= 0) {
                $w = ($this->w - $this->x - $this->rMargin);
                $this->_out ("%width $w page width {$this->w} x {$this->x} rMargin {$this->rMargin}");
                $this->SetMaxTableWidth( $w);
            } else {
                $this->SetMaxTableWidth($max_table_width);
            }
        }
        $rows = count($text_table_data);
        $fr_c = $this->table_framing_color;
        $h_fc = $this->header_fill_color;
        $h_tc = $this->header_text_color;
        $d_fc = $this->data_fill_color;
        $d_tc = $this->data_text_color;
        if ($d_fc !== false)  {
            $fill = 1;
            $this->SetFillColor($d_fc[0],$d_fc[1],$d_fc[2]);
        } else {
            $fill = 0;
        }
        $this->SetTextColor($d_tc[0],$d_tc[1],$d_tc[2]);
        $this->SetDrawColor($fr_c[0],$fr_c[1],$fr_c[2]);
        $fm = $this->text_table->getFontMetric();
        if ($fm instanceof I2CE_FontMetricMultiDirection) {
            $fm->setDirection('H'); //UGLY!!!
        }
        //the column spacing is the space between the center of a vertical line and a vertical text box boundary.
        //thus in each cell there are two widths of $col_space... to the left and to the right of text.
        //this is the same way that $this->cMargin is used.
        //one half of the line width (for the default line style anyways) is the distance from the center of a line
        // to its outer edge (e.g. a radius)
        $col_space = $this->text_table->getColSpacing()/1000; //the spacing between text column spacing in points that the text table has
        if ($border ===1) {
            $line_width = $this->LineWidth * $this->k; //line width in points
            if ($col_space < $line_width/2) {
                $col_space = $line_width/2;
                $this->text_table->setColSpacing($col_space*1000);
            }
        }
        $this->cMargin = $col_space/$this->k; //set the cell margin in user units.
        $font_sizes = array();
        if ($header  ) {
            $font_sizes[0] = $this->FontSizePt + 2;
            for ($r = 1; $r < $rows; $r++) {
                $font_sizes[$r] = $this->FontSizePt;
            }
        } else {
            for ($r = 0; $r < $rows; $r++) {
                $font_sizes[$r] = $this->FontSizePt;
            }
        }
        $ww_table = $this->text_table->wordWrapTable($text_table_data,$font_sizes,true);
        $ww_table_widths = $this->text_table->getWidths();



        foreach ($ww_table_widths as $col=>$width) {
            //convert widths from thousandths of a point to user units
            // and add in the column spacing/padding
            $ww_table_widths[$col] =  ((float)$width )/(1000* $this->k) + 2*$this->cMargin;
        }

        $tot = 0;
        foreach ($ww_table_widths as $col=>$width) {
            $tot += $width ;
        }
        if ($header) { //set the colors as appropriate for the header
            $this->SetFontSize($this->FontSizePt + 2);
            if ($h_fc !== false) {
                $fill = 1;
                $this->SetFillColor($h_fc[0],$h_fc[1],$h_fc[2]);
            } else {
                $fill = 0;
            }
            $this->SetTextColor($h_tc[0],$h_tc[1],$h_tc[2]);
        }  
        $fm = $this->text_table->getFontMetric();
        //$baseLineSkip = ($fm->getAscender() + $fm->getDescender() + $fm->getLinegap())/1000*($this->FontSizePt);
        $baseLineSkip = ( $fm->getDescender() + $fm->getLinegap())/1000*($this->FontSizePt);
        $h = ($this->FontSizePt - $baseLineSkip)*$this->line_spacing; //the height of one text line -- in points;
        $h_user = $h/$this->k; //the height of one text line in user units
        if ($this->y + $h_user +  $this->bMargin > $this->PageBreakTrigger) { 
            //we need a page break
            $this->AddPage(); 
            $prevx = $this->lMargin;
            $prevy = $this->tMargin;
        }
        $this->DisplayTextTableRow($ww_table[0],$ww_table_widths,$h_user,$border,$align_header,$fill);
        if ($header ) { //reset the colors for the data
            $this->SetFontSize($this->FontSizePt - 2);
            if ($d_fc !== false) {
                $this->SetFillColor($d_fc[0],$d_fc[1],$d_fc[2]);
            } else {
                $fill = 0;
            }
            $this->SetTextColor($d_tc[0],$d_tc[1],$d_tc[2]);
        }
        for ($r = 1; $r < $rows ; $r++) {
            $fill = !$fill;
            $num_text_rows = count($ww_table[$r][$cols[0]]);
            if ($this->y + $num_text_rows*$h_user +  $this->bMargin > $this->PageBreakTrigger) { 
                //we need a page break
                $this->AddPage(); 
                $prevx = $this->lMargin;
                $prevy = $this->tMargin;
                $fill = 1;
                if (($header) && ($this->running_header)){
                    //we have a running header which we need to display
                    $this->SetFontSize($this->FontSizePt + 2);
                    if ($h_fc !== false) {
                        $this->SetFillColor($h_fc[0],$h_fc[1],$h_fc[2]);
                        $fill = 1;
                    } else {
                        $fill = 0;
                    }
                    $this->SetTextColor($h_tc[0],$h_tc[1],$h_tc[2]);
                    //$baseLineSkip = ($fm->getAscender() + $fm->getDescender() + $fm->getLinegap())/1000*($this->FontSizePt);
                    $baseLineSkip = ( $fm->getDescender() + $fm->getLinegap())/1000*($this->FontSizePt);
                    $h = ($this->FontSizePt - $baseLineSkip)*$this->line_spacing; //the height of one text line -- in points;
                    $h_user = $h/$this->k; //the height of one text line in user units
                    $this->DisplayTextTableRow($ww_table[0],$ww_table_widths,$h_user,$border,$align_header,1);
                    $this->SetFontSize($this->FontSizePt - 2);
                    if ($d_fc !== false) {
                        $this->SetFillColor($d_fc[0],$d_fc[1],$d_fc[2]);
                        $fill = 1;
                    } else {
                        $fill = 0;
                    }
                    $this->SetTextColor($d_tc[0],$d_tc[1],$d_tc[2]);
                }
            }
            //$baseLineSkip = ($fm->getAscender() + $fm->getDescender() + $fm->getLinegap())/1000*($this->FontSizePt);
            $baseLineSkip = ( $fm->getDescender() + $fm->getLinegap())/1000*($this->FontSizePt);
            $h = ($this->FontSizePt - $baseLineSkip)*$this->line_spacing; //the height of one text line -- in points;
            $h_user = $h/$this->k; //the height of one text line in user units
            $this->DisplayTextTableRow($ww_table[$r],$ww_table_widths,$h_user,$border, $align_data,$fill);
        }
        if($ln == 1) {
            // go to the beginning of the next line
            $this->x = $this->lMargin;
        } elseif($ln == 0) {
            // go to the top-right of the cell
            $this->y = $prevy;
            $this->x = $prevx + $w;
        } elseif($ln == 2) {
            // go to the bottom-left of the cell
            $this->x = $prevx;
        }
        $this->LineWidth = $prev_lw;
        $this->AutoPageBreak = $auto;
        $this->FillColor = $prev_fc;
        $this->DrawColor = $prev_dc;
        $this->TextColor = $prev_tc;
        $this->cMargin = $prev_cmargin;
        $this->_out ("% Ending Table");

    }
        
        
        
        
        
        
        

    /**
     * Draws a Text Table Row using  wordwrapped rows
     * @param array of array of strings $row_data.  first index is column, second index is the text row in the cell
     * @param numeric $h_user the height in user units
     * @param array of numeric $widths in user units
     **/
    protected function DisplayTextTableRow($row_data, $widths, $h_user,$border, $align, $fill) {
        $this->_out ("% Beginning Table Row");
        // save current position
        $prevx = $this->x;
        $prevy = $this->y;
        $b=0;
        if($border==1) {
            $border='LTRB';
            $b='LRT';
            $b2='LR';
        }
        else {
            $b2='';
            if(strpos($border,'L')!==false) {
                $b2.='L';
            }
            if(strpos($border,'R')!==false) {
                $b2.='R';
            }
            $b=(strpos($border,'T')!==false) ? $b2.'T' : $b2;
        }
        $num_text_rows = 0;
        foreach ($widths as $col=>$width) {
            $num_text_rows = max($num_text_rows,count($row_data[0]));
        }
        if ($num_text_rows == 0) { //no rows
            //do nothing
        }else if ($num_text_rows == 1) { //only one row
            if(is_int(strpos($border,'B'))) {
                $b.='B';
            }
            foreach ($widths as $col=>$width) {
                $this->Cell($width , $h_user, $row_data[$col][0], $b, 0, $align, $fill);
            }
            $this->y += $h_user;
                        
        } else { // at least two rows of text per cell
            //the first row
            foreach ($widths as $col=>$width) {
                $this->Cell($width , $h_user, $row_data[$col][0], $b, 0, $align, $fill);
            }
            $this->y += $h_user;
            $this->x = $prevx;
            // the middle rows
            $b = $b2;
            for ($tr = 1; $tr < $num_text_rows - 1; $tr++) {
                if (array_key_exists($col,$row_data)) {
                    foreach ($widths as $col=>$width) {
                        if (!array_key_exists($tr,$row_data[$col])) {
                            continue;
                        }
                        $this->Cell($width , $h_user, $row_data[$col][$tr], $b, 0, $align, $fill);
                    }
                }
                $this->x = $prevx;
                $this->y += $h_user;
            }
            //the last row 
            if(is_int(strpos($border,'B'))) {  
                $b.='B';
            }
            foreach ($widths as $col=>$width) {
                if (!array_key_exists($num_text_rows-1,$row_data[$col])) {
                    continue;
                }
                $this->Cell($width , $h_user, $row_data[$col][$num_text_rows-1], $b, 0, $align, $fill);
            }
            $this->y += $h_user;
        }
        // go to the bottom-left of the row
        $this->x = $prevx;
        $this->_out ("%Ending Table Row");
    }


        


        
        



    /**
     * Get the I2CE_Encoding according to one of the standard adobe encodings
     */
    protected function getAdobeStandardEncoding($encoding) {
        if (isset($this->adobe_standard_encondings[$encoding])) {
            return;
        }
        $enc = new I2CE_Encoding($encoding);
        if (!isset($this->glyph_list)) {
            $this->loadGlyphList();
        }
        $a = $this->load_file('PDF_CORE' , $encoding . '.list');
        foreach ($a as  $l) {
            $l = trim($l);
            $e = explode(" ", rtrim($l));
            $cc = (int) $e[0];
            $gn = $e[1];
            $cps = $this->glyph_list[$gn];
            if (count($cps) == 1) { 
                //a adobe glyphname may be associated to several unicode codepoints (e.g. lamedholam)
                //if this is the case, we ignore it.
                $enc->setGlyphname($cps[0],$gn);
                $enc->setCharacterCode($cps[0],$cc);
            }
        }
        $this->adobe_standard_encodings[$encoding] = $enc;
        return $enc;
    }

    /*
     * protected @var array with values array of int, unicode codepoints, and with keys adobe glyph names
     */

    protected $glyph_list;

    protected function load_file($category,$file_name) {
        $file = I2CE::getFileSearch()->search($category,$file_name);
        if (!$file) {
            I2CE::raiseError("Cannot find file ({$file_name})",E_USER_ERROR);
        }
        $a = file($file);
        if ( empty($a)) {
            I2CE::raiseError( "File ($file) is empty",E_USER_ERROR);
        }
        return $a;
    }

    /*
     * load the glyph list which maps adobe names to unicode code points
     */
    protected function loadGlyphList() {
        $this->glyph_list =  array();         
        $a = $this->load_file('PDF_CORE','glyphlist.txt');
        foreach ($a as $l) {
            $l = trim($l);
            if (strpos($l,'#')===0) {
                continue;
            }
            $e = explode(";",rtrim($l));
            $cps = explode(' ',trim($e[1]));
            foreach ($cps as $n=>$cp) {
                $cps[$n] = hexdec($cp);
            }
            $this->glyph_list[$e[0]] = $cps;
        }
    }


    /**
     * Adds unicode fonts.<br>
     * Based on PDF Reference 1.3 (section 5)
     * @access protected
     * @author Nicola Asuni
     * @since 1.52.0.TC005 (2005-01-05)
     */
    protected function _puttruetypeembedded($font) {
        // Type0 Font 
        // A composite font composed of other fonts, organized hierarchically
        $this->_newobj();
        $this->_out('<</Type /Font');
        $this->_out('/Subtype /Type0');
        $this->_out('/BaseFont /'.$font['name'].'');
        $this->_out('/Encoding /Identity-H'); //The horizontal identity mapping for 2-byte CIDs; may be used with CIDFonts using any Registry, Ordering, and Supplement values.
        $this->_out('/DescendantFonts ['.($this->n + 1).' 0 R]');
        $this->_out('/ToUnicode '.($this->n + 2).' 0 R');
        $this->_out('>>');
        $this->_out('endobj');
                
        // CIDFontType2 
        // A CIDFont whose glyph descriptions are based on TrueType font technology
        $this->_newobj();
        $this->_out('<</Type /Font');
        $this->_out('/Subtype /CIDFontType2');
        $this->_out('/BaseFont /'.$font['name'].'');
        $this->_out('/CIDSystemInfo '.($this->n + 2).' 0 R'); 
        $this->_out('/FontDescriptor '.($this->n + 3).' 0 R');
        if (isset($font['desc']['MissingWidth'])){
            $this->_out('/DW '.$font['desc']['MissingWidth'].''); // The default width for glyphs in the CIDFont MissingWidth
        }
        $w = "";
        $fm = $font['font_metric'];
        $fm->setDirection('H');
        $char_widths = $font['font_metric']->getCharacterWidths();
        foreach ($char_widths as $char=>$width) { 
            $cid = I2CE_UTF8::to_codepoints($char);
            if (empty($cid)) {
                continue;
            }
            $w .= ''.$cid[0].' ['.$width.'] '; // define a specific width for each individual CID
        }
        $this->_out('/W ['.$w.']'); // A description of the widths for the glyphs in the CIDFont
        $this->_out('/CIDToGIDMap '.($this->n + 4).' 0 R');
        $this->_out('>>');
        $this->_out('endobj');
                



        // ToUnicode  
        // is a stream object that contains the definition of the CMap
        // (PDF Reference 1.3 chap. 5.9)
        $this->_newobj();
        $this->_out('<</Length 383>>');
        $this->_out('stream');
        $this->_out('/CIDInit /ProcSet findresource begin');
        $this->_out('12 dict begin');
        $this->_out('begincmap');
        $this->_out('/CIDSystemInfo');
        $this->_out('<</Registry (Adobe)');
        $this->_out('/Ordering (UCS)');
        $this->_out('/Supplement 0');
        $this->_out('>> def');
        $this->_out('/CMapName /Adobe-Identity-UCS def');
        $this->_out('/CMapType 2 def');
        $this->_out('1 begincodespacerange');
        $this->_out('<0000> <FFFF>');
        $this->_out('endcodespacerange');
        $this->_out('1 beginbfrange');
        $this->_out('<0000> <FFFF> <0000>');
        $this->_out('endbfrange');
        $this->_out('endcmap');
        $this->_out('CMapName currentdict /CMap defineresource pop');
        $this->_out('end');
        $this->_out('end');
        $this->_out('endstream');
        $this->_out('endobj');
                
        // CIDSystemInfo dictionary 
        // A dictionary containing entries that define the character collection of the CIDFont.
        $this->_newobj();
        $this->_out('<</Registry (Adobe)'); // A string identifying an issuer of character collections
        $this->_out('/Ordering (UCS)'); // A string that uniquely names a character collection issued by a specific registry
        $this->_out('/Supplement 0'); // The supplement number of the character collection.
        $this->_out('>>');
        $this->_out('endobj');
                
        // Font descriptor -- object $n+4
        // A font descriptor describing the CIDFont default metrics other than its glyph widths
        $this->_newobj();
        $this->_out('<</Type /FontDescriptor');
        $this->_out('/FontName /'.$font['name']);
        foreach ($font['desc'] as $key => $value) {
            $this->_out('/'.$key.' '.$value);
        }
        //the stream containing the TrueType font program
        $this->_out('/FontFile2 '.$this->FontFiles[$font['file']]['n'].' 0 R');
        $this->_out('>>');
        $this->_out('endobj');
                
        // Embed CIDToGIDMap -- object $n+5
        // A specification of the mapping from CIDs to glyph indices
        $this->_newobj();
        $size = strlen($font['cidtogid']);
        $this->_out('<</Length '.$size.'');
        if ($font['compress']) {
            $this->_out('/Filter /FlateDecode');
        }
        $this->_out('>>');
        $this->_putstream($font['cidtogid']);
        $this->_out('endobj');
    }


    /**
     * Adds fonts
     * _putfonts
     * @access protected
     */
    protected function _putfonts() {
        $nf=$this->n;
        foreach($this->diffs as $diff) {
            //Encodings
            $this->_newobj();
            $this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
            $this->_out('endobj');
        }
        foreach($this->FontFiles as $file=>$info) {
            $this->_out("%Embedding font file ($file)");
            //Font file embedding
            $this->_newobj();
            $this->FontFiles[$file]['n']=$this->n;
            $font='';
            if (strtolower($info['type']) == 'truetypeembedded') { 
                $f = fopen ($file,'rb',1);
            } else {
                $f=fopen($this->_getfontpath().strtolower($file),'rb',1);
            }
            if(!$f) {
                I2CE::raiseError('Font file not found: '.$file, E_USER_ERROR);
            }
            while(!feof($f)) {
                $font .= fread($f, 8192);
            }
            fclose($f);
            $compressed=(substr($file,-2)=='.z');
            if(!$compressed && isset($info['length2'])) {
                $header=(ord($font{0})==128);
                if($header) {
                    //Strip first binary header
                    $font=substr($font,6);
                }
                if($header && ord($font{$info['length1']})==128) {
                    //Strip second binary header
                    $font=substr($font,0,$info['length1']).substr($font,$info['length1']+6);
                }
            }
            if(  (!$compressed) && (!isset($info['length2']))) {
                $info['length1'] = strlen($font);
                if ($info['compress']) {
                    $font = gzcompress($font);
                    $compressed = true;
                }
            }
            $this->_out('<</Length '.strlen($font)); 
            if($compressed) { 
                $this->_out('/Filter /FlateDecode');
            }
            $this->_out('/Length1 '.$info['length1']);
            if(isset($info['length2'])) {
                $this->_out('/Length2 '.$info['length2'].' /Length3 0');
            }
            $this->_out('>>');
            $this->_putstream($font);
            $this->_out('endobj');
        }
        foreach($this->fonts as $k=>$font) {
            //Font objects
            $this->fonts[$k]['n']=$this->n+1;
            $type=$font['type'];
            $name=$font['name'];
            $this->_out("%Describing font ($name)");
            if($type=='core') { 
                //Standard font
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /'.$name);
                $this->_out('/Subtype /Type1');
                if($name!='Symbol' && $name!='ZapfDingbats') {
                    $this->_out('/Encoding /WinAnsiEncoding');
                }
                $this->_out('>>');
                $this->_out('endobj');
            } elseif($type=='Type1' || $type=='TrueType') {
                //Additional Type1 or TrueType font
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /'.$name);
                $this->_out('/Subtype /'.$type);
                $this->_out('/FirstChar 32 /LastChar 255');
                //                              $this->_out('/Widths '.($this->n+1).' 0 R');
                $this->_out('/FontDescriptor '.($this->n+1).' 0 R');
                if($font['enc']) {
                    if(isset($font['diff'])) {
                        $this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
                    } else {
                        $this->_out('/Encoding /WinAnsiEncoding');
                    }
                }
                $this->_out('>>');
                $this->_out('endobj');
                //Descriptor
                $this->_newobj();
                $s='<</Type /FontDescriptor /FontName /'.$name;
                foreach($font['desc'] as $k=>$v) {
                    $s.=' /'.$k.' '.$v;
                }
                $file = $font['file'];
                if($file) {
                    $s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
                }
                $this->_out($s.'>>');
                $this->_out('endobj');
                //Widths
                //$this->_newobj();
                //$cw=&$font['cw'];
                //$s='[';
                //for($i=32;$i<=255;$i++) {
                //$s.=$cw[chr($i)].' ';
                //}
                //$this->_out($s.']');
                //$this->_out('endobj');

            } else {
                //Allow for additional types
                $mtd='_put'.strtolower($type);
                if(!method_exists($this, $mtd)) {
                    $this->Error('Unsupported font type: '.$type);
                }
                $this->$mtd($font);
            }
        }
    }


}

        


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
