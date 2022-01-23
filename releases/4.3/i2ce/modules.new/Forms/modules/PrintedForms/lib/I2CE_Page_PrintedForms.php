<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage I2CE
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.4
* @since v4.0.4
* @filesource 
*/ 
/** 
* Class I2CE_Page_PrintedForms
* 
* @access public
*/


class I2CE_Page_PrintedForms extends I2CE_Page{

    /**
     * Perform page actions 
     * 
     * @returns boolean.  true on success
     */    
    protected function action() {
        parent::action();
        if (!$this->hasPermission('task(printed_forms_can_access)')) {
            $this->userMessage("You do not have permission to view the requested page");
            $this->redirect("home");
            return false;
        }
        if ($this->request_exists('id')  && ($id = $this->request('id'))) {
            $ids = array($this->request('id'));
        } else if ($this->request_exists('ids')) {
            $ids =  $this->request('ids');
            if (!is_array($ids)) {
                $ids = array();
            }
        } else {
            $ids = array();
        }
        if (count($ids) == 0) {
            return false;
        }
        $std_form = false;
        if (count($this->request_remainder) > 0) {
            reset($this->request_remainder);
            $std_form = current($this->request_remainder);
        }
        switch ($this->page) {
        case 'print':
            if ($std_form === false || !$this->action_print($ids,$std_form)) {
                $this->userMessage("Could not print document $std_form");
            }
            return true;
        case 'archive':            
            $mf = I2CE_ModuleFactory::instance();
            if (!$this->hasPermission("task(printed_forms_create_all_archives)") || !$mf->isEnabled('BinField') || $std_form === false || ! $this->action_archive($ids,$std_form)) {
                $this->userMessage('Could not archive document');
            }
            $this->setRedirect("PrintedForms/menu?" . http_build_query(array('ids'=>$ids))); 
            return true;
            //break is intentionally not here. we now fall through to the menu.
        case 'home':
        case 'menu':
            return $this->action_menu($ids);
        default:
            //do nothing;
        }
        return false;
    }

