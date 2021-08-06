<?php

class iHRIS_Module_Training extends I2CE_Module {


    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_training' => 'action_person_training'
            );
    }

    /**
     * Return the array of hooks available in this module.
     * @return array
     **/
    public static function getHooks() {
        return array(
                'validate_form_person_training' => 'validate_form_person_training',
                );
    }

     public function action_person_training( $page ) {
        if ( !$page instanceof iHRIS_PageView ) {
            I2CE::raiseError("invalid call");
            return false;
        }
        return $page->addLastChildForm( 'person_training', 'end_date' );
    }


    /**
     * Checks to make sure the end of applicability is after the start of applicability.
     * @param I2CE_Form $form
     **/
     
    public function validate_form_person_training( $form ) {
        if ( $form->start_date->isValid() && $form->end_date->isValid() ) {
            if ( $form->start_date->compare( $form->end_date ) < 0 ) {
                $form->getField( 'end_date' )->setInvalid( 'The end date must be after the start date.' );
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
