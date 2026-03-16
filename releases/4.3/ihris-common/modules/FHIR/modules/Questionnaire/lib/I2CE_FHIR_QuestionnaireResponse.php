<?php
/**
* © Copyright 2016 IntraHealth International, Inc.
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
* @package ihris-common
* @subpackage FHIR
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.1
* @since v4.2.1
* @filesource 
*/ 
/** 
* Class I2CE_QuestionnaireResponse
* 
* @access public
*/

use FHIR_DSTU_TWO\FHIRDomainResource\FHIRQuestionnaire as FHIRQuestionnaire;
use FHIR_DSTU_TWO\FHIRDomainResource\FHIRQuestionnaireResponse as FHIRQuestionnaireResponse;
use FHIR_DSTU_TWO\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseAnswer as FHIRQuestionnaireResponseAnswer;
use FHIR_DSTU_TWO\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseGroup as FHIRQuestionnaireResponseGroup;
use FHIR_DSTU_TWO\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseQuestion as FHIRQuestionnaireResponseQuestion ;
use FHIR_DSTU_TWO\FHIRResource\FHIRBundle as FHIRBundle;
use FHIR_DSTU_TWO\FHIRElement\FHIRString as FHIRString;
use FHIR_DSTU_TWO\FHIRElement\FHIRCoding as FHIRCoding;
use FHIR_DSTU_TWO\FHIRElement\FHIRCode as FHIRCode;
use FHIR_DSTU_TWO\FHIRElement\FHIRDecimal as FHIRDecimal;
use FHIR_DSTU_TWO\FHIRElement\FHIRInteger as FHIRInteger;
use FHIR_DSTU_TWO\FHIRElement\FHIRDate as FHIRDate;
use FHIR_DSTU_TWO\FHIRElement\FHIRDateTime as FHIRDateTime;
use FHIR_DSTU_TWO\FHIRElement\FHIRBoolean as FHIRBoolean;
use FHIR_DSTU_TWO\PHPFHIRResponseParser as PHPFHIRResponseParser;


class I2CE_FHIR_QuestionnaireResponse extends I2CE_FHIR_Base{

