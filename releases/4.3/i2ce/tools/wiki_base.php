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
 * Translate Templates
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2008, 2008 IntraHealth International, Inc. 
 * @version 1.0
 */



/*****************************************
 *
 *  Wiki webservice helper function
 *
 *****************************************/
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CLI.php');
require_once (  dirname(__FILE__) . "/snoopy/Snoopy.class.php");
$wiki_url = "http://wiki.ihris.org/w/";
$wikiroot_url = $wiki_url .  "index.php";
$wikiapi_url = $wiki_url . "api.php";
$snoopy = null;
$login = false;
$create_redirects = true;

function wikiLogin() {
    global $login;
    global $wikilogin;
    global $snoopy;
    global $wikiapi_url;
    while (!$login) {
        $wikilogin['action']= 'login';
        $wikilogin['lgname'] = trim(ask('wiki user name'));
        $wikilogin['lgpassword'] = getPassword('wikis');
        $wikilogin['format'] ='php';
        if (!$snoopy instanceof Snoopy) {
            $snoopy = new Snoopy();
        }
        if(!$snoopy->submit($wikiapi_url, $wikilogin)) {
            I2CE::raiseError("Could not log in to $wikiapi_url");
            continue;
        }
        $res = unserialize($snoopy->results);
        if (array_key_exists('error',$res)) {
            I2CE::raiseError("Could not login:\n" . print_r($res['error'],true));            
            continue;
        }
        if (!array_key_exists('login',$res) || !is_array($res['login']) || !array_key_exists('result',$res['login'])) {
            I2CE::raiseError("Error logging in:" . print_r($res,true));
            continue;
        } 
        if ($res['login']['result'] == 'NeedToken' && array_key_exists('token',$res['login']) && $res['login']['token']) {
            $wikilogin['lgtoken'] = $res['login']['token'];
            $snoopy->setcookies();
            if(!$snoopy->submit($wikiapi_url, $wikilogin)) {
                I2CE::raiseError("Could not log in to $wikiapi_url");
                continue;
            }
            $res = unserialize($snoopy->results);
            if (array_key_exists('error',$res)) {
                I2CE::raiseError("Could not login:\n" . print_r($res['error'],true));            
                continue;
            }
            if (!array_key_exists('login',$res) || !is_array($res['login']) || !array_key_exists('result',$res['login'])) {
                I2CE::raiseError("Error logging in");
                continue;
            } 
        }        
        if ($res['login']['result'] != 'Success') {
            I2CE::raiseError("No success logging in:" . print_r($res,true));
            continue;
        }
        I2CE::raiseError("Logged into $wikiapi_url as " . $wikilogin['lgname']);
        $snoopy->setcookies();
        $login = true;
    }
    return $login;
}

function wikiLookupNamespaceId($namespace) {
   global $wikiapi_url;
   global $snoopy;
   if (!wikiLogin()) {
       I2CE::raiseError("Could not login to wiki");
       return false;
   }
   $post = array(
       'action'=>'query',
       'meta'  =>'siteinfo',
       'siprop'=>'namespaces',
       'format'=>'php'
       );
   if (!$snoopy->submit($wikiapi_url,$post)) {
       I2CE::raiseError("Could not test for $title");
       return false;
   }
   $res = unserialize($snoopy->results);
   if (!is_array($res) || !array_key_exists('query',$res) || !is_array($res['query'])) {
       return false;
   }
   $res = $res['query'];
   if (!array_key_exists('namespaces',$res) || !is_array($res['namespaces'])) {
       return false;
   }
   $res = $res['namespaces'];
   foreach ($res as $id=>$data) {
       if (!is_array($data) || !array_key_exists('canonical',$data)) {
           continue;
       }
       if ($data['canonical'] == $namespace) {
           return $id;
       }
   }
   return false;
}



