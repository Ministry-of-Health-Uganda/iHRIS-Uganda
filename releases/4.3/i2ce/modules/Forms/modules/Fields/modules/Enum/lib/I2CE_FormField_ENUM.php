<?php
/**
 * @copyright © 2013 Intrahealth International, Inc.
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
    * @package I2CE
    * @author Luke Duncan <lduncan@intrahealth.org>
    * @since v4.1.6
    * @version v4.1.6
    */
/**
 * Class for defining ENUM form fields.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_ENUM extends I2CE_FormField_STRING_LINE { 

    /**
     * @var array The list of values for this field.
     */
    protected $enum_values;

    /**
     * Create a new instance of this form field.
     * @param string $name
     * @param array $options
     */
    public function __construct( $name, $options=array() ) {
        parent::__construct( $name, $options );
        $this->enum_values = array();
        $this->setupEnum();
    }

    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If a I2CE_Entry object has been passed to this method then it will return the value for that entry assuming it's an 
     * entry for this field.
     * @return mixed
     */
    public function getDisplayValue( $entry=false,$style = 'default' ) {
        if ( $entry instanceof I2CE_Entry ) {
            $value = $entry->getValue();
        } else {
            $value = $this->getValue();
        }
        $this->setupEnum();
        if ( array_key_exists( $value, $this->enum_values ) ) {
            return $this->enum_values[$value];
        } else {
            return $value;                      
        }
    }

    /**
     * Setup list of enumerated values for this field.
     */
    public function setupEnum( $display='default' ) {
        if ( count( $this->enum_values ) > 0 ) {
            return;
        }
        $values = array();
        if ( ( $enum = $this->getOptionsByPath( "meta/enum") ) !== null ) {
            if ( array_key_exists( 'data', $enum ) ) {
                //$values = array_merge( $values, $enum['data'] );
                $values += $enum['data'];
            }
            if ( array_key_exists( 'hook', $enum ) ) {
                $hooks = I2CE_ModuleFactory::callHooks( $enum['hook'] );
                foreach( $hooks as $hook_data ) {
                    //$values = array_merge( $values, $hook_data );
                    $values += $hook_data;
                }
            }
            if ( array_key_exists( 'method', $enum ) ) {
                if ( array_key_exists( 'static', $enum['method'] ) || array_key_exists( 'module', $enum['method'] ) ) {
                    if ( array_key_exists( 'module', $enum['method'] ) ) {
                        foreach( $enum['method']['module'] as $module => $method ) {
                            $modObj = I2CE_ModuleFactory::instance()->getClass( $module );
                            if ( !$modObj || !method_exists( $modObj, $method ) ) {
                                I2CE::raiseError("No module found for $module when getting enum values or $method doesn't exist for " . $this->name);
                                continue;
                            }
                            //$values = array_merge( $values, $modObj->{$method}() );
                            $values += $modObj->{$method}();
                        }
                    }
                    if ( array_key_exists( 'static', $enum['method'] ) ) {
                        foreach( $enum['method']['static'] as $class => $method ) {
                            if ( !class_exists( $class ) || !method_exists( $class, $method ) ) {
                                I2CE::raiseError("Couldn't find class $class or method $method in class for enum values." );
                                continue;
                            }
                            //$values = array_merge( $values, call_user_func( array( $class, $method ) ) );
                            $values += call_user_func( array( $class, $method ) );
                        }
                    }
                } else {
                    I2CE::raiseError( "No valid arguments for call in ENUM for " . $this->name );
                }
            }
            if ( array_key_exists( 'sort', $enum ) ) {
                if ( $enum['sort'] == "key" ) {
                    ksort( $values );
                } elseif ( $enum['sort'] == "value" ) {
                    asort( $values );
                }
            } else {
                // Default sort by values
                asort( $values );
            }
        } else {
            I2CE::raiseError( "No enum setting for " . $this->name );
        }
        $this->enum_values = $values;

    }

    /**
     * Set up and return all the enum values
     * @param $style
     * @return array
     */
    public function getEnum( $style='default' ) {
        $this->setupEnum( $style );
        return $this->enum_values;
    }

    /**
     * @return array of DOMNode
     */
    public function processDOMEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $this->setupEnum();
        $values = $this->enum_values;

        if ( count( $values ) > 0 ) {
            $selected = $this->getDBValue();
            $selectNode = $template->createElement( 'select', array( 'name' => $ele_name ) );
            $attrs = array( 'id', 'class' );
            foreach( $attrs as $attr ) {
                if ( $form_node->hasAttribute( $attr ) ) {
                    $selectNode->setAttribute( $attr, $form_node->getAttribute($attr) );
                }
            }
            $this->setElement( $selectNode );
            $blank_text = "Select One";
            if ( $form_node->hasAttribute( "blank" ) ) {
                $blank_text = $form_node->getAttribute( "blank" );
            } else {
                I2CE::getConfig()->setIfIsSet( $blank_text, "/modules/forms/template_text/blank" );
            }
            $selectNode->appendChild( $template->createElement( 'option', array( 'value' => '' ), $blank_text ) );
            foreach( $values as $key => $value ) {
                $attrs = array( 'value' => $key );
                if ( $key == $selected ) {
                    $attrs['selected'] = 'selected';
                }
                $text = $value;
                if ( $text == '' ) {
                    $text = $key;
                }
                $selectNode->appendChild( $template->createElement( 'option', $attrs, $text ) );
            }
            $node->appendChild( $selectNode );
        } else {
            $element = $template->createElement( "input", array( "name" => $ele_name, "id" => $ele_name, "type" => "text", "value" => $this->getDBValue() ) );
            $this->setElement($element);
            $node->appendChild( $element) ;
        }
    }



    public function postprocessDOMEditable( $node, $template, $form_node ) {
        if ( !($inputs = $template->query(".//input" ,$node))  instanceof DOMNodeList) {
            return;
        }
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('mootools-more.js');
        $template->addHeaderLink('I2CE_InputFormatter.js');
        
        $validation = '';
        if ( $this->getOption('required') ) {
            $validation = ",{'nonempty':{}}";
        }

        foreach ($inputs as $input) {
            if (!$input instanceof DOMElement) {
                continue;
            }
            $input->setAttribute('onchange','I2CE_InputFormatter.format(this,false,false' . $validation .')');
        }
    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
