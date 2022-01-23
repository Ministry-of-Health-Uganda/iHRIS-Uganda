<?php
/**
 * @copyright © 2014 Intrahealth International, Inc.
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
    * @author Luke Duncan <lduncan@intrahealth.org>
    * @since v4.1.10
    * @version v4.1.10
    */
/**
 * Class for defining the binary file field used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
abstract class I2CE_FormField_STORE_BINARY_FILE extends I2CE_FormField_DB_STRING {

    /**
     * @var protected string $tmp_key
     */
    protected $tmp_key;

    /**
     * @var string The temporary file where this file was uploaded
     */
    protected $temp_file;


    /**
     * Get the file name associated with this binary field.  
     * If none, it will generate one based on the form and field.
     * @return string
     */
    abstract public function getFileName();

    /**
     * Return the last modification time of this field or false if unknown.
     * @return mixed
     */
    abstract public function getModTime();

    /**
     * Set's the  key used for the temporary upload table
     * @praram string
     */
    public function setTempKey($key) {
        $this->tmp_key = $key;
    }

 

    /**
     * Store the data in a temporary location
     * This to call the specific storage used.
     */
    public function storeInTemporaryLocation() {
        $this->storeInTemporaryFile();
    }

    /**
     * Load the value from the temporary location.
     * This to call the specific storage used.
     */
    public function setFromTemporaryLocation() {
        $this->setFromTemporaryFile();
    }

    /**
     * Set the data from post for a file upload
     * @param string $file
     * @param intenger $size
     * @param string $name
     * @param string $type
     * @return boolean;
     */
    protected function setFromPostUpload( $file, $size, $name, $type ) {
        $this->temp_file = $file;
        if ( filesize( $this->temp_file ) != $size ) {
            // an error occurred somehow
            I2CE::raiseError( "Unable to read uploaded file " . $this->temp_file . "\n" . filesize($this->temp_file) . " != $size" );
            $data = null;
            return false;
        }
        $this->setFromData( '', $name, $type );
        return true;
    }
 

    /*
    protected function setValueFromTempFile() {
        // Do nothing to save memory from loading the value.
    }
    */

    public static function setupStorageFile( $record, $container, $field, 
            $mod_time, $file_name ) {
        $path = '';
        I2CE::getConfig()->setIfIsSet( $path, '/modules/BinField/store/path' );
        $base_dir = array();
        if ( $path != '' ) {
            $base_dir = explode( DIRECTORY_SEPARATOR, $path );
        } else {
            $base_dir = array( '', 'var', 'lib', 'iHRIS', 'file_storage' );
        }
        $base_dir[] = I2CE_PDO::details('dbname');
        if ( $container ) {
            $base_dir[] = $container;
        } else {
            $base_dir[] = "I2CE_anonymous";
        }
        $prefix = "";
        $base_dir[] = $field;
        $matches = array();
        if ( preg_match( '/(\d+)(\d{2,2})(\d{2,2})(\d{4,4})/', $mod_time, $matches ) ) {
            $base_dir[] = $matches[1];
            $base_dir[] = $matches[2];
            $base_dir[] = $matches[3];
            $prefix = $matches[4] . "-";
        }
        $prefix .= $record;
        $dir = implode( DIRECTORY_SEPARATOR, $base_dir );
        if ( !file_exists( $dir ) ) {
            mkdir( $dir, '0755', true );
        }
        if ( !is_dir( $dir ) ) {
            I2CE::raiseError( "Failed to create $dir!" );
            die("Failed to create directory for file storage!");
        }
        return $dir . DIRECTORY_SEPARATOR . $prefix . "-" 
            . preg_replace( "/[\/\;\|]/", "_", $file_name );
     }

    /**
     * Set up the storage location for this file and return the file name.
     * @return string
     */
    protected function getStorageFile() {
        $prefix = "0";
        $container = null;
        if ( $this->getContainer() instanceof I2CE_FieldContainer ) {
            $container = $this->getContainer()->getName();
            $prefix = $this->getContainer()->getId();
        }
        return self::setupStorageFile( $prefix, $container, $this->getName(), $this->getModTime(), $this->getFileName() );
    }

    /**
     * Get the value for this field based on the underlying file storage.
     */
    public function getValue() {
        if ( $this->temp_file ) {
            $bin_file = $this->temp_file;
        } else {
            $bin_file = $this->getStorageFile();
        }
        if ( !file_exists( $bin_file ) ) {
            return '';
        } else {
            return file_get_contents( $bin_file );
        }
    }


    /**
     * Gets the length of the conten
     * @returns int
     */
    public function getContentLength() {
        if ( $this->temp_file ) {
            $bin_file = $this->temp_file;
        } else {
            $bin_file = $this->getStorageFile();
        }
        if ( !file_exists( $bin_file ) ) {
            return 0;
        } else {
            return filesize( $bin_file );
        }
    }


    public function setFromDB( $value ) {
        // Don't need to do anything with this because the contents should
        // all be in a file.
        $this->value = 'from_file';
    }

    public function getDBValue() {
        return $this->getMetaValue();
    }

    public function save( $do_check, $user ) {
        $bin_file = $this->getStorageFile();
        if ( $this->temp_file && file_exists( $this->temp_file ) ) {
            //I2CE::raiseMessage("copying from ".$this->temp_file." to $bin_file");
            if ( !rename( $this->temp_file, $bin_file ) ) {
                I2CE::raiseError( "Failed to open $bin_file for binary files!" );
                return false;
            }
        } else {
            if ( strlen($this->value) > 0 && $this->value != 'from_file' ) {
                I2CE::raiseMessage("writing from value to $bin_file");
                $cH = fopen( $bin_file, 'w' );
                if ( !$cH ) {
                    I2CE::raiseError( "Failed to open $bin_file for binary files!" );
                    return false;
                }
                fwrite( $cH, $this->value );
                fclose( $cH );
            } else {
                I2CE::raiseMessage("No data so not creating: $bin_file");
            }
        }
        return parent::save( $do_check, $user );
    }

    /** 
     * Set from the temporary file
     */
    public function setFromTemporaryFile() {
        if (!$this->tmp_key) {
            return false;
        }
        $tmpDir = self::setupTemporaryDirectory();
        $tmpPrefix = $tmpDir . DIRECTORY_SEPARATOR . $this->tmp_key;
        $tmpFile = $tmpPrefix . ".data";
        $metaFile = $tmpPrefix . ".meta";
        $this->temp_file = $tmpFile;
        $this->setFromDB( file_get_contents( $metaFile ) );
    }   

    /**
     * Set up the return the temp directory to use for file uploads.
     * @return string
     */
    public static function setupTemporaryDirectory() {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "I2CE_temp_upload" . DIRECTORY_SEPARATOR . I2CE_PDO::details('dbname');
        if ( !file_exists( $tmpDir ) || ( !is_dir( $tmpDir ) && unlink( $tmpDir ) ) ) {
            mkdir( $tmpDir, '0755', true );
        }
        if ( file_exists( $tmpDir ) && is_dir( $tmpDir ) ) {
            return $tmpDir;
        } else {
            I2CE::raiseError( "Unable to create temporary directory for file uploads!" );
            return sys_get_temp_dir();
        }
    }

    /**
     * Save this value in a temporary file
     * @return boolean
     */
    public function storeInTemporaryFile() {
        $tmpDir = self::setupTemporaryDirectory();
        $tmpPrefix = $tmpDir . DIRECTORY_SEPARATOR . $this->tmp_key;
        $tmpFile = $tmpPrefix . ".data";
        $metaFile = $tmpPrefix . ".meta";
        $mH = fopen( $metaFile, 'w' );
        if ( !$mH ) {
            return false;
        }
        fwrite( $mH, $this->getMetaValue() );
        fclose( $mH );
        if ( $this->temp_file ) {
            $this->value = file_get_contents( $this->temp_file );
            //I2CE::raiseMessage("renaming file to $tmpFile");
            $oldTemp = $this->temp_file;
            $this->temp_file = $tmpFile;
            return rename( $oldTemp, $tmpFile );
        } else {
            $tH = fopen( $tmpFile, 'w' );
            if ( !$tH ) {
                return false;
            }
            fwrite( $tH, $this->value );
            fclose( $tH );
            return true;
        }
    }



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
