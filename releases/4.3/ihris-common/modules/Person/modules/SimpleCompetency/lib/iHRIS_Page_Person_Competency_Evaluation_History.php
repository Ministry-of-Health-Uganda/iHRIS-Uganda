<?php
/**
* © Copyright 2008 IntraHealth International, Inc.
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
*/
/**
*  iHRIS_Page_Person_Competency_Evalutaion_History
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_Page_Person_Competency_Evaluation_History extends I2CE_Page{

    /**
     * Load the HTML template files for editing and confirming the index and demographic information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_view_link.html", "li", "navBarUL", true );
    }

            
    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();
        $person_id = 0;
        if ($this->get_exists('parent')) {
            $person_id = $this->get('parent');
        }
        $factory = I2CE_FormFactory::instance();
        $personForm = $factory->createContainer($person_id);
        if (!$personForm instanceof I2CE_Form) {
            return;
        }
        $personForm->populate();
        $this->setForm($personForm);
        $this->setDisplayData('person_id', 'id=' . $person_id);
        if ($this->get_exists('id')) {
            $competency_ids = array($this->get('id'));
        } else {
            $competency_ids = $personForm->getChildIds('person_competency');
        }
        $compAppendNode = $this->template->getElementById('comp_list');
        if (!$compAppendNode instanceof DOMNode) {
            return;
        }
        foreach ($competency_ids as $competency_id) {
            $compNode =$this->template->appendFileByNode("personal_competency_evaluation_history_comp_each.html",'div',$compAppendNode);
            $compForm = $factory->createContainer('person_competency'.'|'.$competency_id);
            if (!$compForm instanceof I2CE_Form) {
                continue;
            }
            $compForm->populate();
            $this->setForm($compForm,$compNode);
            $competency = $factory->createContainer( $compForm->getField( "competency" )->getDBValue() );
            $competency->populate();
            $this->setForm( $competency, $compNode );
            $appendNode = $this->template->getElementById('evaluation_list',$compNode);
            if (!$appendNode instanceof DOMNode) {
                return;
            }
            $fields = array('evaluation_date','competency_evaluation');
            $compForm->populateHistory($fields);
            $all_dates = array();
            foreach( $fields as $field ) {
                while ( $compForm->getField($field)->hasNextHistory() ) {
                    $entry = $compForm->getField($field)->nextHistory();
                    $all_dates[ $entry->date->dbFormat() ][$field] = $entry;
                }
            }
            foreach ($all_dates as $entries) {
                $evalNode =$this->template->appendFileByNode("personal_competency_evaluation_history_each.html",'tr',$appendNode);
                if (!$evalNode instanceof DOMNode) {
                    return;
                }
                foreach ($entries as $field=>$entry) {
                    $this->template->setDisplayDataImmediate($field ,  $compForm->getField($field)->getDisplayValue( $entry),$evalNode );
                }
            }
        }
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
