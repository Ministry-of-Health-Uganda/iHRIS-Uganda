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
 */
/**
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 */
/**
 * Class for representing an entry in the database.
 * 
 * Multiple entries may exist for any {@link I2CE_FormField} value to track all changes since the record was created.
 * @package I2CE 
 * @access public
 * @see I2CE_FormField
 */
class I2CE_Entry {
        
    /**
     * @var I2CE_Date The date this entry was added.
     */
    public $date;
    /**
     * @var integer The user id of the person who made this entry.
     */
    public $who;
    /**
     * @var integer The type of change for this entry.
     */
    public $change_type;
    /**
     * @var mixed The value for this entry.
     */
    public $value;

    /**
     * Create a new instance of a I2CE_Entry.
     * 
     * This will usually be done by the {@link I2CE_FormField} object when it needs access to the history for this field.
     * @param I2CE_Date $date
     * @param integer $who
     * @param integer $change_type
     * @param mixed $value
     */
    public function __construct( $date, $who, $change_type, $value ) {
        $this->date = $date;
        $this->who = $who;
        $this->change_type = $change_type;
        $this->value = $value;
    }
        
    /**
     * Return the value of this entry
     * @return mixed;
     */ 
    public function getValue() {
        return $this->value;
    }


    
    /**
     * Get the entry as an associative array
     * @returns array with keys 'date' (value is db formatted date), 'who', 'dbvalue', and 'change_type'
     */
    public function getAsArray() {
        $date = null;
        if ($this->date instanceof I2CE_Date) {
            $date = $this->date->dbFormat(true); //allow blank
        }
        return array('date'=>$date,'who'=>$this->who,'value'=>$this->value,'change_type'=>$this->change_type);
    }
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
