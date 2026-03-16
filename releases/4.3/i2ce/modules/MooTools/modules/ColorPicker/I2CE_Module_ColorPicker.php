<?php
/**
 * @copyright © 2007, 2009 Intrahealth International, Inc.
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
*  I2CE_Module_ColorPicker
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/

 
class I2CE_Module_ColorPicker  extends I2CE_Module{

    public static function getMethods() {
        return array(
            // 'I2CE_SwissConfig_Default->displayValue_color_triple_rgb_single'=>'displayValue_color_triple_rgb_single',
            // 'I2CE_SwissConfig_Default->displayValue_color_triple_rgb_many'=>'displayValue_color_triple_rgb_many',
            // 'I2CE_SwissConfig_Default->displayValue_color_triple_hex_single'=>'displayValue_color_triple_hex_single',
            // 'I2CE_SwissConfig_Default->displayValue_color_triple_hex_many'=>'displayValue_color_triple_hex_many',
            // 'I2CE_SwissConfig_Default->displayValue_color_hex_single'=>'displayValue_color_hex_single',
            // 'I2CE_SwissConfig_Default->displayValue_color_hex_many'=>'displayValue_color_hex_many',
            'I2CE_MagicDataTemplate->processValues_color_triple_hex_single'=>'processValues_color_triple_hex_single',
            'I2CE_MagicDataTemplate->processValues_color_triple_hex_many'=>'processValues_color_triple_hex_many',
            'I2CE_MagicDataTemplate->processValues_color_triple_rgb_single'=>'processValues_color_triple_rgb_single',
            'I2CE_MagicDataTemplate->processValues_color_triple_rgb_many'=>'processValues_color_triple_rgb_many',
            'I2CE_MagicDataTemplate->processValues_color_hex_single'=>'processValues_color_hex_single',
            'I2CE_MagicDataTemplate->processValues_color_hex_many'=>'processValues_color_hex_many',
            'I2CE_Page->addColorPickerTriple'=>'addColorPickerTriple',
            'I2CE_Template->addColorPickerTriple'=>'addColorPickerTriple',
            );
    }

    public static function getHooks() {
        return array(
            'template_post_display'=> 'writeOutJS'
            );
    }



    /**
     * An array of color pickers that need to update  triple of rgb
     */
    protected $color_triples;
    public function __construct() {
        parent::__construct();
        $this->color_triples = array();
        $this->update_colors = array();
    }


    public function processValues_color_triple_hex_single($configurator,$value,$status=array()) {
        return $this->ensureColorTripleHex($value);
    }          

    public function processValues_color_triple_rgb_single($configurator,$value,$status=array()) {
        return $this->ensureColorTripleRGB($value);
    }

    public function processValues_color_triple_hex_many($configurator,$valueList,$status=array()) {
        $ret = array();
        foreach ($valueList as $value) {
            $ret[] = $this->ensureColorTripleHex($value);
        }
        if (count($ret) == 0) {
            $ret = null;
        }
        return $ret;
    }

    public function processValues_color_triple_rgb_many($configurator,$valueList,$status=array()) {
        $ret = array();
        foreach ($valueList as $value) {
            $ret[] = $this->ensureColorTripleHex($value);
        }
        if (count($ret) == 0) {
            $ret = null;
        }
        return $ret;
    }




    
    //decimal triple of colors
    public function displayValue_color_triple_rgb_single($swissConfig,$valNode,$status,$config) {
        $template = $swissConfig->getPage()->getTemplate();
        $factory = $swissConfig->getFactory();
        $node = $template->loadFile("configuration_color_triple_rgb_single.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_color_triple_single.html");
            return $template->createElement("div"); //send something
        }
        $inputs = $template->query(".//input[@name='value_color_triple_rgb_single[]']",$node);
        if ($inputs->length != 3) {
            I2CE::raiseError("Unexpected number of input nodes with id 'value_color_triple_rgb_single'" . $inputs->length);
            return $node;
        }                
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return $node;
        }
        $template->setDisplayDataImmediate('displayName',$factory->getTextContent('displayName',$valNode),$node);
        $description = trim($factory->getTextContent('description',$valNode));
        if (strlen($description) == 0) {
            $description = $factory->getTextContent('displayName',$valNode);
        }
        $template->setDisplayDataImmediate('description',$description, $node);
        $template->setDisplayDataImmediate('configDefaultValue',$this->ensureRGBColor($factory->getTextContent('value',$valNode)),$node);
        if (!$config->is_parent()) {
            I2CE::raiseError("Warning: expecting to have a parent node. Instead got leaf node at ". $config->getPath());
            return $node;
        }
        $path = $factory->getModule() . 
            $factory->getPath($swissConfig->getConfigPath(),   $valNode->getAttribute('name'));
        $colorId = 'value_color_triple_rgb_single' . '/'  . $path;
        $colors = $this->ensureColorTripleRGB($config->getAsArray());
        for ($i=0; $i < 3; $i++) {
            $input = $inputs->item($i);
            $input->setAttribute('name', 'value_color_triple_rgb_single' . $config->getPath(false). '[]');
            $input->setAttribute('id', $colorId );
            $input->setAttribute('value',$colors[$i]);
        }
        $showColorPicker = $template->query("./descendant-or-self::node()[@id='showColorPickerBox']",$node);
        if ($showColorPicker->length != 1) {
            I2CE::raiseError("Got unexpected number of nodes with id 'showColorPickerBox':" . $showColorPicker->length);
            return $node;
        }
        $showColorPicker = $showColorPicker->item(0);
        $showColorPicker->setAttribute('id','showColorPickerBox/' .  $path);
        while ($showColorPicker->childNodes->length > 0){
            $showColorPicker->removeChild($showColorPicker->firstChild);
        }
        $showNode = $template->createTextNode( $this->ensureRGBColor($colors));
        $showColorPicker->setAttribute('style','background-color: ' . $this->ensureRGBColor($colors) . ''  );
        $showColorPicker->appendChild ($showNode);
        $colorPicker = $template->query("./descendant-or-self::node()[@id='colorPickerBox']",$node);
        if ($colorPicker->length != 1) {
            I2CE::raiseError("Got unexpected number of nodes with id 'colorPickerBox':" . $colorPicker->length);
            return $node;
        }
        $colorPicker->item(0)->setAttribute('id','colorPickerBox/' .  $path );
        $template->addColorPickerTriple(
            'colorPickerBox/' . $path,
            'showColorPickerBox/' . $path,
            $colorId,$colors);
        $defaultColor = $template->query("./descendant-or-self::node()[@id='defaultColor']",$node);
        if ($defaultColor->length == 1) {
            $defaultColor = $defaultColor->item(0);
            $defaultColor->setAttribute('id','defaultColor/' .  $path);
            $this->addUpdateBackgroundColor(
                'click',
                'defaultColor/' . $path,
                'showColorPickerBox/' . $path,
                $this->ensureHexColor($factory->getTextContent('value',$valNode))            
                );
        }
        return $node;
    }

    
    //decimal triple of colors
    public function displayValue_color_triple_hex_single($swissConfig,$valNode,$status,$config) {
        $template = $swissConfig->getPage()->getTemplate();
        $factory = $swissConfig->getFactory();
        $node = $template->loadFile("configuration_color_triple_hex_single.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_color_triple_single.html");
            return $template->createElement("div"); //send something
        }
        $inputs = $template->query(".//input[@name='value_color_triple_hex_single[]']",$node);
        if ($inputs->length != 3) {
            I2CE::raiseError("Unexpected number of input nodes with id 'value_color_triple_hex_single'" . $inputs->length);
            return $node;
        }                
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return $node;
        }
        $template->setDisplayDataImmediate('displayName',$factory->getTextContent('displayName',$valNode),$node);
        $description = trim($factory->getTextContent('description',$valNode));
        if (strlen($description) == 0) {
            $description = $factory->getTextContent('displayName',$valNode);
        }
        $template->setDisplayDataImmediate('description',$description, $node);
        $template->setDisplayDataImmediate('configDefaultValue',$this->ensureHexColor($factory->getTextContent('value',$valNode)),$node);
        if (!$config->is_parent()) {
            I2CE::raiseError("Warning: expecting to have a parent node. Instead got leaf node at ". $config->getPath());
            return $node;
        }
        $path = $factory->getModule() . 
            $factory->getPath($swissConfig->getConfigPath(),   $valNode->getAttribute('name'));
        $colorId = 'value_color_triple_hex_single' . '/'  . $path;
        $colors = $this->ensureColorTripleRGB($config->getAsArray());
        for ($i=0; $i < 3; $i++) {
            $input = $inputs->item($i);
            $input->setAttribute('name', 'value_color_triple_hex_single' . $config->getPath(false). '[]');
            $input->setAttribute('id', $colorId );
            $input->setAttribute('value',$colors[$i]);
        }
        $showColorPicker = $template->query("./descendant-or-self::node()[@id='showColorPickerBox']",$node);
        if ($showColorPicker->length != 1) {
            I2CE::raiseError("Got unexpected number of nodes with id 'showColorPickerBox':" . $showColorPicker->length);
            return $node;
        }
        $showColorPicker = $showColorPicker->item(0);
        $showColorPicker->setAttribute('id','showColorPickerBox/' .  $path);
        while ($showColorPicker->childNodes->length > 0){
            $showColorPicker->removeChild($showColorPicker->firstChild);
        }
        $showNode = $template->createTextNode( $this->ensureHexColor($colors));
        $showColorPicker->setAttribute('style','background-color: ' . $this->ensureHexColor($colors) . ''  );
        $showColorPicker->appendChild ($showNode);
        $colorPicker = $template->query("./descendant-or-self::node()[@id='colorPickerBox']",$node);
        if ($colorPicker->length != 1) {
            I2CE::raiseError("Got unexpected number of nodes with id 'colorPickerBox':" . $colorPicker->length);
            return $node;
        }
        $colorPicker->item(0)->setAttribute('id','colorPickerBox/' .  $path);
        $template->addColorPickerTriple(
            'colorPickerBox/' . $path,
            'showColorPickerBox/' . $path,
            $colorId,$colors);
        $defaultColor = $template->query("./descendant-or-self::node()[@id='defaultColor']",$node);
        if ($defaultColor->length == 1) {
            $defaultColor = $defaultColor->item(0);
            $defaultColor->setAttribute('id','defaultColor/' .  $path);
            $this->addUpdateBackgroundColor(
                'click',
                'defaultColor/' . $path,
                'showColorPickerBox/' . $path,
                $this->ensureHexColor($factory->getTextContent('value',$valNode))            
                );
        }
        return $node;
    }


    /**
     * Update a background color on a click
     * @param string $event
     * @param string $id_action
     * @param string $id_update
     * @param string $color The hex color
     */
    public function addUpdateBackgroundColor($event,$action_id,$update_id,$color) {
        $this->update_colors[] = array('event'=>$event,'action_id'=>$action_id,'update_id'=>$update_id,'color'=>$color);
    }


    /**
     * Add a color picker which set a triple of (preferably hidden) input values to the RGB values selected
     * @param mixed $attach.  Either the id of a element to append the color picker to.
     * @param string $showColor.  The id of element to set the background color to when clicking on the color picker
     * @param string $colorIDs.  An array indexed by 0,1,2 of the id of input button to update the values to
     * @param mixed $initColor.  An optional inital color to set the color picker to.  Either an array of rgb values (indexed by 012,rgb or RGB)
     * or a hexmode color
     *
     */
    public function addColorPickerTriple($obj,$args) {
        if ($obj instanceof I2CE_Page) {
            $template = $obj->getTemplate();
        } else if ($obj instanceof I2CE_Template) {
            $template = $obj;
        } else {
            I2CE::raiseError("Upexpected");
            return;
        }
        if (count($args) < 3) {
            I2CE::raiseError("Unexpected number of arguements:" . count($args));
            return;
        }
        $triple = array();
        $triple['attach']=$args[0];
        $triple['showColor'] = $args[1];
        $triple['colorID'] = $args[2];
        if (count($args) >= 4) {
            $triple['initColor'] = $args[3];
        }
        $this->color_triples[] = $triple;
    } 

    
    /**
     * Given an array which is a triple of colors or a string turn it into a hex string of colors. If it cannnot
     * recognize it as a color, returns an empty string
     * @param mixed $c
     * @return string
     */
    protected function ensureHexColor($c) {
        $initColor = '';
        if (is_array($c)) {
            if (array_key_exists('r',$c) && array_key_exists('g',$c) && array_key_exists('b',$c)) {
                $initColor = '#' . str_pad(dechex($c['r']),2,'0',STR_PAD_LEFT) . str_pad(dechex($c['g']),2,'0',STR_PAD_LEFT)  . str_pad(dechex($c['b']),2,'0',STR_PAD_LEFT) ;
            } else  if (array_key_exists('R',$c) && array_key_exists('G',$c) && array_key_exists('B',$c)) {
                $initColor = '#' . str_pad(dechex($c['R']),2,'0',STR_PAD_LEFT) . str_pad(dechex($c['G']),2,'0',STR_PAD_LEFT)  . str_pad(dechex($c['B']),2,'0',STR_PAD_LEFT) ;
            } else  if (count($c) == 3 && array_key_exists(0,$c) && array_key_exists(1,$c) && array_key_exists(2,$c)) {
                $initColor = '#' . str_pad(dechex($c[0]),2,'0',STR_PAD_LEFT) . str_pad(dechex($c[1]),2,'0',STR_PAD_LEFT)  . str_pad(dechex($c[2]),2,'0',STR_PAD_LEFT) ;
            }
        } else if (is_string($c)) {
            if (preg_match('/^#?([0-9a-fA-F]+)$/',$c,$matches)) {
                if (strlen($matches[1]) <= 6) {
                    $initColor = '#' . str_pad($matches[1],6,'0',STR_PAD_LEFT);
                }
            }  else if (strpos($c,':') !== false) {
                $values  = preg_split('/:/',$c,-1,PREG_SPLIT_NO_EMPTY);
                for ($k=0; $k < count($values); $k++) {
                    $values[$k] = trim($values[$k]);
                }       
                $initColor = $this->ensureHexColor($values);
            }              
        }
        return $initColor;
    }


    /**
     * Given an array which is a triple of colors or a string turn it into a comma separated string of colors. If it cannnot
     * recognize it as a color, returns an empty string
     * @param mixed $c
     * @return string
     */
    protected function ensureRGBColor($c) {
        $initColor = '';
        $c = $this->ensureColorTripleRGB($c);
        if (count($c) == 3 && array_key_exists(0,$c) && array_key_exists(1,$c) && array_key_exists(2,$c)) {
            return  $c[0] . ',' . $c[1] . ',' . $c[2];
        }
        return '';
    }

    protected function convertColorTripleHexToRGB($c) {
        return array(hexdec($c[0]),hexdec($c[1]),hexdec($c[2]));
    }

    protected function convertColorTripleRGBToHex($c) {
        return array( str_pad(dechex($c['0']),2,'0',STR_PAD_LEFT) , str_pad(dechex($c['1']),2,'0',STR_PAD_LEFT)  , str_pad(dechex($c['2']),2,'0',STR_PAD_LEFT)) ;
    }

    protected function ensureColorTripleRGB($c) {
        $ret = null;
        if (is_array($c)) {
            foreach ($c as $i=>$v) {
                $c[$i] = trim($v);
            }
            if (array_key_exists('r',$c) && array_key_exists('g',$c) && array_key_exists('b',$c)) {
                $ret = array( $c['r'], $c['g'], $c['b']);
            } else  if (array_key_exists('R',$c) && array_key_exists('G',$c) && array_key_exists('B',$c)) {
                $ret = array( $c['R'], $c['G'], $c['B']);
            } else  if (count($c) == 3 && array_key_exists(0,$c) && array_key_exists(1,$c) && array_key_exists(2,$c)) {
                if (! (ctype_digit($c[0]) && ctype_digit($c[1]) && ctype_digit($c[2]))) { //this is not fail proof but we convert obvious hex guys to decimal
                    $ret = $this->convertColorTripleHexToRGB($c);
                } else {
                    $ret = $c; //leave it alone
                }
            }
        } else if (is_string($c)) {
            if (preg_match('/^#?([0-9a-fA-F]+)$/',$c,$matches)) {
                if (strlen($matches[1]) == 6) {
                    $color =  str_pad($matches[1],6,'0',STR_PAD_LEFT);
                    $ret = $this->convertColorTripleHexToRGB(array(substr($color,0,2),substr($color,2,2), substr($color,4,2)));
                }
            } else if (strpos($c,':') !== false) {
                $values  = preg_split('/:/',$c,-1,PREG_SPLIT_NO_EMPTY);
                for ($k=0; $k < count($values); $k++) {
                    $values[$k] = trim($values[$k]);
                }       
                $ret = $this->ensureColorTripleRGB($values);
            }   
        }
        return $ret;
    }

    protected function ensureColorTripleHex($c) {
        $ret = null;
        if (is_array($c)) {
            foreach ($c as $i=>$v) {
                $c[$i] = trim($v);
            }
            if (array_key_exists('r',$c) && array_key_exists('g',$c) && array_key_exists('b',$c)) {
                $ret = $this->convertColorTripleRGBToHex($c['r'],$c['g'],$c['b']);
            } else  if (array_key_exists('R',$c) && array_key_exists('G',$c) && array_key_exists('B',$c)) {
                $ret = $this->convertColorTripleRGBToHex($c['R'],$c['G'],$c['B']);
            } else  if (count($c) == 3 && array_key_exists(0,$c) && array_key_exists(1,$c) && array_key_exists(2,$c)) {
                $ret = $c; //leave it alone 
            }
        } else if (is_string($c)) {
            if (preg_match('/^#?([0-9a-fA-F]+)$/',$c,$matches)) {
                if (strlen($matches[1]) == 6) {
                    $color =  str_pad($matches[1],6,'0',STR_PAD_LEFT);
                    $ret = array(substr($color,0,2),substr($color,2,2), substr($color,4,2));
                }
            } else if (strpos($c,':') !== false) {
                $values  = preg_split('/:/',$c,-1,PREG_SPLIT_NO_EMPTY);
                for ($k=0; $k < count($values); $k++) {
                    $values[$k] = trim($values[$k]);
                }       
                $ret = $this->ensureColorTripleHex($values);
            }   
        }
        return $ret;
    }

    /**
     *writes out the JS in any needed to display the color pickers.
     */
    public function writeOutJS($args) {
        $JS = '';
        foreach ($this->update_colors as $update) { 
            $JS.= "\t\tColorPickerHelper.updateShowColorBoxDecOnEvent('".$update['event']. "','" . $update['action_id'] . "','" . $update['update_id']. "','" . $update['color']  ."');\n";
        }
        foreach ($this->color_triples as $triple) {            
            $initColor = $this->ensureHexColor($triple['initColor']);
            $JS .= "\t\tColorPickerHelper.addColorTripleSelectorDec('" . $triple['attach'] . "','" . $triple['showColor'] . "','" . $triple['colorID'] . "','" . $initColor . "');\n";
        }
        if (strlen($JS) == 0) {
            return;
        }     
        $template = $args['template'];   
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('colorpicker.js');
        $template->addHeaderLink('colorPicker_helper.js');
        $template->addHeaderLink('colorpicker.css');
        $JS = "if (window.addEvent) {	window.addEvent('load',\n\tfunction() {\n" . $JS .  "\t});\n}\n";
        $template->addHeaderText($JS,'script',"color_picker");        
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

