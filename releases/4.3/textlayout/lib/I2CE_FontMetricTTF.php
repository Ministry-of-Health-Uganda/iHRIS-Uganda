<?php



/**
 * Class to contain information about  font metrics.
 * Data extracted from TTF files
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
 */

class I2CE_FontMetricTTF extends I2CE_FontMetricMultiDirection{
    protected $ttf_file;
    protected $ttf_handle;
    protected $directory; 
    protected $numOfLongHorMetrics;
    protected $num_glyphs;
    protected $GIDtoCPs;
    protected $indexToLocFormat;
    protected $glyph_offsets;
    protected $units_per_em;
    protected $file_size;


    /*
     * @param I2CE_Encoding $encoding  -- the encoding we store strings as
     * @param string $ttf_file  the name of the true type file.
     */
    public function __construct(&$encoding,$ttf_file) { 
        parent::__construct(array('H','V'),$encoding); 
        $found_ttf_file = I2CE::getFileSearch()->search('TTF_PATH',$ttf_file);
        if (!$found_ttf_file) {
            die("Cannot find file ($ttf_file)\n");
        }
        $this->ttf_file = $found_ttf_file;
        $this->openForReading();
        $this->loadDirectory();
        $this->read_numGlyphs();
        $this->read_head();
        $this->read_post();
        $this->read_hhea();
        $this->read_hmtx();
        $this->read_loca();
        $this->read_glyf();
        if ($this->inDirectory('kern')) {
            $this->read_kern();
        }
        $this->read_name();
        $this->close();
        $this->setDirection('H');
    }
        
    /*
     * the path and file name for the truetype font file
     * @returns string
     */
    public function getTTFFile() {
        return $this->ttf_file;
    }
 

    protected function gotoTable($table) {
        if (!$this->inDirectory($table)) {
            die ("Requested table ($table) is not in the TTF file.\n");
        } 
        $f = $this->openForReading();
        fseek($f,$this->directory[$table]['offset']);
        return $this->ttf_handle;
    }

    protected function inDirectory($table) {
        return isset($this->directory[$table]);
    }


    protected function openForReading() {
        if (isset($this->ttf_handle )) {
            return $this->ttf_handle;
        }
        $this->ttf_handle = fopen($this->ttf_file, "r"); 
        if  ($this->ttf_handle === FALSE) {
            unset ($this->ttf_handle);
            die ("Cannot open ({$this->ttf_file}) for reading\n");
        }
        return $this->ttf_handle;
    }

    protected function close() {
        if (isset($this->ttf_handle )) {
            fclose($this->ttf_handle);
        }
        unset($this->ttf_handle);               
    }




    /** DATA TYPES:
     * http://developer.apple.com/textfonts/TTRefMan/RM06/Chap6.html#Types
     *Macintosh Data type OS/2 Data Type Description
     *uint8 BYTE 8-bit unsigned integer
     *int8 CHAR 8-bit signed integer
     *uint16 USHORT 16-bit unsigned integer
     *int16 SHORT 16-bit signed integer
     *uint32 ULONG 32-bit unsigned integer
     *int32 LONG 32-bit signed integer
     *shortFrac - 16-bit signed fraction
     *Fixed - 16.16-bit signed fixed-point number
     *FWord - 16-bit signed integer that describes a quantity in FUnits, the smallest measurable distance in em space.
     *uFWord - 16-bit unsigned integer that describes a quantity in FUnits, the smallest measurable distance in em space.
     *F2Dot14 - 16-bit signed fixed number with the low 14 bits representing fraction.
     *longDateTime - The long internal format of a date in seconds since 12:00 midnight, January 1, 1904. It is represented as a signed 64-bit integer.
     *
     * NOTE:  We are Big-Endian here
     */
    protected function read_fixed_pieces() {
        $v = array();
        $v[2] = $this->read_int16();
        $v[1] = $this->read_int16();
        return $v;
    }
    protected function read_uint8() {
        $v = unpack ('C1',fread($this->ttf_handle,1));          
        return $v[1]; 
    }
    protected function read_int8() {
        $v = unpack ('c1',fread($this->ttf_handle,1));          
        return $v[1]; 
    }
    protected function read_uint16() {
        $v = unpack ('n1',fread($this->ttf_handle,2));
        return $v[1]; 
    }
    protected function read_int16() {
        //$v = unpack ('s1',fread($this->ttf_handle,2));                
        $v = unpack ('C2',fread($this->ttf_handle,2));          
        $v = pack ('C2', $v[2],$v[1]);
        $v= unpack('s1',$v);
        return $v[1]; 
    }
    protected function read_uint32() {
        $v = unpack ('N1',fread($this->ttf_handle,4));          
        return $v[1]; 
    }
    protected function read_int32() {
        $v = unpack ('l1',fread($this->ttf_handle,4));          
        return $v[1]; 
    }
    protected function read_FWord() {
        $v = unpack ('C2',fread($this->ttf_handle,2));          
        $v = pack ('C2', $v[2],$v[1]);
        $v= unpack('s1',$v);
        return  (int) ($v[1]*(1000.0/$this->units_per_em));
    }

    protected function read_uFWord() {
        $v = unpack ('n1',fread($this->ttf_handle,2));
        return (int) ($v[1]*(1000.0/$this->units_per_em));;
    }



        


        

