<?php
/**
 * @copyright © 2012 Intrahealth International, Inc.
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
*  I2CE_CustomReport_Display_DefaultAction -- the default HTML display of a report view
*  with an action to be performed in an additional cell
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1.4
* @access public
*/


class I2CE_CustomReport_Display_DefaultAction extends I2CE_CustomReport_Display_Default {

     /**
     * The constuctor
     * @param I2CE_Page $page
     * @param string $view
     * @throws Excecption on error
     */
    public function __construct($page,$view) {
        parent::__construct($page,$view);
        $this->display='Default';
    }
    

    protected function doHeaderRow($contentNode) {
        parent::doHeaderRow($contentNode);
		$action_headers = $this->page->getActionHeader();
		if(!is_array($action_headers)){
			$action_headers = array($action_headers);
		}
		foreach($action_headers as $action_header){			
			$head = $this->template->appendFileByName( "customReports_table_head_cell.html", "th", "report_header", 0, $contentNode, true);
			if (!$head instanceof DOMNode) {
				I2CE::raiseError("Could not add head cell to table");
				return false;
			}					
			$text = $this->template->createTextNode( $action_header );
			$this->template->appendNode( $text, $head );
		}
    }

    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        parent::processResultRow($row, $row_num, $contentNode);

        $per_page = (int) ($this->defaultOptions['limit_per_page']);
        if ($per_page <1) {
            //check it is not bad, if so make it something reasonable -- in fact make it the default per page in I2CE_CustomReport_Display
            $per_page = 100;
        }
        $page = (int) $this->defaultOptions['limit_page'];
        //$page = (int) $this->page->request('limit_page');
        if ($page < 1) {
            $page = 1;
        }
        $appendCount = $row_num - (($page-1)*$per_page);        
        $field_args = array();
        foreach( $this->page->getActionFields() as $field ) {
            $field_args[] = $row->$field;
        }

		$actionNodes = $this->page->getActionNode( $field_args );
		if(!is_array($actionNodes)){
			$actionNodes = array($actionNodes);
		}
		foreach($actionNodes as $actionNode){
			$cellNode = $this->template->appendFileByName( "customReports_table_data_cell.html", "td", "report_row", $appendCount, null, true );
			if (!$cellNode instanceof DOMNode) {
				I2CE::raiseError("Could not add data cell to table");
				return false;
			}
			$this->template->appendNode( $actionNode, $cellNode );
		}
        return true;
    }
            
    /**
     * Return the root of this page since this is generally set up for special displays.
     */
    protected function getPageRoot() {
        return $this->page->page();
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
