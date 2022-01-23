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
 * The page to send feedback about the site.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the feedback form.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PageFeedback extends I2CE_Page {
        
    /**
     * Perform the main actions of the page.
     * @global array
     */
    protected function action() {
        $i2ce_config = I2CE::getConfig()->I2CE;
        parent::action();

                                      
        $fields = array(
            "Name" => false,
            "Company" => false,
            "Title" => false,
            "Industry" => false,
            "Address" => false,
            "City" => false,
            "State" => false,
            "Postal_Code" => false,
            "Country" => false,
            "Telephone" => false,
            "Fax" => false,
            "Email" => false,
            "Comments" => false,
            "referer" => false,
            );
        $valid = true;
        $err_msg = "";
        if ( $this->isPost() ) {
            $message = "";
            foreach( $fields as $name => $required ) {
                if ( $required && !I2CE_Validate::checkString( $this->post($name) ) ) {
                    $valid = false;
                    $err_msg .= "<li>$name is blank.</li>\n";
                }
                $message .= $name . ": " . $this->post($name) . "\n";
            }
            $message .= "Username : " . $this->user->username()  . '--' . $this->user->displayName() .  "\n";
            $message .= "User Role : " . $this->user->getRole() . "\n";
            if ( $valid ) {
                $this->template->addFile( "feedback_thanks.html" );
                $this->template->setDisplayData( "return_link", 'home');
                I2CE_Mailer::mail($i2ce_config->feedback->to, array('Subject'=>$i2ce_config->feedback->subject,'From'=>$this->post('Email')), $message);
                return;
            }
        }
        $this->template->addFile( "feedback_form.html" );
        if (array_key_exists('contact_address',$this->args) && $this->args['contact_address']) { 
            if (($formNode = $this->template->getElementById('feedback_form')) instanceof DOMElement) {
                $formNode->setAttribute('action','mailto:' . $this->args['contact_address'] . '?Subject=iHRIS Feedback');
                $formNode->setAttribute('enctype','text/plain');
            }
        }
        if ( !$valid && $err_msg != "" ) {
            $this->template->addText( '<div id="error">There were some problems with your information:<ul>' . $err_msg . '</ul></div>' );
        }
        if ( $this->isPost() ) {
            foreach( $fields as $name => $required ) {
                if ( $name == "Comments" ) {
                    $this->template->addText( '<textarea name="Comments" rows="10" cols="45" id="Comments">' 
                                              . $this->post($name) . '</textarea>', "textarea", $name );
                } else {                    
                    $this->template->setAttribute( "value", $this->post($name), $name, "." );
                }
            }
        } else { 
            $this->template->setAttribute( "value", $_SERVER['HTTP_REFERER'], "referer", "." );                 
            if (!array_key_exists('auto_populate',$this->args) || $this->args['auto_populate']) { //defaults to true
                foreach (array('email'=>'Email','phone'=>'Phone','fax'=>'Fax') as $detail=>$data) {
                    if (I2CE_User::hasDetail($detail)) {
                        $this->template->setDisplayDataImmediate($data,$this->user->$detail);
                    }
                }
                $this->template->setDisplayDataImmediate('Name',$this->user->displayName());
            }
        }
    }
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
