<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
* 
* This File is part of iHRIS Common 
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
*
* @package iHRIS
* @subpackage Common
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1
* @since v4.1
* @filesource
*/
/**
* Class iHRIS_CustomReports_Display_UserStatistics
*
* @access public
*/


class iHRIS_CustomReport_Display_UserStatistics extends I2CE_CustomReport_Display_Default {

    /**
     * @var array A cache of form objects for displaying the fields.
     */
    protected $forms;

    /**
     * @var I2CE_MagicDataNode
     */
    protected $config;

    /**
     * @var array The fields data for this report
     */
    protected $fieldData;

    /**
     * The constructor
     * @param I2CE_Page $page
     * @param string $view
     * @throws Exception on error
     */
    public function __construct( $page, $view ) {
        $this->forms = array();
        $this->page = $page;
        $this->template = $this->page->getTemplate();
        $this->fieldMaps = array();
        $this->formMaps = array();
        $this->mappedFields = array();
        $this->mappedValues = array();
        $this->formObjs = array();
        $this->display = 'Default';

        $this->config = I2CE::getConfig()->traverse( "/modules/UserStatistics" );

        $this->defaultOptions = $this->getDefaultOptions( $this->page->request(), array() );
        $this->fieldData = $this->getDisplayFieldsData();
    }

    /**
     * Get the page root to use for this page.
     * @return string
     */
    protected function getPageRoot() {
        return "UserStatistics";
    }


