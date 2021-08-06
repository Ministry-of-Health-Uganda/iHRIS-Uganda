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
 *  Launchpad webservice helper function
 *
 *****************************************/
$booleans['existing-oauth'] = false;
$usage[] = 
    "[--existing-oauth=T/F] set to true to use existin OAUTH credential stored in environment variables\n".
    "\tDefaults to F\n"; 


require_once( dirname(__FILE__) .  DIRECTORY_SEPARATOR . '..' .  DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR .'I2CE.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CLI.php');
require_once (  dirname(__FILE__) . "/snoopy/Snoopy.class.php");

$launchpadapi_url ="https://api.launchpad.net/1.0/";
$launchpad_snoopy = null;
$create_redirects = true;
$oauth = false;

function launchpadLogin() {
    global $oauth;
    global $launchpad_snoopy;
    global $launchpadapi_url;
    global $booleans;
    while (!is_array($oauth)) {
        if (!$launchpad_snoopy instanceof Snoopy) {
            $launchpad_snoopy = new Snoopy();
        }

        $consumer_key = 'ihris-automated-release' ; 
        //https://help.launchpad.net/API/SigningRequests
        //first get a request token
        $launchpad_snoopy->curl_path = trim(`which curl`); //needed for https
        if (array_key_exists('LNCHPAD_OATH_TOK',$_SERVER) && array_key_exists('LNCHPAD_OATH_TOK_SEC',$_SERVER)) {
            $tok = $_SERVER['LNCHPAD_OATH_TOK'] ;
            $sec = $_SERVER['LNCHPAD_OATH_TOK_SEC'];
            $oauth=array(
                'oauth_token'=>$tok,
                'oauth_consumer_key'=>$consumer_key,
                'oauth_signature_method'=> "PLAINTEXT" ,
                'oauth_signature'=>'&' . $sec,
                'oauth_version'=>"1.0");
            if ($booleans['existing-oauth']  || simple_prompt("Use " . print_r($oauth,true) . "?")) {
                break;
            } 
            $oauth = false;
        }

        $post =array(
            'oauth_consumer_key'=>$consumer_key ,
            'oauth_signature_method'=> "PLAINTEXT" ,
            'oauth_signature'=> "&"
            );
        if(!@$launchpad_snoopy->submit("https://launchpad.net/+request-token",$post)) {
            I2CE::raiseError("Could not get oauth " . $launchpad_snoopy->error);
            return false;
        }
        $res = array();
        parse_str($launchpad_snoopy->results,$res);
        if (!array_key_exists('oauth_token',$res) || !array_key_exists('oauth_token_secret',$res)) {
            I2CE::raiseError("Could not get proper oauth");
        }
        while (!simple_prompt("Have you given this program authority by visting?     https://edge.launchpad.net/+authorize-token?oauth_token=" . $res['oauth_token'] ."\n")) {
            echo "Then please do so\n";
        }

        I2CE::raiseError( "You can revoke authorization by: https://edge.launchpad.net/~<<USERNAME>>/+oauth-tokens");
        $post =array(
            'oauth_token'=>$res['oauth_token'],
            'oauth_consumer_key'=>$consumer_key,
            'oauth_signature_method'=> "PLAINTEXT" ,
            'oauth_signature'=>'&' . $res['oauth_token_secret']
            );        
        if (!$launchpad_snoopy->submit('https://launchpad.net/+access-token',$post)) {
            I2CE::raiseError("Could not get access token " . $launchpad_snoopy->error);
            return false;
        }
        $res = array();
        parse_str($launchpad_snoopy->results,$res);
        if (!array_key_exists('oauth_token',$res) || !array_key_exists('oauth_token_secret',$res)) {
            I2CE::raiseError("Could not get proper oauth");
            $res = false;
            return false;
        }
        I2CE::raiseError("Do this to avoid having to reauthorize:\n" ."export LNCHPAD_OATH_TOK=" . $res['oauth_token'] . "\n" . "export LNCHPAD_OATH_TOK_SEC=" . $res['oauth_token_secret']);
        $oauth=array(
            'oauth_token'=>$res['oauth_token'],
            'oauth_consumer_key'=>$consumer_key,
            'oauth_signature_method'=> "PLAINTEXT" ,
            'oauth_signature'=>'&' . $res['oauth_token_secret'],
            'oauth_version'=>"1.0"
            );
    }
    return true;
}