    /**
     * walk through the questionnaire and create form name with the given name
     * You should probabl call load_resource() before calling this method.
     * @param string $prefix a prefix used (e.g. identifer key for host system) to attach to forms and form classes
     * @param I2CE_Form $p_form. optional the parent form object we wish to attach this response to. defaults to null
     * @return I2CE_Form the instantiation of the form. 
     */
    public function instantiate_form($prefix, $p_form = null) {
        $ff = I2CE_FormFactory::instance();
        if (! $this->resource instanceof FHIRQuestionnaireResponse
            || (! ($id = $this->resource->id))
            || (! ($q_ref = $this->resource->questionnaire)) instanceof FHIRReference
            || ! ($questionnaire = $this->get_referenced_resource($q_ref,'Questionnaire')) instanceof FHIRQuestionnaire
            || (! ($q_id = $questionnaire->id))
            || (! ($group = $this->resource->group) instanceof FHIRQuestionnaireResponseGroup)
            ) {
            throw new Exception("Invalid questionnaire response");
        }
        $group_queue = array(array($group,$p_form));
        $root_form = false;
        $form_objs =array();
        while (count($group_queue) > 0) {
            list($group,$p_form) = array_shift($group_queue);
            if (! $group instanceof FHIRQuestionnaireResponseGroup
                || ! ($group->linkId instanceof FHIRString)
                || ! ($g_linkId = $group->linkId->value)
                ) {
                continue;
            }
            $formid = $q_id . '.' . $g_linkId;
            $form_name = 'questionnaire-' . $prefix . '-' . $formid;
            $form_name = str_replace(array('=','/'),array('&#61;','&#x2F;'),$form_name);
            if (! ($c_form =$ff->createContainer($form_name)) instanceof I2CE_Form) {
                I2CE::raiseMessage("Could not instantiate $form_name");
                continue;
            }
            if (! $root_form instanceof I2CE_Form) {
                $root_form = $c_form;
                $c_form->setID($id);
            } else {
                //what do we do for child ids? doesn't seem to be anything useful
            }
            if ($p_form instanceof I2CE_Form) {
                $c_form->setParent($p_form);
            }
            $form_objs[] = $c_form;
            foreach ($group->question as $question) {
                $q_linkId =false;
                $type = false;
                if (! $question instanceof FHIRQuestionnaireResponseQuestion
                    || !( $question->linkId instanceof FHIRString)
                    || !( $q_linkId = $question->linkId->value)
                    || ! is_array( $answers = $question->answer)
                    || ! ($answer = array_shift($answers))   instanceof FHIRQuestionnaireResponseAnswer  //ONLY GETTING FIRST ANSWER... what does multiple mean!!!
                    ) {
                    continue;
                }
                $f_name = $id . '.' . $g_linkId . '.' . $q_linkId;
                $f_name =  'questionnaire-' . $prefix . '-' . $formid . '.' . $q_linkId;
                $f_name =  'questionnaire-' . $prefix . '-' . $formid . '.'  . $q_linkId;
                
                $f_name = str_replace(array('=','/'),array('&#61;','&#x2F;'),$f_name);
                $field_obj = $c_form->getField($f_name);

                if ( $field_obj instanceof I2CE_FormField_STRING_LINE) {
                    if ($answer->valueString instanceof FHIRString) {
                        $field_obj->setFromDB($answer->valueString->value);
                    } else {
                        I2CE::raiseMessage("Could not get field $f_name in form $form_name as STRING_LINE");
                    }
                } else if ( $field_obj instanceof I2CE_FormField_INT) {
                    if ($answer->valueInteger instanceof FHIRInteger) {
                        $field_obj->setFromDB($answer->valueInteger->value);
                    } else {
                        I2CE::raiseMessage("Could not get field $f_name in form $form_name as INT");
                    }
                }  else if ( $field_obj instanceof I2CE_FormField_FLOAT) {
                    if ($answer->valueDecimal instanceof FHIRDecimal) {
                        $field_obj->setFromDB($answer->valueDecimal->value);
                    } else {
                        I2CE::raiseMessage("Could not get field $f_name in form $form_name as FLOAT");
                    }
                } else if ( $field_obj instanceof I2CE_FormField_DATE_YMD) {
                    if ($answer->valueDate instanceof FHIRDate) {
                        $field_obj->setFromDB($answer->valueDate->value);
                    } else {
                        I2CE::raiseMessage("Could not get field $f_name in form $form_name as DATE_YMD");
                    }
                } else if ( $field_obj instanceof I2CE_FormField_DATE_TIME) {
                    if ($answer->valueDateTime instanceof FHIRDateTime) {
                        $field_obj->setFromDB($answer->valueDateTime->value);
                    } else {
                        I2CE::raiseMessage("Could not get field $f_name in form $form_name as DATE_TIME");
                    }
                } else if ( $field_obj instanceof I2CE_FormField_BOOL) {
                    if ($answer->valueDateTime instanceof FHIRBoolean) {
                        $field_obj->setFromDB($answer->valueBoolean->value ? 1 : 0);
                    } else {
                        I2CE::raiseMessage("Could not get field $f_name in form $form_name as BOOL");
                    }
                } else if ( $field_obj instanceof I2CE_FormField_MAP) {
                    if ($answer->valueCoding instanceof FHIRCoding
                        && $answer->valueCoding->code instanceof FHIRCode
                        ) {
                        $field_obj->setValue(array($f_name,$answer->valueCoding->code->value));
                    } else {
                        I2CE::raiseMessage("Could not get field $f_name in form $form_name as MAP");
                    }
                } else {
                    I2CE::raiseMessage("Skipping quesition $q_linkId ($type) - unrecongized data type " . get_class($field_obj));
                    continue;
                }
            }

            if (is_array($group->group)) {
                foreach ($group->group as $c_group) {
                    if (!$c_group instanceof  FHIRQuestionnaireResponseGroup) {
                        continue;
                    }
                    $group_queue[] = array($c_group,$c_form);
                }
            }

        }
        if (!$root_form instanceof I2CE_Form) {
            throw new Exception("Could not instantiate root questionnaire response form");
        }
        foreach ($form_objs as $form_obj) {
            $xml = $form_obj->getXMLRepresentation(false);
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
            $doc->loadXML($xml);
            I2CE::raiseMessage("Loaded:\n" . $doc->saveXML());
        }
        return $root_form;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
