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
* Class iHRIS_Module_PersonFileTrack
*
* @access public
*/


class iHRIS_Module_PersonFileTrack extends I2CE_Module {

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_filetrack' => 'validate_form_filetrack',
                );
    }

    /**
     * Checks to make sure the end date is after the start date for the person position.
     * @param I2CE_Form $form
     */
    public function validate_form_filetrack( $form ) {
        if ( $form->date_taken->isValid() && $form->return_date->isValid() ) {
            if ( $form->date_taken->compare( $form->return_date ) < 1 ) {
                $form->getField('return_date')->setInvalid( "The Return Date must be after the Taken Date." );
            }
        }
        
     } 
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_filetrack' => 'action_filetrack'
            );
    }


    public function action_filetrack($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        return $obj->addChildForms('filetrack', 'siteContent');
    }

    public function showCurrentFileTrack($node,$template) {
        return $this->showFileTrack($node,$template,true);
    }

    public function showAllActions($node,$template) {
        return $this->showFileTrack($node,$template,false);
    }


    protected function showFileTrack($node,$template,$only_active) {
        $personForm = $template->getData('FORM','person',$node);
        if (!$personForm instanceof iHRIS_Person || $personForm->getId() =='0') {
            $node->parentNode->removeChild($node);
            return;
        }
        $node->removeAttribute('type');
        $node->removeAttribute('name');
        $filetracks = iHRIS_Module_PersonFileTrack::getPersonFileTracks($personForm,$only_active);
        foreach ($filetracks as $filetrack) {
            $filetrack->populate();
            $actionNode = $template->appendFileByNode('view_filetrack.html','div',$node);
            if (!$actionNode instanceof DOMNode) {
                continue;
            }
            $template->setForm($filetrack,$actionNode);
        }

    }


    public static function getPersonFileTracks($person, $only_active = true) {
        $filetracks = array();
        $factory = I2CE_FormFactory::instance();
        if (is_scalar($person)) {
            $person = $factory->createContainer($person);
        }
        if (!$person instanceof iHRIS_Person) {
            return $filetracks;
        }
        if ($only_active) {
             $where = array(
                    'operator' => 'OR',
                    'operand' => array (
                        0 => array (
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'return_date',
                            'style'=> 'greaterthan_equals',
                            'data'=> I2CE_Date::now(I2CE_DATE::DATE)->getValues()
                            ),
                        1 => array (
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'return_date',
                            'style'=> 'null',
                            )
                        )
                    );
        } else {
            $where = array();
        }
        $filetrackIds = $person->getChildIds('filetrack','date_taken', $where); //get all filetracks in order of increasing start date
        foreach ($filetrackIds as $id) {
            $filetrack = $factory->createContainer('filetrack|'.$id);
            $filetrack->populate();
            if (!$filetrack instanceof iHRIS_FileTrack || $filetrack->getId() == '0') {
                continue;
            }
            $filetracks[] = $filetrack;
        }
        return $filetracks;
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
