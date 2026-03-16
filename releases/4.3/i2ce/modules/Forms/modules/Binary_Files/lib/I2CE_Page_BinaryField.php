<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @subpackage i2ce
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class I2CE_Page_BinaryField
* 
* @access public
*/


class I2CE_Page_BinaryField extends I2CE_Page{


    /**
     * Handles creating hte I2CE_TemplateMeister templates and loading any default templates
     * @returns boolean true on success
     */
    protected function initializeTemplate() {
        //we don't want any tempaltes for this
        return true;
    }





    /**
     * Perform some command line options for the BinField page
     * @param array $args
     * @param array $request_remainder
     */
    public function actionCommandLine( $args, $request_remainder ) {
        $action = array_shift( $request_remainder );
        switch( $action ) {
            case "migrate" :
                $config = I2CE::getConfig()->traverse( '/modules/BinField/store' );
                $config_top = I2CE::getConfig();
                $mech = 'db';
                $config->setIfIsSet( $mech, 'mechanism' );
                if ( $mech != 'file' ) {
                    echo "This migration only works when you've set your binary file storage to file.\n";
                    return;
                }
                $migrated = 0;
                $config->setIfIsSet( $migrated, 'migrated_to_file' );
                if ( $migrated ) {
                    echo "The migration has already occurred.\n";
                    return;
                }
                $db = I2CE::PDO();
                try {
                    $get_field_info = $db->prepare( "SELECT ff.id as ff_id,ff.field as field_id,field.name as field,field.type AS type,ff.form as form_id,form.name as form FROM form_field ff JOIN field ON field.id = ff.field JOIN form ON form.id = ff.form WHERE ff.id = ?" );
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Unable to prepare statement for field info lookup: " );
                    die();
                }
                try {
                    $find_string_field = $db->prepare( "SELECT id from field WHERE name = ? AND type = 'string'" );
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Unable to prepare statement for new field lookup: " );
                    die();
                }
                try {
                    $update_ff = $db->prepare( "UPDATE form_field SET field = ? WHERE id = ?" );
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Unable to prepare statement for updating form field: " );
                    die();
                }
                $updates = array();
                try {
                    $updates['last_entry'] = $db->prepare( "UPDATE last_entry SET blob_value = null, string_value = ? WHERE record = ? AND form_field = ?" );
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Unable to prepare statement for updating last_entry" );
                    die();
                }
                try {
                    $updates['entry'] = $db->prepare( "UPDATE entry SET blob_value = null, string_value = ? WHERE record = ? AND form_field = ? AND UNIX_TIMESTAMP(date) = ?" );
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Unable to prepare statement for updating entry" );
                    die();
                }
                try {
                    $updates['config_alt'] = $db->prepare( "UPDATE config_alt SET value = ? WHERE parent = ? AND name = ?" );
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Unable to prepare statement for updating entry" );
                    die();
                }

                $db->beginTransaction();

                require_once('classDef/I2CE_FormField_STORE_BINARY_FILE.file.php');
                $tables = array( 'last_entry' =>
                        "SELECT *,UNIX_TIMESTAMP(date) AS unixtime FROM last_entry WHERE blob_value IS NOT NULL LIMIT 1",
                        'entry' =>
                        "SELECT *,UNIX_TIMESTAMP(date) AS unixtime FROM entry  WHERE blob_value IS NOT NULL LIMIT 1",
                        'config_alt' =>
                        "SELECT * , RIGHT( parent, INSTR( REVERSE( parent ) ,  '/' ) -1 ) AS field_name, LEFT( parent, LENGTH( parent ) - INSTR( REVERSE( parent ) ,  '/' ) ) AS ppath, LEFT( parent, LENGTH( parent ) - INSTR( REVERSE( parent ) ,  '/' ) -7 ) AS top, ( SELECT UNIX_TIMESTAMP( value ) FROM config_alt WHERE name =  'last_modified' AND parent = top) AS unixtime, ( SELECT value FROM config_alt WHERE name = field_name AND parent = ppath) AS blob_value, ( SELECT RIGHT( top, INSTR( REVERSE( top ) ,  '/' ) -1 )) AS record, SUBSTR( parent, 23, LOCATE(  '/', parent, 23 ) -23 ) AS form FROM  `config_alt` WHERE name =  '=A:binary' AND parent LIKE  '/I2CE/formsData/forms/%/fields/%' LIMIT 1"
                        );
                $processed_ffs = array();
                foreach ( $tables as $table => $query ) {

                    while ( true ) {
                        try {
                            $data = I2CE_PDO::getRow( $query );
                        } catch ( PDOException $e ) {
                            I2CE::pdoError( $e, "Unable to find blob values in DB." );
                            $db->rollback();
                            die();
                        }
                        if ( !$data ) {
                            break;
                        }
                        $form = "form";
                        $field = "field";
                        if ( $table == 'config_alt' ) {
                            $data->blob_value = base64_decode( $data->blob_value );
                            $form = $data->form;
                            $field = $data->field_name;
                        }
                        list( $meta, $value ) = self::processBlob( $data->blob_value );
                        if ( $table != 'config_alt' ) {
                            if ( sizeof( $meta ) == 0 ) {
                                // Just do nothing with this one. It doesn't appear to be a BINARY FILE
                                continue;
                            }
                            try {
                                $get_field_info->execute( array( $data->form_field ) );
                                $field_info = $get_field_info->fetch();
                                $get_field_info->closeCursor();
                                if ( !$field_info ) {
                                    echo "Unable to get and process field info!";
                                    $db->rollback();
                                    die();
                                }
                            } catch ( PDOException $e ) {
                                I2CE::pdoError( $e, "Unable to get details: " );
                                    $db->rollback();
                                    die();
                            }
                            if ( !array_key_exists( $data->form_field, $processed_ffs ) ) {
                                if ( $field_info->type == 'blob' ) {
                                    try {
                                        $find_string_field->execute( array( $field_info->field ) );
                                        $existing_field = $find_string_field->fetch();
                                        $find_string_field->closeCursor();
                                        if ( !$existing_field ) {
                                            try {
                                                $add_res = I2CE_PDO::execParam( "INSERT INTO field ( name, type ) VALUES ( ?, ? )", array( $field_info->field, 'string' ) );
                                                $existing_field = $db->lastInsertId();
                                            } catch ( PDOException $e ) {
                                                I2CE::pdoError( $e, "Unable to add new field to entry storage: " );
                                                $db->rollback();
                                                die();
                                            }
                                        }
                                    } catch ( PDOException $e ) {
                                        I2CE::pdoError( $e, "Unable to lookup existing field: " );
                                        $db->rollback();
                                        die();
                                    }
                                    echo "Got new field $existing_field to use\n";
                                    try {
                                        $update_res = $update_ff->execute( array( $existing_field, $data->form_field ) );
                                    } catch ( PDOException $e ) {
                                        I2CE::pdoError( $e, "Unable to update form field to new string field: " );
                                        $db->rollback();
                                        die();
                                    }
                                    echo 'Updated ' . $data->form_field . ' to use ' . $existing_field . "\n";
                                }
                                $processed_ffs[$data->form_field] = true;
                            }
                            $form = $field_info->form;
                            $field = $field_info->field;
                        }
                        if ( !array_key_exists( 'fmod-time', $meta ) ) {
                            $meta['fmod-time'] = $data->unixtime;
                        }
                        if ( !array_key_exists( 'file-name', $meta ) ) {
                            $meta['file-name'] = 'unknown';
                        }
                        $file = I2CE_FormField_STORE_BINARY_FILE::setupStorageFile( $data->record, $form, $field, $meta['fmod-time'], $meta['file-name'] );
                        echo "File will be $file\n";
                        $nH = fopen( $file, 'w' );
                        if ( !$nH ) {
                            $db->rollback();
                            die( "Unable to open $file" );
                        }
                        fwrite( $nH, $value );
                        fclose( $nH );
                        $meta_string = '';
                        foreach( $meta as $key => $val ) {
                            $meta_string .= "$key<$val>";
                        }
                        if ( $table == 'entry' ) {
                            try {
                            $update_res = $updates['entry']->execute( array( $meta_string, $data->record, $data->form_field, $data->unixtime ) );
                            } catch ( PDOException $e ) {
                                I2CE::pdoError( $e, "Unable to update blob to string value: " );
                                $db->rollback();
                                die();
                            }
                        } elseif ( $table == 'last_entry' ) {
                            try {
                                $update_res = $updates['last_entry']->execute( array( $meta_string, $data->record, $data->form_field ) );
                            } catch ( PDOException $e ) {
                                I2CE::pdoError( $e, "Unable to update blob to string value: " );
                                $db->rollback();
                                die();
                            }
                            echo "Updating same row in entry table.\n";
                            try {
                                $update_res = $updates['entry']->execute( array( $meta_string, $data->record, $data->form_field, $data->unixtime ) );
                            } catch ( PDOException $e ) {
                                I2CE::pdoError( $e, "Unable to update blob to string value: " );
                                $db->rollback();
                                die();
                            }
                        } elseif ( $table == 'config_alt' ) {
                            try {
                                $update_res = $updates['config_alt']->execute( array( $meta_string, $data->ppath, $field ) );
                            } catch ( PDOException $e ) {
                                I2CE::pdoError( $e, "Unable to update blob to string value: " );
                                $db->rollback();
                                die();
                            }
                            $md_field = $config_top->traverse( $data->parent, false, false );
                            if (! $md_field instanceof I2CE_MagicDataNode ) {
                                I2CE::raiseError( "Can't update MD attributes for " . $data->ppath );
                                $db->rollback();
                                die();
                            }
                            $md_field->removeAttribute("binary");
                            $md_field->removeAttribute("encoding");
                        }
                    }
                }
                $db->commit();
                $config->migrated_to_file = 1;
                $path = '/var/lib/iHRIS/file_storage';
                $config->setIfIsSet( $path, 'path' );
                echo "Migration has finished.  You MUST make sure the file storage path ($path) is\nreadable and writeable by www-data (or your web server)!\n";
                break;
            default :
                I2CE::raiseError( "Unknown command line page for bin field: $action\n" );
                break;
        }
    }

