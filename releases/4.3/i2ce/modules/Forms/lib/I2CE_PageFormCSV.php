<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage forms
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1.6
* @since v4.1.6
* @filesource 
*/ 
/** 
* Class I2CE_PageFormCSV
* 
* @access public
*/


abstract class I2CE_PageFormCSV extends I2CE_PageFormBase {

    /**
     * @var array The list of any CSV files uploaded to this page.
     */
    protected $files;

    /**
     * @var array The current row for the given upload key
     */
    protected $current;

    /**
     * @var boolean Set if the CSV file is invalid
     */
    protected $invalid;

    /**
     * @var array A cached of looked up values for a form.
     */
    protected $cache;

    /**
     * Create a new instance of a form page.
     * 
     * This will call the constructor for all Page objects and then set up some additional
     * member variables for forms.
     * @param string $title The title for this page.
     * @param string $defaultHTMLFile The default HTML file for this page.
     * @param mixed $access The role required to access this page.
     * @param array $files The list of template files to load for this page.
     */
    public function __construct( $args,$request_remainder, $get = null,$post = null) {
        parent::__construct( $args,$request_remainder,$get,$post);
        $this->editing = false;
        $this->factory = I2CE_FormFactory::instance();
        $this->invalid = false;
        $this->files = array();
        $this->current = array();
        $this->cache = array();
        // Default to not using a confirm page for this type of page.
        // If an arg exists, it will be set from the parent class
        if (!array_key_exists('confirm', $args)) {
            $this->usesConfirmPage = false;
        }
    }

    /**
     * Set the I2CE_Form object in the page template.
     * 
     * Since this is from a file upload the form doesn't need to be set on the template, but 
     * this can be overridden if necessary.
     */
    protected function setForm() {
    }


    /**
     * Create and load any necessary objects for this form.
     * 
     * Since this is from a file upload the form doesn't need to be set on the template, but 
     * this can be overridden if necessary.
     * @return boolean
     */
    protected function loadObjects() {
        foreach ( $_FILES as $key => $data ) {
            if ( ( array_key_exists( 'error', $data ) && $data['error'] > 0 ) 
                    || ($upload = fopen( $data['tmp_name'], "r" )) === false
              || filesize( $data['tmp_name'] ) != $data['size'] ) {
                $this->userMessage("Could not upload " . $data['name'] );
                return false;
            }
            $this->files[$key] = array();
            $this->files[$key]['file'] = $upload;
            if ( $this->post_exists( array( 'has_header', $key ) ) ) {
                $this->files[$key]['header'] = $this->post( array( 'has_header', $key ) );
            } else {
                $this->files[$key]['header'] = false;
            }
        }
        return true;
    }

    /**
     * Process the header row if it exists for the given key.
     * @param string $key
     * @return boolean;
     */
    protected function processHeaderRow( $key ) {
        if ( !array_key_exists( $key, $this->files ) ) {
            return false;
        }
        $this->current[$key] = array();
        if ( !$this->files[$key]['header'] ) {
            return true;
        }
        fseek( $this->files[$key]['file'], 0 );
        $this->current[$key]['header'] = fgetcsv( $this->files[$key]['file'] );
        $this->current[$key]['row'] = false;
        return true;
    }

    /**
     * Process the next row for the given key
     * @param string $key
     * @return boolean
     */
    protected function processRow( $key ) {
        if ( !array_key_exists( $key, $this->files ) ) {
            return false;
        }
        if ( ($row = fgetcsv( $this->files[$key]['file'] )) === false ) {
            return false;
        }
        while( !array_filter( $row ) ) {
            if ( ($row = fgetcsv( $this->files[$key]['file'] )) === false ) {
                return false;
            }
        }
        // If there are headers, then use that for keys, duplicate headers will cause problems of course.
        if ( $this->files[$key]['header'] ) {
            $this->current[$key]['row'] = array();
            foreach( $this->current[$key]['header'] as $idx => $header ) {
                $this->current[$key]['row'][$header] = $row[$idx];
            }
        } else {
            $this->current[$key]['row'] = $row;
        }
        return true;
    }

    /**
     * Validate the current row of the given key.
     * @param string $key
     * @return boolean
     */
    abstract protected function validateRow( $key );

    /**
     * Save the current row of the given key.
     * @param string $key
     * @return boolean
     */
    abstract protected function saveRow( $key );


    /**
     * Run the validation methods for all the objects being edited.
     * 
     * If this is a form submit then run the validation methods for the default object being edited.  The default method
     * calls the {@link I2CE_Form::validate() validate} method on the {@link $edit_obj} object.
     */
    protected function validate() {
        if ( !$this->checked_validation ) {
            foreach( array_keys($this->files) as $key ) {
                if ( !$this->processHeaderRow( $key ) ) {
                    return false;
                }
                while ( $this->processRow( $key ) ) {
                    if( !$this->validateRow( $key ) ) {
                        return false;
                    }
                }
            }
            $this->checked_validation = true;
        }
        return true;
    }


    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited
     * Most saving will be handled by the processRow functions.
     * @global array
     */
    protected function save() {
        foreach( array_keys($this->files) as $key ) {
            if ( !$this->processHeaderRow( $key ) ) {
                return false;
            }
            while ( $this->processRow( $key ) ) {
                if( !$this->saveRow( $key ) ) {
                    return false;
                }
            }
        }
        I2CE_ModuleFactory::callHooks( 'post_save_csv_upload_' . $this->module . '_page_' . $this->page, $this );
        return true;
    }


    /**
     *Checks to see if any of the forms on this page have invalid messages
     *@returns boolean
     */
    public function hasInvalid() {
        return $this->invalid;
    }

    /**
     * Lookup a list form by name and return the id
     * @param string $form
     * @param string $name
     * @param string $field Defaults to 'name' field.
     */
    protected function lookupList( $form, $name, $field = 'name' ) {
        $name = strtolower( $name );
        if ( !array_key_exists( $form, $this->cache ) ) {
            $this->cache[$form] = array();
        }
        if ( !array_key_exists( $name, $this->cache[$form] ) ) {
            $where = array( 
                    'operator' => 'FIELD_LIMIT',
                    'style' => 'lowerequals',
                    'field' => $field,
                    'data' => array( 'value' => trim($name) )
                    );
            $id = I2CE_FormStorage::search( $form, false, $where, array(), true );
            if ( $id ) {
                $this->cache[$form][$name] = "$form|$id";
            } else {
                $this->cache[$form][$name] = "";
            }
        }
        return $this->cache[$form][$name];
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