function wikiGetTitlesWithNamespace($namespace, $version = '', $redirects_filter = 'all') {
    //redirects_filter can be 'all', 'redirects', 'nonredirects'
   global $wikiapi_url;
   global $snoopy;
   $titles = array();
   if (!wikiLogin()) {
       I2CE::raiseError("Could not login to wiki");
       return $titles;
   }
   $namespace_id = wikiLookupNamespaceId($namespace);
   if ($namespace_id == false) {
       I2CE::raiseError("Namespace $namespace not found");
       return $titles;
   }
   $post = array(
       'action'=>'query',
       'list' =>'allpages',
       'apnamespace' => $namespace_id,
       'format'=>'php',
       'apfilterredir'=>$redirects_filter,
       'aplimit'=>5000
       );
   if (!$snoopy->submit($wikiapi_url,$post)) {
       I2CE::raiseError("Could not test for $title");
       return false;
   }
   $res = unserialize($snoopy->results);
   if (!is_array($res) || !array_key_exists('query',$res)) {
       I2CE::raiseError("Bad query 0");
       return $titles;
   }
   $res = $res['query'];
   if (!is_array($res) || !array_key_exists('allpages',$res)) {
       I2CE::raiseError("Bad query 2");
       return $titles;
   }
   $res = $res['allpages'];
   if ($version) {
       $version = wikiGetVersionedTitleAppend($version);
   }
   $ver_len = strlen($version);
   foreach ($res as $r) {
       if (!array_key_exists('title',$r) || !is_string($r['title'])) {
           continue;
       }
       $title = $r['title'];
       if ($version && substr($title,-$ver_len) != $version) {
           continue;
       }
       $titles[] = $title;
   }
   return $titles;   

}


function wikiGetTitlesWithPrefix($prefix,$redirects_filter = 'all') {
    //redirects_filter can be 'all', 'redirects', 'nonredirects'
   global $wikiapi_url;
   global $snoopy;
   $titles = array();
   if (!wikiLogin()) {
       I2CE::raiseError("Could not login to wiki");
       return $titles;
   }
   $post = array(
       'action'=>'query',
       'list' =>'allpages',
       'apprefix' => $prefix,
       'format'=>'php',
       'apfilterredir'=>$redirects_filter,
       'aplimit'=>5000
       );
   if (!$snoopy->submit($wikiapi_url,$post)) {
       I2CE::raiseError("Could not test for $title");
       return false;
   }
   $res = unserialize($snoopy->results);
   if (!is_array($res) || !array_key_exists('query',$res)) {
       I2CE::raiseError("Bad query 0");
       return $titles;
   }
   $res = $res['query'];
   if (!is_array($res) || !array_key_exists('allpages',$res)) {
       I2CE::raiseError("Bad query 2");
       return $titles;
   }
   $res = $res['allpages'];
   foreach ($res as $r) {
       if (!array_key_exists('title',$r)) {
           continue;
       }
       $titles[] = $r['title'];
   }
   return $titles;   
}


function wikiHasTitle($title) {
    global $snoopy;
    global $wikiapi_url;
    if (!wikiLogin()) {
        I2CE::raiseError("Could not login to wiki");
        return false;
    }
    $post = array(
        'action'=>'query',
        'prop' => 'info',
        'redirects' => 1,
        'format'=>'php',
        'titles'=>$title);
    if (!$snoopy->submit($wikiapi_url,$post)) {
        I2CE::raiseError("Could not test for $title");
        return false;
    }
    $res = unserialize($snoopy->results);
    if (array_key_exists('error',$res)) {
        I2CE::raiseError("Could not find token:\n" . print_r($res['error'],true));
        return false;
    }
    $res = $res['query'];
    if (!is_array($res) ) {
        I2CE::raiseError("Invalid token 0:\n" . print_r($res,true));
        return false;
    }
    if (!array_key_exists('pages',$res)) {
        return false;
    }
    $res = $res['pages'];
    if (!is_array($res) || count($res) != 1) {
        I2CE::raiseError("Invalid token 2:\n" . print_r($res,true));
        return false;
    }
    reset($res);
    return (key($res) > 0);  //key == -1 means the page does not exist
}

function wikiDelete($title) {
   global $wikiapi_url;
   global $snoopy;
   if (!wikiLogin()) {
       I2CE::raiseError("Could not login to wiki");
       return $titles;
   }
    $post = array(
        'action'=>'query',
        'prop' => 'info',
        'format'=>'php',
        'titles'=>$title,
        'intoken' => 'delete');
    if (!$snoopy->submit($wikiapi_url,$post)) {
        I2CE::raiseError("Could not get edit token");
        return false;
    }
    $res = unserialize($snoopy->results);
    if (array_key_exists('error',$res)) {
        I2CE::raiseError("Could not find token:\n" . print_r($res['error'],true));
        return false;
    }
    $res = $res['query'];
    if (!is_array($res) ) {
        I2CE::raiseError("Invalid token 0:\n" . print_r($res,true));
        return false;
    }
    if (!array_key_exists('pages',$res)) {
        I2CE::raiseError("Invalid token 1:\n" . print_r($res,true));
        return false;
    }
    $res = $res['pages'];
    if (!is_array($res) || count($res) != 1) {
        I2CE::raiseError("Invalid token 2:\n" . print_r($res,true));
        return false;
    }
    reset($res);
    $res = current($res);
    if (!array_key_exists('deletetoken',$res)) {
        I2CE::raiseError("Invalid token 3:\n" . print_r($res,true));
        return false;
    }

   $post = array(
       'action'=>'delete',
       'token'=> $res['deletetoken'],
       'format'=>'php',
       'title'=>$title);
   if (!$snoopy->submit($wikiapi_url,$post)) {
       I2CE::raiseError("Could not delete for $title");
       return false;
   }
   $res = unserialize($snoopy->results);
   return (is_array($res) && array_key_exists('delete',$res) && is_array($res['delete']) && count($res['delete']) > 0);
}

