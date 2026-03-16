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
* Class iHRIS_Module_PersonAssessment
*
* @access public
*/


class iHRIS_Module_Assessment extends I2CE_Module {

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_assessment' => 'validate_form_assessment',
                );
    }

    /**
     * Checks to make sure the end date is after the start date for the person position.
     * @param I2CE_Form $form
     */
    public function validate_form_assessment( $form ) {
        if ( $form->start_date->isValid() && $form->end_date->isValid() ) {
            if ( $form->start_date->compare( $form->end_date ) < 1 ) {
                $form->getField('end_date')->setInvalid( "The end date must be after the start date." );
            }
        }
        
     } 
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_assessment' => 'action_assessment'
            );
    }


    public function action_assessment($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        return $obj->addChildForms('assessment', 'siteContent');
    }

    public function showCurrentAssessment($node,$template) {
        return $this->showAssessment($node,$template,true);
    }

    public function showAllActions($node,$template) {
        return $this->showAssessment($node,$template,false);
    }


    protected function showAssessment($node,$template,$only_active) {
        $personForm = $template->getData('FORM','person',$node);
        if (!$personForm instanceof iHRIS_Person || $personForm->getId() =='0') {
            $node->parentNode->removeChild($node);
            return;
        }
        $node->removeAttribute('type');
        $node->removeAttribute('name');
        $assessments = iHRIS_Module_PersonAssessment::getPersonAssessments($personForm,$only_active);
        foreach ($assessments as $assessment) {
            $assessment->populate();
            $actionNode = $template->appendFileByNode('view_assessment.html','div',$node);
            if (!$actionNode instanceof DOMNode) {
                continue;
            }
            $template->setForm($assessment,$actionNode);
        }

    }


    public static function getPersonAssessments($person, $only_active = true) {
        $assessments = array();
        $factory = I2CE_FormFactory::instance();
        if (is_scalar($person)) {
            $person = $factory->createContainer($person);
        }
        if (!$person instanceof iHRIS_Person) {
            return $assessments;
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
        $assessmentIds = $person->getChildIds('assessment','start_date', $where); //get all assessments in order of increasing start date
        foreach ($assessmentIds as $id) {
            $assessment = $factory->createContainer('assessment|'.$id);
            $assessment->populate();
            if (!$assessment instanceof iHRIS_Assessment || $assessment->getId() == '0') {
                continue;
            }
            $assessments[] = $assessment;
        }
        return $assessments;
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
