<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
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
 * @subpackage Qualify
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the a person's record.
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 */
class iHRIS_PageView extends I2CE_PageViewChildren{ 

    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->addHeaderLink("view.js");
        $this->template->appendFileById( "menu_view.html", "li", "navBarUL", true );
        $this->template->appendFileById( "menu_view_person.html", "ul", "menuView" );
    }

    /**
     * creates and populages a person object based on reuqest variables
     * @returns mixed.  iHRIS_Person on succes. false on failure
     */
    protected function loadPerson() {
        return $this->loadPrimaryObject();
    }
    
    protected function getViewChildMethod($parentForm,$childForm) {
        if ($parentForm == 'person') {
            return 'action_' . $childForm;
        } else {
            return parent::getViewChildMethod($parentForm,$childForm);
        }
    }

    protected function getViewChildTemplate($parentForm,$childForm) {
        if ($parentForm == 'person') {
            return 'view_' . $childForm . '.html';
        } else {
            return parent::getViewChildTemplate($parentForm,$childForm);
        }
    }

    
    public function getPerson() {
        return $this->person;
    }

  }



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