    public static function processBlob( $value ) {
        $meta = array();
        while( strlen( $value ) >= 10 && $value[9] == '<' ) {
            $key = substr( $value, 0, 9 );
            if ( ($pos = strpos($value,'>') )=== false ) {
                break;
            }
            $meta[$key] = substr( $value, 10, $pos-10 );
            $value =  substr( $value, $pos+1 );
        }
        return array( $meta, $value );
    }


    /*
     * Handles GET requests for a binary field
     * The relevant get variables are:
     *<ul><li> cat -- the I2CE_FileSearch category we should be looking for</li>
     *    <li> name -- the filename we are looking for</li>
     *</ul>
     *As an alternative (mainly b/c libxml's xmlSetProp (which is used by PHP's DOM) which
     * will automatically escape &)  you can set the variable encoded=ENC_BLAH   where ENC_BLAH is
     * an urlencoded string  with the cat and name variables set e.g urlencode("cat=SCRIPTS&name=somescript.js")
     */
    public function displayWeb($supress_output = false) {
        if ( $_SERVER['REQUEST_METHOD'] != "GET" && $_SERVER['REQUEST_METHOD'] != "HEAD" || !$this->request_exists('formid') || !$this->request_exists('field') ){
            //do nothing if it is not a GET request
            exit();
        }        
        $field = $this->request('field');
        $formid = $this->request('formid');
        if ($this->request_exists('tmp_key')) {
            if (! ($formObj = I2CE_FormFactory::instance()->createContainer($formid)) instanceof I2CE_Form) {
                exit();
            }
            if (!$field ||  ! ($fieldObj=$formObj->getField($field)) instanceof I2CE_FormField_BINARY_FILE) {
                exit();
            }
            $fieldObj->setTempKey($this->request('tmp_key'));
            //$fieldObj->setFromTemporaryTable();
            $fieldObj->setFromTemporaryLocation();
        } else {
            if (!$this->request_exists('formid')) {
                exit();
            }

            if (! ($formObj = I2CE_FormFactory::instance()->createContainer($formid)) instanceof I2CE_Form) {
                exit();
            }
            if (!$field ||  ! ($fieldObj=$formObj->getField($field)) instanceof I2CE_FormField_BINARY_FILE) {
                exit();
            }
            $formObj->populate();
        }
        //we are good to go  dump and die.        
        $cacheTime = 60;  // defaults to 1 minute
        if (I2CE::getConfig()->setIfIsSet($cacheTime,'/modules/BinField/cache_time')) {
            $cacheTime = $cacheTime * 60;
        }
        $mod_time = $fieldObj->getModTime();
        if (!$mod_time) { $mod_time = time();}
        I2CE_Dumper::prepForDump(
            $mod_time,
            $fieldObj->getContentLength(),
            $fieldObj->getHeaders(),
            $cacheTime);
        echo $fieldObj->getValue();
        exit();
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
