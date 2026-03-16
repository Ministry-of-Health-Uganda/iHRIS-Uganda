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

 
class I2CE_Module_DatePicker  extends I2CE_Module{

    public static function getMethods() {
        return array( 
            'I2CE_Page->addDatePicker'=>'addDatePicker',
            'I2CE_Template->addDatePicker'=>'addDatePicker',
            );
    }

    public static function getHooks() {
        return array(
            'post_page_prepare_display_I2CE_Template'=> 'writeOutJS'
            );
    }

    /**
     * @var array List of timestamps to generate default display values for internationalized month/day of week names.
     */
    protected static $intl_times = array(
        'months' => array( 
            0 => 978332400,
            1 => 981010800,
            2 => 983430000,
            3 => 986108400,
            4 => 988696800,
            5 => 991375200,
            6 => 993967200,
            7 => 996645600,
            8 => 999324000,
            9 => 1001916000,
            10 => 1004598000,
            11 => 1007190000 ),
        'days' => array(
            0 => 986108400,
            1 => 978332400,
            2 => 988696800,
            3 => 996645600,
            4 => 981010800,
            5 => 991375200,
            6 => 999324000,
            ),
        );



     /**
     * An array of javscript date pickers
     */
    protected $date_pickers; 
    public function __construct() {
        parent::__construct();
        $this->date_pickers = array();
        if ( class_exists( 'IntlDateFormatter' ) ) {
            $locale = I2CE_Locales::getPreferredLocale();
            $config = I2CE::getConfig()->modules->DatePicker;
            $intl_setup = false;
            $config->setIfIsSet( $intl_setup, "intl_setup/$locale" );
            if ( !$intl_setup ) {
                $formatter = array( 
                        'months' => 
                        new IntlDateFormatter( $locale, null, null, 'UTC', null, 'MMMM' ),
                        'days' => 
                        new IntlDateFormatter( $locale, null, null, 'UTC', null, 'EEEE' ),
                        );
                foreach( self::$intl_times as $type => $times ) {
                    foreach( $times as $idx => $time ) {
                        if ( !$config->is_translated( $locale, "options/$type/$idx" ) ) {
                            $config->setTranslatable("options/$type/$idx");
                            $config->setTranslation( $locale, $formatter[$type]->format( $time ), "options/$type/$idx" );
                        }
                    }
                }
            }
            $config->intl_setup->$locale = 1;
        }
    }




    /**
     * Add a date picker to the page
     * @param mixed $obj.  Either I2CE_Page or I2CE_Template
     * @param string $class.   The html class to create the date picker on.
     * @param array $args.  Defaults to the empty array.  Array of options to pass to the datepicker object     
     *
     */
    public function addDatePicker($obj,$class, $args = array()) {
        if ($obj instanceof I2CE_Page) {
            $template = $obj->getTemplate();
        } else if ($obj instanceof I2CE_Template) {
            $template = $obj;
        } else {
            I2CE::raiseError("Unexpected");
            return;
        }
        if (!is_string($class) || strlen($class) == 0) {
            I2CE::raiseError("Invalid node id when adding date picker");
            return;
        }
        //Date picker takes any mootools selector, but I want to enforce that it is an id
        $var_name = "DP_" . $class;
        $class = '.' . $class;
        if (!is_array($args)) {
            I2CE::raiseError("Invalid constructor arguments passed to the date picker");
            $args = array();
        }
        if ( ! is_array($options = I2CE::getConfig()->getAsArray("/modules/DatePicker/options"))) {
            $options = array();
        }

        I2CE_Util::merge_recursive($options,$args,true,false);
        $onSelect = false;
        if (!array_key_exists('onSelect',$args) || !is_scalar($args['onSelect']) || !$args['onSelect']) {            
            $onSelect = "function(d) { if (this.hasAttribute('onchange')) { eval(this.getAttribute('onchange'));}}";
            unset($args['onSelect']);
        } else {
            $onSelect = $args['onSelect'];
            unset($args['onSelect']);
        }
        $onShow =false;
        if (!array_key_exists('onShow',$args) || !is_scalar($args['onShow']) || !$args['onShow']) {            
            $onShow = "function(d) { if (this.hasAttribute('onclick')) { eval(this.getAttribute('onchange'));}}";
            unset($args['onShow']);
        } else {
            $onShow= $args['onShow'];
            unset($args['onShow']);
        }

        $options = json_encode($args);        
        if (($pos=strrpos($options,'}')) !== false) {
            $options = substr($options,0,$pos );
            if ($onSelect ) {
                $options .= ' , "onSelect": ' . $onSelect;
            }
            if ($onShow ) {
                $options .= ' , "onShow": ' . $onShow;
            }
            $options .= '}';
        }
        $this->date_pickers[$class] = $var_name . " = new DatePicker( '" . addslashes($class) . "'," . $options . ');';
    } 

    
    /**
     *writes out the JS in any needed to display the color pickers.
     */
    public function writeOutJS($page) {
        if (count($this->date_pickers) == 0) {
            return;
        }
        if (!$page instanceof I2CE_Page) {
            return;
        }
        $template= $page->getTemplate();
        if (!$template instanceof I2CE_Template) {
            return;
        }
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('datepicker.js');
        $template->addHeaderLink('datepicker.css');
        $JS = "if (window.addEvent) {	window.addEvent('load',\n\tfunction() {\n\t" . implode("\n\t"  , $this->date_pickers) .  "\n\t});\n}\n";
        $template->addHeaderText($JS,'script',"datepicker");        
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

