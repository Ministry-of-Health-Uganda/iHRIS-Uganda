<?php
/**
 * @copyright © 2009 Intrahealth International, Inc.
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

  /**
   * I2CE_Timer
   * @package I2CE
   * @todo Better Documentation
   */
class I2CE_Timer extends I2CE_Module{

    static function getHooks() {
        return array('post_configure'=>'startTimer',
                     'pre_template_display_DISPLAY'=>'setTime',
                     'template_post_display'=>'showTimes');
    }

        
    public function startTimer() {
        self::startProcess();
    }



        
    public function showTimes($args) {
        $template = $args['template'];
        $timers = array_keys(self::$starttime);
        $head = $template->getElementByTagName('head',0);
        foreach ($timers as $timer) {
            if ($timer == 'default') {
                $timerNode = $template->createElement('meta',array('name'=>"process_time", 'content'=>self::processTime()));
            } else {
                $timerNode = $template->createElement('meta',array('name'=>"process_time_$timer", 'content'=>self::processTime($timer)));
            }
            $head->appendChild($timerNode);
        }
    }

        
    public function setTime($args) {
        $template = $args['template'];
        $template->setDisplayData( "process_time", self::processTime() );
    }



    /**
     * Starts the process timer
     * @param string $timer The name of a timing process.  Default to 'default'
     */
    public static function startProcess($timer= 'default') {
        self::$starttime[$timer] = self::getMicroTime();
                                
    }

    /**
     * gets the current time
     */
    public static function getMicroTime() {
        $timeparts = explode(' ',microtime()); 
        return $timeparts[1].substr($timeparts[0],1);           
    }

    /*
     * gets the time from when the timer started 
     * @param string $timer.  Defaults to 'default'
     */
    public static function processTime($timer = 'default') {
        return  bcsub( self::getMicroTime(), self::$starttime[$timer], 6 );
    }

    public static $starttime = array();
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
