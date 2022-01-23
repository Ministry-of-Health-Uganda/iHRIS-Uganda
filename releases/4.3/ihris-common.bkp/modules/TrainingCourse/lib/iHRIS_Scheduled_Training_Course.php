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
*  iHRIS_Scheduled_Training_Course
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


class iHRIS_Scheduled_Training_Course extends I2CE_List{
            
    public function enrolledStudentsAjax($node,$template) {
        $template->addHeaderLink("stubs.js");
        $template->addHeaderLink("editstudent.js");
        $js = "return ajaxLoadStudents(this,'" . $this->getNameId() . "' );";
        $node->appendChild(
            $template->createElement(
                'a',
                array('title'=>'Expand', 
                      'onclick'=>$js
                    ),
                'Load Student Exam Results'));        

    }

    public function enrolledStudentsExpander($node,$template) {
        $node->appendChild(
            $template->createElement(
                'a',
                array('title'=>'Expand', 
                      'onclick'=>"return hideDiv('students_" . $this->getNameId() . "', this );"
                    ),
                'Show Students'));
    }

    public function evaluateStudents($node,$template) {
        $list = $this->getEnrolledStudents();
        $template->addHeaderLink("mootools-core.js");
        $template->addHeaderLink("mootools-more.js");
        $template->addHeaderLink("editstudent.js");
        $evals = I2CE_List::listOptions('training_course_evaluation');
        foreach ($evals as $id=>$data) {
            if (!is_array($data) || !array_key_exists('value',$data) || !array_key_exists('display',$data)) {
                unset($evals[$i]);
            }
        }

        foreach ($list as $id=>$sdata) {
            $studentNode = $template->appendFileByNode('evaluate_students.html','div',$node);
            if (!$studentNode instanceof DOMNode) {
                return false;
            }
            $studId = 'student_' . $id;
            $studentNode->setAttribute('id',$studId);
            $template->setDisplayDataImmediate('id','id=' . $id, $studentNode);
            $template->setDisplayDataImmediate('firstname',$sdata['firstname']  , $studentNode);
            $template->setDisplayDataImmediate('surname',$sdata['surname'], $studentNode);
            if (! ($evalNode =  $template->getElementByName('evaluation',0,$studentNode)) instanceof DOMElement) {                
                continue;
            }
            list($f,$fid) = array_pad(explode("|",$sdata['person_scheduled_training_course'],2),2,'');
            $eval = I2CE_FormStorage::lookupField('person_scheduled_training_course',$fid,array('training_course_evaluation'),'');

            foreach ($evals as $data) {
                $id=$data['value'];
                $disp=$data['display'];
                $attrs = array('value'=>$id);
                if ($eval == $id) {
                    $attrs['selected'] = 'selected';
                }
                $evalNode->appendChild($template->createElement('option',$attrs,$disp));
            }
            
            $js = "changeStudentEvaluation(this,'" . addslashes($sdata['person_scheduled_training_course']) . "');";
            $evalNode->setAttribute('onchange',$js);
        }
    }





