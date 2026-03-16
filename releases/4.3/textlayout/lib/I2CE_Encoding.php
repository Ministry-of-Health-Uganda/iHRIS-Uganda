<?php


/**
 *  helper library to store/look-up glyphnames  associated to
 *  a character code.
 *  
 */

class I2CE_Encoding {
        

    /**
     * protected @var  $glyph_names  an array of arrays with values glyph names  and keys unicode codepoints
     *  the first index of the array is the encoding type
     */
    protected $glyph_names=array();


    /*
     * protected @var $encoding an array with values character codes and keys unicode codepoints
     */
    protected $encoding = array();  

    /**
     *  protected @var string $unicode_encoding
     */
    protected $mb_encoding;


    /**
     * @param string $encoding_type -- set to the encoding used 
     * needs to be  one of PHP multibyte strings encodings
     */
    public function __construct($mb_encoding) {
        $this->setEncodingType($mb_encoding);
    }


    /*
     * set the encoding used
     * @param string $mb_encoding the encoding
     */
    protected function setEncodingType($mb_encoding) {
        $this->mb_encoding = $mb_encoding;
        $this->useMB = true;
        if( ($this->mb_encoding == 'ASCII') 
            || (strpos($this->mb_encoding,"ISO-8859") !== false )
            || (strpos($this->mb_encoding,"CP125") !== false )
            || (strpos($this->mb_encoding,"Windows-125" ) !== false)) {
            //we are a single byte encoding that respects ASCII
            $this->useMB = false;
        }
    }

    /*
     *  @var boolean  -- whether or not we need to use multibye strings
     */
    protected $useMB;

    /*
     *  Whether or not we need to use multibyte string functions for this encoding
     *  @returns boolean
     */
    public function useMB() {
        return $this->useMB;
    }

    /**
     *  Get the unicode encoding to use
     *  @returns string $encoding with values:
     */

    public function getEncodingType() {
        return $this->mb_encoding;
    }
        



    /**
     * get the multibyte character associated to a glyphname
     * @param string $gn the glyp hname
     * @returns string n character in the selected encoding
     */
    public function UnicodeFromGlyphname($gn) {
        $uc = false;
        $uc=array_search($gn,$this->glyph_names);
        if ( $uc === false) {
            $uc = 0xFFFD; //use the replacement character
        }
        return $uc;
    }

    /**
     * get the multibyte character associated to a character code
     * @param int $cc the character code
     * @returns int unicode codepoint
     */
    public function UnicodeFromCharactercode($cc) {
        return array_search($cc,$this->encoding);
    }

    /**
     * get the character code to a unicode codepoint
     * @param mixed $in
     */
    public function UnicodeToCharacterCode($in) {
        return $this->encoding[$in];
    }

    /**
     * get the glpyh name associated to to a unicode codepoint
     * @param mixed $in
     */
    public function UnicodeToGlyphname($in) {
        return $this->glyph_names[$in];
    }



    /**
     * Set the gylph name of a character
     * @param int $cp the unicode code point
     * @param  string $gn the glyphname
     */
    public function  setGlyphname($cp,$gn) {
        $this->glyph_names[$cp]= $gn;                                           
    }

    /**
     * Set the character code assoicated to a unicode code point
     * @param int $cp the unicode code point
     * @param mixed $cc the character code (either int or a character)
     */
    public function  setCharacterCode($cp,$cc) {
        $this->encoding[$cp]= $cc;                                              
    }



    /**
     * Read in a character encoding from a file
     * @param string $enc_file the file containing the character encoded 
     * exmaples are cp1250.map ISO-8859-1.map etc.
     * this function has been graciously stolen from makefont.php
     */
    public function readMap($enc_file)  {
        $a=file($enc_file);
        if(empty($a))
            die('<B>Error:</B> encoding not found: '.$enc);
        $this->encoding = array();
        $this->glyph_names = array();
        foreach($a as $l){
            if($l{0}=='!')      {
                $e=preg_split('/[ \\t]+/',rtrim($l));
                $cc=hexdec(substr($e[0],1));
                $uc=hexdec(substr($e[1],2,4));
                $gn=$e[2];
                $this->glyph_names[$uc] = $gn;
                $this->encoding[$uc] = $cc;
            }
        }
                
    }


    /**
     * Change the glyph names 
     * @param $names an associative array with the new value being the value
     * and the old value being the key
     */
    public function changeGlyphNames($names) {
        foreach ($names as $old=>$new) {
            $uc = array_search($old,$this->glyph_names);
            if (isset($uc)) {
                $this->glyph_names[$uc] = $new;
            }
        }
    }


    /**
     * Make sure U+20AC has the glyphname euro
     * if it exists in this encoding
     */
    public function fixEuro() {
        $this->glyph_names[0x20AC] = 'Euro';
    }

    /**
     * @var fix  Some common incorrect glyph names 
     */
    public static $fix=array('Edot'=>'Edotaccent','edot'=>'edotaccent','Idot'=>'Idotaccent','Zdot'=>'Zdotaccent','zdot'=>'zdotaccent',
                             'Odblacute'=>'Ohungarumlaut','odblacute'=>'ohungarumlaut','Udblacute'=>'Uhungarumlaut','udblacute'=>'uhungarumlaut',
                             'Gcedilla'=>'Gcommaaccent','gcedilla'=>'gcommaaccent','Kcedilla'=>'Kcommaaccent','kcedilla'=>'kcommaaccent',
                             'Lcedilla'=>'Lcommaaccent','lcedilla'=>'lcommaaccent','Ncedilla'=>'Ncommaaccent','ncedilla'=>'ncommaaccent',
                             'Rcedilla'=>'Rcommaaccent','rcedilla'=>'rcommaaccent','Scedilla'=>'Scommaaccent','scedilla'=>'scommaaccent',
                             'Tcedilla'=>'Tcommaaccent','tcedilla'=>'tcommaaccent','Dslash'=>'Dcroat','dslash'=>'dcroat','Dmacron'=>'Dcroat',
                             'dmacron'=>'dcroat','combininggraveaccent'=>'gravecomb','combininghookabove'=>'hookabovecomb',
                             'combiningtildeaccent'=>'tildecomb','combiningacuteaccent'=>'acutecomb','combiningdotbelow'=>'dotbelowcomb',
                             'dongsign'=>'dong');
        
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
