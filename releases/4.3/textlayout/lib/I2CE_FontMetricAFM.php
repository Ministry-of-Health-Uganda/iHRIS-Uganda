<?php

/**
 * Class to contain information about  font metrics.  Modeled
 * after the data contained in the Adobe Font Metrics File Format Specification
 * http://www.adobe.com/devnet/font/pdfs/5004.AFM_Spec.pdf
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
 *
 * 
 * <br> Note 1
 * the function getCharacterInfo 
 * @returns mixed
 * @param key are the  character information (besides the width)
 * in section 8 of Adobe Font Metrics File Format Specification
 *
 * <br>Note 2
 * the function getFontCharacteristic
 * @returns mixed
 * @param string $key are the keys 
 * in section 4.2 of Adobe Font Metrics File Format Specification
 * values and keys are
 * FontName string
 * FullName string
 * FamilyName string
 * Weight string
 * FontBBox number number number number
 * Version string
 * Notice string
 * EncodingScheme string
 * MappingScheme integer
 * EscChar integer
 * CharacterSet string
 * Characters integer
 * IsBaseFont boolean
 * VVector number number
 * IsFixedV boolean
 * CapHeight number
 * XHeight number
 * Ascender number
 * Descender number
 *  
 * We also include section 4,3 of Adobe Font Metrics File Format Specification
 * for the  following possible keys which are direction dependent
 * StartDirection integer
 * EndDirection
 * UnderlinePosition number
 * UnderlineThickness number
 * ItalicAngle number
 * CharWidth number number
 * IsFixedPitch boolean
 */


class I2CE_FontMetricAFM extends I2CE_FontMetricMultiDirection {        
    /**
     * @param I2CE_Encoding $encoding -- the encoding used for the internal storage of strings/characters
     *       needs to be one that is a valid encoding for PHP multibyte strings. 
     *  @param I2CE_Encoding $file_encoding -- the character encoding  used in the file afm file
     *  @param string $afmfile  the afm file to load.
     */
    public function __construct(&$internal_encoding,&$file_encoding,$afmfile ) {
        $this->gn2cc = array();
        parent::__construct(array('H','V'),$internal_encoding); 
        $this->loadFontMetricFromAFM($file_encoding,$afmfile);
    }

    /**
     *@var protected array.  Array are glpyh names values are character codes.
     */
    protected $gn2cc;

