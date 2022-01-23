<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 * Edit participants action for a training
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author  Carl Leitner <litlfred@ibiblio.org> 
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.3
 * @version v4.1.3
 */

/**
 * The action page class for editing particpants for a training
 * @package iHRIS
 * @subpackage Train
 * @access public
 */
class iHRIS_PageActionStudentCertificates extends I2CE_PageGenerateRelationshipTemplate {

    protected $person_id = false;

    protected function loadPrimary() {
        if ($this->request_exists('student_id')
            && in_array('person',$this->formRelationship->getFormNames())
            && ($student_id = $this->request('student_id'))
            && ($pos = strpos($student_id,'|')) !== false
            && ($id = substr($student_id,$pos + 1)) != ''
            && ($stc_id = I2CE_FormStorage::lookupField('person_scheduled_training_course',$id,array('scheduled_training_course'),'')) != ''
            && ($person_id = I2CE_FormStorage::lookupField('person_scheduled_training_course',$id,array('parent'),'')) != ''
            ) {
            $formFactory = I2CE_FormFactory::instance();
            if  (! ($this->primObj = $formFactory->createContainer($stc_id)) instanceof I2CE_Form
                 || $this->formRelationship->getPrimaryForm() != $this->primObj->getName()
                ) {
                I2CE::raiseError("invalid form id :" . print_r($this->request(),true) . "\ndoes not match " . $this->formRelationship->getPrimaryForm());
                return false;
            }
            $this->primObj->populate();
            $this->person_id = $person_id;
        } else {
            return parent::loadPrimary();
        }
    }
        


    /**
     *Loads in the requeted data from the relationship
     * @returns boolean  True on success
     */
    protected function loadData($as_iterator = true) {
        I2CE::raiseError("LD0:" . $this->person_id);
        if ($this->person_id) {
            $p_where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'parent',
                'style'=>'equals',
                'data'=>array(
                    'value'=>$this->person_id
                    )
                );            
            I2CE::raiseError("Limiting to " . $this->person_id);
            $this->formRelationship->setAdditionalLimit('person_scheduled_training_course',$p_where);
        }
        return parent::loadData($as_iterator);
    }

    protected function getODTTemplate() {
        //first we see if we have a certificate uploaded to the training course itself
        $have_certificate =false;
        if (($tcField = $this->primObj->getField('training_course')) instanceof I2CE_FormField_MAP
            &&  ($tcObj = $tcField->getMappedFormObject()) instanceof iHRIS_Training_Course) {
            $tcObj->populate();
            if ( ($docField = $tcObj->getField('certificate')) instanceof I2CE_FormField_DOCUMENT
                 && $docField->isValid()
                 && ($content = $docField->getValue())
                 && ($name = $docField->getFileName())) {
                $pos = strrpos($name,'.');
                $ext ='';
                if ($pos !== false) {
                    $ext = substr($name,$pos);
                    $name = substr($name, 0,$pos);
                }
                $this->template_file = tempnam(sys_get_temp_dir(), basename($name .'_' ))  . $ext;
                I2CE::raiseError($this->template_file);
                file_put_contents($this->template_file,$content);            
                return true;
            }
        }
        return parent::getODTTemplate();
    }

    
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
