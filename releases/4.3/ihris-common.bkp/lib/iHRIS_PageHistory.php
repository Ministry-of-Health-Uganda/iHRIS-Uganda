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
 * The page class for displaying the history of a form associated with a person's record.
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */


/**
 * The page class for displaying the history of a form associated with a person's record.
 * @package iHRIS
 * @subpackage Common
 * @access public
 */
class iHRIS_PageHistory extends I2CE_Page {
        
    /**
     * @var I2CE_Form The history object being displayed by this page.
     */
    protected $history;
        
    /**
     * Return a prefix to be used for templates associated with this page.
     * @return string
     */
    protected function getPrefix() { return ""; }

    /**
     * Load the HTML template files for viewing an object's history.
     */
    protected function loadHTMLTemplates() {
        list($formName,$formID) = explode("|", $this->get('id'));
        if ($formName == 'person') {
            parent::loadHTMLTemplates();
        } else {
            $this->template->addFile( "history_" .$formName . ".html");
        }
        $this->template->appendFileById( "menu_view_" . $this->getPrefix() . "link.html", "li", "navBarUL", true );
    }
        
    /**
     * Load the history object for this page.
     */
    protected function loadObjects() {
        $factory = I2CE_FormFactory::instance();
        $this->history = $factory->createContainer($this->get('id') );
        $this->history->populate();
    }
        
    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();
        $this->loadObjects();
        $orderBy = array();
        $form = $this->get('form');
        $config = I2CE::getConfig();
        $title = "";
        $config->setIfIsSet($title,"/modules/forms/forms/$form/display");
        if ( $this->get_exists('order_by') ) {
            $orders = $this->get('order_by');
            if (is_array($orders) && array_key_exists($form,$orders) && is_array($orders[$form])) {
                $orderBy[$form]  = array_keys($orders[$form]);
            }
        }
        $this->history->populateChildren( $form , $orderBy);

                
        $this->template->setForm( $this->history );
        $this->template->setDisplayData( "history_header", $title . " History" );
        
        if ( count( $this->history->children ) > 0 ) {
            foreach( $this->history->children as $form => $list ) {
                foreach( $list as $i => $obj ) {
                    if ( $i == 0 ) {}//$this->template->setDisplayData( "history_header", $title . " History" );
                    else $this->template->appendFileById( "hr.html", "hr", "history" );
                    $node = $this->template->appendFileById( "view_" . $this->getPrefix() . $form . ".html", "div", "history" );
                    $this->template->setForm( $obj, $node );
                }
            }
            /**
             * Since each view_form template includes editing information it can be stripped out
             * for the historical view.
             */
            if (!$this->get_exists('show_edit')) {
                $this->template->findAndRemoveNodes( "//div[@class='editRecord']" );
            }
            $this->template->findAndRemoveNodes( "//span[@history='false']" );
        }
    }
        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
