<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
* 
* This File is part of I2CE 
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
* @package I2CE
* @subpackage admin
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.11
* @since v4.0.11
* @filesource 
*/ 
/** 
* Class I2CE_Page_ExportReport
* 
* @access public
*/

class I2CE_Page_ExportReport extends I2CE_Page {


    protected  $cli;
    /**
     * Create a new instance of a page.
     * 
     * The default constructor should be called by any pages extending this object.  It creates the
     * {@link I2CE_Template} and {@link I2CE_User} objects and sets up the basic member variables.
     * @param array $args
     * @param array $request_remainder The remainder of the request path
     */
    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
        $this->cli = new I2CE_CLI();
        $this->cli->addUsage("[--relationship=XXXX]: The relationship to export.  If not set, then user will be prompted.\n");
        $this->cli->addUsage("[--description=XXXX]: Optional description to use in the  export\n");
        $this->cli->addUsage("[--version=XXXX]: Optional version to use in the  export\n");
        $this->cli->addUsage("[--display=XXXX]: Optional display name to use in the  export\n");
        $this->cli->addUsage("[--module=XXXX]: Optional module name to use in the  export\n");
        $this->cli->addUsage("[--output=XXXX]: Optional file to ouput to.  \n");
        parent::__construct($args,$request_remainder,$get,$post);
        $this->cli->processArgs();
    }

    
    /**
     * The business method if this page is called from the commmand line
     * @param array $request_remainder the remainder of the request after the page specfication.  
     * @param array $args the array of unix style command line arguments 
     */

    protected function actionCommandLine($args,$request_remainder) { 
        $config = I2CE::getConfig()->traverse( "/modules/CustomReports" );
        if ($this->cli->hasValue('relationship')) {
            $relationship = $this->cli->getValue('relationship');
        } else {
            $relationship = trim($this->cli->chooseMenuValue("Please enter the name of the relationship you wish to export.", $config->relationships->getKeys()));
        }
        if (!$relationship) {
            $this->cli->usage( "You must enter a relationship to export!\n" );
        }

        
        $defaults = array( 
            'module' => "CustomReports-full-" . $relationship,
            'description' => "Relationship '" . $relationship . "' and all reports and report views.",
            'display' => "Relationship: " . $relationship,
            'version' => '4.0.11.' . date('Y.m.d') 
            );
        foreach( $defaults as $key => $val ) {
            if (!$this->cli->getValue($key))  {
                $this->cli->setValue($key,$val);
            }
        }

        if ( !$config->__isset( "relationships/" . $relationship ) ) {
            $this->cli->usage( "The relationship (" . $relationship . ") doesn't exist!\n" );
        }

        $include_relationship = false;
        if ( $this->cli->simple_prompt( "Do you want to include the relationship details in your module?" ) ) {
            $include_relationship = true;
        }



        $erasers = array();
        $export_page = new I2CE_Page_MagicDataExport( 
            array( 'template' => 'I2CE_MagicDataExport_Template', 'templates' => array( 'export_magicdata.xml' ) ), 
            array( "modules", "CustomReports", "relationships", $relationship ),
            array( 'description' => $this->cli->getValue('description'),
                   'displayName' => $this->cli->getValue('display'),
                   'version' => $this->cli->getValue('version') ,
                   'supress_ouput'=>true
                )
            );
        $export_page->action();

        $template = $export_page->getTemplate();

        $results = $template->query( "/I2CEConfiguration" );
        $top_node = $results->item(0);
        $top_node->setAttribute( "name", $this->cli->getValue('module') );

        $results = $template->query( "/I2CEConfiguration/configurationGroup" );
        $main_node = $results->item(0);
        $main_node->setAttribute( "name", $relationship );

        $new_node = $template->createElement( "configurationGroup", 
                                              array( "name" => $this->cli->getValue('module'), "path" => "/modules/CustomReports" ) );

        $top_node->appendChild( $new_node );
        if ( $include_relationship ) {
            $erasers[] = "/modules/CustomReports/relationships/" . $relationship;
            $new_node->appendChild( $results->item(0) );
        } else {
            $top_node->removeChild( $results->item(0) );
        }


        foreach( $config->reportViews as $view_name => $reportView ) {
            if ( $reportView->__isset( "report" ) ) {
                $views[ $reportView->report ][] = $view_name;
            }
        }

        $reports = array();
        foreach( $config->reports as $report_name => $report ) {
            if ( $report->__isset( "relationship" ) 
                 && $report->relationship == $relationship ) {
                $reports[] = $report_name;
            }
        }

        $selectedReports = $this->cli->chooseMenuIndices( "Select which reports you want to include in this module.", $reports );

        foreach( $selectedReports as $report_idx ) {
            $report_name = $reports[$report_idx];
            $erasers[] = "/modules/CustomReports/reports/" . $report_name;
            $report_page = new I2CE_Page_MagicDataExport( 
                array( 'template' => 'I2CE_MagicDataExport_Template', 
                       'templates' => array( 'export_magicdata.xml' ) ), 
                array( "modules", "CustomReports", "reports", $report_name ),
                array( "version" => $this->cli->getValue('version')  ,'supress_ouput'=>true)
                );
            $report_page->action();
            $report_template = $report_page->getTemplate();
            $results = $report_template->query( "/I2CEConfiguration/configurationGroup" );

            $report_node = $results->item(0);
            
            $report_node->setAttribute( "name", $report_name );

            $add_node = $template->getDoc()->importNode( $report_node, true );
    
            $new_node->appendChild( $add_node );

            $selectedViews = $this->cli->chooseMenuIndices( "Select which report views you want to include in this module.", $views[$report_name] );

            foreach( $selectedViews as $view_idx ) {
                $view = $views[$report_name][$view_idx];
                $erasers[] = "/modules/CustomReports/reportViews/" . $view;
                $view_page = new I2CE_Page_MagicDataExport( 
                    array( 'template' => 'I2CE_MagicDataExport_Template', 
                           'templates' => array( 'export_magicdata.xml' ) ), 
                    array( "modules", "CustomReports", "reportViews", $view ),
                    array( "version" => $this->cli->getValue('version')  ,'supress_ouput'=>true)
                    );
                $view_page->action();
                $view_template = $view_page->getTemplate();

                $results = $view_template->query( "/I2CEConfiguration/configurationGroup" );
                $view_node = $results->item(0);

                $view_node->setAttribute( "name", $view );

                $add_node = $template->getDoc()->importNode( $view_node, true );

                $new_node->appendChild( $add_node );
        
            }
        }

       // Clean it up!
       // From fixup_report_xmls.php

        $remove_qry = '/I2CEConfiguration/configurationGroup//configuration[@name="enabled" and value=0 ]/..';
        if ( ($remove_nodes = $template->query( $remove_qry ) ) instanceof DOMNodeList ) {
            foreach( $remove_nodes as $node ) {
                $node->parentNode->removeChild( $node );
            }
        }

        $cleanup_qry = '/I2CEConfiguration/configurationGroup/configurationGroup';
        if ( ($cleanup_nodes = $template->query( $cleanup_qry ) ) instanceof DOMNodeList ) {
            foreach( $cleanup_nodes as $node ) {
                if ( $this->can_remove( $node, 0 ) ) {
                    $node->parentNode->removeChild( $node );
                }
            }
        }

        $locale_qry = "/I2CEConfiguration/configurationGroup//configuration[@name = 'name' or @name = 'description' or @name = 'display_name' or @name = 'header']";
        if ( ($locale_nodes = $template->query( $locale_qry ) ) instanceof DOMNodeList ) {
            foreach ( $locale_nodes as $node ) {
                if ( $node->hasAttribute( "locale" ) ) {
                    continue;
                }
                $node->setAttribute( "locale", I2CE_LOCALES::DEFAULT_LOCALE );
            }
        }


        //erase the things we selected to export.
        //get the top configurationGrou
        foreach ($erasers as $erase) {
            $lessNode = $template->createElement('lessThan',array('version'=>$this->cli->getValue('version')));
            $eraseNode = $template->createElement( "erase", array('path'=>$erase));
            $eraseNode->appendChild($lessNode);            
            //the top level configuration group is $main_node
            $top_node->insertBefore($eraseNode,$new_node);
        }


        if ($this->cli->hasValue('output')) {
            $filename = $this->cli->getValue('output');
        } else {
            $filename = $this->cli->getValue('module') . ".xml";
            $fcnt = 0;
            while ( file_exists( $filename ) ) {
                $filename = sprintf( "%s%03d.xml", $this->cli->getValue('module'), ++$fcnt );
            }
        }

        if ( !file_put_contents( $filename, $template->getDisplay() ) ) {
            die( "Couldn't write to file: $filename\n" );
        } else {
            echo "Create module file: $filename\n";
        }
        
 
    }



    protected function can_remove( $node, $depth ) {
        if (!$node instanceof DOMElement) {
            return true;
        }
        switch ($node->tagName) {
        case 'value':
            return strlen(trim($node->textContent)) == 0;
        case 'configuration':
            if (!$node->childNodes instanceof DOMNodeList) {
                return true;
            }
            $can_remove = true;
            foreach ($node->childNodes as $n) {
                $can_remove &= $this->can_remove($n,$depth+1);
            }
            return $can_remove;
        case 'configurationGroup':
            if ( !$node->childNodes instanceof DOMNodeList) {
                return true;
            }
            $can_remove = true;
            $nodes = $node->childNodes;
            $removed = true;
            while ($removed) {
                $removed = false;
                $nodes = $node->childNodes;
                foreach ($nodes as $n) {
                    if ($this->can_remove($n,  $depth+1)) {
                        if (!$n instanceof DOMElement || $n->tagName != 'displayName') {
                            $node->removeChild($n);
                            $removed = true;
                            $nodes = $node->childNodes;
                            break;
                        }
                    } else {
                        $can_remove = false;
                    }
                }
            }
            return $can_remove;
        default:
            return true;
        }
    }


  }











# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