    protected function read_name() {
        $f = $this->gotoTable('name');
        /*
         *Table 36: 'name' table
         *Type Name Description
         *UInt16 format Format selector. Set to 0.
         *UInt16 count The number of nameRecords in this name table.
         *UInt16 stringOffset Offset in bytes to the beginning of the name character strings.
         *NameRecord nameRecord[count] The name records array.
         *variable name character strings The character strings of the names. Note that these are not necessarily AS
         */
        $this->setGlobal();
        $this->read_uint16();//format
        $count = $this->read_uint16();
        $stringOffset = $this->read_uint16();
        $nameRecords = array();
        for ($c =0; $c < $count; $c++) {
            /*Type Name Description
             *UInt16 platformID Platform identifier code.
             *UInt16 platformSpecificID Platform-specific encoding identifier.
             *UInt16 languageID Language identifier.
             *UInt16 nameID Name identifiers.
             *UInt16 length Name string length in bytes.
             *UInt16 offset Name string offset in bytes from stringOffset
             */
            $nameRecords[$c] = array(
                'platformID' => $this->read_uint16(),
                'platformSpecificID' => $this->read_uint16(),
                'language' => $this->read_uint16(),
                'nameID' => $this->read_uint16(),
                'length' => $this->read_uint16(),
                'offset' => $this->read_uint16(),
                );
        }
        /** Some of the NameID code(s)
         *0 Copyright notice.
         *1 Font Family. This string is the font family name the user sees on Macintosh platforms.
         *2 Font Subfamily. This string is the font family the user sees on Macintosh platforms.
         *3 Unique subfamily identification.
         *4 Full name of the font.
         *5 Version of the name table.
         *6 PostScript name of the font. Note: A font may have only one PostScript name and that name must be ASCII.
         */
        $codes = array( 0 => 'copyright',
                        1 => 'family',
                        2 => 'subfamily',
                        3 => 'unique_subfamily',
                        4=> 'name',
                        5 => 'version',
                        6 => 'postscriptName',
                        7 => 'tm',
                        13 =>'lic',
                        14 =>'licURL'
            );
        foreach ($nameRecords as $nameRecord) {
            $ok = false;
            if (($nameRecord['platformID'] == 3) && ($nameRecord['platformSpecificID'] == 1)) {
                $ok = true; //microsoft unicode
            }
            if (($nameRecord['platformID'] == 0) && ($nameRecord['platformSpecificID'] == 3)) {
                $ok = true; //unicode 2.0 or later
            }
            if (($nameRecord['platformID'] == 0) && ($nameRecord['platformSpecificID'] == 0)) {
                $ok = true; //unicode default
            }
            if (!$ok) {
                $continue;
            }
            if (isset($codes[$nameRecord['nameID']])) {
                $name = $this->getFontCharacteristic($codes[$nameRecord['nameID']]);
                if ($name) {
                    //this was already set, so move on.
                    continue;
                }
                fseek($f, $this->directory['name']['offset'] + $stringOffset + $nameRecord['offset']);
                $name = fread($f,$nameRecord['length']);
                $this->setFontCharacteristic($codes[$nameRecord['nameID']],$name);
            }
        }
                
    }

    protected function read_hhea() { 
        $f = $this->gotoTable('hhea');
        /*Type   Name Description  http://developer.apple.com/textfonts/TTRefMan/RM06/Chap6hhea.html
         *Fixed version 0x00010000 (1.0)
         *FWord ascent Distance from baseline of highest ascender
         * FWord descent Distance from baseline of lowest descender
         * FWord lineGap typographic line gap
         * uFWord advanceWidthMax must be consistent with horizontal metrics
         * FWord minLeftSideBearing must be consistent with horizontal metrics
         * FWord minRightSideBearing must be consistent with horizontal metrics
         * FWord xMaxExtent max(lsb + (xMax-xMin))
         * int16 caretSlopeRise used to calculate the slope of the caret (rise/run) set to 1 for vertical caret
         *int16 caretSlopeRun 0 for vertical
         *FWord caretOffset set value to 0 for non-slanted fonts
         *int16 reserved set value to 0
         *int16 reserved set value to 0
         *int16 reserved set value to 0
         *int16 reserved set value to 0
         *int16 metricDataFormat 0 for current format
         *uint16 numOfLongHorMetrics number of advance widths in metrics table*/
        fread($f,4); //ignore version
        $this->setDirection('H');
        $this->setAscender($this->read_Fword());
        $this->setDescender($this->read_Fword());
        $this->setLinegap($this->read_Fword());
        $this->setFontCharacteristic('MaxWidth', $this->read_uFword());
        $this->setFontCharacteristic('minLSB',$this->read_Fword());
        $this->setFontCharacteristic('minRSB',$this->read_Fword());
        $this->setFontCharacteristic('xMaxExtent', $this->read_Fword());
        $this->setFontCharacteristic('caretSlopeRise', $this->read_int16());
        $this->setFontCharacteristic('caretSlopeRun', $this->read_int16());
        $this->setFontCharacteristic('caretOffset', $this->read_FWord());
        fread($f,2+2+2+2+2);
        $this->numOfLongHorMetrics = $this->read_uint16();
        $this->setDirection(-1);//return to global state
    }

        


    protected function read_loca() {
        $f = $this->gotoTable('loca');
        $this->glyph_offsets = array();
        switch($this->indexToLocFor) {
        case 0: //short version
            for ($n = 0; $n <= $this->num_glyphs ; $n++) {
                $this->glpyh_offsets[$n] = 2*$this->read_uint16() + $this->directory['glyf']['offset'];
            }
            break;
        default:  //case 1 -- long version
            for ($n = 0; $n <= $this->num_glyphs ; $n++) {
                $this->glyph_offsets[$n] = $this->read_uint32() + $this->directory['glyf']['offset'];
            }
            break;
        }
    }