    /**  
     * @param boolean $check_restart defaults to true in which case if the results are paginated and the offeset is more than the number of results, we restart it setting the page to 1
     * @returns mixed false on failure on succes an array. at index 'results' and  PDOStatement object  at index 'num_results' the
     * number of results that would be found without the limit
     */
    protected function getResults($check_restart = true) {

        if (array_key_exists('limit_paginated',$this->defaultOptions) && $this->defaultOptions['limit_paginated']) {
            if (!array_key_exists('limit_page', $this->defaultOptions) || !(is_integer($this->defaultOptions['limit_page']) || ctype_digit($this->defaultOptions['limit_page'])) || ((int)$this->defaultOptions['limit_page']) < 1) {
                $this->defaultOptions['limit_page'] = 1; 
            }    
            if (!array_key_exists('limit_per_page',$this->defaultOptions) || ! (is_integer($this->defaultOptions['limit_per_page']) || ctype_digit($this->defaultOptions['limit_per_page'] ))|| ((int)$this->defaultOptions['limit_per_page']) < 1) {
                //we don't have a valid 'limit_per_page'
                $this->defaultOptions['limit_per_page'] = 100; //default to 100 
            }    
            $this->defaultOptions['limit_page'] = (int) $this->defaultOptions['limit_page'];
            $this->defaultOptions['limit_per_page'] = (int) $this->defaultOptions['limit_per_page'];
            $limit_offset  =  ( $this->defaultOptions['limit_page'] -1) * ($this->defaultOptions['limit_per_page']);
            $limit_amount = $this->defaultOptions['limit_per_page'];
        }  else {      
            $limit_offset = $this->defaultOptions['limit_offset'];
            //$limit_amount = $this->defaultOptions['limit_per_page'];
            $limit_amount = false;
            if ($limit_offset !==false || $limit_offset !== 'false' )  { 
                if ( !(is_integer($limit_offset) || ctype_digit($limit_offset)) || $limit_offset < 0) {
                    $limit_offset =0;
                }    
            }    
            if ($limit_amount === null || empty($limit_amount) || $limit_amount === 'false') {
                $limit_amount = false;
            }    
            if ($limit_amount !== false) {
                if ( !(is_integer($limit_amount) || ctype_digit($limit_amount)) || $limit_amount < 1) {
                    $limit_amount = 100;
                }
            }
        }
        if ($this->defaultOptions['sort_order'] == 'none') {
            $sort_order = array();
        } else {
            $sort_order = explode(',',$this->defaultOptions['sort_order'] . '');
            foreach ($sort_order as $i=>$field) {
                if ($field == 'none') {
                    unset($sort_order[$i]);
                }
            }
        }


        $sort_order = $this->validateSortFields( $sort_order, array_keys( $this->fieldData ) );


        $db = I2CE::PDO();
        if ( $limit_offset !== false && $limit_amount !== false ) {
            $limit = " LIMIT $limit_amount OFFSET $limit_offset ";
            $this->row_start = $limit_offset;
            $this->row_amount = $limit_amount;
        } else {
            $limit = '';
            $this->row_start = false;
            $this->row_amount = false;
        }


        $sorts = array();
        foreach( $sort_order as $formfield ) {
            if ( strlen( $formfield ) == 0 ) {
                continue;
            }
            if ( $formfield[0] == '-' ) {
                $sort_field = substr( $formfield, 1 );
                $sort_postfix = ' DESC';
            } else {
                $sort_field = $formfield;
                $sort_postfix = '';
            }
            $sorts[$sort_field] = '`' . $sort_field . '`' . $sort_postfix;
        }

        $order_by = '';
        if ( count( $sorts ) > 0 ) {
            $order_by = ' ORDER BY ' . implode( ',', $sorts );
        }

        $group_by = '';
        if ( array_key_exists( 'nested_limits', $this->defaultOptions ) ) {
            $limits = $this->defaultOptions['nested_limits'];
            // Hack to handle a special group by
            if ( array_key_exists( 'record', $limits ) &&
                    array_key_exists( 'null_not_null', $limits['record'] ) ) {
                if ( !$limits['record']['null_not_null']['value'] ) {
                    $group_by = " GROUP BY `date`,`record`,`user` ";
                }
                unset( $limits['record']['null_not_null'] );
            }
            $where = $this->processWhere( $limits );
        } else {
            $where = '';
        }
        //I2CE::raiseMessage("where is $where " . print_r( $this->defaultOptions['nested_limits'], true) );

        $fields = array();
        foreach( $this->fieldData as $field => $data ) {
            if ( array_key_exists( 'db_field', $data ) ) {
                if ( is_array( $data['db_field'] ) ) {
                    foreach( $data['db_field'] as $db_field ) {
                        $fields[] = "$db_field";
                    }
                } else {
                    $fields[] = $data['db_field'] . ' AS `' . $field . '`';
                }
            } else {
                $fields[] = "$field AS `$field`";
            }
        }
        if ( count($fields) == 0 ) {
            I2CE::raiseError( "No fields to display for UserStatistics report." );
            return false;
        }

        //$qry = "SELECT SQL_CALC_FOUND_ROWS e.*,form.name AS form_name, field.name AS field_name,u.username,IF(e.who = 0, 'I2CE Admin', CONCAT_WS(' ', u.firstname, u.lastname)) AS user FROM entry e LEFT JOIN user u ON u.id = e.who JOIN form_field ff ON e.form_field = ff.id JOIN form ON ff.form = form.id JOIN field ON ff.field = field.id $where $order_by $limit";
        //$qry = "SELECT SQL_CALC_FOUND_ROWS " . implode( ', ', $fields ) . " FROM entry LEFT JOIN user ON user.id = entry.who JOIN form_field ff ON entry.form_field = ff.id JOIN form ON ff.form = form.id JOIN field ON ff.field = field.id JOIN record ON record.id = entry.record $where $group_by $order_by $limit";
        $qry = "SELECT " . implode( ', ', $fields ) . " FROM zebra__user_statistics $where $group_by $order_by $limit";
        I2CE::raiseMessage("query is: $qry");
        try {
            $res = $db->query( $qry );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Could not get results" );
            return false;
        }
        //$num_rows = $db->queryRow( "SELECT FOUND_ROWS() AS num_rows" );
        try {
            $num_rows = I2CE_PDO::getRow( "SELECT COUNT(*) AS num_rows FROM zebra__user_statistics $where $group_by" );
            $num_rows = (int) $num_rows->num_rows;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Could not get total number of results" );
            $num_rows = false;
        }

        return array( 'results' => $res, 'num_results' => $num_rows, 'has_total' => false );
    }

