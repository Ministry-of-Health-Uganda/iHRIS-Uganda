<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_SwissConfig_CustomReports_Report_ReportingInternals
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1.4
* @since 4.1.4
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingInternals extends I2CE_Swiss_CustomReports_Report_Base{


    
    public function getChildType($child) {
        return 'CustomReports_Report_ReportingInternal';
    }



    public function displayValues($contentNode, $transient_options, $action) {
        $this->ensureInternals();
        $mainNode = $this->template->appendFileByNode('customReports_report_internals_has.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported internals template");
            return false;
        }

        $listNode = $this->template->getElementById('report_internals_list',$mainNode);
        if (!$listNode instanceof DOMNode) {
            I2CE::raiseError("Don't know where to add internals");
            return false;
        }
        foreach ($this as $internal=>$swissInternal) {
            $internalNode = $this->template->appendFileByNode('customReports_report_internals_each.html', 'div',$listNode);
            if (!$internalNode instanceof DOMNode) {
                I2CE::raiseError("Bad internals_each");
                return false;
            }
            $swissInternal->addLink('internal_contents','internal_link',$internalNode,$action, $transient_options);            
        }
        return true;
    }

    /**
     * @var boolean Set when the internals have been loaded
     */
    protected $ensured;

    /**
     * Ensure all the internals are loaded.
     */
    protected function ensureInternals() {
        if ( $this->ensured ) {
            return;
        }
        $this->getChild('last_modified', true );
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