function wikiIsRedirect($title) {
    global $snoopy;
    global $wikiapi_url;
    if (!wikiLogin()) {
        I2CE::raiseError("Could not login to wiki");
        return false;
    }
    $post = array(
        'action'=>'query',
        'prop' => 'info',
        'redirects' => 1,
        'format'=>'php',
        'titles'=>$title);
    if (!$snoopy->submit($wikiapi_url,$post)) {
        I2CE::raiseError("Could not test for $title");
        return false;
    }
    $res = unserialize($snoopy->results);
    if (array_key_exists('error',$res)) {
        I2CE::raiseError("Could not find token:\n" . print_r($res['error'],true));
        return false;
    }
    $res = $res['query'];
    if (!is_array($res) ) {
        I2CE::raiseError("Invalid token 0:\n" . print_r($res,true));
        return false;
    }
    return (array_key_exists('redirects',$res) && is_array($res['redirects']) && count($res['redirects']) > 0);
}

function wikiPut($title,$text) {
    global $wikiapi_url;
    global $snoopy;
    $post = array(
        'action'=>'query',
        'prop' => 'info',
        'format'=>'php',
        'titles'=>$title,
        'intoken' => 'edit');
    if (!$snoopy->submit($wikiapi_url,$post)) {
        I2CE::raiseError("Could not get edit token");
        return false;
    }
    $res = unserialize($snoopy->results);
    if (array_key_exists('error',$res)) {
        I2CE::raiseError("Could not find token:\n" . print_r($res['error'],true));
        return false;
    }
    if (array_key_exists('warnings',$res)) {
        I2CE::raiseError("Warning on file upload -- aborting\n" . print_r($res['warnings'],true));
        return false;
    }
    $res = $res['query'];
    if (!is_array($res) ) {
        I2CE::raiseError("Invalid token 0:\n" . print_r($res,true));
        return false;
    }
    if (!array_key_exists('pages',$res)) {
        I2CE::raiseError("Invalid token 1:\n" . print_r($res,true));
        return false;
    }
    $res = $res['pages'];
    if (!is_array($res) || count($res) != 1) {
        I2CE::raiseError("Invalid token 2:\n" . print_r($res,true));
        return false;
    }
    reset($res);
    $res = current($res);
    if (!array_key_exists('edittoken',$res)) {
        I2CE::raiseError("Invalid token 3:\n" . print_r($res,true));
        return false;
    }
    $post = array(
        'action' => 'edit',
        'title' => $title,
        'text'=>$text,
        'format'=>'php',
        'token'=> $res['edittoken'],
        'watch' => 1);
    if (!$snoopy->submit($wikiapi_url,$post)) {
        I2CE::raiseError("Could not submit the page: $title");
        return false;
    }
    $res = unserialize($snoopy->results);
    if (!is_array($res) ) {
        I2CE::raiseError("Could not submit:\n" . print_r($snoopy->results,true) . "\nLength=" . strlen($text));
        return false;
    }
    if (array_key_exists('error',$res)) {
        I2CE::raiseError("Could not submit:\n" . print_r($res['error'],true));
        return false;
    } 
    return true;
}




function wikiGetVersionedTitleAppend($version) {
    return  ' (' . $version . ')';
}

function wikiGetVersionedTitle($title,$version) {
    return $title . wikiGetVersionedTitleAppend($version);
}



function wikiGetAllVersions($title) {
    $existing_versions = array();
    $len = strlen($title);
    foreach (  wikiGetTitlesWithPrefix($title . ' (', 'nonredirects') as $t) {
        $extra = trim(substr($t,$len));
        if (strlen($extra) == 0) {
            continue;
        }
        if (!preg_match('/^\(([0-9]\.[0-9]+\.[0-9]+)\)$/',$extra,$matches)) {
            continue;
        }
        $existing_versions[$matches[1]] = $t;
    }
    return $existing_versions;
}