    /**
     * Process the results
     * @param array $results_data
     * @param DOMNode $contentNode
     * @return boolean
     */
    protected function processResults( $results_data, $contentNode = null ) {
        if ( parent::processResults( $results_data, $contentNode ) ) {
            $links = $this->template->query( "descendant::span[@name='report_data' and starts-with(.,'link:')]", $contentNode );
            $link_len = $links->length;
            if ( $link_len > 0) {
                for( $i = 0; $i < $link_len; $i++ ) {
                    $link_node = $links->item($i);
                    $link_data = explode( ':', $link_node->nodeValue, 3 );
                    if ( count($link_data) == 3 ) {
                        $anchor = $this->template->createElement( "a",
                                array( "href" => $link_data[1] ),
                                $link_data[2] );
                        $link_node->replaceChild( $anchor, $link_node->firstChild );
                    }
                }
            }
            return true;
        }
        return false;
    }
 
    /**  
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into. If null, we do not do any of the DOM processing stuff, do
     * not call the report display controls, limits etc. It will however still call processResults with a DOMNode of null
     * @param boolean $processResults Defaults to true meaning we run through the results.  If false, we do not process results.
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display( $contentNode, $processResults = true, $controls = null ) {
        if ( $processResults ) {
            $data = $this->getResults();
        }
        if ( $contentNode instanceof DOMNode ) {
            $baseHTML = "customReports_display_Default_base.html";
            $baseNode = $this->template->appendFileByNode( $baseHTML, 'div', $contentNode );
            if ( !$baseNode instanceof DOMNode ) {
                I2CE::raiseError("Unable to add {$this->display} display base template.");
                return false;
            }
            if ( !$this->displayMetaInfo( $baseNode ) ) {
                I2CE::raiseError( "Could not display meta info.");
                return false;
            }

            $reportLimitsNode = $this->template->getElementById( 'report_limits', $baseNode );
            if ( !$reportLimitsNode instanceof DOMNode ) {
                I2CE::raiseError( "Don't know where to add report limits.");
                return false;
            }
            if ( !$this->displayReportLimits( $reportLimitsNode ) ) {
                return false;
            }

            $reportControlsNode = $this->template->getElementById( 'report_controls', $baseNode );
            if ( !$reportControlsNode instanceof DOMNode ) {
                I2CE::raiseError( "Don't know where to add report controls.");
                return false;
            }
            if ( !$this->displayReportControls( $reportControlsNode, $controls ) ) {
                return false;
            }


            $resultsNode = $this->template->getElementById( 'report_results', $baseNode );
        } else {
            $resultsNode = null;
        }
        if ( $processResults ) {
            $this->template->setDisplayData( 'show_results', '1' );
            if ( !$this->processResults( $data, $resultsNode ) ) {
                return false;
            }
        } else {
            $this->template->setDisplayData( 'show_results', '' );
        }
        return true;

    }

    /**
     * This method is not need for this extension, but is here to avoid errors if somehow called.
     * @param boolean $getDisabled
     * @param boolean $all_aggregates
     * @return array
     */
    protected function getReportViewDisplayedFields( $getDisabled = false, $all_aggregates = false ) {
        return array();
    }


   /**
     * Displays any report limits in the content node
     * @param DOMNode $contentNode
     * @param I2CE_MagicDataNode $rv_config
     * @param string $report
     * @param array $limitValues
     * @param array $excludes
     * @param string $merge
     */
    protected function _displayReportLimits( $contentNode, $rv_config, $report, $limitValues, $excludes, $merge = '' ) {
        foreach ( $this->fieldData as $field => $fieldData ) {
            if ( array_key_exists( 'limits', $fieldData ) && is_array( $fieldData['limits'] ) ) {
                foreach ( $fieldData['limits'] as $limit => $limitData ) {
                    if ( !array_key_exists( 'enabled', $limitData ) || !$limitData['enabled'] ) {
                        continue;
                    }
                    $this->displayUSLimit( $field, $limit, $contentNode, $limitValues, $excludes, $merge );
                }
            }
        }
    }

