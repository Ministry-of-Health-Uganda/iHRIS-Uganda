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
* Class I2CE_Questionnaire
* 
* @access public
*/

use FHIR_DSTU_TWO\FHIRDomainResource\FHIRQuestionnaire as FHIRQuestionnaire;
use FHIR_DSTU_TWO\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireGroup as FHIRQuestionnaireGroup;
use FHIR_DSTU_TWO\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireQuestion as FHIRQuestionnaireQuestion;
use FHIR_DSTU_TWO\PHPFHIRResponseParser as PHPFHIRResponseParser;
use FHIR_DSTU_TWO\FHIRElement\FHIRReference as FHIRReference;
use FHIR_DSTU_TWO\FHIRElement\FHIRString as FHIRString;
use FHIR_DSTU_TWO\FHIRDomainResource\FHIRValueSet as FHIRValueSet;
use FHIR_DSTU_TWO\FHIRElement\FHIRAnswerFormat as FHIRAnswerFormat;

class I2CE_FHIR_Questionnaire extends I2CE_FHIR_Base {

    /* ValueSet handler
     * @var I2CE_FHIR_ValueSet $valueset_handler;
     */
    public $valueset_handlder;


    public function __construct() {
        parent::__construct();
        $this->valueset_handler =new I2CE_FHIR_ValueSet();
    }