    protected function read_glyf() {
        $f = $this->ttf_handle;
        /*Table 14: Glyph description
         *Type Name Description
         *int16 numberOfContours If the number of contours is positive or zero, it is a single glyph;
         *      If the number of contours is -1, the glyph is compound
         *FWord xMin Minimum x for coordinate data
         *FWord yMin Minimum y for coordinate data
         *FWord xMax Maximum x for coordinate data
         *FWord yMax Maximum y for coordinate data
         */
        $this->setDirection('H');
        for ($n=0; $n < $this->num_glyphs; $n++) {
            fseek($f,$this->glyph_offsets[$n]);
            $c = $this->getEncodedCharactersFromGlyphID($n);
            $this->read_int16();
            $bbox = array($this->read_FWord(),$this->read_FWord(),$this->read_FWord(),$this->read_FWord());
            $this->setCharacterInfo($c,'BoundingBox',$bbox);
            $this->setCharacterHeight($c,$bbox[3]);
        }
    }

    /**
     *See http://developer.apple.com/textfonts/TTRefMan/RM06/Chap6hmtx.html
     */
    protected function read_hmtx() { 
        $f = $this->gotoTable('hmtx');
        $this->setDirection('H');
        /**A longHorMetric is defined by the C structure shown here:
         *struct {
         *   uint16 advanceWidth;
         *   int16 leftSideBearing;
         *}
         *'hmtx' table
         *Type Name Description
         *longHorMetric hMetrics[numOfLongHorMetrics] The value numOfLongHorMetrics comes from 
         *              the 'hhea' table. If the font is monospaced, only one entry need be in 
         *              the array but that entry is required.
         * FWord leftSideBearing[] Here the advanceWidth is assumed to be the same as the 
         *               advanceWidth for the last entry above. The number of entries in this 
         *               array is derived from the total number of glyphs minus numOfLongHorMetrics. 
         *               This generally is used with a run of monospaced glyphs (e.g. Kanji fonts or 
         *               Courier fonts). Only one run is allowed and it must be at the end.
         */
        $last_read_width = 0;
        for ($i=0; $i < $this->numOfLongHorMetrics; $i++) {
            $c = $this->getEncodedCharactersFromGlyphID($i);
            if (empty($c)) {
                I2CE::raiseError("Glyph id $i has no encoded character", E_USER_NOTICE);
                continue;
            }
            $last_read_width = $this->read_uFWord(); 
            $this->setCharacterWidth($c,$last_read_width);  
            $this->setCharacterInfo($c,'LeftSideBearing',$this->read_FWord()); 
        }
        for ($i = $this->numOfLongHorMetrics; $i < $this->num_glyphs ; $i++) { 
            $c = $this->getEncodedCharactersFromGlyphID($i); 
            if (empty($c)) {
                I2CE::raiseError("Glyph id $i has no encoded character", E_USER_NOTICE);
                continue;
            }
            $this->setCharacterWidth($c,$last_read_width); 
            $this->setCharacterInfo($c,'LeftSideBearing',$this->read_FWord()); 
        } 
        $this->setDirection(-1); 
    }

    protected function read_head() {
        if ($this->inDirectory('bhed')) {
            //there is only one of bhed or head.
            $f = $this->gotoTable('bhed'); //bimapped true type
        } else {
            $f = $this->gotoTable('head'); //not bitmapped
        }
        /*Type Name Description
         *Fixed version 0x00010000 if (version 1.0)
         *Fixed fontRevision set by font manufacturer
         *uint32 checkSumAdjustment To compute: set it to 0, calculate the checksum for the 'head' 
         *       table and put it in the table directory, sum the entire font as uint32, then store
         *        B1B0AFBA - sum. The checksum for the 'head' table will not be wrong. That is OK.
         *uint32 magicNumber set to 0x5F0F3CF5
         *uint16 flags 
         *       bit 0 - y value of 0 specifies baseline 
         *       bit 1 - x position of left most black bit is LSB
         *       bit 2 - scaled point size and actual point size will differ (i.e. 24 point glyph differs from 12 point glyph scaled by factor of 2)
         *       bit 3 - use integer scaling instead of fractional
         *       bit 4 - (used by the Microsoft implementation of the TrueType scaler)
         *       bit 5 - This bit should be set in fonts that are intended to e laid out vertically, 
         *               and in which the glyphs have been drawn such that an x-coordinate of 0 corresponds to the desired vertical baseline.
         *      bit 6 - This bit must be set to zero.
         *      bit 7 - This bit should be set if the font requires layout for correct linguistic rendering (e.g. Arabic fonts).
         *      bit 8 - This bit should be set for a GX font which has one or more metamorphosis effects designated as happening by default.
         *      bit 9 - This bit should be set if the font contains any strong right-to-left glyphs.
         *      bit 10 - This bit should be set if the font contains Indic-style rearrangement effects.
         *      bits 11-12 - Defined by Adobe.
         *uint16 unitsPerEm range from 64 to 16384
         *longDateTime created international date
         *longDateTime modified international date
         *FWord xMin for all glyph bounding boxes
         *FWord yMin for all glyph bounding boxes
         *FWord xMax for all glyph bounding boxes
         *FWord yMax for all glyph bounding boxes
         *uint16 macStyle 
         *        bit 0 bold
         *        bit 1 italic
         *        bit 2 underline
         *        bit 3 outline
         *        bit 4 shadow
         *        bit 5 condensed (narrow)
         *        bit 6 extended
         * uint16 lowestRecPPEM smallest readable size in pixels
         *  int16 fontDirectionHint 
         *       0 Mixed directional glyphs
         *       1 Only strongly left to right glyphs
         *       2 Like 1 but also contains neutrals
         *       -1 Only strongly right to left glyphs
         *       -2 Like -1 but also contains neutrals
         *  int16 indexToLocFormat 0 for short offsets, 1 for long
         * int16 glyphDataFormat 0 for current format
         */
        $this->read_fixed_pieces(); //version
        $this->read_fixed_pieces(); //fontRevision;
        $this->read_uint32(); //checksum
        $this->read_uint32(); // magicnum
        $this->read_uint16(); //flags
        $this->units_per_em = $this->read_uint16(); //units per em
        //skip timestamps
        fread($f,8+8);
        $this->setDirection(-1);
        $this->setBoundingBox(  array( $this->read_FWord(),$this->read_FWord(),$this->read_FWord(),$this->read_FWord()));
        $this->read_uint16(); // mac style;
        $this->read_uint16(); //lowestRECPPEM
        $this->setDirection(-1); //global context
        $this->setFontCharacteristic('FontDirectionHint',$this->read_int16());
        $this->indexToLocFor = $this->read_int16();
    }

