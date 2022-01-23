<?php
/**
 * @copyright © 2014 Intrahealth International, Inc.
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
 * Page for displaying OpenLayers maps.
 *
 * @package I2CE
 * @subpackage Core
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @version 4.1
 * @access public
 */

class I2CE_PageOpenLayers extends I2CE_Page {

    /**
     * Load the HTML templates.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();

        $this->template->addHeaderLink( 'mootools-core.js' );
        $this->template->addHeaderLink( 'mootools-more.js' );
        $this->template->addHeaderLink( 'ol.js' );
        $this->template->addHeaderLink( 'ol.css' );
        $this->template->addHeaderLink( 'i2ce_ol.css' );
        $this->template->addHeaderLink( 'openlayers_base.js' );
        $page_css = I2CE::getFileSearch()->search( 'CSS', 'openlayers_' . $this->page . '.css' );
        if ( $page_css ) {
            $this->template->addHeaderLink( 'openlayers_' . $this->page . '.css' );
        }
    }

    /*
     * Perform the actions for this page
     */
    protected function action() {
        parent::action();
        if ( !array_key_exists( 'maps', $this->args ) || !is_array( $this->args['maps'] ) 
                || count( $this->args['maps'] ) == 0 ) {
            $this->template->setDisplayDataImmediate('map_error', 'No map data in page arguments.' );
            return true;
        }
        if ( array_key_exists( 'title', $this->args ) ) {
            $this->template->setDisplayDataImmediate( 'openlayers_title', $this->args['title'] );
        }
        $maps = $this->args['maps'];
        $ol_mod = I2CE_ModuleFactory::instance()->getClass('OpenLayers');
        $ol_mod->addMapDefaults($maps);

        $default_height = null;
        $default_width = null;
        if ( array_key_exists( '_height', $maps ) ) {
            $default_height = $maps['_height'];
        }
        if ( array_key_exists( '_width', $maps ) ) {
            $default_width = $maps['_width'];
        }

        //$maps['map']['layers']['facilities']['gradient'] = array( '#000', '#00f', '#0f0', '#ff0', '#f00' );

        
        $map_data = $ol_mod->processOptions( 'maps', $maps );
        if ( count( $map_data ) != 2 || !is_array( $map_data[1] ) ) {
            $this->template->setDisplayDataImmediate('map_error', 'Invalid map data in page arguments.' );
            return true;
        }
 
        foreach( $maps as $map_name => $map ) {
            if ( $map_name[0] == '_' ) {
                continue;
            }

            $map_node = $this->template->appendFileById( "openlayers_map.html", "div", "maps" );

            $this->template->setAttribute( 'id', "${map_name}_map", 'map', null, $map_node );
            $this->template->setAttribute( 'id', "${map_name}_feature_details", 'feature_details', null, $map_node );

            $height = $default_height;
            if ( array_key_exists( '_height', $map) ) {
                $height = $map['_height'];
            } 
            $width = $default_width;
            if ( array_key_exists( '_width', $map ) ) {
                $width = $map['_width'];
            } 
            if ( $width ) {
                $this->template->setAttribute( 'style', "width: $width;", "${map_name}_feature_details", null, $map_node );
            }
            if ( $height || $width ) {
                $this->template->setAttribute( 'style', ($height ? "height: $height;" : '').($width ? "width: $width;":''), "${map_name}_map", null, $map_node );
            }

            if ( !array_key_exists( 'layers', $map ) ) {
                $this->template->setDisplayDataImmediate('map_error', "No layer data in page arguments for $map_name." );
                return true;
            }

        }

        $js = "window.addEvent('domready', function() { \n";
        foreach( $map_data[1] as $prepend ) {
            $js .= "$prepend\n";
        }
        $js .= $map_data[0] . "\n";

        $page_js = I2CE::getFileSearch()->search( 'SCRIPTS', 'openlayers_post_inline_' . $this->page . '.js' );
        if ( $page_js ) {
            //$this->template->addHeaderLink( 'openlayers_' . $this->page . '.js' );
            $js .= file_get_contents( $page_js );
        }
        $js .= "});\n";

        $this->template->addHeaderText( $js, "script", 'openlayers_maps' );
 
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
