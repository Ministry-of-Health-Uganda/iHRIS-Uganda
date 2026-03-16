<?php
/*
 * © Copyright 2014,2015 IntraHealth International, Inc.
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
 * View a person's record.
 * @package iHRIS
 * @subpackage Train DES
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2014, 2015 IntraHealth International, Inc. 
 * @since v4.1.10
 * @version v4.1.10
 */

/**
 * The page class for displaying an assessment
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageViewAssessment extends I2CE_PageViewChildren{ 


   protected $factory;
    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->addHeaderLink("view.js");
        $this->template->addHeaderLink("assessment.css");

        
       $factory = I2CE_FormFactory::instance();
       $assessment = $factory->createContainer( $this->get('id') );
       $assessment->populate();
       $person = $factory->createContainer($assessment->getParent() );
       $person->populate();
       $this->template->setForm( $assessment );
       $this->template->setForm( $person );

        
   }

    protected function getViewChildMethod($parentForm,$childForm) {
        if ($parentForm == 'assessment') {
            return 'action_' . $childForm;
        } else {
            return parent::getViewChildMethod($parentForm,$childForm);
        }
    }

    protected function getViewChildTemplate($parentForm,$childForm) {
        I2CE::raiseMessage("getting template $parentForm $childForm");
        if ($parentForm == 'assessment') {
            return 'view_ass_' . $childForm . '.html';
        } else {
            return parent::getViewChildTemplate($parentForm,$childForm);
        }
    }

    protected function getChildTemplate($form) {
        return 'view_ass_' . $form . '.html';
    }

    
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
