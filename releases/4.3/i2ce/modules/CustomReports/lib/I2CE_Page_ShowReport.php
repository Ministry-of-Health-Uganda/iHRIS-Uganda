<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
*  I2CE_Page_ReportRelationship
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Page_ShowReport extends I2CE_Page implements I2CE_ShowReport_Interface{
    
    


    /**
     * Perform any command line actions for the page
     *
     * The business method if this page is called from the commmand line
     * @param array $request_remainder the remainder of the request after the page specfication.  
     * @param array $args the array of unix style command line arguments      
     * @returns boolean.  true on success
     */ 
    public function actionCommandLine($args, $request_remainder) {
        //parent::actionCommandLine( $args, $request_remainder );        
        if (count($this->request_remainder) == 0) {
            $msg = "You need to specify a report view";
            $this->userMessage($msg);
            I2CE::raiseError($msg);
            return false;
        }
        reset($this->request_remainder);
        $view = current($this->request_remainder);

        if (($this->page ==='flash_data')) {
            $this->get('flash_data', true);
            //$this->actionView();
            $view = array_shift($this->request_remainder);
            $displayObj = new I2CE_CustomReport_Display_PieChart($this,$view);                
            $displayObj->display(null);
            return true; 
        } else {
            //$this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='CustomReports/view/reportViews']" );
            return $this->actionShow( $view );
        }
    }

    /**
     * Set the active menu
     */
    protected function setActiveMenu() {
        $this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='CustomReports/view/reportViews']" );
    }


    


    /**
     * Perform any actions for the page
     * 
     * @returns boolean.  true on sucess
     */
    public function action() {
        parent::action();
        if (count($this->request_remainder) == 0) {
            $msg = "You need to specify a report view";
            $this->userMessage($msg);
            I2CE::raiseError($msg);
            return false;
        }
        reset($this->request_remainder);
        $view = current($this->request_remainder);
        if (!$this->canViewReport($view)) {
            $msg ="You do not have permission to  view this reportview ($view)";
            $this->userMessage($msg);
            I2CE::raiseError($msg);
            $this->setRedirect('noaccess');
            return false;                                
        }

        if ($this->page === 'saveOptions' && $this->hasPermission('task(custom_reports_can_edit_reportViews)'))  {            
            if (!$this->actionSaveOptions($view)) {
                $this->userMessage('Could not save display options','notice',false); //message is not delayed as we are showing below
            } else {
                $this->userMessage('Saved display options','notice',false);  //message is not delayed as we are showing below
            }
        }
        return $this->actionShow($view);
    }


    /**
     *Check to ensure we can view the indicated report
     * @param string $view
     * @returns boolean
     */
    protected function canViewReport($view) {
        if (!I2CE_MagicDataNode::checkKey($view) || !$this->hasPermission('task(custom_reports_can_access)')) {
            return false;
        }
        $config = I2CE::getConfig()->modules->CustomReports;
        if ($config->is_scalar("reportViews/$view/limit_view_to") && $config->reportViews->$view->limit_view_to) {
            if (!$this->hasPermission(' task(custom_reports_admin) or ' . $config->reportViews->$view->limit_view_to)) {
                return false;
            }
        }
        return true;
    }


    /**
     *Save display options for the indicated report view
     *@param string $view
     *@returns boolean
     */
    protected function actionSaveOptions($view) {
        $displayObj = $this->getDisplay($view);
        if (!$displayObj) {
            return false;
        }
        return $displayObj->saveDisplayOptions();
    }



    /**
     *Determine all the allowed for the indicated report view
     * @param string $view
     *@returns array of string. 
     */
    public function getAllowedDisplays($view) {
        return I2CE::getConfig()->getKeys("/modules/CustomReports/displays");         
    }

    /**
     *Determine the desired displays for the indicated report view
     * @param string $view
     *@returns array of string. 
     */
    public function getDesiredDisplays($view) {
        $config = I2CE::getConfig()->modules->CustomReports;
        $displays = array(); //a list in increasing priority of displays we will try to fall back on.
        if (count($this->request_remainder) > 1) {
            $displays[] =   $this->request_remainder[1];
        }
        if (I2CE_MagicDataNode::checkKey($view) && $config->is_scalar("reportViews/$view/default_display") && $config->reportViews->$view->default_display) {
            $displays[] = $config->reportViews->$view->default_display;
        }        
        if (isset($config->default_display) && is_string($config->default_display) && strlen($config->default_display) > 0) {
            $displays[] = $config->default_display;
        }
        return array_unique($displays);
    }


    /**
     * @var protected array $displays of I2CE_CustomReportDisplay
     */
    protected $displays = array();

    /**
     *Try to instantiate display object
     * @param string $display
     * @param string $view
     * @returns mixed.  false on failture I2CE_CustomReport_Display on succcess
     */
    public function instantiateDisplay($display,$view) {
        if (!array_key_exists($display,$this->displays)) {
            $config = I2CE::getConfig()->modules->CustomReports;
            if (!isset($config->displays->$display) || !isset($config->displays->$display->class))  {
                $this->displays[$display] = false;
                return false;
            }
            $displayClass = $config->displays->$display->class;
            if (!class_exists($displayClass) || !is_subclass_of($displayClass,'I2CE_CustomReport_Display')) {
                $this->displays[$display] = false;
                return false;
            }
            try {
                $displayObj = new $displayClass($this,$view);                
            }
            catch (Exception $e) {
                $msg = $e->getMessage();
                I2CE::raiseError($msg);
                $this->userMessage($msg);
                $displayObj = null;
            }
            if (!$displayObj instanceof I2CE_CustomReport_Display) {
                $displayObj = false;
            }
            $this->displays[$display] = $displayObj;
        }
        return $this->displays[$display];        
    }

    /**
     * Gets the dislpay object for the indicated report view
     * @param string $view
     * @returns mixed.  false on failture I2CE_CustomReport_Display on succcess
     */
    public function getDisplay($view) {        
        // expected format for URL is CustomReports/show/reportView(/display)
        if ( !is_array($displays = $this->getDesiredDisplays($view))) {
            $displays = array();
        }
        if (!in_array('Default',$displays)) {
            $displays[]= 'Default';       
        }
        $displayObj = null;
        $msg ='';
        while (count($displays) > 0 && !$displayObj instanceof I2CE_CustomReport_Display) {
            $display = array_shift($displays);
            $displayObj = $this->instantiateDisplay($display,$view);
        }        
        if (!$displayObj instanceof I2CE_CustomReport_Display) {
            $msg = "Could not find any valid displays for the report view $view: " . $msg;
            I2CE::raiseError($msg);
            $this->userMessage($msg);
            return false;
        }
        return $displayObj;
    }

    

    /**
     *Show the indicated report view
     * $param string  $view
     */
    protected function actionShow($view) {        

        $displayObj = $this->getDisplay($view);
        if (!$displayObj) {
            $this->setRedirect('CustomReports/nodisplay');
            return false;
        }
        $contentNode = null;
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            $this->template->loadRootText("<span id='siteContent'/>");
        }
        $contentNode = $this->template->getElementById('siteContent');
        if (!$contentNode instanceof DOMNode) {
            $this->setRedirect('CustomReports/view/reportViews');
            return false;
        }
        //we are good to go at this point.
        $this->template->addHeaderLink("CustomReports.css");
        $this->template->addHeaderLink("CustomReports_iehacks.css", array('ie6' => true ));
        $this->template->setDisplayData( "limit_description", false );

        if (! $displayObj->display($contentNode)) {
            return false;
        }
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            echo $this->template->getDisplay($view);
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