function wikiMakeVersioned($title,$text, $version) {
    global $wikiapi_url;
    global $snoopy;
    global $is_dev;
    global $main_version;
    global $create_redirects;
    if (!wikiLogin()) {
        return false;
    }
    $title = trim($title);
    if (strlen($title) == 0) {
        I2CE::raiseError("Empty page title");
        return false;
    }
    if (!$text) {
        I2CE::raiseError("No text for $title");
        return false;
    }
    $vers_title = wikiGetVersionedTitle($title,$version);
    $dev_title = wikiGetVersionedTitle($title,'Development');
    $dev_version_exists = wikiHasTitle($dev_title);
    $other_versions_template = "{{otherversions|$title}}\n";
    $other_versions_page_title = wikiGetVersionedTitle($title ,'versions');
    $other_versions_page = "The page [[$title]] has multiple versions:\n";
    $all_versions = wikiGetAllVersions($title);
    if ($is_dev) {
        $page_title = $dev_title;
    }else {
        $page_title = $vers_title;
        if (!array_key_exists($version,$all_versions)) {
            $all_versions[$version] = $vers_title;
        }
    }
    krsort($all_versions);
    foreach ( $all_versions   as $v=>$v_title) {
        $other_versions_page .= "* [[ $v_title | Version $v]]\n";
    }
    if ($is_dev || $dev_version_exists) {
        $other_versions_page .= "* [[ $dev_title | Development Version]]\n";
    }
    $other_versions_page .= "{{disambig}}\n{{DEFAULTSORT:" . wikiGetVersionedTitle($title,'versions') . "}}\n[[Category:Broadcast call sign disambiguation pages]]\n";
    if (!wikiPut($other_versions_page_title,$other_versions_page)) {
        I2CE::raiseError("Could not put $other_versions_page_title");        
    }    
    if (!wikiPut($page_title,    $other_versions_template .  $text)) {
        I2CE::raiseError("Could not put $title");
    }
    if ($create_redirects) {
        //add in redirect        
        $redirect =   "#REDIRECT [[" . $page_title . "]]\n";
        if (!wikiPut($title,$redirect)) {
            I2CE::raiseError("Could not put redrirect for $title");
        }
    }
    return true;
}

function wikiUploadVersioned($text, $version = null) {
    //http://lists.wikimedia.org/pipermail/mediawiki-api/2007-October/000117.html
    global $wikiapi_url;
    global $snoopy;
    global $is_dev;
    global $main_version;
    list($title,$text) =  explode("\n",$text,2);
    if (is_null($version)) {
        $version = $main_version;
    }
    if (substr($title,0,7) != '__PAGE:') {
        I2CE::raiseError("No page title in <$title>");
        return false;
    }
    $title = trim(substr($title,7));
    if (strlen($title) == 0) {
        I2CE::raiseError("Empty page title");
        return false;
    }
    return wikiMakeVersioned($title,$text,$version);
}



function wikiGetPageData($title) {
    global $snoopy;
    global $wikiapi_url;
    if (!wikiLogin()) {
        I2CE::raiseError("Could not login to wiki");
        return false;
    }
    $post = array(
        'action'=>'query',       
        'titles'=>$title,
        'prop'=>'revisions',
        'rvprop'=>'ids|flags|timestamp|user|size|comment|content',
        'format'=>'php',
        );
    if (!$snoopy->submit($wikiapi_url,$post)) {
        I2CE::raiseError("Could not test for $title");
        return false;
    }
    $res = unserialize($snoopy->results);
    if (array_key_exists('error',$res)) {
        I2CE::raiseError("Could not find token:\n" . print_r($res['error'],true));
        return false;
    }
    $res = $res['query'];
    if (!is_array($res) ) {
        I2CE::raiseError("Invalid token 0:\n" . print_r($res,true));
        return false;
    }
    if (!array_key_exists('pages',$res)) {
        return false;
    }
    $res = $res['pages'];
    if (!is_array($res) || count($res) != 1) {
        I2CE::raiseError("Invalid token 2:\n" . print_r($res,true));
        return false;
    }
    reset($res);
    if (key($res) < 0) {
       //key == -1 means the page does not exist
        return false;
    }
    $res = current($res);
    return $res;
}

function wikiGetText($title) {
    $res = wikiGetPageData($title);
    if (!is_array($res) || !array_key_exists('revisions',$res) || !is_array($res['revisions']) || count($res['revisions']) != 1) {
        //print_r(array( !is_array($res) , !array_key_exists('revisions',$res) , !is_array($res['revisions']) , count($res['revisions']) != 1));
        return false;
    }
    $res = current($res['revisions']);    
    if (!is_array($res) || !array_key_exists('*',$res)) {
        return false;
    }
    return $res['*'];
    
}

