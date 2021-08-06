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
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_CSV
* 
* @access public
*/


class I2CE_FormStorage_CSV extends   I2CE_FormStorage_File_Base {

    /**
     * Get the search category
     * @param string $form
     * @returns string
     */
    protected function getSearchCategory($form) {
        return 'CSV';
    }



    /**
     * Reads a line of data from ta CSV filestring an array of readable and enabled datavalues from the CSV indexed by the field names, 'id' and 'parent'
     * @param resource $fp
     * @param string $form
     * @returns mixed array on success, false on failure
     */
    protected function getFormData($form,$line) {
        $storageOptions = $this->getStorageOptions($form);
        if (!$storageOptions instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Bad storage options");
            return false;
        }
        $delimiter =',';
        $storageOptions->setIfIsSet($delimiter,'delimiter');
        $enclosure = '"';
        $storageOptions->setIfIsSet($enclosure,'enclosure');
        $parent = true;
        $storageOptions->setIfIsSet($parent,'parent/enabled');
        $data = str_getcsv($line, $delimiter, $enclosure);
        if (!is_array($data)) {
            return false;
        }
        $ret = array();
        foreach ($this->field_indices[$form] as $field=>$index) {
            if (array_key_exists($index,$data)) {
                $ret[$field] = $data[$index];
            } else {
                $ret[$field] = null;
            }
        }
        if (!$parent || !array_key_exists('parent',$ret) || $ret['parent'] === null) {
            $ret['parent'] = '0';
        }
        return $ret;
    }



    /**
     * The column indices for the fields
     * @var protected array $field_indices.  Arrays indexed by form names of pairs $field=>$index
     */
    protected $field_indices = array();
    

    /**
     * Worker moethod to  the offset in the CSV file for the specified form id and store it into the {$locations} cache.
     * Reads through the file until the specified is found.
     * @param string $form
     */
    protected function ensureLocations($form) {
        if (array_key_exists($form,$this->locations)  && is_array($this->locations[$form])) {
            return ;
        }
        $this->locations[$form] = array();
        if ( count($contents = $this->getFileData($form,true)) == 0) {
            return;
        }
        //do the header stuff
        if (($data_offset = $this->ensureIndices($form,$contents[0])) === false) {
            return;
        }
        reset($contents);
        if ($data_offset == 1)  {
            next($contents);
        }
        $count = 1;
        while (key($contents) !== null) {
            $line = current($contents);
            if ( $id = $this->getLocationId($form,$line,$count)) {
                $this->locations[$form][$id] = $line;
                $count++;
            }
            next($contents);
        }
    }


    /**
     * Get the id associated to the given location data object
     * @param string $form
     * @param string $line
     * @param int count
     * @returns string.  '0' or null  on failure a string on success.
     */
    protected function getLocationId($form, $line, $count) {
        if (!array_key_exists('id',$this->field_indices[$form] ) || $this->field_indices[$form]['id'] === false) {
            return $count;
        }
        $storageOptions = $this->getStorageOptions($form);
        if (!$storageOptions instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Bad storage options");
            return false;
        }
        $delimiter =',';
        $storageOptions->setIfIsSet($delimiter,'delimiter');
        $enclosure = '"';
        $storageOptions->setIfIsSet($enclosure,'enclosure');
        $form_prepended = true;
        $storageOptions->setIfIsSet($form_prepended,'id/form_prepended');
        $data = str_getcsv($line, $delimiter, $enclosure);
        if (!array_key_exists($this->field_indices[$form]['id'], $data)) {
            return false;
        }
        $id = $data[$this->field_indices[$form]['id']];
        $pos = strpos($id,'|');
        if ($form_prepended) {
            if ($pos === false) {
                //bad id.
                return false; 
            } else {
                $id = substr($id,$pos+1);
            }
        }
        return $id;
        
    }


    /**
     * Ensures that the column indices for the fields are set.
     * @param string $form
     * @returns true on success
     */
    protected function ensureIndices($form,$header_line) {
        $storageOptions = $this->getStorageOptions($form);
        if (!$storageOptions instanceof I2CE_MagicDataNode) {
            $this->field_indices[$form] = false;
            return false;
        }
        $factory = I2CE_FormFactory::instance();
        $formObj = $factory->createContainer($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate $form");
            $this->field_indices[$form] = false;
            return false;
        }
        $use_header = true;
        $has_header = true;
        $delimiter =',';
        $enclosure = '"';
        $storageOptions->setIfIsSet($use_header,'use_header');
        $storageOptions->setIfIsSet($has_header,'has_header');
        $storageOptions->setIfIsSet($delimiter,'delimiter');
        $storageOptions->setIfIsSet($enclosure,'enclosure');
        $fields = array('parent'=>'parent','id'=>'id');
        foreach ($formObj->getFieldNames() as $field) {
            $fields[$field] = "fields/$field";
        }
        $formObj->cleanup();
        $indices = array();
        if ($use_header && $has_header) {
            $headers = str_getcsv($header_line, $delimiter, $enclosure);
            if (!is_array($headers)) {
                I2CE::raiseError("Could not get headers");
                $this->field_indices[$form] = false;
                return false;
            }
            foreach ($headers as &$header) {
                $header = strtolower(trim($header));
            }
            unset($header);
            foreach ($fields as $field=>$path) {
                $enabled = true;
                $storageOptions->setIfIsSet($enabled,$path . '/' . $enabled);
                if (!$enabled) {
                    continue;
                }
                $header = $field;
                $storageOptions->setIfIsSet($header,$path . '/' . $header);
                $index = array_search(strtolower(trim($header)), $headers);
                if ($index !== false) {
                    $indices[$field] = $index;
                }
            }
        } else {
            foreach ($fields as $field=>$path) {
                $enabled = true;
                if ($field != 'id' && $field != 'parent') {
                    $storageOptions->setIfIsSet($enabled,$path . '/' . $enabled);
                    if (!$enabled) {
                        continue;
                    }
                }
                $index = false;
                if ($storageOptions->setIfIsSet($index, $path . '/index')) {
                    $indices[$field] = $index;
                }
            }
        }
        $this->field_indices[$form] = $indices;
        if ($has_header) {
            return 1; 
        } else {
            return 0;
        }
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