    /**
     *Reference:http://developer.apple.com/textfonts/TTRefMan/RM06/Chap6.html
     */
    protected function loadDirectory() {
        /*read in the font directory. The first subtable is the offset subtable
         *Table 4 : The offset subtable
         *Type Name Description
         *      uint32 scaler type A tag to indicate the OFA scaler to be used to rasterize this font; 
         *      uint16 numTables number of tables
         *      uint16 searchRange (maximum power of 2 <= numTables)*16
         *      uint16 entrySelector log2(maximum power of 2 <= numTables)
         *      uint16 rangeShift numTables*16-searchRange
         */
        $f = $this->ttf_handle;
        fread($f,4);
        $num_tables =$this->read_uint16();
        fread($f,2+2+2); //read searchRange, entrySelector, rangeShift and ignore
        /* read in the table directory:
         * Type  Name  Description
         * uint32 tag 4-byte identifier
         * uint32 checkSum checksum for this table
         * uint32 offset offset from beginning of sfnt
         * uint32 length length of this table in byte (actual length not padded length)
         */
        $dir = array();
        for ($n=0; $n < $num_tables; $n++) {
            $tag = fread($f,4); 
            $this->read_uint32(); //checksum
            $offset = $this->read_uint32();
            $length = $this->read_uint32();
            $dir[$tag] = array(  'offset' => $offset, 'length'=>$length);
        }
        $this->directory = $dir;
    }

        
    protected function setCIDtoGID(&$CIDtoGID,$cid,$gid,$warning,$try_unicode=false) {
        if ($try_unicode){
            if ((count($this->GIDtoCPs[$gid]) ==1) &&( $this->GIDtoCPs[$gid][0] == -1)) {
                $this->GIDtoCPs[$gid] = array($cid);
            } 
        } 
        $c = $this->getEncodedCharactersFromGlyphID($gid);
        if ( (isset($CIDtoGID[$cid]))   && ($CIDtoGID[$cid] !== $gid)) {
            $warning .=  "\tPrevious value is {$CIDtoGID[$cid]} for character (".
                $this->getEncodedCharactersFromGlyphID($CIDtoGID[$cid]).")\n";
            $CIDtoGID[$cid] = $gid;
            $warning .=  "\tNew value is {$CIDtoGID[$cid]} for character (".
                $this->getEncodedCharactersFromGlyphID($CIDtoGID[$cid]).")\n";
            I2CE::raiseError($warning, E_NOTICE);
        } else {
            $CIDtoGID[$cid] = $gid;
        }
    }
        
        
    /**
     * $param int $platform_id is the Platform ID.  Default is null which means we dont care.
     * The platform ID follows this table
     * <table> <tr><td>Platform ID</td><td>  Platform  </td><td>Specific encoding</td></tr>
     * <tr><td>0</td><td> Unicode </td><td>Indicates Unicode version.</td></tr>
     * <tr><td>1</td><td> Macintosh </td><td>Script Manager code.</td></tr>
     * <tr><td>3</td><td> Microsoft</td><td> Microsoft encoding.</td></tr></table>
     * @param int $platform_specific_id. 
     * The platform specific id's identify languages used on a platform.
     * Their values are outline in:http://developer.apple.com/textfonts/TTRefMan/RM06/Chap6name.html#ID.
     * Set to null(default) if you dont care which one
     */
    public function  getCIDtoGIDmap($platform_id = null, $platform_specific_id = null) { 
        $CIDtoGID = array();
        $f = $this->gotoTable('cmap');
        /* read in the cmap: see http://developer.apple.com/textfonts/TTRefMan/RM06/Chap6cmap.html
         * cmap begins with:
         * Type Name Description
         * UInt16 version Version number (Set to zero)
         * UInt16 numberSubtables Number of encoding subtables
         */
        $this->read_uint16(); //read the version. ignore 
        $num_sub_tables = $this->read_uint16();
        $encodings = array();
        for ($n = 0; $n < $num_sub_tables; $n++) { //read in the encoding subtables
            /*Encoding subtables
             *Type  Name  Description
             *UInt16 platformID Platform identifier
             *UInt16 platformSpecificID Platform-specific encoding identifier
             *UInt32 offset Offset of the mapping table
             *
             * Note: Microsoft strongly recommends type (3,1) which is the UGL character set
             * to have format 4. (UGL= Unified Glyph List)
             */
            $plat_id = $this->read_uint16();
            $plat_spec_id = $this->read_uint16();
            $offset = $this->read_uint32(); //offsets from the beginning of cmap
            $encodings[$n] = array( 'plat_id' => $plat_id,
                                    'plat_spec_id' => $plat_spec_id, 
                                    'offset'=> $offset + $this->directory['cmap']['offset']);
        }
        for ($n = 0; $n < $num_sub_tables; $n++) {
            if ((isset($platform_id)) &&  ($encodings[$n]['plat_id'] !== $platform_id)) {
                if (isset($platform_specific_id)) {
                    if ($encodings[$n]['plat_spec_id'] !== $platform_specific_id ) {
                        continue;
                    }
                } else {
                    //skip over non-requested platforms
                    continue;
                }
            }
            $try_unicode = false;
            if (($encodings[$n]['plat_id']==3) && ($encodings[$n]['plat_spec_id']==1)) {
                $try_unicode= true;
            }
            if (($encodings[$n]['plat_id']==0)) {
                $try_unicode= true;
            }
            fseek($f, $encodings[$n]['offset']); //go the beginning of the cmap
            //get the format. It is either 'S' or '16.16' which is a pain.
            $format = array();
            $format[1] = $this->read_uint16();
            $format[2] = $this->read_int16();
            switch ($format[1]) {
            case 12: //segmented coverage format was 'l'
                /*Type Name Description
                 *Fixed32 format Subtable format; set to 12.0
                 *UInt32 length Byte length of this subtable (including the header)
                 *UInt32 language 0 if don't care
                 *UInt32 nGroups Number of groupings which follow
                 */
                $length =$this->read_uint32();
                $language = $this->read_uint32();
                $num_groupings =  $this->read_uint32();
                /*Here follow the individual groups, each of which has the following format:
                 *Type Name Description
                 *UInt32 startCharCode First character code in this group
                 *UInt32 endCharCode Last character code in this group
                 *UInt32 startGlyphCode Glyph index corresponding to the starting character code
                 */
                $groups = array();
                for ($i = 0 ; $i < $num_groupings; $i++) {
                    $start_code = $this->read_uint32();
                    $end_code =$this->read_uint32();
                    $start_glyph_code =$this->read_uint32();
                    for ($j = $start_code; $j <= $end_code; $j++) {
                        $this->setCIDtoGID($CIDtoGID,
                                           $j,$start_glyph_code + $j,
                                           "Warning: Duplicated CID $j in CIDtoGID Map: format 12  table $s\n"
                            );
                    }
                }
                break; 
            case 10: //trimmed array format was 'l'
                /*Type Name Description
                 *Fixed32 format Subtable format; set to 10.0
                 *UInt32 length Byte length of this subtable (including the header)
                 *UInt32 language 0 if don't care
                 *UInt32 startCharCode First character code covered
                 *UInt32 numChars Number of character codes covered
                 *UInt16 glyphs[] Array of glyph indices for the character codes covered
                 */
                $length = $this->read_uint32();
                $language = $this->read_uint32();
                $startCharCode = $this->read_uint32();
                $numChars = $this->read_uint32();
                for ($i = 0; $i < $numChars; $i++) {
                    $this->setCIDtoGID($CIDtoGID,
                                       $i + $startCharCode,
                                       $this->read_uint16(),
                                       "Warning: Duplicated CID ". $i + $startCharCode . " in CIDtoGID Map: format 10  table $s\n"
                        );
                                        
                }
                break; 
            case 8: //mixed 16 and 32 format was 'l'
                //NOT DONE!!                            
                break;
            case 6: //16 bit/two byte format is 'S'
                /*Type Name Description
                 *UInt16 format Format number is set to 6
                 *UInt16 length Length in bytes
                 *UInt16 language Language code for this encoding subtable, or zero if language-independent
                 *UInt16 firstCode First character code of subrange
                 *UInt16 entryCount Number of character codes in subrange
                 *UInt16 glyphIndexArray[entryCount] Array of glyph index values for character codes in the range
                 */
                $length = $format[2];
                $language = $this->read_uint16();
                $firstCode = $this->read_uint16();
                $entryCount = $this->read_uint16();
                for ($i=0; $i < $entryCount; $i++) {
                    $this->setCIDtoGID($CIDtoGID,
                                       $i+$firstCode,
                                       $this->read_uint16(),
                                       "Warning: Duplicated GID " . $i + $firstCode . " in CIDtoGID Map:format 6  table $s\n"
                        );
                }
                break;
            case 4: //two byte encoding format is 'S'
                /*Table 10: Format 4
                 *Type Name Description  
                 *UInt16 format Format number is set to 4  
                 *UInt16 length Length of subtable in bytes  
                 *UInt16 language Language code for this encoding subtable, or zero if language-independent  
                 *UInt16 segCountX2 2 * segCount  
                 *UInt16 searchRange 2 * (2**FLOOR(log2(segCount)))  
                 *UInt16 entrySelector log2(searchRange/2)  
                 *UInt16 rangeShift (2 * segCount) - searchRange  
                 *UInt16 endCode[segCount] Ending character code for each segment, last = 0xFFFF. 
                 *UInt16 reservedPad This value should be zero 
                 *UInt16 startCode[segCount] Starting character code for each segment 
                 *UInt16 idDelta[segCount] Delta for all character codes in segment  
                 *UInt16 idRangeOffset[segCount] Offset in bytes to glyph indexArray, or 0  
                 *UInt16 glyphIndexArray[variable] Glyph index array  
                 */
                $length = $format[2];
                $this->read_uint16(); //ignore language
                $seg_count = (int)  ($this->read_uint16()/2);
                $this->read_uint16(); //ignore search range
                $this->read_uint16(); //ignore entry selector
                $this->read_uint16(); //ignore range shift
                $end_code = array();
                for ($s =0; $s < $seg_count; $s++) {
                    $end_code[$s] = $this->read_uint16();
                }
                $this->read_uint16(); //pad
                $start_code = array();
                for ($s =0; $s < $seg_count; $s++) {
                    $start_code[$s] = $this->read_uint16();
                }
                $idDelta = array();
                for ($s =0; $s < $seg_count; $s++) {
                    $idDelta[$s] = $this->read_uint16();
                }
                $idRangeOffset = array();
                $idRangeOffset_offset= ftell($f);
                for ($s =0; $s < $seg_count; $s++) {
                    $idRangeOffset[$s] = $this->read_uint16();
                } 
                for  ($s=0; $s<$seg_count; $s++) {
                    if (($idRangeOffset[$s] == 0) || ($idRangeOffset[$s] == 65536)) { // support the buggy FOG with its range=65535 for final segment
                        for ($cid = $start_code[$s]; $cid <= $end_code[$s]; $cid++) {
                            $this->setCIDtoGID($CIDtoGID,
                                               $cid,
                                               ($cid + $idDelta[$s]) % 65536,
                                               "Warning:  duplicated CID $cid in CIDtoGID map: format 4 -- 0  table $s\n"
                                );
                        }
                    } else {
                        //go to begininning of where we are supposed to read the glyph index array.
                        fseek($f,$idRangeOffset_offset + 2*$s + $idRangeOffset[$s]);
                        for ($cid = $start_code[$s]; $cid <= $end_code[$s]; $cid++) {
                            //and read in glyph indices
                            $gid =  $this->read_uint16();
                            $this->setCIDtoGID($CIDtoGID,
                                               $cid,
                                               $gid,
                                               "Warning:  duplicated CID $cid in CIDtoGID map: format 4 -- nonzezo  table $s\n",
                                               $try_unicode
                                );
                        }
                    }
                }
                break;
            case 2: //chinese/japanese/korean format is 'S'
                $length = $format[2];
                //NOT DONE!! 
                break;
            default: //format 0
                /*Type Name Description
                 *UInt16 format Set to 0
                 *UInt16 length Length in bytes of the subtable (set to 262 for format 0)
                 *UInt16 language Language code for this encoding subtable, or zero if language-independent
                 *UInt8 glyphIndexArray[256] An array that maps character codes to glyph index values
                 */
                $length = $format[2];
                $language = $this->read_uint16();
                for ($i = 0; $i < 256; $i++) {
                    $this->setCIDtoGID($CIDtoGID,
                                       $i,
                                       $this->read_uint8(),
                                       "Warning: duplicated CID $i in CIDtoGID map when reading format 0\n"
                        );
                }
                break;
            }
                        
        }
        ksort($CIDtoGID);
        return $CIDtoGID;
    }