function launchpadSetAuth() {
    global $launchpad_snoopy;
    global $oauth;
    $t = $oauth;
    $t['oauth_nonce'] = rand(1,32768);
    $t['oauth_timestamp'] = time(); 
    $auth = 'OAuth realm="https://api.launchpad.net/",';
    foreach ($t as $key=>&$val) {
        $val = $key .'="' . urlencode(trim($val)) . '"';
    }
    $auth .= implode(",", $t);
    $launchpad_snoopy->auth = $auth;

}


function launchpadFetch($url_append,$get=array(),$type ='json') {
    global $launchpad_snoopy;
    global $launchpadapi_url;
    if (!launchpadLogin()) {
        I2CE::raiseError("Could not login to launchpad");
        return false;
    }
    $url = $launchpadapi_url.$url_append;
    launchpadSetAuth();
    $launchpad_snoopy->set_submit_normal();
    if (! ($launchpad_snoopy->fetch($url))) {
        I2CE::raiseError("Error on $url_append " . $launchpad_snoopy->error);
        return false;
    }
    return launchpadResults($type);
}


function launchpadSubmit($url_append,$post=array(),$files =array(),$type ='json'){
    global $launchpad_snoopy;
    global $launchpadapi_url;
    if (!launchpadLogin()) {
        I2CE::raiseError("Could not login to launchpad");
        return false;
    }
    if (count($files) > 0) {
        $launchpad_snoopy->set_submit_multipart();        
    }
    launchpadSetAuth();
    if (substr($url_append,0,7) == 'http://') {
        $url = $url_append;
    } else {
        $url = $launchpadapi_url.$url_append;
    }
    if (! ($launchpad_snoopy->submit($url,$post,$files))) {
        $launchpad_snoopy->set_submit_normal();
        I2CE::raiseError("Error on $url_append " . $launchpad_snoopy->error);
        return false;
    }
    $launchpad_snoopy->set_submit_normal();
    return launchpadResults($type);
}

function launchpadResults($type) {
    global $launchpad_snoopy;
    switch ($type) {
    case 'code':
        return $launchpad_snoopy->response_code;
    case 'json':
        $res = json_decode($launchpad_snoopy->results,true);
        if ($res === null) {
            $res =false;
        }
        return $res;
    case 'parse':
        $res = array();
        parse_str($launchpad_snoopy->results,$res);
        if ($res === null) {
            $res =false;
        }
        return $res;
    default:
        return $launchpad_snoopy->results;
    }
}


function getProjectDetails($project,$series) {    
    if (! $res =launchpadFetch( $project .'/' . $series)) {
        return false;
    }
    return $res;
}
function getReleaseDetails($project,$series,$version) {
   //https://api.edge.launchpad.net/1.0/<project.name>/<project_series.name>/<release.version>
    if (! $res =launchpadFetch( $project .'/' . $series . '/' . $version)) {
        return false;
    }
    return $res;

}

