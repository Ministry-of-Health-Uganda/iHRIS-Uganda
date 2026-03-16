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
    * @author Luke Duncan <lduncan@intrahealth.org>
    * @since v2.0.0
    * @version v2.0.0
    */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
abstract class I2CE_FormField_DB_DATE extends I2CE_FormField { 
        
    /**
     * A string type for the field 
     */
    const FIELD_TYPE_I2CE = 'date';
    /**
     * The database type for the field
     */
    const FIELD_TYPE_DB = 'datetime default NULL';


    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        $value = $this->getValue();
        if ($value instanceof I2CE_Date) {
            foreach ($value->getValues() as $k=>$v) {
                $field_node->appendChild($doc->createElement($k,$v));
            }
        }
    }
        



   /**
     * Return the value of this field from the database format for the given type
     * @param integer $type The type of the field to be returned.
     * @param mixed $value
     */
    public function getFromDB(  $value ) {
        return I2CE_Date::fromDB( $value );
    }

    /**
     * @var integer The start year for drop downs for picking the year if this is a date.
     */
    protected $start_year;
    /**
     * @var integer The end year for drop downs for picking the year if this is a date.
     */
    protected $end_year;


    protected $use_date_picker;

    /**
     * Create a new instance of a I2CE_FormField
     * @param string $name
     * @param array $options A list of options for this form field.
     */
    public function __construct( $name, $options ) {
        parent::__construct($name, $options);
        $this->use_date_picker = I2CE_ModuleFactory::instance()->isEnabled('DatePicker');
        $this->start_year = 0;
        $this->end_year = 0;
    }
        
                
        
        
    /**  
     * Returns the value of this field ready to be stored in the database.
     * @return mixed
     */
    public function getDBValue() {
        $date = $this->getValue();
        if (!$date instanceof I2CE_Date) {
            return null;
        }
        return $date->dbFormat(true); //allow blank values
    }

        
    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If a I2CE_Entry object has been passed to this method then it will return the value for that entry assuming it's an 
     * entry for this field.
     * @return mixed
     */
    public function getDisplayValue( $entry=false,$style = 'default' ) {
        if ( $entry instanceof I2CE_Entry ) {
            $value = $entry->getValue();
        } else {
            $value = $this->getValue();
        }
        if ($value instanceof I2CE_Date) {
            return $value->displayDate();
        } else {
            return I2CE_Date::blank()->displayDate();
        }
    }


    /**
     * Checks to see if the value has been set.
     * @return boolean
     */
    public function issetValue() {
        return ($this->value instanceof I2CE_Date);
    }
        
        
    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if ( !$this->issetValue() ) {
            return false;
        }
        $value = $this->getValue();
        $allow_blank_date = 1;
        if ($this->hasOption('allow_blank_date') && !$this->getOption('allow_blank_date')) {
            $allow_blank_date = 0;
        }
         if ($allow_blank_date && $value->isBlank()) {
            return true;
        }
        return I2CE_Validate::checkDate( $this->getValue());
    }

    /**
     * Check to see if the given DB value is equivalent to this value.
     * @param mixed $value
     * @return boolean
     */
    public function isSameValue( $db_value ) {        
        return ($this->compare($db_value) == 0);
    }   

        /**
     * Compares this form field agains the given form field.
     * @param mixed $db_value Either a DB Value or an I2CE_FormField
     * @returns -1,0,1
     */
    public function compare($db_value) {
        if (is_string($db_value)) {
            $db_value = I2CE_Date::fromDB($db_value);
        } else if ($db_value instanceof I2CE_FormField_DB_Date) {
            $db_value = $db_value->getValue();
        }
        if (!$db_value instanceof I2CE_Date) {
            if ( $this->isValid() ) {
                return 1;
            } else {
                return 0;
            }
        } elseif(!$this->getValue() instanceof I2CE_Date) {
            I2CE::raiseError("Trying to compare a non-set date");
            return -1;
        }
        return $this->getValue()->compare($db_value);
    }


    public function setYearRange( $start, $end=0 ) {
        $this->start_year = $start;
        $this->end_year = $end;
    }
    /**
     * Return the year range for this field if it's a date.
     * @return array
     * @global array
     */
    public function getYearRange() {
        $config = I2CE::getConfig()->modules->forms->defaults;
        if ( $this->start_year == 0 ) {
            if (isset($config->default_start_year) && (! $config->default_start_year instanceof  I2CE_MagicDataNode)) {
                $this->start_year = $config->default_start_year;
            } else {
                $this->start_year = 1955;
            }
        }
        if ( $this->end_year == 0 ) {
            $now = I2CE_Date::now();
            if (isset($config->default_end_year_increment) && (!$config->default_end_year_increment instanceof I2CE_MagicDataNode)) {
                $this->end_year = $now->year() + $config->default_end_year_increment;
            } else {
                $this->end_year = $now->year() + 10;
            }
        }
        return array( $this->start_year, $this->end_year );
    }




          



        



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