    public function generate_PDF_CMAP($platform_id = null, $platform_specific_id = null) {  
        $CIDtoGID = $this->getCIDtoGIDMap($platform_id.$platform_specific_id);
        $cmap = str_pad('', 256*256*2, "\x00");
    }


    /*
     * Old format is used by mac and windows.
     * New format is used by mac
     */
    public function read_kern($old_format = true) { 
        $f = $this->gotoTable('kern');
        if ($old_format) {
            $nTables = $this->read_uint16();
        } else {
            $nTables = $this->read_uint32();
        }
        $table_offset = ftell($f);;
        for ($n = 0; $n < $nTables; $n++) {
            /* Kerner table header:
             *Type  Name  Description
             *uint32 length The length of this subtable in bytes, including this header.
             *uint16 coverage Circumstances under which this table is used. See below for description.
             *uint16 tupleIndex The tuple index (used for variations fonts). This value specifies which tuple this subtable covers
             */
            $length = $this->read_uint32();
            $coverage = $this->read_uint16();
            fread($f,2); //ignore the tupleIndex -- at least for now
            /*Table 27: coverage
             *Mask value Name Description
             *0x8000 kernVertical Set if table has vertical kerning values.
             *0x4000 kernCrossStream Set if table has cross-stream kerning values.
             *0x2000 kernVariation Set if table has variation kerning values.
             *0x1F00 kernUnusedBits Set to 0.
             *0x00FF kernFormatMask Set the format of this subtable (0-3 currently defined).
             */
            if (($coverage & 0xff00) == 0) { //we do only normal horizontal kerning for now, format 0
                $this->setDirection('H');
                /* 'kern' format 0
                 *Type Name Description
                 *uint16 nPairs The number of kerning pairs in this subtable.
                 *uint16 searchRange The largest power of two less than or equal to the value of nPairs, 
                 *       multiplied by the size in bytes of an entry in the subtable.
                 *uint16 entrySelector This is calculated as log2 of the largest power of two less than or
                 *       equal to the value of nPairs. This value indicates how many iterations of the search 
                 *       loop have to be made. For example, in a list of eight items, there would be three 
                 *       iterations of the loop.
                 *uint16 rangeShift The value of nPairs minus the largest power of two less than or equal to 
                 *       nPairs. This is multiplied by the size in bytes of an entry in the table.
                 */
                $nPairs = $this->read_uint16();
                fread($f,2+2+2); //ignore range shift stuff
                for ($i = 0; $i < $nPairs; $i++) {
                    /*Type  Name  Description
                     *uint16 left The glyph index for the lefthand glyph in the kerning pair.
                     *uint16 right The glyph index for the righthand glyph in the kerning pair.
                     *sint16 value The kerning value in FUnits for the left and right pair in 
                     *       FUnits. If this value is greater than zero, the glyphs are moved apart. 
                     *   If this value is less than zero, the glyphs are moved together.
                     */
                    $left =$this->read_uint16();
                    $right =$this->read_uint16();
                    $value =$this->read_uint16();
                    $leftc = $this->getEncodedCharactersFromGlyphID($left);
                    $rightc = $this->getEncodedCharactersFromGlyphID($right);
                    $this->setKerningByPair($leftc,$rightc,$value);
                }
            }
            $table_offset +=$length;
            fseek($f,$table_offset);
        }               
    }