    /**
     * Display the limit for user statistics fields.
     * @param string $fied
     * @param string $limit
     * @param DOMNode $contentNode
     * @param array $limitValues
     * @param array $excludes
     * @param string $merge
     */
    protected function displayUSLimit( $field, $limit, $contentNode, $limitValues, $excludes, $merge ) {
        $formfield = "STRING_LINE";
        if ( array_key_exists( 'formfield', $this->fieldData[$field] ) ) {
            $formfield = $this->fieldData[$field]['formfield'];
        }
        $fieldObj = $this->getFieldObj( $field, $formfield );
        $method = 'processLimitMenu_' . $limit;
        if ( array_key_exists( $field, $limitValues ) && array_key_exists( $limit, $limitValues[$field] ) ) {
            $limitLimitValues = $limitValues[$field][$limit];
        } else {
            $limitLimitValues = array();
        }
        $data = $fieldObj->$method( $limitLimitValues, false );
        //$data = $fieldObj->$method( array(), false );
        $method = 'getLimitMenu_' . $limit;
        $node = $fieldObj->$method($this->template, "limits:$field:$limit", $data );
        if ( !$node instanceof DOMNode ) {
            return;
        }
        $limitConfig = $this->config->fields->$field->limits->$limit;
        $this->displayReportLimit( $contentNode, $node, $limitConfig );
    }

    /**
     * Return a field object for the given field in the report view with the form field type to create.
     * @param string $name
     * @param string $formfield
     * @return I2CE_FormField
     */
    protected function getFieldObj( $name, $formfield ) {
        if ( !$formfield ) {
            return false;
        }
        $class = '';
        if ( !I2CE::getConfig()->setIfIsSet( $class, "/modules/forms/FORMFIELD/$formfield" ) ) {
            return false;
        }
        if ( !$class || !class_exists( $class ) ) {
            return false;
        }
        $options = array(
                'in_db' => false,
                'required' => false,
                'unique' => false,
                'meta' => array(),
                );
        $selects = array();
        if ( array_key_exists( 'select_forms', $this->fieldData[$name] ) ) {
            $selects = preg_split( '/\s*,\s*/', $this->fieldData[$name]['select_forms'], -1, PREG_SPLIT_NO_EMPTY );
        }
        if ( count( $selects ) > 0 ) {
            $options['meta']['form'] = $selects;
        }
        $fieldObj = new $class( $name, $options );
        if ( !$fieldObj instanceof I2CE_FormField ) {
            return false;
        }
        return $fieldObj;
    }

    /**
     * Maps any mapped fields in a results.
     * For UserStatistics this will specially map any of the *_value fields from the result to the 'value'
     * field for display.  This will do any appropriate lookups as necessary for mapped values.
     * @param Object $result A result row with variables of the form "$field"  with unmapped value
     * @return array the mapped results
     */
    protected function mapResults( $result ) {
        $ret = array();
        foreach( $result as $field => $value ) {
            $ret[$field] = $value;
        }
        I2CE::getConfig()->setIfIsSet( $ret['change_type'], "/I2CE/formsData/forms/entry_change_type/" . $ret['change_type'] 
                . "/fields/name" );
        if ( $result->form == "person" ) {
            $ret['record'] = "link:view?id=person|" . $ret['record'] . ':' . $ret['record'];
        } elseif ( substr( $result->parent_id, 0, 7 ) == 'person|' ) {
            $ret['parent_id'] = "link:view?id=" . $ret['parent_id'] . ':' . $ret['parent_id'];
        }
        if ( $result->username == 'i2ce_admin' ) {
            $ret['username'] = '';
        }
        if ( $result->parent_id == '0|0' ) {
            $ret['parent_id'] = '';
        }
        if ( !array_key_exists( $result->form, $this->forms ) ) {
            $this->forms[ $result->form ] = I2CE_FormFactory::instance()->createContainer( $result->form );
        }
        if ( !$this->forms[$result->form] instanceof I2CE_Form ) {
            I2CE::raiseError("Error trying to create form " . $result->form . " for user statistics.");
            $ret['value'] = $result->string_value . $result->integer_value . $result->text_value . $result->date_value . $result->blob_value;
            return $ret;
        }
        $field = $this->forms[$result->form]->getField($result->field);
        if ( !$field instanceof I2CE_FormField ) {
            //I2CE::raiseError("Error getting " . $result->field . " in form " . $result->form );
            $ret['value'] = $result->string_value . $result->integer_value . $result->text_value . $result->date_value . $result->blob_value;
            return $ret;
        }
        $value_column = $field->getTypeString() . "_value";
        $field->setFromDB( $result->$value_column );
        $ret['value'] = $field->getDisplayValue();
        return $ret;
    }