    /**
     *  Load the font metrics from an afm  file
     *  (Some of this code was stolen from makefont.php)
     *  @param I2CE_Encoding $encoding -- the character encoding  used in the file
     *  @param string $afmfile
     *  Caution:  Units for these files are 1/1000 of a point where a point is 1/72 of an inch
     */
    protected function loadFontMetricFromAFM($encoding,$afmfile) {
        $prev_dir = $this->getDirection();
        $sub = mb_substitute_character();
        mb_substitute_character("none");
        //Read a font metric file
        $found_afmfile = I2CE::getFileSearch()->search('AFM_PATH',$afmfile);
        if (!$found_afmfile) {
            die('Error: AFM file not found: '.$afmfile);
        } else {
            $afmfile = $found_afmfile;
        }
        $a=file($found_afmfile);
        if(empty($a))   die('File empty' . $afmfile);
        $this->setGlobal();  //global information is the default
        $this->setLinegap(0); //no line gap information is present in a AFM file
        $mode = 0;
        /**
         * Modes are 0: header or wirting direction
         * 10: Character metrics
         * 20: Kerning
         * 21: Kerning Tracking
         * 21: Kerning Pairs 
         * 30: Composites
         */
        foreach($a as $l){
            $e=explode(' ',rtrim($l));
            $code = $e[0];
            switch  ($mode) {
            case 10: //character metrics
                $vals = array();
                unset($cc);
                unset($gn);
                switch ($code) {
                case 'EndCharacterMetrics':
                case 'EndCharMetrics':
                    $mode = 0;
                    continue 2; //break out of the switch($mode)
                case 'C':
                    $cc = (int) $e[1];  
                    break;
                case 'CH':
                    $cc = hexdec($e[1]); 
                    break;
                }
                if (!isset($cc)) {
                    break;
                }
                $i = 3;
                while ($i < count($e)) { 
                    $subcode = $e[$i];
                    switch ($subcode) {
                    case 'WX':
                    case 'W0X':
                    case 'W1X':
                    case 'WY':
                    case 'W0Y':
                    case 'W1Y': //widths and heights
                        $vals[$subcode] = ((float) $e[$i+1]);
                        $i = $i +2;
                        break;
                    case 'W':
                    case 'W0':
                    case 'W1':
                    case  'VV': //widths and heights
                        $vals[$subcode] = array(((float) $e[$i+1]),((float) $e[$i+2]));
                        $i = $i +3;
                        break;
                    case 'N': //postscript glyph name
                        $gn = $e[$i+1];
                        $i = $i +2;
                        break;
                    case 'B': //bounding box
                        $vals[$subcode] = array(((float) $e[$i+1])
                                                ,((float) $e[$i+2])
                                                ,((float) $e[$i+3])
                                                ,((float) $e[$i+4]));
                        $i = $i +5;
                        break;
                    case 'L'://Ligature sequence -- may have more than one
                        if (!isset($vals['L'])) {
                            $vals['L'] = array();
                        }
                        $vals['L'][] = array($e[$i+1],$e[$i+2]);
                        $i = $i + 3;
                        $i++;
                        break;
                    default:
                        $i++;
                        break;
                    }   
                }
                unset($uc);
                if ( $cc < 0 ) { //not a valid character code
                    if ($gn !== null) {
                        //try to get the unicode code point of the glyphname based on our encoding
                        $uc = $encoding->UnicodeFromGlyphname($gn);  
                        if ($uc === null ) { //we failed
                            //try to get a unicode code point from the glyphname
                            if (preg_match('/^uni([0-9A-F]{4})$/',$gn,$ucs)){ 
                                //we have a unicode codepoint
                                $uc = $ucs[1];
                            }
                        }
                    }
                } else { //we have a valid character code
                    $uc = $encoding->UnicodeFromCharactercode($cc);
                    if ($gn !== null) {
                        $this->gn2cc[$gn] = $cc;
                    }
                }
                if (($uc !== null) && 
                    ($uc <= 0xFFFF) && 
                    ($uc >= 0) && 
                    ($uc !== 0xFFFD) ){                  //replacement character
                    $uc = I2CE_UTF8::cp_to_code($uc); //convert to UTF8
                    $cc = mb_convert_encoding($uc,$this->getEncoding()->getEncodingType(),'UTF-8'); 
                    if ($this->getEncoding()->useMB()) {
                        if (mb_strlen($cc,$this->getEncoding()->getEncodingType())===0) {
                            $cc = -1;
                        }
                    } else {
                        if (strlen($cc) === 0) {
                            $cc = -1;
                        }
                    }
                }  else {
                    $cc = -1;
                }
                foreach (array('WX', 'W0X') as $key) {
                    if (array_key_exists($key,$vals) && $vals[$key] !== null ) {
                        $this->setDirection('H');
                        if ($cc !== -1) {
                            $this->setCharacterWidth($cc,$vals[$key]);
                        }
                        if ( $gn !== null) {
                            $this->setCharacterWidth($gn,$vals[$key]);
                        }
                    }
                }
                if (array_key_exists('W1X',$vals) && $vals['W1X'] !== null) {
                    $this->setDirection('V');
                    if ($cc !== -1) {
                        $this->setCharacterWidth($cc,$vals['W1X']);
                    }
                    if (null !==($gn)) {
                        $this->setCharacterWidth($gn,$vals['W1X']);
                    }
                }
                foreach (array('WY', 'W0Y') as $key) {
                    if (array_key_exists($key,$vals) && null !==($vals[$key])) {
                        $this->setDirection('H');
                        if ($cc !== -1) {
                            $this->setCharacterHeight($cc,$vals[$key]);
                        }
                        if (null !==($gn)) {
                            $this->setCharacterHeight($gn,$vals[$key]);
                        }
                    }
                }
                if (array_key_exists('W1Y',$vals) && null !==($vals['W1Y'])) {
                    $this->setDirection('V');
                    if ($cc !== -1) {
                        $this->setCharacterHeight($cc,$vals['W1Y']);
                    }
                    if (null !==($gn)) {
                        $this->setCharacterHeight($gn,$vals['W1Y']);
                    }

                }
                foreach (array('W', 'W0') as $key) {
                    $this->setDirection('H');
                    if (array_key_exists($key,$vals) && null !==($vals[$key])) {
                        if ($cc !== -1) {
                            $this->setCharacterWidth($cc,$vals[$key]);
                            $this->setCharacterHeight($cc,$vals[$key]);
                        }
                        if (null !==($gn)) {
                            $this->setCharacterWidth($gn,$vals[$key]);
                            $this->setCharacterHeight($gn,$vals[$key]);
                        }

                    }
                }
                if (array_key_exists('W1',$vals) && null !==($vals['W1'])) {
                    $this->setDirection('V');
                    if ($cc !== -1) {
                        $this->setCharacterWidth($cc,$vals[$key]);
                        $this->setCharacterHeight($cc,$vals[$key]);
                    }
                    if (null !==($gn)) {
                        $this->setCharacterWidth($gn,$vals[$key]);
                        $this->setCharacterHeight($gn,$vals[$key]);
                    }
                }
                foreach (array('VVector'=>'VV','BoundingBox'=>'B','Ligature'=>'L') as $name=>$key) { 
                    $this->setGlobal();
                    if (array_key_exists($key,$vals) && null !==($vals[$key])) {
                        if ($cc !== -1) {
                            $this->setCharacterInfo($cc,$name,$vals[$key]);
                        }
                        if (null !==($gn)) {
                            $this->setCharacterInfo($gn,$name,$vals[$key]);
                        } 
                    }
                }
                break;
            case 20: //kerning
                switch ($code) {
                case 'EndKernData':
                    $mode  = 0;
                    break;
                case 'StartTrackKern':
                    $mode = 21;
                    break;
                case 'StartKernPairs':
                case  'StartKernPairs0':
                    $this->setDirection('H');
                    $mode = 22;
                    break;
                case 'StartKernPairs1':
                    $this->setDirection('V');
                    $mode = 22;
                    break;
                }
                break; 
            case 21: //kerning tracking
                switch ($code) { 
                case 'EndTrackKern': 
                    $mode = 20; 
                    break;
                case '':
                    break;
                }
                break;
            case 22:  //kerning pairs
                switch ($code) {
                case 'EndKernPairs':
                    $mode = 21;
                    break;
                case 'KP': //kerning pairs are given by glyph name
                    $this->setDirection('H');
                    $this->setKerningByPair($e[1],$e[2],((float) $e[3]));
                    $this->setDirection('V');
                    $this->setKerningByPair($e[1],$e[2],((float) $e[4]));
                    //get the corresponding character codes and insert into the table
                    $this->setGlobal();
                    $cc1 = $this->getEncoding()->getCodeFromGlyphname($e[1]);
                    $cc2 = $this->getEncoding()->getCodeFromGlyphname($e[2]);
                    if (($cc1 !== -1) && ($cc2 !== -1)) {
                        $this->setDirection('H');
                        $this->setKerningByPair($cc1,$cc2,((float) $e[3]));
                        $this->setDirection('V');
                        $this->setKerningByPair($cc1,$cc2,((float) $e[4]));
                    }
                    break;
                case 'KPH': //not sure what is best to do here.
                    $ch1 = ltrim(rtrim($e[1],'>'),'<');
                    $ch2 = ltrim(rtrim($e[1],'>'),'<');
                    $this->setDirection('H');
                    $this->setKerningByPair($ch1,$ch2,((float) $e[3]));
                    $this->setDirection('V');
                    $this->setKerningByPair($ch1,$ch2,((float) $e[4]));
                    break;
                case 'KPX':
                    $this->setDirection('H');
                    $this->setKerningByPair($e[1],$e[2], ((float) $e[3]));
                    if (!array_key_exists($e[1],$this->gn2cc) || !array_key_exists($e[2],$this->gn2cc)) {
                        break;
                    }
                    $cc1 = $this->gn2cc[$e[1]];
                    $cc2 = $this->gn2cc[$e[2]];
                    if ( ($cc1 !== -1) && ($cc2 !== -1)) {
                        $this->setKerningByPair($cc1,$cc2, ((float) $e[3]));
                    }
                    break;
                case 'KPY':
                    $this->setDirection('V');
                    $this->setKerningByPair($e[1],$e[2], ((float) $e[3]));
                    if (!array_key_exists($e[1],$this->gn2cc) || !array_key_exists($e[2],$this->gn2cc)) {
                        break;
                    }
                    $cc1 = $this->gn2cc[$e[1]];
                    $cc2 = $this->gn2cc[$e[2]];
                    if (($cc1 !== -1) && ($cc2 !== -1)) {
                        $this->setKerningByPair($cc1,$cc2, ((float) $e[3]));
                    }
                    break;
                }
                break;
            case 30: //composites
                switch ($code) {
                case  'EndComposites':
                    $mode = 0;
                    break;
                }
                break;
            default: //header/global information or writing direction
                switch ($code) {
                case 'BeginCharacterMetrics':
                case 'StartCharMetrics':
                    $mode = 10;
                    break;
                case 'BeginKernData':
                case 'StartKernData':
                    $mode = 20;
                    break;
                case 'BeginComposites':
                case 'StartComposites':
                    $mode = 03;
                    break;
                case 'StartDirection': //does not have to exist in which case we are in direction 0
                    if ( $e[1] == '1') {
                        $this->setDirection('V');
                    } else {
                        $this->setDirection('H');
                    }
                    break;
                case 'EndDirection':
                    $this->setGlobal();
                    break;
                case 'CharWidth':
                    $dir = $this->getDirection(); //this is not global information
                    if ($dir === -1) { // however the keyword StartDirection is optional
                        $this->setDirection('H');
                    }
                    $this->setFixedWidth(true);
                    $this->setFixedWidthSize(((float) $e[1]));
                    $this->setFixedHeightSize(((float) $e[2]));
                    $this->setDirection($dir);
                    break;
                case 'UnderlinePosition':
                case 'UnderlineThickness':
                case 'ItalicAngle':
                    $dir = $this->getDirection();//this is not global information
                    if ($dir === -1) {// however the keyword StartDirection is optional
                        $this->setDirection('H');
                    }
                    $this->setFontCharacteristic($code, $e[1]);
                    $this->setDirection($dir);
                    break;                              
                case 'IsFixedPitch':
                    $isfixed = (strpos(strtolower($e[1]),'true')===0);
                    $dir = $this->getDirection();//this is not global information
                    if ($dir === -1) {// however the keyword StartDirection is optional
                        $this->setDirection('H');
                    }
                    $this->setFixedWidth($isfixed);
                    $this->setDirection($dir);
                    break;
                case 'isFixedV':
                case  'isBaseFont':
                    $this->setGlobal();
                    $this->setFontCharacteristic($code,strpos(strtolower($e[1]),'true'));
                    break;
                case 'MappingScheme':
                case 'EscCar': 
                case 'Characters':
                    $this->setGlobal();
                    $this->setFontCharacteristic($code,(int) $e[1]);
                    break;
                case 'Notice':
                case 'Comment':
                    $this->setGlobal();
                    $val = $this->getFontCharacteristic($code);
                    if ($val === null) {
                        $val = "";
                    }
                    $this->setFontCharacteristic($code,
                                                 $val .
                                                 substr($l,strlen($code) + 1));
                    break;
                case 'CapHeight':
                case 'XHeight': 
                    $this->setGlobal();
                    $this->setFontCharacteristic($code,((float) $e[1]));
                    break;
                case 'Ascender':
                    $this->setGlobal();
                    $this->setAscender(((float) $e[1]));
                    break;
                case 'Descender':
                    $this->setGlobal();
                    $this->setDescender(((float) $e[1]));
                    break;
                case 'VVector':
                    $this->setGlobal();
                    $this->setFontCharacteristic($code,
                                                 array (((float) $e[1]), ((float) $e[2])));
                case 'FontBBox':  
                    $this->setGlobal();
                    $this->setBoundingBox(array(
                                              ((float) $e[1]),
                                              ((float) $e[2]),
                                              ((float) $e[3]),
                                              ((float) $e[4])
                                              ));
                    break;
                default: //the rest are strings for global font information
                    $this->setGlobal();
                    $this->setFontCharacteristic($code,$e[1]);
                    break;
                }
                break;
            }
        }
        //normalize a few values.
        $this->setGlobal();
        $asc = $this->getAscender();
        if ($asc  == 0) {
            $d = mb_convert_encoding('d',$this->getEncoding()->getEncodingType(),'ASCII');
            $ht = $this->getCharacterHeight($d);
            if ($ht != 0) {
                $this->setAscender($ht);
            } else {
                $this->setDirection('H');
                $ht = $this->getCharacterHeight($d);
                if ($ht != 0) {
                    $this->setAscender($ht);
                } else {
                    $this->setGlobal();
                    $bbox = $this->getBoundingBox();
                    $this->setAscender($bbox[3]);
                }
            }
        }
        $this->setGlobal();
        $dsc = $this->getAscender();
        if ($dsc ==0) {
            $p = mb_convert_encoding('p',$this->getEncoding()->getEncodingType(),'ASCII');
            $bbox = $this->getCharacterInfo($p,'BoundingBox');
            if (!$bbox == null) {
                $this->setDescender($bbox[1]);
            } else {
                $this->setDirection('H');
                $bbox = $this->getCharacterInfo($p,'BoundingBox');
                if (!$bbox == null) {
                    $this->setDescender($bbox[1]);
                } else {
                    $this->setGlobal();
                    $bbox = $this->getBoundingBox();
                    $this->setDescender($bbox[1]);
                }
            }
                        
        }
        $this->setDirection($prev_dir);
        mb_substitute_character($sub);

    }
        

        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