function createRelease($project,$series,$version,$desc='') {
    global $launchpad_snoopy;
    $milestone_url = $project .'/+milestone/' .$version;
    if ( !is_array( $res =launchpadFetch( $milestone_url ))) {
        $has_release = false;
        if (!simple_prompt("Milestone $version has not been created for $project.  Create?")) {
            return false;
        }
        $post = array(
            'ws.op'=>'newMilestone',
            'name'=>$version,
            'date_targeted'=>strftime('%Y-%m-%dT11:22:33Z'),
            'description'=>$desc
            );
        if ( substr( $res =launchpadSubmit( $project .'/' . $series  ,$post,array(),'code'),0,strlen('HTTP/1.1 201')) != 'HTTP/1.1 201') {
            I2CE::raiseError("Could not create milestone to $project/$series/$version release [$res]\n" . $launchpad_snoopy->results);
            return false;
        }
    } else {
        $res_data = json_decode($launchpad_snoopy->results,true);
        if ( array_key_exists('release_link',$res_data) && $res_data['release_link']) {
            //we already have a release link
            return true;
    }
    }
    //create the release
    $post = array(
        'ws.op'=>'createProductRelease',
        'date_released'=>strftime('%Y-%m-%dT11:22:33Z'),
        'milestone'=>$version,
        'name'=>$version,
        'description'=>$desc
        );
    if ( substr( $res =launchpadSubmit($milestone_url ,$post,array(),'code'),0,strlen('HTTP/1.1 201')) != 'HTTP/1.1 201') {
        I2CE::raiseError("Could not create milestone to $project/$series/$version release [$res]\n" . $launchpad_snoopy->results);
        return false;
    }
    return true;
}


function uploadReleaseTarBall($tar_ball,$project,$series,$version,$desc) {
    global $launchpad_snoopy;
    if (!is_readable($tar_ball)) {
        I2CE::raiseError("Cannot read $tar_ball");
        return false;
    }
    $release_link = $project . '/' . $series . '/' . $version;

    $post = array(
        'ws.op'=>'add_file',
        'content_type'=>'application/octet-stream',
        'description'=>$desc,
        'file_type'=>'Code Release Tarball',
        'filename'=>basename($tar_ball),
        );    
    $files = array('file_content'=>$tar_ball);
    I2CE::raiseError("Begining upload of " . $post['filename']);
    //$project .'/' . $series . '/' . $version
    if ( substr($res =launchpadSubmit( $release_link,$post,$files,'code'),0,strlen('HTTP/1.1 201')) != 'HTTP/1.1 201') {
        I2CE::raiseError("Could not post " . basename($tar_ball)  . " to $project/$series/$version release [$res]\n" . $launchpad_snoopy->results);
        return false;
    }
    I2CE::raiseError("Finished upload of " . $post['filename']);
    //I2CE::raiseError("Posted to $res");
    return true;
}


$existing_ppas = false;
function getExistingPPAs($name, $cache = true) {
    global $existing_ppas;
    global $launchpadapi_url;
    $ppa_link  = getPPALink($name,$cache);
    if (!$ppa_link) {
        I2CE::raiseError("$name has not ppa collection");
        return false;
    }
    $res = launchpadFetch($ppa_link);
    if (!is_array($res)) {
        I2CE::raiseError("Could not get PPAs for $name");
        return false;
    }
    $existing_ppas  = array();
    if (array_key_exists('entries',$res) && is_array($res['entries'])) {
        foreach ($res['entries'] as $key=>$data) {
            if (!is_array($data) || !array_key_exists('name',$data)) {
                continue;
            }
            $existing_ppas[$data['name']] = substr($data['self_link'],strlen($launchpadapi_url));
        }
    }
    return $existing_ppas;
}

$ppa_link = false;
function getPPALink($name, $cache = true) {
    global $ppa_link;
    global $launchpadapi_url;
    if($cache && $ppa_link) {
        return $ppa_link;
    }
    $res = launchpadFetch('~' . $name);
    if (!is_array($res)) {
        I2CE::raiseError("Could not get info for $name");
        return false;
    }
    if (!array_key_exists('ppas_collection_link',$res)) {
        I2CE::raiseError("$name has not ppa collection");
        return false;
    }
    $ppa_link  = substr($res['ppas_collection_link'], strlen($launchpadapi_url));
    return $ppa_link;
}

