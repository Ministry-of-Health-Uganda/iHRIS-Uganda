<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 3/19/13
 * Time: 9:50 AM
 * To change this template use File | Settings | File Templates.
 */
class iHRIS_PageFormDataSet extends I2CE_PageForm
{
    protected function loadObjects() {
        $factory = I2CE_FormFactory::instance();
        if ($this->isPost()) {
            $dataset = $factory->createContainer('dataset');
            if (!$dataset instanceof iHRIS_DataSet) {
                I2CE::raiseError("Could not create Data Set form");
                return;
            }
            $dataset->load($this->post);
            //$surname_ignore = $person->getField('surname_ignore');
            //$ignore_path = array('forms','person',$person->getID(),'ignore','surname');
//            if ($surname_ignore instanceof I2CE_FormField && $this->post_exists($ignore_path)) {
//                $surname_ignore->setFromPost($this->post($ignore_path));
//            }
        } else {
            if ($this->get_exists('id')) {
                $id = $this->get('id');
                if (strpos($id,'|')=== false) {
                    I2CE::raiseError("Depcreated use of id variable");
                    $id = 'dataset|' . $id;
                }
            } else {
                $id = 'dataset|0';
            }
            $dataset = $factory->createContainer($id);
            if (!$dataset instanceof iHRIS_DataSet) {
                I2CE::raiseError("Could not create valid Data Set form from id:$id");
                return;
            }
            $dataset->populate();
            $dataset->load($this->request());
        }
        $this->setObject( $dataset);
    }

    protected function save() {
        parent::save();
        /*$message = "This record has been saved.";
        I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_save" );
        $this->userMessage($message);
        $this->setRedirect(  "view?id=" . $this->getPrimary()->getNameId() );*/
        $this->setRedirect(  "view_list?type=dataset&id=" . $this->getPrimary()->getNameId() );
    }

    protected  function displayControls( $save = false, $show_edit = true ) {
        if ( $save ) {
            parent::displayControls( $save, $show_edit );
        }  else {
            $this->template->addFile( 'button_confirm_notchild.html' );
        }
    }

}
