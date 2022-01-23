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

class I2CE_Module_OpenLayers extends I2CE_Module {

    /**
     * Convert the argument to a number value if it is numeric (for proper json_encoding).
     * @param mixed $num
     * @return mixed
     */
    protected static function toNumbers( &$num ) {
        if ( is_numeric( $num ) ) {
            $num = $num + 0;
        }
    }

    /**
     * Encode the string for json and make sure it's all numeric when needed.
     * @param mixed $val
     * @return string
     */
    protected static function encode( $val ) {
        if ( is_scalar( $val ) ) {
            self::toNumbers($val);
            return json_encode( $val );
        } elseif( is_array( $val ) ) {
            array_walk( $val, array( 'self', 'toNumbers' ) );
            ksort( $val );
            return json_encode( $val );
        }
    }

    /**
     * Determine if the given array is associative or not
     * @param array $arr
     * @return boolean
     */
    public static function isAssoc( $arr ) {
        return ( $arr !== array_values( $arr ) );
    }

    /**
     * Add any necessary defaults to the given map details.
     * This is currently just a default view and any layers
     * defined in the _order, but not in the layers data.
     * @param array &$maps The maps data
     */
    public function addMapDefaults( &$maps ) {
        $config = I2CE::getConfig()->modules->OpenLayers;
        if ( $config->is_parent( 'default' ) ) { 
            $defaults = array( '_height', '_width' );
            foreach( $defaults as $default ) {
                if ( !array_key_exists( $default, $maps ) && $config->default->is_scalar($default) ) {
                    $maps[$default] = $config->default->$default;
                }
            }
            foreach( $maps as $map_name => &$map ) {
                if ( $map_name[0] == '_' ) {
                    continue;
                }
                if ( $config->is_parent("default/layers") ) { 
                    if ( array_key_exists( 'layers', $map ) && is_array( $map['layers'] )
                            && array_key_exists( '_order', $map['layers'] ) && is_array($map['layers']['_order'] ) ) { 
                        foreach( $map['layers']['_order'] as $layer => $order ) { 
                            if ( !array_key_exists( $layer, $map['layers'] ) && $config->is_parent("default/layers/$layer") ) { 
                                $map['layers'][$layer] = $config->default->layers->$layer->getAsArray();
                            }   
                        }   
                    }   
                }   
                if ( $config->is_parent("default/view") ) { 
                    if ( array_key_exists( 'view', $map ) ) { 
                        $map['view'] = array_merge( $config->default->view->getAsArray(), $map['view'] );
                    } else {
                        $map['view'] = $config->default->view->getAsArray();
                    }   
                }   
            }
        }

    }

    /**
     * Process the map options from an array and return the OpenLayers
     * javascript code to display the map.
     * @param string $key The key for this value
     * @param mixed $val The option value to process
     * @param string $type The type for this value (or null)
     * @return array An array of two values, the first is the OpenLayers map
     *               and the second is an array of javascript code to
     *               be run before the map code.
     */
    public function processOptions( $key, $val, $type=null ) {
        $class = null;
        if ( !$type ) { 
            $type = $key;
        }   
        if ( is_array( $val ) ) { 
            if ( array_key_exists( '_full_class', $val ) ) { 
                $class = $val['_full_class'];
            } else {
                $prefix = 'ol.';
                if ( in_array( $type, array( 'layer', 'source', 'style' ) ) ) { 
                    $prefix .= "$type.";
                }   
                if ( array_key_exists( '_class', $val ) ) { 
                    $class = $prefix . $val['_class'];
                } else {
                    $class = $prefix . ucfirst( $key );
                }   
            }   
        }   
        if ( !is_array($val) || !self::isAssoc( $val ) ) { 
            return "$key : " . self::encode( $val );
        }   
        if ( array_key_exists( 'func', $val ) ) { 
            $func = $val['func'];
            $args = array();
            if ( array_key_exists( 'args', $val ) ) { 
                foreach( $val['args'] as $arg ) { 
                    $args[] = self::encode( $arg );
                }
            }
            if ( count($args) > 0 ) { 
                return "$key : $func(" . implode( ',', $args ) . ")";
            } else {
                return "$key : $func";
            }
        }   
        $sub_str = array();
        $subtypes = array( 'layers' => 'layer', 'style' => 'style', 'maps' => 'map' );
        $prepend = array();
        foreach( $val as $subkey => $subval ) { 
            if ( $subkey[0] == '_' ) {
                continue;
            }
            $processed = $this->processOptions( $subkey, $subval, (array_key_exists( $type, $subtypes ) ? $subtypes[$type] : null) );
            if ( is_array( $processed ) ) { 
                if ( is_array( $processed[1] ) ) { 
                    foreach( $processed[1] as $add_prepend ) { 
                        $prepend[] = $add_prepend;
                    }
                } else {
                    $prepend[] = $processed[1];
                }
                $append = $processed[0];
            } else {
                $append = $processed;
            }
            $sub_str[$subkey] = $append;
        }   
        switch( $type ) { 
            case "maps" :
                return array( implode( ";", $sub_str ) . ";", $prepend );
                break;
            case "map" :
                if ( !array_key_exists( 'target', $sub_str ) ) {
                    $sub_str['target'] = "target : '${key}_map'";
                }
                return array( "var $key = new $class({ " . implode( ",", $sub_str ) . " });", $prepend );
                break;
            case "layers" :
                $ord = array();
                if ( array_key_exists( '_order', $val ) ) {
                    $ord = $val['_order'];
                    $ord = array_flip( $ord );
                    ksort( $ord );
                }
                $ordered = array_merge( array_intersect( $ord, $sub_str ), array_diff( $sub_str, $ord ) );
                //usort( $sub_str, array( $this, 'sortLayers' ) );
                return array( "layers : [ " . implode( ",", $ordered ) . " ]", $prepend );
                break;
            case "layer" :
                return array( $key, "var $key = new $class({ " . implode( ",", $sub_str ) . " });" );
                break;
            default :
                if ( $class ) { 
                    return array( "$key : new $class({ " . implode( ",", $sub_str ) . " })", $prepend );
                } else {
                    return array( implode( "", $sub_str ), $prepend );
                }
                break;
        }   

    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
