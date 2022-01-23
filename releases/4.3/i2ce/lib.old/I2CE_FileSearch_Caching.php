<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
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
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */


if (!class_exists('I2CE_FileSearch_Caching',false)) {

    /**
     * pull in main class
     */
    require_once 'I2CE_FileSearch.php';

/**
 *  I2CE_FileSearch_Caching
 * @package I2CE
 * @todo Better Documentation
 */
    class I2CE_FileSearch_Caching extends I2CE_FileSearch{
        /**
         * Constructor for the FileSearch class
         * @param boolean $hidden. Defaults to false.  Whether or not to search
         *        hidden sub-directories.  At the moment it does not have meaning
         *        on non unix like platforms.
         * @param boolean $current_working. Defaults to false.  Whether or not to
         *        search the current working directory for the file.  If so, it
         *        checks there first, before the other paths.
         * @param boolean $make_absolut.  Defaults to false.  Whether
         *        or not to make a relative path absolute when adding it.
         */
        public function  __construct( $hidden = false,$current_working=false,$make_absolut = false) {
            parent::__construct($hidden,$current_working,$make_absolut);
            $this->stale_time = 60;
            I2CE::getConfig()->setIfIsSet($this->stale_time,'/I2CE/fileSearch/stale_time');
        }


        /**
         *Set the prefix to be used for the caching file search
         * @param string $prefix
         */
        public function setPrefix($prefix) {
            $this->prefix = $prefix;
        }

        /**
         * The rpefix to use fir the APC keys
         *
         */
        protected $prefix;

        
        /**
         * A local copy of the data (if any) stored in the magic data at /I2CE/fileSearch/stale_time
         * @var protected int $stale_time
         */
        protected $stale_time;
        /**
         * Find a file (or directory) of a certain category
         * @param string $category  the category of the file
         * @param string $file_name the file name of the file we wish to find
         * @param boolean $find_all Defatults to false
         * @returns mixed.  Returns either a string which is the path and file name of the file 
         * we found, or null if we did not find the file.
         */
        public function search($category,$file_name, $find_all = false,$namespace = false) {
            if (!(array_key_exists($category,$this->ordered_paths))) {                 
                $factory = I2CE_ModuleFactory::instance();
                $factory->loadPaths(null,$category,false,$this);
            }
            if ($find_all || $this->stale_time  < 0) {
                return parent::search($category,$file_name, $find_all);
            }
            if (array_key_exists($category,$this->preferred_locales) && is_array($this->preferred_locales[$category])) {
                $locales = $this->preferred_locales[$category];
            } else {
                $locales = I2CE_Locales::getPreferredLocales();
            }                        
            if (array_key_exists('I2CE_FileSearch_Caching',$_SESSION) && 
                is_array($_SESSION['I2CE_FileSearch_Caching']) &&
                array_key_exists($category, $_SESSION['I2CE_FileSearch_Caching']) &&
                is_array($_SESSION['I2CE_FileSearch_Caching'][$category]) &&
                array_key_exists($file_name, $_SESSION['I2CE_FileSearch_Caching'][$category]) &&
                is_array($_SESSION['I2CE_FileSearch_Caching'][$category][$file_name])) {
                $data = $_SESSION['I2CE_FileSearch_Caching'][$category][$file_name];
                if (array_key_exists('time',$data) && array_key_exists('location',$data) && array_key_exists('locale',$data)) {
                    if (is_readable($data['location']) 
                        &&  (time() - $data['time'] < $this->stale_time))  {
                        $this->found_locales = $data['locale'];
                        return $data['location'];
                    } else {
                        unset($_SESSION['I2CE_FileSearch_Caching'][$category][$file_name]);
                    }
                }
            }
            //did not find the file
            $location =  parent::search($category,$file_name, false);
            if (!is_string($location) || strlen($location) == 0) {
                return null;
            }
            $_SESSION['I2CE_FileSearch_Caching'][$category][$file_name] = 
                array(
                    'time'=>time(),
                    'location'=>$location,
                    'locale'=>$this->found_locales
                    );
            return $location;
        }

        public function clearCache() {
            unset($_SESSION['I2CE_FileSearch_Caching']);
        }


        

    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
