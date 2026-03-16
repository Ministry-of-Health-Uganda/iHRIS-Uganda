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


class I2CE_MessageBox extends I2CE_Module{      

    public static function getHooks() {
        return array(  
            'display_messages_default' => 'displayMessages'
            );
                
    }

        

    public function displayMessages($args) {
        $template = $args['template'];
        $messages = $args['messages'];
        if (count($messages) == 0) {
            return;
        }
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('messageDefault.js');
        $template->addHeaderLink('messageDefault.css');
        $messageDiv = $template->createElement('div',array('id'=>'message_box_default'),''); 
        $js = 'var e=$("message_box_default"); if (e) {e.destroy();}';
        $messageClose = $template->createElement('div',array('id'=>'message_box_close','class'=>'message_box_close','onClick'=>$js),'[Close]'); 
        $messageDiv->appendChild($messageClose);
        $siteContent = $template->getElementById('siteContent');
        if (!$siteContent instanceof DOMNode) {
            $siteContent =  $template->getElementByTagName( "body", 0 );
        }
        $siteContent->insertBefore($messageDiv,$siteContent->firstChild);
        if (count($messages) == 1) {
            $messageDiv->appendChild($this->createMsg($template,$messages[0]));
        } else {
            $ulNode = $template->createElement('ul');
            $messageDiv->appendChild($ulNode);
            foreach ($messages as $msg) {
                $liNode = $template->createElement('li');
                $ulNode->appendChild($liNode);
                $liNode->appendChild($this->createMsg($template,$msg));
            }
        }
    }


    protected function createMsg($template,$msg) {
        if (preg_match("/^[\n\s]*(<([\w]+)[^>]*?>)(.*?)(<\/\\2>)[\n\s]*$/", $msg, $matches)) {
            return $template->importText( $msg, $matches[2] );
        } else {
            return $template->importText('<div>'.$msg.'</div>','div');
        }
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
