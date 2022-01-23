<?php

/**
 * class that will handle trying to figure out mime types in various ways.
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
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
 * @version 0.1
 * @access public
 */


class I2CE_MimeTypes {
    /**
     * protected static @var finfo $finfo.
     * See http://us2.php.net/fileinfo
     */
    protected static $finfo = null;

    /**
     * Tries to determine the file type magically
     * @param string $data
     * @returns a string,   the mime type, or null if none was found.
     */
    public  static function magicMimeType($data) {
        if (self::$finfo == null) {
            if (!class_exists('finfo')) {
                I2CE::raiseError('Magic file utilties not enabled.  Please run \'pecl install Fileinfo\' or some such thing.');
                return null;
            }
            $config = I2CE::getConfig();
            $magic_file = null;
            if ($config->setIfIsSet($magic_file,"/modules/MimeTypes/magic_file")) {
                $magic_file = I2CE::getFileSearch()->search('MIME',$magic_file);
                if (!$magic_file) {
                    $magic_file == null;
                }
            }
            //I2CE::raiseError("Using $magic_file");
            //@self::$finfo = new finfo(FILEINFO_MIME, $magic_file);
            @self::$finfo = new finfo(FILEINFO_MIME);
            if (!self::$finfo) {
                I2CE::raiseError('Unable to load magic file database ' . $magic_file, E_USER_NOTICE);
                return null;
            }
        }
        if (!($mime_type = self::$finfo->buffer($data))) {
            I2CE::raiseError('Unable to determine mime type magically', E_USER_NOTICE);
            //some error occured
            return null;
        }
        return $mime_type;
    }


    
    /**
     *protected static @var array $extToMimeTypes an array with keys file extensions
     * and values mime types
     */
    protected static $extToMimeTypes;
        



    /**
     * Loads in the file containing mime types and extensions
     */
    protected static  function loadMimeTypes() { 
        self::$extToMimeTypes = array();
        $mime_file = null;
        if (I2CE::getConfig()->setIfIsSet($mime_file,"/modules/MimeTypes/mime_types")) {
            $mime_file = I2CE::getFileSearch()->search('MIME',$mime_file);
        }
        if (empty($mime_file)) {
            I2CE::raiseError('Unable to find mime.types file.',E_USER_WARNING);
            return;
        }
        $a = file($mime_file);
        if (empty($a)) {
            I2CE::raiseError('mime.types file is empty.',E_USER_WARNING);
            return;
        }
        foreach ($a as $l) {
            $l = trim($l);
            if (strlen($l) < 1 || $l[0] == '#') {
                //skip comments
                continue;
            }
            $pieces = preg_split("/\s+/",$l, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($pieces)) {
                //a blank line
                continue;
            }
            $mime = strtolower(array_shift($pieces));
            foreach ($pieces as $ext) {
                self::$extToMimeTypes[strtolower($ext)] = $mime;
            }
        }
                
    }

        
    /**
     * Given a file extension,  it determine the mime-type
     * @param string $ext.  Either a file name or extension
     * @returns string The mime-type, or null if not found.
     */
    public  static function extToMimeType($ext) {
        if (!self::$extToMimeTypes) {
            self::loadMimeTypes();
        }
        $tmp_ext = strrchr($ext, ".");
        if ($tmp_ext) {
            $ext = substr($tmp_ext, 1);
        } 
        $ext = strtolower($ext);
        return  self::$extToMimeTypes[$ext];
    }


    /**
     * Get a file extension for the associated mime type
     * @param string $mime_type
     * @returns mixed. String, the extension, on success, false on failure
     */
    public static function mimeTypeToExt($mime_type) {
        if (!self::$extToMimeTypes) {
            self::loadMimeTypes();
        }
        $mime_type = strtolower($mime_type);
        return array_search($mime_type, self::$extToMimeTypes);
    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
