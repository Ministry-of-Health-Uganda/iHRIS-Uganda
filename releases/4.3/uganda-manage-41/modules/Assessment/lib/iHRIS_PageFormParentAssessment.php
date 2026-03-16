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
 * @subpackage Manage Assessment
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2014, 2015 IntraHealth International, Inc. 
 * @since v4.1.10
 * @version v4.1.10
 */

/**
 * The page class for displaying an inspection child form
 * @package iHRIS
 * @subpackage Manage Assessment
 * @access public
 */
class iHRIS_PageFormParentAssessment extends I2CE_PageFormParent { 

    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->addHeaderLink("assessment.css");
    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
