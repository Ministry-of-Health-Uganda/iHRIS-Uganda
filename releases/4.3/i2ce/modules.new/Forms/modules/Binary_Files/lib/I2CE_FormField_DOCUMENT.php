<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
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
    * @package I2CE
    * @author Carl Leitner <litlfred@ibiblio.org>
    * @since v2.0.0
    * @version v2.0.0
    */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_DOCUMENT extends I2CE_FormField_BINARY_FILE {
    protected static $valid_types  = array(
        'application/pdf',                            
        'application/postscript',                            
        'application/msword',
        'application/vnd.ms-excel',
        'application/x-abiword',
        'application/x-kword',
        'application/vnd.lotus-wordpro',
        'application/vnd.wordperfect',
        'application/vnd.wordperfect5.1',
        'application/vnd.truedoc',
        'application/docbook+xml',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', //Excel 2007
        'application/vnd.ms-powerpoint',
        'application/xhtml+xml',
        'application/xml',
        'application/zip',
        'text/plain',
        'text/html',
        'text/richtext',
        'text/rtf'
        );
    /**
     * Checks to see if a mime type is a valid docuement mime type
     * @param string $mime_type
     * @returns true if valid.  false otherwise
     */
    public function isValidMimeType($mime_type) {        
        foreach (self::$valid_types as $type) {
            if (strpos($mime_type,$type)  !== false) {
                return true;
            }
        } 
        return false;
    }



    /**
     *get the default extension for this document
     *@returns string
     */
    protected function defaultExtension() {
        return 'pdf';
    }


    /**
     *get the default extension for this docuemnt
     *@returns string
     */
    protected function defaultMimeType() {
        return "application/pdf";
    }

    


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
