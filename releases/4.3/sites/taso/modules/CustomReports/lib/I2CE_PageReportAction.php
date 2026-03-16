<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
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
 * The page class for working with report actions.
 * @package I2CE
 * @subpackage Core
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.3
 * @version v4.1.3
 */

/**
 * The page class for working with report actions.
 * @package I2CE
 * @subpackage Core
 * @access public
 */
abstract class I2CE_PageReportAction extends I2CE_Page { 

    /**
     * @var I2CE_CustomReport_Display_Default_Action The display for this page.
     */
    protected $display;

    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();
        $required_args = array( 'report_view', 'action_header', 'action_fields', 
                'action_method', 'action_script' );
        foreach( $required_args as $required ) {
            if ( !array_key_exists( $required, $this->args ) ) {
                I2CE::raiseError( "A $required must be defined in the page args for I2CE_PageReportAction pages.");
                return false;
            }
        }
        return true;
    }

    /**
     * Create the report display and add it to the page.
     * @param string $query The query string to pass to the action for applying limits.
     * @return boolean
     */
    protected function actionReport( $query='' ) {
        try {
            $this->display = new I2CE_CustomReport_Display_DefaultAction( $this, $this->args['report_view'] );
        } catch (Exception $e) {
            I2CE::raiseError("Could not get for " . $this->args['report_view'] . "\n" . $e);
            return false;

        }
        $this->template->addHeaderLink( $this->args['action_script'] );



        $this->template->addHeaderLink("CustomReports.css");
        $this->template->addHeaderLink("CustomReports_iehacks.css", array('ie6' => true));
        $this->template->setDisplayData( "limit_description", false );

        $contentNode = $this->template->getElementById("siteContent");
        if ( !$contentNode instanceof DOMNode || !$this->display->display( $contentNode ) ) {
            I2CE::raiseError( "Couldn't display report.  Either no content node or an error occurred displaying the report." );
            return false;
        }

        $reportLimitsNode = $this->template->getElementById('report_limits');
        if ( !$reportLimitsNode instanceof DOMNode ) {
            I2CE::raiseError("Unable to find report_limits node.");
        } else {
            $applyNode = $this->template->appendFileByNode(
                    "customReports_display_limit_apply_Default.html", "tr", 
                    $reportLimitsNode );
            $form = $this->template->query( ".//*[@id='limit_form']", $contentNode );
            if ( $form->length == 1 ) {
                $form = $form->item(0)->setAttribute('action', $this->page() . "?$query");
            }
        }

        return true;
    }

    /**
     * Return the action node for this page.
     * By default it will create an anchor tag with an onClick method to pass to
     * JavaScript but this can be overridden if necessary on certain pages.
     * @param array $field_args The arguments to pass to this from the report values.
     * @return DOMNode
     */
    public function getActionNode( $field_args ) {
        $method = "return " . $this->args['action_method'] . "( ";
        $first = true;
        foreach( $this->getActionArguments() as $arg ) {
            if ( $first ) {
                $first = false;
            } else {
                $method .= ", ";
            }
            $method .= $arg;
        }
        foreach( $field_args as $arg ) {
            if ( $first ) {
                $first = false;
            } else {
                $method .= ", ";
            }
            // These are values from the report so they need to be escaped.
            $method .= "'" . addslashes( $arg ) . "'";
        }
        $method .= " );";
        return $this->template->createElement( "a", 
                array( "onclick" => $method ),
                $this->getActionText( $field_args ) );
     }

    /**
     * Return the action cell header for this page.
     * @return string
     */
    public function getActionHeader() {
        return $this->args['action_header'];
    }

    /**
     * Return the action fields for this page.
     * @return array
     */
    public function getActionFields() {
        return $this->args['action_fields'];
    }

    /**
     * Return the arguments to pass to the action method.
     * These arguments should be ready to pass directly to the javascript
     * method so must be quoted and escaped if needed.
     * @return array
     */
    abstract public function getActionArguments();

    /**
     * Return the action text to display in each cell based on the fields passed.
     * @param array $fields The field values for this row
     * @return string
     */
    abstract public function getActionText( $fields );

     
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