function createPPA($name,$ppa, $displayName,$description, $cache = true) {
    global $launchpad_snoopy;
    $existing_ppas = getExistingPPAs($name,$cache);
    if (!is_array($existing_ppas)) {
        I2CE::raiseError("Cannot determine existing PPAs");
        return false;
    }
    if (array_key_exists($ppa,$existing_ppas)) {
        I2CE::raiseError("PPA $ppa already exists for $name");
        return false;
    }
    // $ppa_link  = getPPALink($name,$cache);
    // if (!$ppa_link) {
    //     I2CE::raiseError("$name has not ppa collection");
    //     return false;
    // }
    $ppa_link = "~$name";
    I2CE::raiseError("Creating $ppa for $name");
    $post = array(
        'ws.op'=>'createPPA',
        'name'=>$ppa,
        'displayname'=>trim($displayName),
        'description'=>preg_replace('/\s+/',' ', trim($description)));
    if ( substr( $res =launchpadSubmit( $ppa_link ,$post,array(),'code'),0,strlen('HTTP/1.1 201')) != 'HTTP/1.1 201') {
        I2CE::raiseError("Could not create ppa $ppa for $name\n" . $launchpad_snoopy->results);
        return false;
    }
    return true;
}



function getExistingBuildSources($user,$ppa) {
    global $launchpad_snoopy;
    $ppas = getExistingPPAs($user);
    if (!is_array($ppas)) {
        I2CE::raiseError("Could not get PPAs for $user");
        return false;
    }
    if (!array_key_exists($ppa,$ppas) ) {
        I2CE::raiseError("PPA $ppa does not exist for $user");
        return false;
    }
    $start = 0;
    $src_links = array();
    do {
        $check_link = $ppas[$ppa] . '?ws.op=getBuildRecords&ws.start=' . $start;
        $res = launchpadFetch($check_link);
        if (!is_array($res)) {
            if ($start == 0 ) {
                return false;
            }  else {
                I2CE::raiseError("Could not fetch at start $start");
                return $src_links;
            }
        }
        if (array_key_exists('entries',$res) && is_array($res['entries'])) {
            foreach ($res['entries'] as $i=>$buildData) {
                I2CE::raiseError("Processing Source $i of " . count($res['entries']) ."\n" .print_r($buildData,true));
                if (!array_key_exists('current_source_publication_link',$buildData) || !$buildData['current_source_publication_link']) {
                    continue;
                }
                $src_link = $buildData['current_source_publication_link'];
                $res_src = launchpadFetch($src_link);
                if (!is_array($res_src)) {
                    I2CE::raiseError("Invliad build source from " . $buildData['self_link']);
                }
                $src_links[$buildData['self_link']] = $res_src;
            }
        }
        $start = $start + count($res['entries']);
    } while ($res['total_size'] > $start);
    return $src_links;
}

function getExistingBuildVersions($user,$ppa) {
    $buildSources = getExistingBuildSources($user,$ppa);
    if (!is_array($buildSources)) {
        return false;
    }
    $sources = array();
    foreach ($buildSources as $srcData) {
        if (!array_key_exists('source_package_version',$srcData) || !$srcData['source_package_version']) {
            continue;
        }
        if (!array_key_exists($srcData['source_package_name'],$sources)) {
            $sources[$srcData['source_package_name']] = array();
        }
        $sources[$srcData['source_package_name']][] = $srcData['source_package_version'];
    }
    return $sources;
}

function getExistingPublishedVersions($user,$ppa) {//total_size and start
    I2CE::raiseError("Getting published source versions for ppa:$user/$ppa");
    $start = 0;
    $sources = array();
    do {
        $res = launchpadFetch("~$user/+archive/$ppa?ws.op=getPublishedSources&ws.start=$start");
        if (!is_array($res)) {
            if ($start == 0 ) {
                return false;
            }  else {
                I2CE::raiseError("Could not fetch at start");
                return $sources;
            }
        }
        foreach ($res['entries'] as $srcData) {
            if (!array_key_exists('source_package_version',$srcData) || !$srcData['source_package_version']) {
                continue;
            }
            if (!array_key_exists($srcData['source_package_name'],$sources)) {
                $sources[$srcData['source_package_name']] = array();
            }
                $sources[$srcData['source_package_name']][] = $srcData['source_package_version'];
        }
        $start = $start + count($res['entries']);
    } while ($res['total_size'] > $start);
    
    return $sources;
}