    /**
     * Adds any report display controls that can be added for this view.
     * @param DOMNode $conentNode
     * @param mixed $controls  If null (default), we display all the report controls.  If string or an
     *                         array of string, we only display the indicated controls
     * @returns boolean 
     */
    protected function displayReportControls( $contentNode, $controls=null ) {
        $this->template->addHeaderLink( 'mootools-core.js' );
        $this->template->addHeaderLink( 'I2CE_ClassValues.js' );
        $this->template->addHeaderLink( 'I2CE_SubmitButton.js' );
        $displays = array();
        $displayConfig = I2CE::getConfig()->modules->CustomReports->displays;
        if ( !in_array( 'Default', $displays ) ) {
            $displays[] = 'Default';
        }
        if ( is_string( $controls ) ) {
            $controls = array( $controls );
        }
        if ( is_array( $controls ) ) {
            $displays = array_intersect( $displays, $controls );
        }
        if ( count( $displays ) == 0 ) {
            $displays[] = 'Default';
        }

        if ( in_array( 'Default', $displays ) && count( $displays ) > 1 ) {
            foreach( $displays as $i => $display ) {
                $hide = false;
                $displayConfig->setIfIsSet( $hide, "$display/hide_with_default" );
                if ( $hide ) {
                    unset( $displays[$i] );
                }
            }
        }
        if ( count( $displays ) > 1
                && I2CE::getFileSearch()->search( 'TEMPLATES',
                    "customReports_display_limit_apply_{$this->display}.html" ) ) {
            $reportLimitsNode = $this->template->getElementById( 'report_limits' );
            if ( $reportLimitsNode instanceof DOMNode ) {
                $applyNode = $this->template->appendFileByNode(
                        "customReports_display_limit_apply_{$this->display}.html",
                        "tr", $reportLimitsNode );
            }
        }
        foreach( $displays as $display ) {
            if ( $display != $this->display ) {
                if ( !($displayObj = $this->page->instantiateDisplay( $display, "UserStatistics" ) ) instanceof I2CE_CustomReport_Display ) {
                    continue;
                }
                if ( !$displayObj->canView() ) {
                    continue;
                }
            } else {
                $displayObj = $this;
            }
            $controlNode = $this->template->createElement( 'span', array( 'class' => 'CustomReport_control', 'id' => "CustomReport_controls_$display" ) );
            $contentNode->appendChild( $controlNode );
            $displayObj->displayReportControl( $controlNode );
        }
        return true;
        
    }

    /**
     * Get the data about the fields to be displayed.
     * @return array
     */
    public function getDisplayFieldsData() {
        $disp_order = '';
        $this->config->setIfIsSet( $disp_order, 'display_order' );
        if ( $disp_order ) {
            $displayOrder = explode( ',', $disp_order );
            $fields = $this->config->fields->getAsArray();
            $results = array();
            foreach( $displayOrder as $field ) {
                $results[$field] = $fields[$field];
                unset( $fields[$field] );
            }
            foreach( $fields as $field => $data ) {
                $results[$field] = $data;
            }
            return $results;
        }
        return $this->config->fields->getAsArray();
    }

