<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_Module_YAML
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/

require_once('spyc.php');

class I2CE_Module_YAML extends I2CE_Module {

    public static function getMethods() {
        return array(
            'I2CE_Configurator->loadConfigFile_YAML'=>'loadConfigFile_YAML'
            );
    }


    /**
     * fuzzy method attached to I2CE_Configurator
     * Load a config file with an .yaml extension and set the verbosity of its error reporting
     * @param I2CE_Configurator $configurator
     * @param $contents
     * @param $verbose_errors
     * @returns I2CE_MagicDataTemplate_YAML on sucess, null on failure
     */
    protected function  loadConfigFile_YAML($configurator,$contents,$verbose_errors) {
        $yaml = new I2CE_MagicDataTemplate_YAML();
        $yaml->setVerboseErrors($verbose_errors);
        $yaml->loadRootFile('yaml_magicdata.xml');
        if (!$yaml->loadFromYAMLArray(Spyc::YAMLLoad($contents))) {
            I2CE::raiseError("Unable to load YAML config ");
            return null;
        } else {
            return $yaml;
        }
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
