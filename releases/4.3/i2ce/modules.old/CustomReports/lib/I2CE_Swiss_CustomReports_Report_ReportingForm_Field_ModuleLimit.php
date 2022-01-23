<?php
/**
 * @copyright © 2011, 2012 Intrahealth International, Inc.
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
*  I2CE_Swiss_CustomReports_Report_ReportingForm_Field_ModuleLimit
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @since 4.1
* @version 4.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm_Field_ModuleLimit extends I2CE_Swiss_CustomReports_Report {


    /**
     * Return the display name for this object.
     * @return string
     */
    public function getDisplayName() {
        return $this->getModuleDisplay();
    }

    /**
     * Return the module display name for this object.
     * @return string
     */
    public function getModuleDisplay() {
        $module = $this->getName();
        return I2CE::getConfig()->config->data->$module->displayName;
    }


    /**
     * Return the link field data for this object.
     * @return string
     */
    public function getLinkField() {
        if ($this->hasField('link_field')) {
            return $this->getField('link_field');
        }
    }
    /**
     * See if the link field exists in this object.
     * @return boolean
     */
    public function hasLinkField() {
        return $this->hasField('link_field');
    }
    /**
     * Set the link field for this object
     * @param string $link_field
     */
    public function setLinkField($link_field) {
        $this->setField('link_field',$link_field);
    }


    /**
     * Process all the values passed to this object to set the correct data.
     * @param array $vals
     * @return boolean
     */
    public function processValues($vals) {
        if (!array_key_exists('submit',$vals)) {
            return true;
        }
        if (array_key_exists('link_field',$vals) ) {
            $this->setLinkField($vals['link_field']);
        }
        return true;
    }

    /**
     * @var array The list of field options available for this object.
     */
    protected $field_options = array();

    /**
     * Set the field options for this object.
     * @param array $opts
     */
    public function setFieldOptions( $opts ) {
        if ( is_array( $opts ) ) {
            $this->field_options = $opts;
        }
    }


    /**
     * Handle displaying the values in the template.
     * @param DOMNode $contentNode
     * @param array $transient_options
     * @param string $action
     * @return boolean
     */
    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_form_field_module_limit.html','tr',$contentNode);
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to find limit option template");
            return false;
        }
        $this->template->setDisplayDataImmediate('module_display', $this->getModuleDisplay(), $mainNode);
        $this->template->setDisplayDataImmediate('link_field', $this->field_options, $mainNode );
        $this->template->selectOptionsImmediate('link_field', $this->getLinkField(),$mainNode);        
        if ($action == 'edit') {
            $this->renameInputs('*',$mainNode);
        }
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