    /**
     * Process the fields to make limits based on their limiting values
     * @param array $limitValues an array defining limiting values for particular fields
     * @param string $report
     * @return string
     */
    protected function processWhere( $limitValues, $report = null ) {
        $wheres = array();
        if ( is_array( $limitValues ) && count( $limitValues ) > 0 ) {
            $t_excludes = I2CE::getConfig()->modules->CustomReports->limit_excludes->displayed->getAsArray();
            $excludes = array();
            foreach( $t_excludes as $exclude ) {
                $excludes[$exclude] = true;
            }
            foreach( $limitValues as $field => $style ) {
                if ( !is_array( $style ) ) {
                    continue;
                }
                $style = array_diff_key( $style, $excludes );
                if ( count( $style ) == 0 ) {
                    continue;
                }
                $where = $this->processWhereByUserStatistics( $field, $style );
                if ( strlen( $where ) > 0 ) {
                    $wheres[] = '( ' . $where . ' )';
                }
            }
        }

        $where = '';
        if ( count( $wheres ) > 0 ) {
            $where = ' WHERE (' . implode( ' AND ', $wheres ) . ' ) ';
        }
        return $where;
    }

    /**
     * Process the where for the field based on the UserStatistics display since this is a special type of field/limit
     * and return the where clause to use for the query.
     * @param string $field
     * @param array $limitStyles
     * @return string
     */
    protected function processWhereByUserStatistics( $field, $limitStyles ) {
        if ( !is_array( $limitStyles ) || count( $limitStyles ) == 0 ) {
            return '';
        }
        $db_field = $field;
        if ( array_key_exists( 'db_field', $this->fieldData[$field] ) ) {
            $db_field = $this->fieldData[$field]['db_field'];
            /*
            if ( is_array( $this->fieldData[$field]['db_field'] ) ) {
                //return '';
                $db_field = $this->fieldData[$field]['db_field'][0];
                I2CE::raiseMessage("using $db_field");
            } else {
                $db_field = $this->fieldData[$field]['db_field'];
            }
            */
        }
        $config = $this->config->traverse( "fields/$field/limits", false, false );
        if ( !$config instanceof I2CE_MagicDataNode ) {
            return '';
        }
        $wheres = array();
        foreach ( $limitStyles as $limitStyle => $values ) {
            if ( !isset( $config->$limitStyle ) ) {
                continue;
            }
            if ( !isset( $config->$limitStyle->enabled ) || !$config->$limitStyle->enabled ) {
                continue;
            }
            if ( !is_array( $values ) || count( $values ) == 0 ) {
                continue;
            }
            $formfield = "STRING_LINE";
            if ( array_key_exists( 'formfield', $this->fieldData[$field] ) ) {
                $formfield = $this->fieldData[$field]['formfield'];
            }
            $fieldObj = $this->getFieldObj( $field, $formfield );
            if ( !$fieldObj instanceof I2CE_FormField ) {
                I2CE::raiseError( "Couldn't get formfield for limit $field" );
                continue;
            }
            if ( is_array( $db_field ) ) {
                $sub_where = array();
                foreach( $db_field as $dbf ) {
                    $new_where = trim( $fieldObj->generateLimit( array( 'style' => $limitStyle, 'data' => $values ), "$dbf" ) );
                    if ( strlen( $new_where ) > 0 ) {
                        $sub_where[] = $new_where;
                    }
                }
                if ( count( $sub_where ) > 0 ) {
                    $where = '(' . implode( ' OR ', $sub_where ) . ' )';
                } else {
                    $where = '';
                }
            } else {
                $where = trim( $fieldObj->generateLimit( array( 'style' => $limitStyle, 'data' => $values ), "$db_field" ) );
            }
            if ( strlen( $where ) > 0 ) {
                $wheres[] = "( $where )";
            }
        }
        $where = '';
        if ( count( $wheres ) > 0 ) {
            $where = '(' . implode( ' AND ', $wheres ) . ' )';
        }
        if ( $field == "username" ) {
            $where = str_replace( "user|", "", $where );
        }
        if ( $field == "change_type" ) {
            $where = str_replace( "entry_change_type|", "", $where );
        }
        return $where;

    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