    public function getUnicodeCodepointsFromGlyphID($gid) {
        return $this->GIDtoCPs[$gid];
    }

    public function getEncodedCharactersFromGlyphID($gid) {
        $cps = $this->GIDtoCPs[$gid];
        $out = mb_convert_encoding('',$this->getEncoding()->getEncodingType(), 'ASCII');
        if (is_array($cps)) {
            foreach ($cps as $cp) {
                if ($cp == -1) {
                    $cp = 0xFFFD;
                }
                $out .=  mb_convert_encoding(I2CE_UTF8::cp_to_code($cp), $this->getEncoding()->getEncodingType(), 'UTF-8');
            }
        }
        return $out;
    }



        
    protected function read_numGlyphs() { 
        $f = $this->gotoTable('maxp'); 
        fread($f,4); 
        $this->num_glyphs = $this->read_uint16(); 
    }


    /**
     * @var fix  Some common incorrect glyph names 
     */
    protected static $fix=array(
        'Edot'=>'Edotaccent','edot'=>'edotaccent','Idot'=>'Idotaccent','Zdot'=>'Zdotaccent','zdot'=>'zdotaccent',
        'Odblacute'=>'Ohungarumlaut','odblacute'=>'ohungarumlaut','Udblacute'=>'Uhungarumlaut','udblacute'=>'uhungarumlaut',
        'Gcedilla'=>'Gcommaaccent','gcedilla'=>'gcommaaccent','Kcedilla'=>'Kcommaaccent','kcedilla'=>'kcommaaccent',
        'Lcedilla'=>'Lcommaaccent','lcedilla'=>'lcommaaccent','Ncedilla'=>'Ncommaaccent','ncedilla'=>'ncommaaccent',
        'Rcedilla'=>'Rcommaaccent','rcedilla'=>'rcommaaccent','Scedilla'=>'Scommaaccent','scedilla'=>'scommaaccent',
        'Tcedilla'=>'Tcommaaccent','tcedilla'=>'tcommaaccent','Dslash'=>'Dcroat','dslash'=>'dcroat','Dmacron'=>'Dcroat',
        'dmacron'=>'dcroat','combininggraveaccent'=>'gravecomb','combininghookabove'=>'hookabovecomb',
        'combiningtildeaccent'=>'tildecomb','combiningacuteaccent'=>'acutecomb','combiningdotbelow'=>'dotbelowcomb',
        'dongsign'=>'dong',
        'shindot'=>'shindothebrew',
        'sindot'=>'sindothebrew',
        'sofpasuq'=> 'sofpasuqhebrew',
        'upperdot'=>'upperdothebrew',
        'meteg'=>'siluqhebrew',
        'maqaf'=>'maqafhebrew',
        'paseq'=>'paseqhebrew',
        'doublevav'=>'vavvavhebrew',
        'vavyod'=>'vavyodhebrew',
        'doubleyod'=>'yodyodhebrew',
        'geresh'=>'gereshhebrew',
        'gershayim'=>'gershayimhebrew',
        'afii52400'=>'afii57410',
        'afii57461'=>'decimalseparatorarabic',
        'afii57470'=>'heharabic',
        'afii57447'=>'afii57470',
        'afii62840'=>'zeropersian',
        'afii62841'=>'onepersian',
        'afii62842'=>'twopersian',
        'afii62843'=>'threepersian',
        'afii62844'=>'fourpersian', 
        'afii62845'=>'fivepersian', 
        'afii62846'=>'sixpersian',
        'afii62847'=>'sevenpersian',
        'afii62848'=>'eightpersian',
        'afii62849'=>'ninepersian',
        'lefttorightmark'=>'afii299',
        'righttoleftmark'=>'afii300',
        'radicalex'=>'overline',
        'undercommaaccent'=>'cedilla',
        'reshdagesh'=>'reshdageshhebrew',
        'betrafe'=>'betrafehebrew',
        'kafrafe'=>'kafrafehebrew',
        'perafe'=>'perafehebrew',
        'aleflamed'=>'aleflamedhebrew',
        'afii62958'=>'pehfinalarabic',
        'afii62956'=>'pehinitialarabic',
        'altayin'=>'ayinaltonehebrew',
        'alefpatah'=>'alefpatahhebrew',
        'alefqamats'=>'alefqamatshebrew',
        'afii52957'=>'pehmedialarabic',
        'afii62961'=> 'tchehfinalarabic',
        'afii62959'=>'tchehinitialarabic',
        'afii62960'=>'tchehmedialarabic',
        'afii6296'=>'jehfinalarabic'
        );

