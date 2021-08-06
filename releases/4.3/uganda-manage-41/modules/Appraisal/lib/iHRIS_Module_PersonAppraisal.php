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
*
* @package ihris-common
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2
* @since v3.2
* @filesource
*/
/**
* Class iHRIS_Module_PersonAppraisal
*
* @access public
*/


class iHRIS_Module_PersonAppraisal extends I2CE_Module {

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_appraisal' => 'validate_form_appraisal',
                );
    }

    /**
     * Checks to make sure the end date is after the start date for the person position.
     * @param I2CE_Form $form
     */
    public function validate_form_appraisal( $form ) {
        if ( $form->start_date->isValid() && $form->end_date->isValid() ) {
            if ( $form->start_date->compare( $form->end_date ) < 1 ) {
                $form->getField('end_date')->setInvalid( "The end date must be after the start date." );
            }
        }
        
     } 
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_appraisal' => 'action_appraisal'
            );
    }


    public function action_appraisal($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        return $obj->addChildForms('appraisal', 'siteContent');
    }

    public function showCurrentAppraisal($node,$template) {
        return $this->showAppraisal($node,$template,true);
    }

    public function showAllActions($node,$template) {
        return $this->showAppraisal($node,$template,false);
    }


    protected function showAppraisal($node,$template,$only_active) {
        $personForm = $template->getData('FORM','person',$node);
        if (!$personForm instanceof iHRIS_Person || $personForm->getId() =='0') {
            $node->parentNode->removeChild($node);
            return;
        }
        $node->removeAttribute('type');
        $node->removeAttribute('name');
        $appraisals = iHRIS_Module_PersonAppraisal::getPersonAppraisals($personForm,$only_active);
        foreach ($appraisals as $appraisal) {
            $appraisal->populate();
            $actionNode = $template->appendFileByNode('view_appraisal.html','div',$node);
            if (!$actionNode instanceof DOMNode) {
                continue;
            }
            $template->setForm($appraisal,$actionNode);
        }

    }


    public static function getPersonAppraisals($person, $only_active = true) {
        $appraisals = array();
        $factory = I2CE_FormFactory::instance();
        if (is_scalar($person)) {
            $person = $factory->createContainer($person);
        }
        if (!$person instanceof iHRIS_Person) {
            return $appraisals;
        }
        if ($only_active) {
            $where = array (
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'end_date',
                            'style'=> 'max_parent_form',
                          );
        } else {
            $where = array();
        }
        $appraisalIds = $person->getChildIds('appraisal','start_date', $where); //get all appraisals in order of increasing start date
        foreach ($appraisalIds as $id) {
            $appraisal = $factory->createContainer('appraisal|'.$id);
            $appraisal->populate();
            if (!$appraisal instanceof iHRIS_Appraisal || $appraisal->getId() == '0') {
                continue;
            }
            $appraisals[] = $appraisal;
        }
        return $appraisals;
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
