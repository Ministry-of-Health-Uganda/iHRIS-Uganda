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
 */
/**
 ** The module that is a basic message handler
 * @package I2CE
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since Demo-v1.a
 * @version Demo-v2.a
 */


class I2CE_MessageHandler extends I2CE_Module{  
        
    public static function getHooks() {
        return array(  
            'post_page_prepare_display_I2CE_Template'=>'handleMessages',
            'pre_displayed_page_action'=> 'rollOverMessages'
            );
                
    }
        

    public static function getMethods() {
        return array('I2CE_Fuzzy->userMessage'=>'addUserMessage');
    } 

    public function handleMessages($page) { 
        $template = $page->getTemplate();
        if (!is_array($this->immediate_messages)) {
            $this->immediate_messages = array();
        }
        if (!array_key_exists('user_messages',$_SESSION) || !is_array($_SESSION['user_messages'])) {
            $_SESSION['user_messages'] = array();
        }
        if (!array_key_exists('current',$_SESSION['user_messages']) || !is_array($_SESSION['user_messages']['current'])) {
            $_SESSION['user_messages']['current'] = array();
        }
        foreach ($_SESSION['user_messages']['current'] as $class=>$msgs) {              
            if (!array_key_exists($class,$this->immediate_messages)) {
                $this->immediate_messages[$class] = array();
            }
            $this->immediate_messages[$class] =  array_merge($this->immediate_messages[$class],$msgs);
        }
        foreach ($this->immediate_messages as $class=>$msgs) {
            $msgs = array_unique($msgs);
            I2CE_ModuleFactory::callHooks("display_messages_$class",array('template'=>$template,'messages'=>$msgs));
        }
        $this->immediate_messages = array();
        $_SESSION['user_messages']['current'] = array();
    }


    public  function rollOverMessages() {
        if (!array_key_exists('user_messages', $_SESSION) || !is_array($_SESSION['user_messages'])) {
            $_SESSION['user_messages'] = array();
        }
        if (array_key_exists('next', $_SESSION['user_messages'])) {
            $_SESSION['user_messages']['current'] = $_SESSION['user_messages']['next']; 
        } 
        unset( $_SESSION['user_messages']['next']); 
    }

        

    /**
     * Adds a user message to the message handeler
     * @param mixed $obj The calling object.
     * @param string $msg.  The message
     * @param string $class.  The message handler. Defaults to 'default'
     * @param boolean $delated.  Defaults to true.  If falwe then we add it to the current page rather than wait for the next request.
     */
    public function addUserMessage($obj,$msg,$class='default',$delayed = true) {
        if ($delayed) {
            $_SESSION['user_messages']['next'][$class][] = $msg;
        } else {
            if (!is_array($this->immediate_messages)) {
                $this->immediate_messages = array();
            }
            if (!array_key_exists($class, $this->immediate_messages) || !is_array($this->immediate_messages[$class])) {
                $this->immediate_messages = array();
            }
            $this->immediate_messages[$class][]  = $msg;            
        }
    }

    /**
     * An array of arrays of strings index by the message class of messages that should
     * be displayed on the current page.
     * @var protected array $immediate_messages
     */
    protected $immediate_messages;
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