    public function enrolledStudentsList($node,$template) {
        if ( ! ($main_node =$template->appendFileByNode('training_course_exam_base.html','div',$node )) instanceof DOMNode ) {
            return false;
        }
        if (! ($selNode = $template->getElementById('exam_selector',$main_node)) instanceof DOMNode) {
            return false;
        }
        if (! ($list_node = $template->getElementById('student_exams',$main_node)) instanceof DOMNode) {
            return false;
        }
        $node->appendChild($list_node);
        $list = $this->getEnrolledStudents();
        $template->addHeaderLink("mootools-core.js");
        $template->addHeaderLink("mootools-more.js");
        $template->addHeaderLink("editstudent.js");
        $has_exam = I2CE_ModuleFactory::instance()->isEnabled('training-exam'); 
        $where_exam =  
            array('operator' => 'OR',
            'operand'=>array(
                array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'i2ce_hidden',
                    'style'=>'no'),
                array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'i2ce_hidden',
                    'style'=>'null')
                ));
                
        $exams = I2CE_FormStorage::listDisplayFields('training_course_exam_type',array('name'),false, $where_exam,array('name'));

        $passing_score = '';
        if ($has_exam) {
            $passing_scores = I2CE_FormStorage::lookupField('training_course',$this->getField('training_course')->getMappedID(), array('passing_score'),false);
            if (count($passing_scores) == 1) {
                $passing_score = current($passing_scores);
                if (!is_numeric($passing_score) || $passing_score < 0 || $passing_score > 100) {
                    $passing_score = '';
                }
            }
        }
        $stc_dup_where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'training_course',
            'style'=>'equals',
            'data'=>array(
                'value'=> $this->getField('training_course')->getDBValue()
                ));

        
        $dup_where = array(); //this will be a search on person_scheduled_training_course
        foreach (I2CE_FormStorage::search('scheduled_training_course',false,$stc_dup_where) as $stc) {
            if ($stc == $this->getID()) {
                //don't include this course when looking for duplicate scheuling.
                continue;
            }
            $dup_where[] = 
                array(
                    'operator'=>'AND',
                    'operand'=>array(
                        0=> array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'scheduled_training_course',
                            'style'=>'equals',
                            'data'=>array(
                                'value'=>'scheduled_training_course|' .$stc
                                )),
                        1=>array(
                            'operator' => 'OR',
                            'operand'=>array(
                                0=>array(
                                    'operator'=>'FIELD_LIMIT',
                                    'field'=>'attending',
                                    'style'=>'equals',
                                    'data'=>array(
                                        'value'=>1
                                        )),
                                1=>array(
                                    'operator'=>'FIELD_LIMIT',
                                    'field'=>'attending',
                                    'style'=>'null',
                                    'data'=>array()
                                    )
                                )
                            )
                        )
                    );
        }
        if (count($dup_where) > 0)  {
            $dup_where = array('operator'=>'OR','operand'=>$dup_where);
        } else {
            $dup_where = false;
        }

        $student_dup = array();
        foreach ($list as $person_id=>$data) {
            if ($dup_where) {
                $alreadyTookCourse= count(I2CE_FormStorage::search('person_scheduled_training_course', $person_id,$dup_where)) > 0;
            } else {
                $alreadyTookCourse = false;
            }
            $student_dup[$person_id]= $alreadyTookCourse;
        }

        foreach ($exams as $examid=>$data) {
            if (!is_array($data) || !array_key_exists('name',$data) || !$data['name']) {
                continue;
            }
            $selNode->appendChild($template->createElement('option',array('value'=>'exam_results_' . $examid),$data['name']));
            
            if ($passing_score) {
                $examsNode =$template->appendFileByNode('exam_summary.html','div',$list_node );
            } else {
                $examsNode =$template->appendFileByNode('exam_summary_no_score.html','div',$list_node );
            }
            if ((!$examsNode instanceof DOMNode) ||  ! ($sumNode =$template->getElementByName('exam_student_list',0,$examsNode))) {
                continue;
            }
            $template->setDisplayData('passing_score',$passing_score,$examsNode);
            $sumNode->setAttribute('id','exam_results_'. $examid);            
            $template->setDisplayData('exam_type',$data['name'],$examsNode);

            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'training_course_exam_type',
                'style'=>'equals',
                'data'=>array(
                    'value'=>'training_course_exam_type|' . $examid
                    ));

            foreach ($list as $id=>$sdata) {
                $studentNode = $template->appendFileByNode('enrolled_students.html','div',$sumNode);
                if (!$studentNode instanceof DOMNode) {
                    return false;
                }
                $studId = 'student_' . $id;
                $studentNode->setAttribute('id',$studId);
                $template->setDisplayDataImmediate('id','id=' . $id, $studentNode);
                $template->setDisplayDataImmediate('firstname',$sdata['firstname']  , $studentNode);
                $template->setDisplayDataImmediate('surname',$sdata['surname'], $studentNode);
                if ($student_dup[$id]) {
                    $studentNode->setAttribute('style','background:#FFFF66');
                    $studentNode->setAttribute('title','Student has already been enrolled in this course');
                }
                if (($remNode =  $template->getElementByName('remove_student',0,$studentNode)) instanceof DOMElement) {                
                    $js = "removeStudentByInstance(this,'" .  addslashes($sdata['person_scheduled_training_course'])  . "','" . addslashes($id)  . "','" .addslashes($studId). "');";
                    $remNode->setAttribute('onclick',$js);
                }
                
                if (($printCertif =  $template->getElementByName('person_scheduled_training_course:id',0,$studentNode)) instanceof DOMElement) {                
                    $url = 'studentcertificates?student_id='.addslashes($sdata['person_scheduled_training_course']);
                    $printCertif->setAttribute('href',$url);
                }
                
                if ($has_exam && ($examNode =  $template->getElementByName('student_exam',0,$studentNode)) instanceof DOMElement) {                
                    $score = null;
                    $scores = I2CE_FormStorage::listFields('training_course_exam',array('score'),$sdata['person_scheduled_training_course'],$where);
                    $exam_id ='0';
                    if ( count($scores) == 1) {
                        $exam_id =  'training_course_exam|' . key($scores);
                        $s_data = current($scores);
                        if (is_array($s_data) && array_key_exists('score',$s_data)) {
                            $score = $s_data['score'];
                        }
                    }
                    $template->appendFileByNode('student_exam.html','div',$examNode );
                    $finalNode = $template->getElementByName('final_exam',0,$examNode);
                    if ($finalNode) {
                        $template->setDisplayDataImmediate('final_exam',$score,$finalNode);
                        if ($passing_score) {
                            if ($score >= $passing_score) {
                                $finalNode->setAttribute('style','color:green');
                            } else {
                                $finalNode->setAttribute('style','color:red');
                            }
                        }
        
                        if (!$passing_score) {
                            $js = "changeFinalExamGrade(this,'".addslashes($examid)."','" .  addslashes($sdata['person_scheduled_training_course'])  .  "','" . addslashes($exam_id) . "',0);";
                        } else {
                            $js = "changeFinalExamGrade(this,'".addslashes($examid)."','" .  addslashes($sdata['person_scheduled_training_course'])  .  "','" . addslashes($exam_id) . "'," . $passing_score . ");";
                        }
                        $finalNode->setAttribute('onchange',$js);
                    }

                }
            }
        }
    }




    public function enrolledStudentsListCompact($node,$template) {
        if ( ! ($main_node =$template->appendFileByNode('training_course_exam_base_compact.html','div',$node )) instanceof DOMNode ) {
            return false;
        }
        if (! ($list_node = $template->getElementById('student_exams',$main_node)) instanceof DOMNode) {
            return false;
        }
        $node->appendChild($list_node);
        $list = $this->getEnrolledStudents();
        $template->addHeaderLink("mootools-core.js");
        $template->addHeaderLink("mootools-more.js");
        $template->addHeaderLink("editstudent.js");
        $has_exam = I2CE_ModuleFactory::instance()->isEnabled('training-exam'); 
        if (!$has_exam) {
            return;
        }
        $where_exam =  
            array('operator' => 'OR',
            'operand'=>array(
                array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'i2ce_hidden',
                    'style'=>'no'),
                array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'i2ce_hidden',
                    'style'=>'null')
                ));
                
        $exams = I2CE_FormStorage::listDisplayFields('training_course_exam_type',array('name'),false,$where_exam,array('name'));
        $passing_score = '';
        $passing_scores = I2CE_FormStorage::lookupField('training_course',$this->getField('training_course')->getMappedID(), array('passing_score'),false);
        if (count($passing_scores) == 1) {
            $passing_score = current($passing_scores);
            if (!is_numeric($passing_score) || $passing_score < 0 || $passing_score > 100) {
                $passing_score = '';
            }
        }
        
        $stc_dup_where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'training_course',
            'style'=>'equals',
            'data'=>array(
                'value'=> $this->getField('training_course')->getDBValue()
                ));

        
        $dup_where = array(); //this will be a search on person_scheduled_training_course
        foreach (I2CE_FormStorage::search('scheduled_training_course',false,$stc_dup_where) as $stc) {
            if ($stc == $this->getID()) {
                //don't include this course when looking for duplicate scheuling.
                continue;
            }
            $dup_where[] = 
                array(
                    'operator'=>'AND',
                    'operand'=>array(
                        0=> array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'scheduled_training_course',
                            'style'=>'equals',
                            'data'=>array(
                                'value'=>'scheduled_training_course|' .$stc
                                )),
                        1=>array(
                            'operator' => 'OR',
                            'operand'=>array(
                                0=>array(
                                    'operator'=>'FIELD_LIMIT',
                                    'field'=>'attending',
                                    'style'=>'equals',
                                    'data'=>array(
                                        'value'=>1
                                        )),
                                1=>array(
                                    'operator'=>'FIELD_LIMIT',
                                    'field'=>'attending',
                                    'style'=>'null',
                                    'data'=>array()
                                    )
                                )
                            )
                        )
                    );
        }
        foreach ($exams as $i=>$data)  {
            if (!is_array($data) || !array_key_exists('name',$data) || !$data['name']) {
                unset($exams[$i]);
                continue;
            }
        }
        
        $num_exams = count($exams);
        if ($num_exams > 0) {
            $style = "display:table-cell;width:"  . (66/$num_exams) .   '%';
        }else {
            $style = "display:table-cell";
        }

        if (($header_node = $template->getElementByID("header_row")) instanceof DOMNode) {
            foreach ($exams as $id=>$data) {
                $exam_title_node = $template->createElement("div",array('style'=>$style),$data['name']);
                $template->appendNode($exam_title_node,$header_node);
            }
        }
        foreach ($list as $id=>$sdata) {
            $studentNode = $template->appendFileByNode('enrolled_students_compact.html','div',$list_node);
            if (!$studentNode instanceof DOMNode) {
                return false;
            }
            $studId = 'student_' . $id;
            $studentNode->setAttribute('id',$studId);
            $template->setDisplayDataImmediate('id','id=' . $id, $studentNode);
            $template->setDisplayDataImmediate('firstname',$sdata['firstname']  , $studentNode);
            $template->setDisplayDataImmediate('surname',$sdata['surname'], $studentNode);

            $scores = I2CE_FormStorage::listFields('training_course_exam',array('score','training_course_exam_type'),$sdata['person_scheduled_training_course']);
            
            foreach ($exams as $examid=>$data) {
                $examsNode = $template->createElement("div",array('style'=>$style));
                $template->appendNode($examsNode,$studentNode);
                
                $template->setDisplayData('passing_score',$passing_score,$examsNode);

                $score = false;
                
                $exam_id ='0';
                foreach ($scores as $sid=>$data) {
                    if (!is_array($data) 
                        || !array_key_existS('score',$data) 
                        || !array_key_exists('training_course_exam_type',$data)
                        || $data['training_course_exam_type'] != "training_course_exam_type|" . $examid
                        )
                    {
                        continue;
                    }
                    $score =$data['score'];
                    break;
                }
                
                $template->appendFileByNode('student_exam.html','div',$examsNode );
                $finalNode = $template->getElementByName('final_exam',0,$examsNode);
                if (!$finalNode) {
                    continue;
                }
                $template->setDisplayDataImmediate('final_exam',$score,$finalNode);
                if ($passing_score) {
                    if ($score >= $passing_score) {
                        $finalNode->setAttribute('style','color:green');
                    } else {
                        $finalNode->setAttribute('style','color:red');
                    }
                }
                if (!$passing_score) {
                    $passing_score = 0;
                }
                $js = "changeFinalExamGrade(this,'".addslashes($examid)."','" .  addslashes($sdata['person_scheduled_training_course'])  .  "','" . addslashes($exam_id) . "'," . $passing_score . ");";
                $finalNode->setAttribute('onchange',$js);

            }
            $clear_node = $template->createElement("hr",array("style"=>"clear: both; max-height:0px; padding:0px; margin:0px;"));
            $template->appendNode($clear_node,$list_node);

        }
    }

    public function studentModules($node,$template) { //this really should be a fuzzy method in the training exam module
        if ( ! ($tcField = $this->getField('training_course')) instanceof I2CE_FormField_MAP
             || ! ($tcObj = $tcField->getMappedFormObject()) instanceof iHRIS_Training_Course
             || ! ($modField = $tcObj->getField('training_course_mod')) instanceof I2CE_FormField_MAP_MULT
            ) {
            I2CE::raiseError("Could not get training course modules");
            return false;
        }
        $allowed_ids = $modField->getValue();
        $allowed = array();
        foreach ($allowed_ids as $allowed_id) {
            list($form,$id) = $allowed_id;
            if ($form != 'training_course_mod') {
                continue;
            }
            $dv = I2CE_List::lookup($id,$form);
            if (!$dv) {
                contine;
            }
            $allowed[$form . '|' . $id] = $dv;
        }
        if (!is_array($allowed) || count($allowed) == 0) {
            if ( ! ($main_node =$template->appendFileByNode('training_course_nomods.html','div',$node )) instanceof DOMNode ) {
                return false;
            }
            return true;            
        }
        if ( ! ($main_node =$template->appendFileByNode('training_course_mod_base.html','div',$node )) instanceof DOMNode ) {
            return false;
        }
        if (! ($list_node = $template->getElementById('student_mods',$main_node)) instanceof DOMNode) {
            return false;
        }
        $template->addHeaderLink("editstudent.js");
        $width = (int) floor((100 / (count($allowed) + 1)));
        $rowAttrs = array('style'=>'display:table-row;');
        $colAttrs = array('style'=>'display:table-cell;width:' . $width . '%');
        $chkAttrs = $colAttrs;
        $chkAttrs['type']='checkbox';
        $list_node->appendChild($headNode =$template->createElement('div',$rowAttrs));
        $headNode->appendChild($template->createElement('div',$colAttrs,'Student'));
        foreach ($allowed as $all_id=>$all_val) {            
            $headNode->appendChild($template->createElement('div',$colAttrs,$all_val));
        }
        $list = $this->getEnrolledStudents();
        $ff = I2CE_FormFactory::instance();
        foreach ($list as $id=>$data) {
            if ( ! ($pstcObj = $ff->createContainer($data['person_scheduled_training_course'])) instanceof iHRIS_Person_Scheduled_Training_Course
                 || ! ($modField = $pstcObj->getField('training_course_mod')) instanceof I2CE_FormField_MAP_MULT
                ) {
                I2CE::raiseError("No training course module");
                continue;
            }
            $pstcObj->populate();
            $list_node->appendChild($rowNode = $template->createElement('div',$rowAttrs));
            $sAttrs = $colAttrs;
            $sAttrs['href']='view?id='.$id;
            $colNode = $template->createElement('div',$colAttrs);
            $colNode->appendChild($template->createElement('a',$sAttrs,$data['firstname'] . ' ' . $data['surname']));
            $rowNode->appendChild($colNode);
            $selected = $modField->getValue();
            foreach ($selected as &$sel) {
                $sel = implode("|",$sel);
            }
            unset($set);
            foreach ($allowed as $all_id=>$all_val) {
                $t_chkAttrs = $chkAttrs;
                if (in_array($all_id,$selected)) {
                    $t_chkAttrs['checked'] = 'checked';
                }
                $t_chkAttrs['onchange']='toggleStudentModule(this,"' . addslashes($id) . '","'.addslashes($pstcObj->getNameID()) .'","'.addslashes($all_id) . '")';
                $colNode = $template->createElement('div',$colAttrs);
                $colNode->appendChild($template->createElement('input',$t_chkAttrs));
                $rowNode->appendChild($colNode);
            }
        }
        return true;
    }




    public function prepostScores($node,$template) { //this really should be a fuzzy method in the training exam module
        // if (! (I2CE_ModuleFactory::instance()->isEnabled('training-exam'))) {
        //     return false;
        // }
        if ( ! ($main_node =$template->appendFileByNode('training_course_prepost_base.html','div',$node )) instanceof DOMNode ) {
            return false;
        }
        if (! ($list_node = $template->getElementById('student_exams',$main_node)) instanceof DOMNode) {
            return false;
        }
        $template->addHeaderLink("editstudent.js");
        $list = $this->getEnrolledStudents();
        $exam_ids = array('pretest','final');
        $exams = I2CE_FormStorage::listDisplayFields('training_course_exam_type',array('name'),false,array(),array('name'));
        foreach ($exam_ids as $e_id=>$exam_id) {
            if (!in_array($exam_id,array_keys($exams))) {
                I2CE::raiseError("Exam $exam_id not found in " . implode(",",array_keys($exams)));
                unset($exam_ids[$e_id]);
            }
        }
        $passing_score = '';
        $passing_scores = I2CE_FormStorage::lookupField('training_course',$this->getField('training_course')->getMappedID(), array('passing_score'),false);
        if (count($passing_scores) == 1) {
            $passing_score = current($passing_scores);
            if (!is_numeric($passing_score) || $passing_score < 0 || $passing_score > 100) {
                $passing_score = '';
            }
        }
        if ($passing_score) {
            $examsNode =$template->appendFileByNode('exam_summary_prepost.html','div',$list_node );
        } else {
            $examsNode =$template->appendFileByNode('exam_summary_prepost_no_score.html','div',$list_node );
        }
        if ((!$examsNode instanceof DOMNode) ||  ! ($sumNode =$template->getElementByName('exam_student_list',0,$examsNode))) {
            I2CE::raiseError("Couldnt get exam_student_list");
            return false;
        }
        foreach ($exam_ids as $examid) {
            $data = $exams[$examid];
            if (!is_array($data) || !array_key_exists('name',$data) || !$data['name']) {
                continue;
            }
            $template->setDisplayData('passing_score',$passing_score,$examsNode);
            $sumNode->setAttribute('id','exam_results_'. $examid);            
            $template->setDisplayData('exam_type_' . $examid,$data['name'],$examsNode);
        }
        $ff = I2CE_FormFactory::instance();
        $do_date = false;
        if ( ($tcField = $this->getField('training_course')) instanceof I2CE_FormField_MAP
             && ($tcObj = $tcField->getMappedFormObject()) instanceof iHRIS_TrainingCourse
             && ($doCert = $tcObj->getField('has_certification_date')) instanceof I2CE_FormField_YESNO
             && $doCert->getValue()
            ) {
            $do_date =true;
        }
        if ($do_date) {
            $template->setDisplayDataImmediate('has_certification_date',1,$examsNode);
        } else {
            $template->setDisplayDataImmediate('has_certification_date',0,$examsNode);
        }
        foreach ($list as $id=>$data) {
            $studentNode = $template->appendFileByNode('students_prepost.html','div',$list_node);
            if (!$studentNode instanceof DOMNode) {
                continue;
            }
            $studId = 'student_' . $id;
            $studentNode->setAttribute('id',$studId);
            $template->setDisplayDataImmediate('id','id=' . $id, $studentNode);
            $template->setDisplayDataImmediate('firstname',$data['firstname']  , $studentNode);
            $template->setDisplayDataImmediate('surname',$data['surname'], $studentNode);
            foreach ($exam_ids as $examid) {
                $where = array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'training_course_exam_type',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>'training_course_exam_type|' . $examid
                        ));               
                $score = null;
                $scores = I2CE_FormStorage::listFields('training_course_exam',array('score'),$data['person_scheduled_training_course'],$where);
                $exam_id ='0';
                if ( count($scores) == 1) {
                    $exam_id =  'training_course_exam|' . key($scores);
                    $s_data = current($scores);
                    if (is_array($s_data) && array_key_exists('score',$s_data)) {
                        $score = $s_data['score'];
                    }
                }
                if ( ! ($finalNode = $template->getElementByName('student_exam_'  . $examid,0,$studentNode)) instanceof DOMNode) {
                    continue;
                }
                $template->setDisplayDataImmediate('student_exam_' . $examid,$score,$finalNode);
                if ($passing_score) {
                    if ($score >= $passing_score) {
                        $finalNode->setAttribute('style','color:green');
                    } else {
                        $finalNode->setAttribute('style','color:red');
                    }
                }
            }            
            if (!$do_date) {
                continue;
            }
            //now handle certifacation date
            if ( ! ($pstcObj = $ff->createContainer($data['person_scheduled_training_course'])) instanceof iHRIS_Person_Scheduled_Training_Course
                 || ! ($certField = $pstcObj->getField('certification_date')) instanceof I2CE_FormField_DATE_YMD
                 || ! ($certNode = $template->getElementByName('certification_date',0,$studentNode)) instanceof DOMElement
                ) {
                //echo get_class($pstcObj); echo get_class($certField); echo get_class($certNode);
                continue;
            }
            $certNode->setAttribute('onchange','setCertification(this,"' . addslashes($id) .'","' .addslashes($pstcObj->getNameID()) . '", this.get("value"));');
            $pstcObj->populate();
            $certField->processDOMEditable($certNode,$template,$certNode);
            
        }
        return true;
    }

    public function numberEnrolledStudents($node,$template) {
        $node->appendChild(
            $template->createTextNode(count ($this->getScheduledStudentIds()))
            );
    }

    public function getEnrolledStudents() {
        if ($this->id == 0 ) {
            return array();
        }
        $where = array(
            'operator' => 'AND',
            'operand'=>array(
                0=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'scheduled_training_course',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>$this->getNameId()
                        )),
                1=> array(
                
                    'operator' => 'OR',
                    'operand'=>array(
                        0=>array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'attending',
                            'style'=>'equals',
                            'data'=>array(
                                'value'=>1
                                )),
                        1=>array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'attending',
                            'style'=>'null',
                            'data'=>array()
                            )
                        )
                    )
                )
            );
        $pstcs =  I2CE_FormStorage::listFields('person_scheduled_training_course' , array(),true,$where);
        $list = array();
        foreach ($pstcs as $pstc_id => $pstc) {
            if (strpos($pstc['parent'],'|')===false) {
                continue;
            }
            list($parent,$id) = explode('|',$pstc['parent'],2);
            if ($parent != 'person') {
                continue;
            }            
            $list[$pstc['parent']] = I2CE_FormStorage::lookupField('person',$id,array('surname','firstname'),false);
            $list[$pstc['parent']]['person_scheduled_training_course'] = 'person_scheduled_training_course|' . $pstc_id;
        }
        uasort($list,array($this,'surnameSort'));
        return $list;
    }
    

    public function surnameSort($a,$b) {
        if (!is_array($a) || !array_key_exists('surname',$a) || !array_key_exists('firstname',$a)) {
            return 1;
            if (!is_array($b) || !array_key_exists('surname',$b) || !array_key_exists('firstname',$b)) {
                return 0;
            } else {
                return -1;
            }
        }  else  if (!is_array($b) || !array_key_exists('surname',$b) || !array_key_exists('firstname',$b)) {
            return -1;
        }
        $cmp = strnatcasecmp( $a['surname'] , $b['surname']);        
        if ($cmp == 0) {
            strnatcasecmp( $a['firstname'] , $b['firstname']);
        }else {
            return $cmp;
        }
    }


    public function getScheduledStudentIds() {
        if ($this->id == 0 ) {
            return array();
        }
        $where = array(
            'operator' => 'AND',
            'operand'=>array(
                0=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'scheduled_training_course',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>$this->getNameId()
                        )),
                1=> array(
                
                    'operator' => 'OR',
                    'operand'=>array(
                        0=>array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'attending',
                            'style'=>'equals',
                            'data'=>array(
                                'value'=>1
                                )),
                        1=>array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'attending',
                            'style'=>'null',
                            'data'=>array()
                            )
                        )
                    )
                )
            );
        return I2CE_FormStorage::search('person_scheduled_training_course' ,false,$where);
    }
    
    public function enrolledStudents($node,$template,$args) {
        $node->appendChild($template->createTextNode(count($this->getEnrolledStudents())));
    }




    /**
     * Return the list of scheduled course for the given course id.
     * @param integer $course_id.  Defaults to zero meaning we get all courses
     * @param boolean $flat.  defaults to false
     * @return array the keys are the id of the scheduled course, the values are the string "$start_date -- $end_date"
     */
    public static function getScheduledCourses($course_id =0, $flat = false) {
        if ($course_id > 0) {
            $flat =true;
        }
        $values = array();
        foreach (array('start_date','end_date') as $field) {
            $data = I2CE_FormField::getFormFieldIdAndType('scheduled_training_course',$field);
            if (!is_array($data)) {
                I2CE::raiseError("Could not available courses b/c could not find field $field in form scheduled_training_course");
                return array();
            }
            $values[] = $data['id'];
        }
        $query = "SELECT le_start_date.record AS id, le_start_date.date_value AS start_date, le_end_date.date_value AS end_date,  r.parent AS parent ";
        $query.= "FROM last_entry le_start_date ";
        $query.= "JOIN last_entry le_end_date ON le_start_date.record = le_end_date.record ";
        $query .= "JOIN record r ON le_start_date.record = r.id ";
        $query.= "WHERE le_start_date.form_field = ? ";
        $query.= "AND le_end_date.form_field = ? ";
        if ($course_id > 0) {
            $query .= "AND r.parent = ? ";
            $values[] = $course_id;
        }
        $query .= "ORDER BY le_start_date.date_value DESC, le_end_date.date_value ASC";
        $db = I2CE::PDO();
        try {
            $results = $db->prepare($query);
            $results->execute($values);
            $scheduled_courses = array();

            while ( $result = $results->fetch() ) {
                $start_date = I2CE_Date::fromDB($result->start_date);
                $end_date = I2CE_Date::fromDB($result->end_date);
                if ($flat) {
                    $scheduled_courses[$result->id] = $start_date->displayDate() . " - " . $end_date->displayDate();
                } else {
                    $scheduled_courses[$result->parent][$result->id] = $start_date->displayDate() . " - " . $end_date->displayDate();
                }
            }
            $results->closeCursor();
            unset( $results );
            return $scheduled_courses;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Could not get available courses");
            return array();
        }
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