    /**
     * walk through the questionnaire and create form name with the given name
     * You should probabl call load_resource() before calling this method.
     * @param string $prefix a prefix used (e.g. identifer key for host system) to attach to forms and form classes
     * @param string $parent_form the parent form (if any) we want to attach the questionnaire form to.  Defaults to false.
     */
    public function attach_form($prefix , $parent_form = '') {
        if (! $this->resource instanceof FHIRQuestionnaire
            || (! ($id = $this->resource->id))
            || (! ($group = $this->resource->group) instanceof FHIRQuestionnaireGroup)
            ) {
            throw new Exception("Invalid questionnaire ");
        }
        $form_classes = array();
        $forms = array();
        $group_queue = array(array($group,$parent_form));
        $parent_forms = array();
        $lists = array();
        $root_title = false;
        while (count($group_queue) > 0) {
            list($group,$p_form) = array_shift($group_queue);
            if (! $group instanceof FHIRQuestionnaireGroup
                || ! ($group->linkId instanceof FHIRString)
                || ! ($g_linkId = $group->linkId->value)
                ) {
                continue;
            }
            if ( ( $group->title instanceof FHIRString)
                && ( $title = $group->title->value)
                ) {
                $title = $group->title->value;
            } else {
                $title = $g_linkId;
            }

            if (!$root_title) {
                $root_title = 'Questionnaire: ' . $title;
                $title = $root_title;
            } else {
                $title = $root_title . ' - ' . $title;
            }
            $fields = array();
            $formid = $id . '.' . $g_linkId;
            $form_class = 'Questionnaire-' . $prefix . '-' . $formid;
            $form_name = 'questionnaire-' . $prefix . '-' . $formid;
            $form_class = preg_replace('/[^\da-z]/i', '_',$form_class);
            $form_name = str_replace(array('=','/'),array('&#61;','&#x2F;'),$form_name);
            foreach ($group->question as $question) {
                $q_linkId =false;
                $type = false;
                if (! $question instanceof FHIRQuestionnaireQuestion
                    || !( $question->linkId instanceof FHIRString)
                    || !( $q_linkId = $question->linkId->value)
                    || !( $question->type instanceof FHIRAnswerFormat)
                    || !( $type = $question->type->value) 
                    ) {
                    I2CE::raiseMessage("Skipping invalid question $q_linkId ($type) in $form_name");
                    continue;
                }
                if ( !( $question->text instanceof FHIRString)
                     || !( $f_title = $question->text->value)
                    ){
                    $f_title = $q_linkId;
                }
                $f_name = 'questionnaire-' . $prefix . '-' .$id . '.' . $g_linkId . '.' . $q_linkId;                
                $f_name = str_replace(array('=','/'),array('&#61;','&#x2F;'),$f_name);
                $formfield = false;
                switch($type) {
                case 'string':
                    $formfield = 'STRING_LINE';
                    break;
                case 'integer':
                    $formfield = 'INT';
                    break;
                case 'date':
                    $formfield = 'DATE_YMD';
                    break;
                case 'dateTime':
                    $formfield = 'DATE_TIME';
                    break;
                case 'decimal':
                    $formfield = 'FLOAT';
                    break;
                case 'boolean':
                    $formfield = 'BOOL';
                    break;
                case 'choice':
                    $formfield = 'MAP';                    
                    $values = array();
                    try {
                        if (! ($reference = $question->options) instanceof FHIRReference
                            || ! ($valueset = $this->get_referenced_resource($reference,'ValueSet')) instanceof FHIRValueSet
                            ) {
                            I2CE::raiseMessage("Could not get value set reference");
                            break;                            
                        }
                        $this->valueset_handler->resource = $valueset;
                        $values = $this->valueset_handler->get_simple_list();
                    } catch(Exception $e) {
                        I2CE::raiseMessage("could not get valueset values from " . $reference . "\n\t" . $e);
                        break;
                    } 
                    $forms[$f_name] =
                        array(
                            'class'=>'I2CE_SimpleList',
                            'display'=> $root_title . ' -  ' . $f_title,
                            'storage'=>'magicadata' 
                            );
                    $lists[$f_name] = $values;
                    break;
                default:
                    break;
                }
                if (!$formfield) { 
                    I2CE::raiseMessage("Skipping quesition $q_linkId ($type) in $form_name");
                    continue;
                }
                $fields[$f_name] = 
                    array(
                        'formfield'=>$formfield,
                        'headers'=>array('default'=>$f_title),                    
                        );
            }

            $form_classes[$form_class] = 
                array(
                    'extends'=>'I2CE_Form',
                    'fields'=>$fields
                    );
            $forms[$form_name] = 
                array(
                    'class'=> $form_class,
                    'display'=>$title,                
                    );
            if ($p_form) {
                if (!array_key_exists($p_form,$parent_forms)) {
                    $parent_forms[$p_form] = array();
                }
                $parent_forms[$p_form][] = $form_name;
            }
            if (is_array($group->group)) {
                foreach ($group->group as $c_group) {
                    if (!$c_group instanceof  FHIRQuestionnaireGroup) {
                        continue;
                    }
                    $group_queue[] = array($c_group,$form_name);
                }
            }

        }

        $ff =I2CE_FormFactory::instance();
        foreach ($parent_forms as $p=>$cs) {
            if (!in_array($p,$forms)) {
                $forms[$p] = array();
            }
            if (!array_key_exists('meta',$forms[$p])) {
                $forms[$p]['meta'] = array();
            }
            if (!array_key_exists('child_forms',$forms[$p]['meta'])) {
                $forms[$p]['meta']['child_forms'] = array();
            }                  
            foreach ($cs as $c)  {
                if ( ($pObj = $ff->createContainer($p)) instanceof I2CE_Form
                     && in_array($c,$pObj->getChildForms())
                    ) {
                    continue;
                }
                $forms[$p]['meta']['child_forms'][] = $c;
            }
        }
        $magicdata = array(
            'I2CE'=>array(
                'formsData'=>array(
                    'forms' => $lists
                    )
                ),
            'modules'=>array(                
                'forms'=>array(
                    'formClasses' => $form_classes,
                    'forms' => $forms          
                    )
                )
            );
        I2CE::raiseMessage("Creating form from:\n" . print_r($magicdata,true));
        $suc = I2CE::getConfig()->setValue($magicdata);
        I2CE::getConfig()->clearCache();
        return $suc;
    }
    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
