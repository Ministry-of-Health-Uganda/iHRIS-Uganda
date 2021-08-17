<?php
/*
 * © Copyright 2007-11 IntraHealth International, Inc.
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
 * Export relationships from the command line
 * 
 * @package I2CE
 * @subpackage Tools
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org></lduncan>
 * @copyright Copyright &copy; 2007-11 IntraHealth International, Inc. 
 * @since 4.0.10
 * @version 4.0.10
 */


$i2ce_site_user_access_init = null;
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR 
        . 'pages' . DIRECTORY_SEPARATOR 
        . 'config.values.php');

$local_config = dirname(__FILE__) . DIRECTORY_SEPARATOR
        . 'pages' . DIRECTORY_SEPARATOR 
        . 'local' . DIRECTORY_SEPARATOR . 'config.values.php';
if (file_exists($local_config)) {
    require_once($local_config);
}
$i2ce_site_i2ce_path = preg_replace( "/^..\//", '', $i2ce_site_i2ce_path );
$i2ce_site_module_config = preg_replace( "/^..\//", '', $i2ce_site_module_config );

if(!isset($i2ce_site_i2ce_path) || !is_dir($i2ce_site_i2ce_path)) {
    echo "Please set the \$i2ce_site_i2ce_path in $local_config";
    exit(55);
}

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

if (isset($i2ce_site_dsn)) {
    @I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);         
} else if (isset($i2ce_site_database_user)) {
    @I2CE::initialize($i2ce_site_database_user,     $i2ce_site_database_password,   $i2ce_site_database,  $i2ce_site_user_database,    $i2ce_site_module_config   );
} else {
    die("No proper site initializtion found.  Please check your config.values.php\n");
}

require_once( $i2ce_site_i2ce_path . DIRECTORY_SEPARATOR  . 'tools' . DIRECTORY_SEPARATOR . 'CLI.php' );

unset($i2ce_site_user_access_init);
unset($i2ce_site_dsn);
unset($i2ce_site_i2ce_path);
unset($i2ce_site_module_config);


class MyExport extends I2CE_Page_MagicDataExport {
    public function doit() {
        $this->action();
        $this->_display(true);
    }
}

$args = getopt( "", array(
            "relationship:",
            "description::",
            "version::",
            "display::",
            "module::",
            ) );
if ( !array_key_exists( "relationship", $args ) ) {
    die( "You must enter a relationship to export!\n" );
}

$defaults = array( 
        'module' => "CustomReports-full-" . $args['relationship'],
        'description' => "Relationship '" . $args['relationship'] . "' and all reports and report views.",
        'display' => "Relationship: " . $args['relationship'],
        'version' => '4.0.11.' . time(),
        );
foreach( $defaults as $key => $val ) {
    if ( !array_key_exists( $key, $args ) ) {
        $args[$key] = $val;
    }
}

$config = I2CE::getConfig()->traverse( "/modules/CustomReports" );
if ( !$config->__isset( "relationships/" . $args['relationship'] ) ) {
    die( "That relationship (" . $args['relationship'] . ") doesn't exist!\n" );
}

$include_relationship = false;
if ( simple_prompt( "Do you want to include the relationship details in your module?" ) ) {
    $include_relationship = true;
}




$export_page = new MyExport( array( 'template' => 'I2CE_MagicDataExport_Template', 'templates' => array( 'export_magicdata.xml' ) ), 
        array( "modules", "CustomReports", "relationships", $args['relationship'] ),
        array( 'description' => $args['description'],
            'displayName' => $args['display'],
            'version' => $args['version'] )
        );
$export_page->doit();

$template = $export_page->getTemplate();

$results = $template->query( "/I2CEConfiguration" );
$top_node = $results->item(0);
$top_node->setAttribute( "name", $args['module'] );

$results = $template->query( "/I2CEConfiguration/configurationGroup" );
$main_node = $results->item(0);
$main_node->setAttribute( "name", $args['relationship'] );

$new_node = $template->createElement( "configurationGroup", 
        array( "name" => $args['module'], "path" => "/modules/CustomReports" ) );

$top_node->appendChild( $new_node );
if ( $include_relationship ) {
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
            && $report->relationship == $args['relationship'] ) {
        $reports[] = $report_name;
    }
}

$selectedReports = chooseMenuIndices( "Select which reports you want to include in this module.", $reports );

foreach( $selectedReports as $report_idx ) {
    $report_name = $reports[$report_idx];
    $report_page = new MyExport( array( 'template' => 'I2CE_MagicDataExport_Template', 
                'templates' => array( 'export_magicdata.xml' ) ), 
            array( "modules", "CustomReports", "reports", $report_name ),
            array( "version" => $args['version'] )
            );
    $report_page->doit();
    $report_template = $report_page->getTemplate();

    $results = $report_template->query( "/I2CEConfiguration/configurationGroup" );
    $report_node = $results->item(0);

    $report_node->setAttribute( "name", $report_name );

    $add_node = $template->getDoc()->importNode( $report_node, true );
    
    $new_node->appendChild( $add_node );

    $selectedViews = chooseMenuIndices( "Select which report views you want to include in this module.", $views[$report_name] );

    foreach( $selectedViews as $view_idx ) {
        $view = $views[$report_name][$view_idx];

        $view_page = new MyExport( array( 'template' => 'I2CE_MagicDataExport_Template', 
                    'templates' => array( 'export_magicdata.xml' ) ), 
                array( "modules", "CustomReports", "reportViews", $view ),
                array( "version" => $args['version'] )
            );
        $view_page->doit();
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
        if ( can_remove( $node, 0 ) ) {
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


//echo $template->getDisplay();
//

$filename = $args['module'] . ".xml";
$fcnt = 0;
while ( file_exists( $filename ) ) {
    $filename = sprintf( "%s%03d.xml", $args['module'], ++$fcnt );
}

if ( !file_put_contents( $filename, $template->getDisplay() ) ) {
    die( "Couldn't write to file: $filename\n" );
} else {
    echo "Create module file: $filename\n";
}


function can_remove( $node, $depth ) {
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
            $can_remove &= can_remove($n,$depth+1);
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
                if (can_remove($n,  $depth+1)) {
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



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