    function read_post() {
        $f = $this->gotoTable('post');
        /*Table 72: 'post' table
         *Type Name Description
         *Fixed format Format of this table
         *Fixed italicAngle Italic angle in degrees
         *FWord underlinePosition Underline position
         *FWord underlineThickness Underline thickness
         *uint32 isFixedPitch Font is monospaced; set to 1 if the font is monospaced and 0 otherwise 
         *       (N.B., to maintain compatibility with older versions of the TrueType spec, accept 
         *        any non-zero value as meaning that the font is monospaced)
         *uint32 minMemType42 Minimum memory usage when a TrueType font is downloaded as a Type 42 font
         *uint32 maxMemType42 Maximum memory usage when a TrueType font is downloaded as a Type 42 font
         *uint32 minMemType1 Minimum memory usage when a TrueType font is downloaded as a Type 1 font
         *uint32 maxMemType1 Maximum memory usage when a TrueType font is downloaded as a Type 1 font
         */
        $format_pieces = $this->read_fixed_pieces();
        $this->setDirection('-1');
        $this->setFontCharacteristic('ItalicAngle',$this->read_fixed_pieces()); //FIXME
        $this->setFontCharacteristic('UnderlinePosition',$this->read_FWord());
        $this->setFontCharacteristic('UnderlineThickness',$this->read_FWord());
        $this->setFixedWidth($this->read_uint32() >0 );
        $this->read_uint32();
        $this->read_uint32();
        $this->read_uint32();
        $this->read_uint32();
        switch ($format_pieces[2]) {
        case 1:
            //258 GIDs in the standard mac ordering
            die ("UNFINISHED BUSINESS\n");
            break;
        case 2:
            switch ($format_pieces[1]) {
            case 0: //format is 2.0
                //this is the useful one.
                /* Format 2 is used for fonts that contain some glyphs not in the standard set or whose glyph 
                 *ordering is non-standard. The glyph name index array in this subtable maps the glyphs in this 
                 *font to a name index. If the name index is between 0 and 257, treat the name index as a glyph 
                 *index in the Macintosh standard order. If the name index is between 258 and 32767, then subtract 
                 *258 and use that to index into the list of Pascal strings at the end of the table. In this manner 
                 *a font may map some of its glyphs to the standard glyph names, and some to its own names
                 */
                /*Table 73: 'post' format 2
                 *Type Name Description
                 *uint16 numberOfGlyphs number of glyphs
                 *uint16 glyphNameIndex[numberOfGlyphs] Ordinal number of this glyph in 'post' string tables. This is not an offset.
                 *Pascal string names[numberNewGlyphs] glyph names with length bytes [variable] (a Pascal string)
                 */
                $num_glyphs = $this->read_uint16();
                if ($num_glyphs != $this->num_glyphs) {
                    die ("Glyph number mismatch!\n");
                }
                $glyphNameIndex = array();
                $numberNewGlyphs = 0;
                for ($gid =0; $gid < $num_glyphs; $gid++) {
                    $nameIndex = $this->read_uint16();
                    $glyphNameIndex[$gid] = $nameIndex;
                    if (258 < $nameIndex )  {  
                        $numberNewGlyphs++;
                    }
                }
                $name = array();
                $mac_ordering_file =  I2CE::getFileSearch()->search('PDF_CORE','mac-ordering');
                if (!$mac_ordering_file) {
                    die ("Cannont find the file <mac-ordering>\n");
                }
                $a = $this->loadin($mac_ordering_file);
                $n =0;
                foreach ($a as  $l) {
                    $l = rtrim($l);
                    $e = explode(" ", $l);
                    $names[ (int) $e[0]] = $e[1];
                    $n++;
                }
                if ($n != 258) {
                    die ("Invalid number of encodings in {$mac_ordering_file}");
                }
                for ($n =258; $n <= 258 + $numberNewGlyphs; $n++) {
                    //read in pascal strings.  these are strings that are prefixed with a
                    //byte value which is their length;
                    $str_len = $this->read_uint8();
                    $names[$n] = fread($f,$str_len); //READ PASCAL STRING
                } 
                //read in the file containing postisctip name /codepoint mapping
                $glyphToCP =  array();
                $postscript_names_file = 
                    I2CE::getFileSearch()->search('PDF_CORE','glyphlist.txt');
                if (!$postscript_names_file) {
                    die ("Cannont find the file <glyphlist.txt>\n");
                }
                $a = $this->loadin($postscript_names_file);
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
                    $glyphToCP[$e[0]] = $cps;
                }
                //fix some incorrect glyphnames:
                foreach ($names as $idx=>$name) {
                    if (isset(self::$fix[$name] ))  {
                        $names[$idx] = self::$fix[$name];
                    }
                }
                //now convert glyphames to codepoints
                $this->GIDtoCPs =array();
                for ($gid = 0; $gid < $num_glyphs; $gid++) {
                    $cps =  $glyphToCP[ $names[$glyphNameIndex[$gid]]];
                    if ($cps ===null) {
                        $name = $names[$glyphNameIndex[$gid]];
                        //Check to see if name starts with 'uni' followed by a 4 digit  hexadicimal 'uniHHHH' 
                        //pull out the hexadecimal part
                        if (($name == '.notdef')) {
                            $cps = array (0xFFFD);
                        } else  if (preg_match('/^uni([0-9A-Fa-f]{4,4})/',$name,$matches)) {
                            $cps = array(hexdec($matches[1]));
                        } else if (preg_match('/^([0-9A-Fa-f]{4,4})$/',$name,$matches)) {
                            $cps = array(hexdec($matches[1]));
                        } else {
                            //mark it as unknown.  we may try to fix it in setCIDtoGIDmap
                            I2CE::raiseError(
                                "Warning: Unknown unicode codepoint for glyphname <$name>",
                                E_NOTICE
                                );
                            $cps = array(-1);
                        }
                    }
                    $this->GIDtoCPs[$gid] = $cps;
                }
                break;
            default: //format is 2.5
                break;
            }
            break;
        case 3:
            //useless
            break;
        case 4:
            //japanese/korean/chinese
            break;
        }
                
    }


    protected function loadin($file){
        if (!file_exists( $file)) { 
            die ("Cannot open {$file}");
        };
        $a = file ($file); 
        if (empty($a)) {
            die ("File {$file} was empty");
        }
        return $a;
    }


    public function showCharacterInfo() {
        $this->setDirection('H');
        $out = "";
        foreach ($this->getCharacterWidths() as $ch=>$w) {
            $out .= "Character ($ch) has width ($w) and lsb (" .
                $this->getCharacterInfo($ch,'LeftSideBearing') . ")\n";
            $out .= "\tBounding Box " .$this->getCharacterInfo($ch,'llx') . " " 
                .$this->getCharacterInfo($ch,'lly') . " " 
                .$this->getCharacterInfo($ch,'urx') . " " 
                .$this->getCharacterInfo($ch,'ury') . "\n";
        }
        return $out;
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
