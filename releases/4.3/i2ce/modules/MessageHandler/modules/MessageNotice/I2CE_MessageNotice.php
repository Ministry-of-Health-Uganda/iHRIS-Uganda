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
 ** The module for displaying default messages
 * @package I2CE
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since Demo-v1.a
 * @version Demo-v2.a
 */


class I2CE_MessageNotice extends I2CE_Module{   

    public static function getHooks() {
        return array(  
            'display_messages_notice' => 'displayMessages'
            );
                
    }

        

    public function displayMessages($args) {
        $template = $args['template'];
        $messages = $args['messages'];
        if (count($messages) == 0) {
            return;
        }
        $rand = rand(1000,9999);
        $msgrand = 'message_box_notice_msgs_' .$rand . '_msg';
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('mootools-more.js');
        $template->addHeaderLink('messageBox.js');
        $template->addHeaderLink('messageBox.css');
        $siteContent = $template->getElementById('siteContent');
        if (!$siteContent instanceof DOMNode) {
            $siteContent =  $template->getElementByTagName( "body", 0 );
        }
        if (!$siteContent instanceof DOMNode) {
            return;
        }
        $js = 'if (!MessageBoxInstance) {
       var MessageBoxInstance = new MessageBox("' . $msgrand . '");
}
MessageBoxInstance.show("1")';
        if (! ($messageDiv = $template->loadFile("message_notice.html")) instanceof DOMElement) {
            I2CE::raiseError("Could not load message_notice.html");
            return false;
        }
        $nodes = $template->query(".//div[@class='MessageBoxMessageList']",$messageDiv);
        if (!$nodes instanceof DOMNodeList || $nodes->length != 1) {
            I2CE::raiseError("Couldn't find message list:" . $nodes->length);
            return false;
        }
        $divNode = $nodes->item(0);

        $messageDiv->setAttribute('id',$msgrand);
        $siteContent->insertBefore($messageDiv,$siteContent->firstChild);
        
        $count = 0;

        foreach ($messages as $msg) {
            $msgNode = $template->createElement('span');
            $divNode->appendChild($msgNode);
            $count++;
            $msgNode->setAttribute('style','display:none');
            $msgNode->appendChild($this->createMsg($template,$msg));
            $msgNode->setAttribute('id', $msgrand . '_' . $count);
            $msgNode->setAttribute('class', 'MessageBox ' . $msgrand  );
        }
        $template->addHeaderText($js,'javascript', 'message_box_notice');

    }


    protected function createMsg($template,$msg) {
        if (preg_match("/^\s*<(\w+)/", $msg, $matches)) {
            return $template->importText( $msg, $matches[1] );
        } else {
            return $template->createTextNode($msg);
        }
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
