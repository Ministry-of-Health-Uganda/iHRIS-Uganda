<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
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
* @package i2ce
* @subpackage mail
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1
* @since v4.1
* @filesource 
*/ 
/** 
* Class I2CE_Mailer
* 
* @access public
*/

require_once 'Mail.php'; 
require_once 'Mail/mime.php';

class I2CE_Mailer {

    protected static $method = null;
    protected static $headers = array();
    protected static $params = array();
    protected static $pear_mailer = null;

    /**
     * Mail a message
     * @param mixed $recipients  - an array or a string with comma separated recipients. 
     * @param array $headers an associative array of headers. The header name is used as key and the header value as value. If you want to override the envelope sender of the email, set the Return-Path header and that value will be used instead of the value of the From: header.   Will merge with anyting under /modules/Mailer/mail_message_headers
     * @param string $body
     * @param mixed $html.  Defaults to false.  It set, it will include it as an html attachment
     * @param array $attachments List of attachments to include if using the pear mailer.
     */
    public static function mail($recipients, $headers, $body, $html =false, $attachments = array() ) {
        if (self::$method === null) {
            self::initMailer();
        }
        $headers = array_merge(self::$headers,$headers);
        switch (self::$method) {
        case 'pear':
            return self::mail_pear($recipients, $headers, $body,$html, $attachments );
        case 'localhost':
            if ( count( $attachments ) > 0 ) {
                I2CE::raiseError( "Tried to send attachments when not supported by mailer." );
            }
            return self::mail_localhost($recipients, $headers, $body,$html);
        default:
            return false;
        }
    }



    protected static function initMailer() {
        $backend  = "mail";
        I2CE::getConfig()->setIfIsSet($backend,"/modules/Mailer/mail_server_backend");
        I2CE::getConfig()->setIfIsSet(self::$params,"/modules/Mailer/mail_server_params",true);
        I2CE::getConfig()->setIfIsSet(self::$headers,"/modules/Mailer/mail_message_headers",true);
        if (class_exists('Mail',false)) {
            self::$method = 'pear';
            $allowed_backends = array("mail","smtp","sendmail");
        } else {
            I2CE::raiseError("No Mail class : try 'sudo apt-get install pear-mail'");
            self::$method = 'localhost';
            $allowed_backends = array("localhost");
        }
        if (!in_array($backend,$allowed_backends)) {
            I2CE::raiseError("Mailing backend $backend is not in list: " . implode(" ", $allowed_backends));
            self::$method = false;
            return false;
        }
        if (self::$method == 'pear') {
            if (array_key_exists('auth',self::$params) && self::$params['auth'] == 1) {
                self::$params['auth'] = true;
            }
            try {
                self::$pear_mailer =& Mail::factory($backend, self::$params);
            }catch (Exception $e) {
                I2CE::raiseError("Could not create pear mailer:\n" . $e->getMessage());
                self::$method = false;
                return false;
            }
        }
    }

    protected static function mail_localhost($recipients, $headers, $body,$html = false)  {
        if (array_key_exists('Subject',$headers)) {
            $subject = $headers['Subject'];
            unset($headers['Subject']);
        } else {
            I2CE::raiseError("Warning no subject");
            $subject = '';
        }
        if (is_array($recipients)) {
            $recipients = implode(" , ",$recipients);
        }
        $result = mail($recipients,$subject,$body,implode("\r\n",$headers),implode(" ", self::$params));
        if (!$result) {
            I2CE::raiseError("Could not mail message to localhost");
        }
        return $result;

    }



    protected static function mail_pear($recipients, $headers, $body,$html = false, $attachments = array() ) {
        try {
            if ($html || count($attachments) > 0) {
                //$headers['Content-Type'] = 'multipart/mixed';
                $mime = new Mail_mime(
                    array(
                        'eol'=>"\n",
                        'text_charset'=>'UTF-8',
                        'html_charset'=>'UTF-8'
                    ));//array('eol' => "\n"));
                //I2CE::raiseError($html);
                $mime->setHTMLBody($html);
                $mime->setTXTBody($body);
                foreach( $attachments as $attachment ) {
                    $mime->addAttachment( $attachment['data'], 
                            $attachment['type'], $attachment['name'], 
                            false );
                }
                $body = $mime->get();
                $headers = $mime->headers($headers);

                //adapted from http://pear.php.net/manual/en/package.mail.mail-mime.example.php#10541
                
                // $boundary = array();
                // preg_match("/boundary=\"(.[^\"]*)\"/e", $headers["Content-Type"], $boundary);                
                // //echo "<pre>"; var_dump($headers); var_dump($boundary); die();
                // $boundary = $boundary[1];
                // $boundaryNew =  uniqid() ;
                // $headers["Content-Type"] = preg_replace('/boundary="(.[^"]*)"/', 'boundary="' . $boundaryNew . '"', $headers["Content-Type"]);
                // $body = preg_replace("/^\-\-" . $boundary . "/s", "--" . $boundaryNew, $body);
                // $body = preg_replace("/" . $boundary . "--$/s", $boundaryNew . "--", $body);
                // $body = preg_replace("/" . $boundary . "--(\s*)--" . $boundary . "/s", $boundary . "--$1--" . $boundaryNew, $body);

                // // Workaround, because "\r" breaks the e-mails (possibly a problem with Pear::Mail_Mime and Postfix)
                // foreach($headers as $key=>$header) {
                //     $headers[$key] = str_replace("\r\n", "\n", $header);
                // }

            }
            $result = self::$pear_mailer->send($recipients, $headers, $body);
            if (I2CE::pearError($result, "Could not send message via pear mailer")) {
                return false;
            }
        } catch (Exception $e) {
            I2CE::raiseError("Could not send message via pear mailer:\n" . $e->getMessage());
            return false;
        }
        return true;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