    /**
     * Perform menu action 
     * 
     * @param array $ids of string
     * @returns boolean.  true on success
     */    
    protected function action_menu($ids) {
        reset($ids);
        list($form,$junk) = explode('|',current($ids),2);
        $form = trim($form);
        $valid = I2CE_Module_PrintedForms::getValidPrintedForms($form);
        $mf = I2CE_ModuleFactory::instance();
        if (count($valid) == 0 || !$this->hasPermission("task(printed_forms_all_generate)")) {
            $this->template->setDisplayDataImmediate('has_valid_forms',0);
        } else {
            $archive = $mf->isEnabled('BinField') && $this->hasPermission("task(printed_forms_create_all_archives)");
            $this->template->setDisplayDataImmediate('has_valid_forms',1);
            $append_node = $this->template->getElementById('printed_forms_list');
            if (!$append_node instanceof DOMNode) {
                I2CE::raiseError("Could no find printed_forms_list");
                return false;
            }
            $add_node = $this->template->loadFile('printed_forms_menu_each.html','li'); 
            if (!$add_node instanceof DOMElement) {
                I2CE::raiseError("Bad 'printed_forms_menu_each.html'");
                return true;
            } else {
                foreach ($valid as $pf => $data) {
                    $node = $add_node->cloneNode(true);
                    $this->template->appendNode($node,$append_node);
                    $link = "PrintedForms/print/$pf?" . http_build_query(array('ids'=>$ids));
                    $archive_form = false;
                    if ($archive && $data['archive']) {
                        $archive_link = "PrintedForms/archive/$pf?" . http_build_query(array('ids'=>$ids));
                    } else {
                        $archive_link = '';
                    }
                    $this->template->setDisplayDataImmediate('standard_form_display_name',$data['displayName'],$node);
                    $this->template->setDisplayDataImmediate('standard_form_link',$link,$node);
                    $this->template->setDisplayDataImmediate('standard_form_archive_link',$archive_link,$node);
                }
            }
        }
        $archive = $mf->isEnabled('BinField') && $this->hasPermission("task(printed_forms_view_all_archives)");
        if (count($ids) != 1 || !$archive || !$this->hasPermission("task(printed_forms_view_all_archives)") ) {
            $this->template->setDisplayDataImmediate('show_archive',0);
            return true;
        }
        $this->template->setDisplayDataImmediate('show_archive',1);
        $archives = I2CE_Module_PrintedForms::getValidArchivedForms($form);
        reset($ids);
        $id = current($ids);
        $ff = I2CE_FormFactory::instance();
        $docData= I2CE_FormStorage::listDisplayFields('generated_doc', array('date','description'), $id, array(),array('date')); //gets the generated docs which have this id as a parent
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'primary_form',
            'style'=>'equals',
            'data'=>array(
                'value'=>$id
                )
            );
        $docDataPrim = I2CE_FormStorage::listDisplayFields('generated_doc', array('date','description'), false, $where,array('date')); //gets the generated docs which have this id as a parent
        $docData = $docData +$docDataPrim;
        if (count($docData) == 0) {
            $this->template->setDisplayData('has_archives',0);
            return true;
        }
        $this->template->setDisplayData('has_archives',1);
        $append_node = $this->template->getElementById('printed_forms_archive');
        if (!$append_node instanceof DOMNode) {
            I2CE::raiseError("Could no find printed_forms_archive");
            return false;
        }
        $add_node = $this->template->loadFile('printed_forms_menu_archive_each.html','li'); 
        if (!$add_node instanceof DOMElement) {
            I2CE::raiseError("Bad 'printed_forms_menu_archive each.html'");
            return true;
        }
        foreach ($docData as $id=>$data) {
            $node = $add_node->cloneNode(true);
            $this->template->appendNode($node,$append_node);
            $link = I2CE_FormField_BINARY_FILE::getFieldLink("generated_doc|" . $id,'document');
            $this->template->setDisplayDataImmediate('view_archive_link',$link,$node);
            $this->template->setDisplayData('gen_date',$data['date'],$node);
            $this->template->setDisplayData('gen_desc',$data['description'],$node);
        }
        return true;
    }

    /**
     * Perform rendering action 
     * 
     * @param array $ids of string
     * @param string $std_form
     * @returns boolean.  true on success
     */    
    protected function action_print($ids,$std_form) {
        $render = 'I2CE_PrintedForm_Render_PDF';
        if ( I2CE::getConfig()->setIfIsSet($t_render,"/modules/PrintedForms/forms/$std_form/render")) {
            $render = 'I2CE_PrintedForm_Render_' .      $t_render ;
        }
        if ($this->request_exists('render')) {
            $render = 'I2CE_PrintedForm_Render_' . $this->request('render');
        }
        if (!class_exists($render) || !is_subclass_of($render,'I2CE_PrintedForm_Render')) { 
            I2CE::raiseError("Bad renderer $render");
            return false;
        }
        $renderObj = new $render($std_form,$ids);
        if (!$renderObj->render()) {
            $this->userMessage("Error generating form");
            return false;
        }
        return $renderObj->display();
    }


    /**
     * Perform archiving action
     * 
     * @param array $ids of string
     * @param string $std_form
     * @returns boolean.  true on success
     */    
    protected function action_archive($ids,$std_form) {
        if (!is_string($std_form) || strlen($std_form) == false) {
            I2CE::raiseError("invalid form to archive");
            return false;
        }
        $parentForm = false;
        I2CE::getConfig()->setIfIsSet($parentForm,"/modules/PrintedForms/forms/$std_form/archive");
        $dn = $std_form;
        I2CE::getConfig()->setIfIsSet($dn,"/modules/PrintedForms/forms/$std_form/displayName");
        if (!$parentForm) {
            I2CE::raiseError("No archive form set for $std_form");
            return false;
        }
        $render = 'I2CE_PrintedForm_Render_PDF';
        $t_render = false;
        if ( I2CE::getConfig()->setIfIsSet($t_render,"/modules/PrintedForms/forms/$std_form/render")) {
            $render = 'I2CE_PrintedForm_Render_' .      $t_render ;
        }
        if ($this->request_exists('render')) {
            $render = 'I2CE_PrintedForm_Render_' . $this->request('render');
        }
        if (!class_exists($render) || !is_subclass_of($render,'I2CE_PrintedForm_Render')) { 
            I2CE::raiseError("Bad renderer $render");
            return false;
        }
        $ff = I2CE_FormFactory::instance();        
        foreach ($ids as $id) {
            $renderObj = new $render($std_form,array($id));
            if (!$renderObj->render()) {
                I2CE::raiseError("Render error");
                $this->userMessage("Error generating form");
                return false;
            }
            $doc =  $renderObj->display(true);
            if (!$doc) {
                I2CE::raiseError("Could not archive $std_form for id $id");
                continue;
            }
            $data = $renderObj->getFormData($id);
            if (!is_array($data) || !array_key_exists($parentForm,$data) || !$data[$parentForm] instanceof I2CE_Form || $data[$parentForm]->getId() == '0')  {
                I2CE::raiseError("Could not determine parent form for $std_form on $id");
                continue;
            }            
            $genDocObj = $ff->createContainer('generated_doc');
            if (!$genDocObj instanceof I2CE_GeneratedDoc) {
                I2CE::raiseError("Could not instantiate generated_doc form");
                return false;
            }
            $docField = $genDocObj->getField('document');
            if (!$docField instanceof I2CE_FormField_BINARY_FILE) {
                I2CE::raiseError("document field is bad:" . get_class($docField));
                return false;
            }
            I2CE::raiseError("Rendered: " . $renderObj->getMimeType());
            $docField->setFromData($doc,$renderObj->getFileName(), $renderObj->getMimeType());
            $genDocObj->description = $dn;
            $genDocObj->primary_form = $id;
            $genDocObj->setParent($data[$parentForm]->getNameId());
            $genDocObj->save($this->user);            
            I2CE::raiseError("Saved" . $genDocObj->getNameID());
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
