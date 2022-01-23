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
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */

/**
 * I2CE_Dumper
 * @package I2CE
 * @todo Better Documentation
 */
class I2CE_Dumper {

    public static function cacheFileLocation($prefix,$file_name,$locale,$headers, $file_loc,$cacheTime, $ttl) {
        if (!function_exists('apc_fetch')) {
            return;
        }
        apc_store("I2CE_Dumper_{$prefix}_CacheTime",$cacheTime,$ttl);
        apc_store("I2CE_Dumper_{$prefix}_Location:$locale:$file_name",$file_loc,$ttl);
        apc_store("I2CE_Dumper_{$prefix}_Headers:$file_name",$headers,$ttl);
    }


    public static function dumpCacheFileLocation($prefix,$file_name) {
        if (!function_exists('apc_fetch')) {
            return false;
        }
        $locales = I2CE_Locales::getPreferredLocales();
        foreach ($locales as $locale) {
            $file_loc = apc_fetch("I2CE_Dumper_{$prefix}_Location:$locale:$file_name");
            if ($file_loc) {
                break;
            }
        }
        if (!$file_loc) {
            return false;
        }
        $headers = apc_fetch("I2CE_Dumper_{$prefix}_Headers:$file_name");
        if (!$headers) {
            return false;
        }
        $cacheTime = apc_fetch("I2CE_Dumper_{$prefix}_CacheTime");
        return  self::dumpContents($file_loc,$headers, $cacheTime);
    }


    /**
     * Cleanly end all output buffers except that if the bottom-most output buffer is not the default handler (e.g. 'zlib output compression') then it preserves it.
     * @returns string Any buffered ouput
     */
    public static function cleanlyEndOutputBuffers() {
        $errors = '';
        if (ob_get_level() > 0) {
            if (self::usesDefaultOutputBuffering()) {
                $start_clear = 0;
            } else {
                $start_clear = 1;
            }
            while (($lvl = ob_get_level()) > $start_clear) {
                $errors .= ob_get_contents();
                if (!@ob_end_clean()) {
                    I2CE::raiseError("Could not end clean at: $lvl");
                    break;
                }
            }
        }
        return rtrim($errors);
    }
    
    /**
     * check to see if we are using default output buffering.
     */
    public static function usesDefaultOutputBuffering() {
        $obs = ob_get_status(true);
        return (is_array($obs) && count($obs) > 0 && is_array($obs[0]) && array_key_exists('name',$obs[0]) && $obs[0]['name'] == 'default output handler');
    }


    public static function prepForDump($file_mtime,$file_len,$headers, $cacheTime) {
        if ( ($errors = self::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Errors:\n" . $errors);
        }
        if (is_array($headers)) {
            foreach ($headers as $header) {
                if (is_array($header)) {
                    switch (count($header)) {
                    case '3':
                        header($header[0], $header[1], $header[2]);
                        break;
                    case '2':
                        header($header[0], $header[1]);
                        break;
                    case '1':
                        header($header[0]);
                        break;
                    default:
                        I2CE::raiseError("Unrecognized header format");
                    }
                } else if (is_string($header)) {
                    header($header);
                }
            }
        }
        if ($cacheTime === false || $file_mtime ===false ) {
            //make this file not cached.
            header("Cache-Control: max-age=1, s-maxage=1, no-store, no-cache, must-revalidate");
            header( 'Cache-Control: post-check=0, pre-check=0', false );
            header( 'Pragma: no-cache' ); 
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()-10) . " GMT");
            header("ETag: PUB" . time());
            header("Pragma: no-cache");
            session_cache_limiter("nocache");
        } else {
            $req_headers = apache_request_headers();
            if (isset($req_headers['If-Modified-Since']) && (strtotime($req_headers['If-Modified-Since']) == $file_mtime)) {
                // Client's cache IS current, so we just respond '304 Not Modified'.
                header('Last-Modified: '.gmdate('D, d M Y H:i:s', $file_mtime).' GMT', true, 304);
                header("Cache-Control: public,max-age=$cacheTime");
                header("Pragma: public");
                header('Expires: ' . gmdate('D, d M Y H:i:s', time()+ $cacheTime) . ' GMT');
                die();
            } else {
                //set the cache to be $cacheTime seconds in the future
                //header("Cache-Control: must-revalidate");
                header("Cache-Control: public, max-age=$cacheTime,pre-check=$cacheTime,post-check=$cacheTime");
                header("Pragma: public");
                header('Expires: ' . gmdate('D, d M Y H:i:s', time()+ $cacheTime) . ' GMT');
            }
        }
        if ($file_mtime !== false)  {
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $file_mtime).' GMT', true, 200);
        }
        if ($file_len !== false) {
            header("Content-length: $file_len");         
        }
    }
    


    public static function dumpContents($file_loc,$headers, $cacheTime) {
        //now do the caching header for a file... http://www.php.net/manual/en/function.header.php#61903

        if ( (!is_readable($file_loc))) {
            I2CE::raiseError("Unable to read file at {$file_loc}", E_USER_NOTICE);
            //do something else?
            return false;
        }   
        
        self::prepForDump(filemtime($file_loc),filesize($file_loc),$headers,$cacheTime);

        //open the file
        if ( is_array($_GET) && array_key_exists('newman', $_GET) ) {
            $contents = file_get_contents($file_loc);
            $contents = str_replace( "{I2CESITEROOT}", I2CE::getAccessedBaseURL(), $contents );
            echo $contents;
        } else {
            readfile($file_loc);
        }
        return true;
    }




    public static function dumpStaticURL($key_prefix,$url_prefix = 'file') {
        if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
            return false;
        }
        if (!function_exists('apc_fetch')) {
            return false;
        }
        if (!array_key_exists('PATH_INFO',$_SERVER) || ! $_SERVER['PATH_INFO']) {
            return false;
        }
        $path = $_SERVER['PATH_INFO'];
        $path = trim($path,'/');
        $url_prefix = ltrim($url_prefix,'/');
        $prefix_len = strlen($url_prefix);
        if (strlen($path) == 0 || $prefix_len == 0) {
            return false;
        }
        if ($url_prefix[$prefix_len - 1]  != '/') {
            $url_prefix .= '/';
            $prefix_len++;
        }
        if (substr($path, 0,$prefix_len) != $url_prefix) {
            return false;
        }
        return self::dumpCacheFileLocation($key_prefix,substr($path,$prefix_len));
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
