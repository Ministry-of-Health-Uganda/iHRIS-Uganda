<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 *
 * This File is part of iHRIS
 *
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc.
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the report list
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageDashboard extends I2CE_Page {

    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        function set_cookie($Name, $Value = '', $MaxAge = 0, $Path = '', $Domain = '', $Secure = false, $HTTPOnly = false) {
            header('Set-Cookie: ' . rawurlencode($Name) . '=' . rawurlencode($Value)
                . (empty($MaxAge) ? '' : '; Max-Age=' . $MaxAge)
                . (empty($Path)   ? '' : '; path=' . $Path)
                . (empty($Domain) ? '' : '; domain=' . $Domain)
                . (!$Secure       ? '' : '; secure')
                . (!$HTTPOnly     ? '' : '; HttpOnly'), false);
        }

        $url = false;
        if (! I2CE::getConfig()->setIfIsSet($url , "/modules/DHIS-Dashboard/urls/web_dash")
            || !$url) {
            I2CE::raiseError("Bad url for web dashboard: $url");
            return false;
        }
            

        $myCurl = new MyCurl($url);
        $myCurl->useAuth(true);
        $myCurl->setName("admin");
        $myCurl->setPass("district");
        $myCurl->setPost(true);

        $myCurl->setIncludeHeader(true);

        $myCurl->createCurl($url);
        $data = $myCurl->getWebpage();

// In this case, I am only passing along the PHPSESSID, but you will likely want to pass along everything.
        $cstart = strpos($data, "JSESSIONID");
        $cend = strpos($data, ";", $cstart) - $cstart;
        $cookie = substr($data, $cstart, $cend );

// Likewise.
        $cookie = explode('=', $cookie);
        $cookie_domain = false;
        $cookie_path = false;
        if (!I2CE::getConfig()->setIfIsSet($cookie_domain,"/modules/DHIS-Dashboard/cookie/domain") 
            || !$domain) {
            I2CE::raiseError("No domain set for cookie");
            return false;
        }
        if (!I2CE::getConfig()->setIfIsSet($cookie_path,"/modules/DHIS-Dashboard/cookie/path") 
            || !$path) {
            I2CE::raiseError("No path set for cookie");
            return false;
        }

        set_cookie('JSESSIONID', $cookie[1], time() + 6000, $cookie_path, $cookie_domain,0);
//echo $_COOKIE['JSESSIONID'];

        $this->template->setAttribute( "class", "active", "menuDashboard", "a[@href='sagar']" );
        parent::action();
    }

}

class MyCurl {
    protected $_useragent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
    protected $_url;
    protected $_followlocation;
    protected $_timeout;
    protected $_maxRedirects;
    protected $_cookieFileLocation = 'cookie.txt';
    protected $_post;
    protected $_postFields;
    protected $_referer ="http://www.google.com";

    protected $_session;
    protected $_webpage;
    protected $_includeHeader;
    protected $_noBody;
    protected $_status;
    protected $_binaryTransfer;
    public    $authentication = 0;
    public    $auth_name      = '';
    public    $auth_pass      = '';

    public function useAuth($use){
        $this->authentication = 0;
        if($use == true) $this->authentication = 1;
    }

    public function setIncludeHeader($includeHeader){
        $this->_includeHeader = $includeHeader;
    }

    public function setName($name){
        $this->auth_name = $name;
    }
    public function setPass($pass){
        $this->auth_pass = $pass;
    }

    public function setUserAgent ($userAgent){
        $this->_useragent = $userAgent;
    }

    public function __construct($url,$followlocation = true,$timeOut = 3000,$maxRedirecs = 4,$binaryTransfer = false,$includeHeader = false,$noBody = false)
    {
        $this->_url = $url;
        $this->_followlocation = $followlocation;
        $this->_timeout = $timeOut;
        $this->_maxRedirects = $maxRedirecs;
        $this->_noBody = $noBody;
        $this->_includeHeader = $includeHeader;
        $this->_binaryTransfer = $binaryTransfer;

        $this->_cookieFileLocation = 'cookie.txt';

    }

    public function setReferer($referer){
        $this->_referer = $referer;
    }

    public function setCookiFileLocation($path)
    {
        $this->_cookieFileLocation = $path;
    }

    public function setPost ($postFields)
    {
        $this->_post = true;
        $this->_postFields = $postFields;
    }

    public function createCurl($url = 'nul')
    {
        if($url != 'nul'){
            $this->_url = $url;
        }

        $s = curl_init();

        curl_setopt($s,CURLOPT_URL,$this->_url);
        curl_setopt($s,CURLOPT_TIMEOUT,$this->_timeout);
        curl_setopt($s,CURLOPT_MAXREDIRS,$this->_maxRedirects);
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($s,CURLOPT_FOLLOWLOCATION,$this->_followlocation);
        curl_setopt($s,CURLOPT_COOKIEJAR,$this->_cookieFileLocation);


        if($this->authentication == 1){
            curl_setopt($s, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass);
        }
        if($this->_post)
        {
            curl_setopt($s,CURLOPT_POST,true);
            curl_setopt($s,CURLOPT_POSTFIELDS,$this->_postFields);

        }

        if($this->_includeHeader)
        {
            curl_setopt($s,CURLOPT_HEADER,true);
        }

        if($this->_noBody)
        {
            curl_setopt($s,CURLOPT_NOBODY,true);
        }

//        if($this->_binary)
//        {
//            curl_setopt($s,CURLOPT_BINARYTRANSFER,true);
//        }

        curl_setopt($s,CURLOPT_USERAGENT,$this->_useragent);
        curl_setopt($s,CURLOPT_REFERER,$this->_referer);

        $this->_webpage = curl_exec($s);
        $this->_status = curl_getinfo($s,CURLINFO_HTTP_CODE);


        curl_close($s);
    }

    public function getHttpStatus()
    {
        return $this->_status;
    }

    public function __tostring(){
        return $this->_webpage;
    }

    public function getWebpage(){
        return $this->_webpage;
    }

    public function getFileLocation (){
        return $this->_cookieFileLocation;
    }
};







# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
