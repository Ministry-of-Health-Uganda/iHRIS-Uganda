<?php
/*
 * © Copyright 2010 IntraHealth International, Inc.
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


$i2ce_dir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
$user_details = posix_getpwuid(getmyuid());
$tmp_dir_base = $user_details['dir']  .  '/ihris-release';

$def_locale_file = 'modules/Pages/modules/LocaleSelector/modules/DefaultLocales/DefaultLocales.xml';
$locale_cmd = 'grep "/value" ' . $i2ce_dir .  $def_locale_file . '  | awk -F\> \'{print $2}\' | awk -F: \'{print $1}\'';
$locales = array();
exec($locale_cmd,$locales);
foreach ($locales as &$loc) {
    $loc = trim($loc);
    if (!$loc) {
        unset($locales[$i]);
    }
}
unset($loc);
$locales = implode(",",$locales);

$base_dir = getcwd();
$launchpad_login = false;
$default_release = false;
$full_tar_balls = array(
    'i2ce'=>'i2ce textlayout',
    'ihris-manage'=>'i2ce textlayout ihris-common ihris-manage',
    'ihris-qualify'=>'i2ce textlayout ihris-common ihris-qualify',
    'ihris-retain'=>'i2ce textlayout ihris-common ihris-retain',
    'ihris-train'=>'i2ce textlayout ihris-common ihris-train',
    'ihris-common'=>'i2ce textlayout ihris-common'
    );

$usage[] = 
    "[--launchpad-login=T/F] set to login name/team for PPA\n";


$search_dirs = array();
require_once( $base_dir .  DIRECTORY_SEPARATOR . 'i2ce' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'base.php');
require_once( $base_dir .  DIRECTORY_SEPARATOR . 'i2ce' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'launchpad_base.php');
require_once( $i2ce_dir. DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'I2CE_Validate.php');


foreach ($args as $key=>$val) {
    switch($key) {
    case 'launchpad-login':
        $launchpad_login = $val;
        break;
    case 'default_release' :
        $default_release = $val;
        break;
    }
}


function vers_compare($vers1,$vers2) {
    if (I2CE_Validate::checkVersion($vers1,'=',$vers2)) { 
        return 0;
    } 
    if (I2CE_Validate::checkVersion($vers1,"<",$vers2)) {
        return 1;
    } else {
        return -1;
    }
}



if (!simple_prompt("use $tmp_dir_base to store release branches and tarballs?")) {
    while( true) {
        $tmp_dir_base = trim(ask("What directory should I use"));
        if (is_dir($tmp_dir_base)) {
            break;
        } else {
            if (simple_prompt("Create $tmp_dir_base?")) {
                I2CE::raiseError ("mkdir -p $tmp_dir_base");
                exec ("mkdir -p $tmp_dir_base");
                break;
            }
        }
    }
}

$t_dirs = $arg_files;
if (count($t_dirs) == 0) {
    $t_dirs = glob("*",GLOB_ONLYDIR);
}
I2CE::raiseError("Examining the following for release " . implode(",", $t_dirs));
$site_dirs = array();
$uncommitted = null;
$top_dirs = array();
foreach ($t_dirs as $i=>$dir) {
    $ret = 0;
    $out = array();
    $info = @exec("bzr info $dir 2> /dev/null",$out,$ret);
    if ($ret !== 0) {
        unset($t_dirs[$i]);
        continue;
    }
    $ret = 0;
    $out = array();
    $info = @exec("bzr status $dir 2> /dev/null",$out,$ret);
    if (count($out)>0) {
        if (!prompt("Ignore uncomitted changes ($dir)?",$uncommitted)) {
            die(basename($dir) . " has uncommitted changes\n" );
        } else {
            I2CE::raiseError("Warning $dir has uncommitted changes");
        }
    }
    $dir = realpath($dir);
    $top_dirs[]  = $dir;
    $site_dirs = array_merge($site_dirs, glob("$dir/sites/*",GLOB_ONLYDIR));    
}
unset($dir);
$search_dirs = array_merge($site_dirs,$top_dirs);

if (!simple_prompt("Work with the following directories " . implode(",",$t_dirs) ."?")) {
    die("Stopping\n");
}


$do_versioning = simple_prompt("Check for changes since last release and update versions?");

    


$last_release = array();
$default_next_release = null;
foreach ($top_dirs as $dir) {
    
    if (! ($pcre = trim(`which pcregrep`))) {
        die("Please do: sudo apt-get install pcregrep\n");
    }
    $cmd = "bzr log --line $dir | $pcre '" . '\\{.*[0-9\\.]+-release.*\\}'  . "' | head -1";
    $line = trim(`$cmd`);
    $no_release = null;
    if (!preg_match('/^([0-9\\.]+):.*?\\{(.*[0-9\\.]+-release.*)\}/',$line,$matches)) {
        $no_release = true;
    }
    if ( $no_release && $default_release ) {
        $rels = array( $default_release );
        $revno = 0;
    } else {
        if ( !$matches ) {
            I2CE::raiseError("Thought I had a match, but didn't for $dir:\n$line");
            die();
        }
        $revno =  $matches[1];
        //we have at least one release tag.  let's ge them all split up and sorted
        if (!preg_match_all('/([0-9\\.]+)-release/',$line,$matches)) {
            I2CE::raiseError("Thought I had a match (2), but didn't for $dir:\n$line");
            die();
        }
        if (!count($matches) == 2) {
            I2CE::raiseError("Thought I had a match (3), but didn't for $dir:\n$line");
            die();
        }
        $rels = $matches[1];
    }
    if (count($rels) == 0) {
        I2CE::raiseError("Thought I had a release, but it ran away from me ...: $line");
        die();        
    }
    //sort so that  the maximum release as the first entry.                
    usort($rels,'vers_compare'); 
    reset($rels);
    $release = current($rels);

    $cmd = "pcregrep   'I2CEConfiguration\s+name' $dir/*xml ";
    if (!preg_match('/name=["\'](.*?)["\']/' ,trim( `$cmd`),$matches)) {
        die("Could not find a module in $dir\n");
    }
    $cmd = "grep  '/version' $dir/*xml | head -1 ";
    $out = trim( `$cmd`);
    if (!preg_match('/([0-9\.]+)/' ,$out,$matches2)) {
        die("Could not find a version in $dir\n");
    }
    $vers_comps = explode(".",$matches2[1]);
    $last_release_comps = explode(".",$release);

    if (count($last_release_comps) != 3) {
        die("Unrecognized(1) release $release\n");
    }    
    if (count($vers_comps) < 3) {
        die("Unrecognized(2) release $release\n");
    }    
    $next_release_comps = $last_release_comps;
    $next_release_comps[2]++;
    $next_release = implode(".",$next_release_comps);
    if (!$do_versioning) {
	$next_release = false;
    } else  if ($last_release_comps[0] != $vers_comps[0]) {
        die("Skipping Major Verison not supported\n");
    } else   if ($last_release_comps[1] != $vers_comps[1]) {
        $next_release = trim(ask("This is a new sub-major release.  Please enter the new version number for " . $matches[1] . " (config file has " . $matches2[0]  .")" ));        
        $next_release_comps  = explode(".", $next_release);
        if ((count($next_release_comps) != 3) || ($next_release_comps[0] != $vers_comps[0]) || ($next_release_comps[1] != $vers_comps[1]) || $next_release_comps[1] <= $vers_comps[2]) {
            echo "Please enter a release of the form " . implode(".", array_slice($vers_comps,0,2)) . ".X where X > " . $next_release_comps[2] ."\n";
        }

        $release = $next_release;
    } else if ($do_versioning) {
        if (!simple_prompt("Previous release of " . $matches[1] . " was $release.  Use $next_release for the next release?",$default_next_release)) {
            while (true) {
                $next_release = ask("Last release of " . $matches[1] . " was $release.  What should I use for the next release?");
                $next_release_comps  = explode(".", $next_release);
                if ((count($next_release_comps) != 3) || ($next_release_comps[0] != $last_release_comps[0]) || ($next_release_comps[1] != $last_release_comps[1]) || $next_release_comps[2] <= $last_release_comps[2]) {
                    echo "Please enter a release of the form " . implode(".", array_slice($last_release_comps,0,2)) . ".X where X > " . $next_release_comps[2] ."\n";
                }
                //we have something valid
                break;
            }
        }
    }
    $last_release[$matches[1]] = array('revno'=>$revno,'release'=>$release, 'topmodulevers'=>$matches2[1],'next_release'=>$next_release);
}
foreach($last_release as $key=>$value){
	if($key == 'I2CE'){
		$key='i2ce';
		$last_release[$key] = $value;
		unset($last_release['I2CE']);
	}
}

if (!array_key_exists('i2ce',$last_release)) {
    die("I2CE is not present\n");
}


getAvailableModules();
$top_mod_dirs = array();
foreach ($found_modules as $module=>$top_module) {
    if ($module != $top_module ) {
	continue;
    }
    $config_file = false;
    if ( ! $storage->setIfIsSet($config_file,"/config/data/$module/file")) {
	I2CE::raiseError( "No config file for $module -- Skipping");
	continue;
    }
    $config_dir = rtrim(dirname($config_file),'/');
    foreach ($top_dirs as $dir) {
	if ( strpos($dir,$config_dir) === 0) {
	   	if($module == 'I2CE'){
			$module='i2ce';
		}
	    $top_mod_dirs[$module] = $dir;
	    continue 2;
	}
    }
}

if ($do_versioning) {
    $tmp_dir = $tmp_dir_base . '/' . $last_release['i2ce']['next_release'];
} else {
    $tmp_dir = $tmp_dir_base . '/' . $last_release['i2ce']['release'];
}
$cmd ="rm -fr $tmp_dir";
if (is_dir($tmp_dir) &&  simple_prompt("The directory $tmp_dir already exists.  Remove it [$cmd]?"))  {
    exec($cmd);   
}
`mkdir -p $tmp_dir`;


if ($do_versioning) {
    $versioned_files = array();


    I2CE::raiseError("Getting all versioned files by module");
    foreach ($found_modules as $module=>$top_module) {
	$v = array();
	$config_file = false;
	if ( ! $storage->setIfIsSet($config_file,"/config/data/$module/file")) {
	    I2CE::raiseError( "No config file for $module -- Skipping");
	    continue;
	}
	$config_dir = rtrim(dirname($config_file),'/');
	$dirs = array();
	$ignore = array('MODULES','TEMPLATES','CONFIGS');
	foreach ($ignore as $type) {
	    $dirs[$type] = array();
	    $storage->setIfIsSet($dirs[$type],"/config/data/$module/paths/" . $type,true);
	    foreach ($dirs[$type]  as $i=>&$s_d) {
		if ($s_d[0] != '/') {
		    $s_d = realpath($config_dir . '/' . $s_d);
		} else {
		    $s_d = realpath($s_d);
		} 
		if (!is_dir($s_d) || strpos($s_d,$config_dir) !== 0) { //check to see ensure is a subdir
		    unset($dirs[$type][$i]);
		    continue;
		}
	    }
	    unset($s_d);
	}

	$cmd = "bzr ls -R -V $config_dir";
	$v = array();
	@exec($cmd,$v);
	foreach ($v as $i=>&$f) {
	    $f = trim($f);
	    //first we ignore things that are not a subdirectory
	    foreach ($ignore as $type) {
		foreach ($dirs[$type] as $s_d) {
		    if (strpos($f,$s_d) === 0) {
			unset($v[$i]);
			continue 3;
		    }
		}
	    }
	    //if $top_module == $module we ignore the translations dir
	    if ($top_module == $module) {
		foreach (array('translations','tools','t','tests') as $ig) {
		    if (strpos($f,$config_dir . '/' . $ig) === 0) {
			unset($v[$i]);
			continue 2;
		    }
		}
	    }
	}
	unset($f);
	$versioned_files[$module] = $v;
    }
    I2CE::raiseError("Got all versioned files by module");

    $changed_modules = array();
    I2CE::raiseError("Getting changes since last release");
    foreach ($found_modules as $module=>$top_module) {
	if (count($versioned_files[$module]) == 0) {
	    continue;
	}
	//exit values
	// 1 - changed
	// 2 - unrepresentable changes
	// 3 - error
	// 0 - no change
	$t_module =false;
	if (!array_key_exists($top_module,$last_release)) {
	    //it is in a site directory.  get the package module
	    $config_file = false;
	    if ( ! $storage->setIfIsSet($config_file,"/config/data/$module/file")) {
		I2CE::raiseError( "No config file for $module -- Skipping");
		continue;
	    }
	    $config_dir = rtrim(dirname($config_file),'/');
	    foreach ($top_mod_dirs as $mod=>$top_dir) {
		if (strpos($config_dir,$top_dir) === 0) {
		    $t_module = $mod;
		}
	    }
	    if ($t_module == false) {
		I2CE::raiseError("No containing module found for $module");
		continue;
	    }
	} else {
	    $t_module = $top_module;
	}
	foreach ($versioned_files[$module] as &$f) {
	    $f = escapeshellarg($f);
	}
	if($t_module == 'I2CE'){
		$t_module = 'i2ce';
	}
	unset($f);
	$cmd = "cd $t_module && bzr diff -q -r " .  $last_release[$t_module]['revno'] . ' ' . implode( " " , $versioned_files[$module]);
	$ret = 0;
	$out = array();
	$info = @exec($cmd,$out,$ret);
	if ($ret == 3) {
	    die("Badness on module $module\n");
	}
	if ($ret == 0) {
	    continue;
	}
	$changed_modules[$module]=$t_module;
    }
    I2CE::raiseError("Got changes since last release");

    I2CE::raiseError( "The following modules have changed since last release:\n\t" . implode(",",array_keys($changed_modules) ));

    
    $top_bump = null;
    foreach ($top_mod_dirs as $mod=>$top_dir) {
	if (array_key_exists($mod,$changed_modules)) {
	    continue;
	}
	if (!prompt("Would you also like to mark the top-level module ($mod) as being changed?", $top_bump)) {
	    continue;
	}
	$changed_modules[$mod] = $mod;
    }
    

    $bump_version = null;
    foreach ($changed_modules as $module => $pkg_module) {
	$last_release_vers = $last_release[$pkg_module]['release'];
	$next_release = $last_release[$pkg_module]['next_release'];
	$old_version = false;
	if (!$storage->setIfIsSet($old_version,"/config/data/$module/version")) {
	    I2CE::raiseError("Cannot get top existing version for $module.  Skipping");
	    continue;
	}

	$last_release_comps = explode(".",$last_release_vers);
	$next_release_comps = explode(".",$next_release);
	$old_version_comps = explode('.',$old_version);
	for ($i =0; $i < 2; $i++) {
	    if ($old_version_comps[$i] != $last_release_comps[$i]) {
		//Mismatch on sub-major version $top_version and $last_release
		continue 2;
	    }
	}
	if ($old_version_comps[2] >= $next_release_comps[2]) {
	    //we have already bumped this modules' version
	    continue;
	}
	$new_version = $next_release . '.0';
	if (!prompt("Changes were made to $module.  Would you like to bump the version of $module from $old_version to $new_version as part of the $next_release release of $pkg_module?",$bump_version)) {
	    continue;
	}

	$mod_file = false;
	$storage->setIfIsSet($mod_file,"/config/data/$module/file");
	if (!$mod_file) {
	    I2CE::raiseError("No file recored for $module");
	    continue;
	}
	$loc_files = glob (dirname($mod_file) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . basename($mod_file));
	$loc_files[] = $mod_file;
	foreach ($loc_files as $file) {
	    $template = new I2CE_TemplateMeister();
	    if (!$template->loadRootFile($file)) {
		echo "\tCould not load\n";
		continue;
	    }        
	    if (( ! ($res = $template->query('/I2CEConfiguration/metadata/version')) instanceof DOMNodeList) || ($res->length == 0)) {
		echo "\tVersion not found\n";
		continue;
	    }
	    $versNode = $res->item(0);
	    while ($versNode->hasChildNodes()) {
		$versNode->removeChild($versNode->firstChild);
	    }
	    $versNode->appendChild($template->createTextNode($new_version));
	    file_put_contents($file, $template->getDisplay());
	}
    }
} //end checking for changes since last release


$commit_changes = null;
$tag_release = null;
if ($do_versioning) {
    foreach ($top_mod_dirs as $mod=>$dir) {
	if($mod == 'I2CE'){
		$mod = 'i2ce';
	}
	$next_release = $last_release[$mod]['next_release'];
	$ret = 0;
	$out = array();
	$info = @exec("bzr status $dir 2> /dev/null",$out,$ret);
	if (count($out)>0) {
	    if (prompt("$mod has uncommitted changes. Commit them?\n[Assuming that $mod is bound to launchpad]",$commit_changes,implode("\n",$out))) {
		$cmd = "bzr commit $dir -m 'Automated version bumps for release $next_release'";
		exec($cmd);
	    }
	}
	if (prompt("Tag the branch $mod as {$next_release}-release?",$tag_release)) {
	$cmd = "bzr tag {$next_release}-release -d $dir";
	exec($cmd);    
        
	}
    }
}




foreach ($top_mod_dirs as $mod=>$dir) {
    if (is_dir("$tmp_dir/$mod")) {
	continue;
    }
    $cmd = "bzr branch $dir $tmp_dir/$mod";    
    exec($cmd);        
}


$create_release_minor_branch = null;
$push_release_branch = null;
if (simple_prompt("Create Release Branches?")) {
    if ( !$launchpad_login) {
        $launchpad_login = trim(ask("What is the launchpad name/team to put packages under?"));
    }

    foreach ($top_mod_dirs as $mod=>$dir) {
	if( $mod == 'I2CE'){
		$mod='i2ce';
	}
	if ($do_versioning) {
	    $release = $last_release[$mod]['next_release'];
	}else {
	    $release = $last_release[$mod]['release'];
	}
        $lp_release = "lp:~{$launchpad_login}/" . strtolower($mod) . "/$release-release";
        if (prompt("Create $lp_release?",$create_release_minor)) {
            $cmd = "cd $tmp_dir/$mod && bzr push $lp_release";
            exec($cmd);        
        }
        $sub_major = implode(".",array_slice(explode(".",$release),0,2));
        $lp_release = "lp:~{$launchpad_login}/" . strtolower($mod) . "/$sub_major" . '-release';
        if (prompt("Push to $lp_release?",$push_release_branch)) {
            $cmd = "cd $tmp_dir/$mod && bzr push $lp_release";        
            exec($cmd);        
        }
    }
}


$create_trans = null;
foreach ($top_mod_dirs as $mod=>$dir) {
    if (prompt("Create translations $locales for $mod?",$create_trans)) {
        $cmd = "cd $tmp_dir/$mod && php $tmp_dir/i2ce/tools/translate_templates.php --locales=$locales 2> /dev/null";
        exec($cmd);
    }
}


$created_tarballs = array('i2ce'=>array());
if ($ask_upload = simple_prompt("Create tarballs?")) {
    $excludes = array(
	'CVS/',
	'RCS/',
	'SCCS/', 
	'.git/', 
	'.gitignore',
	'.cvsignore',
	'.svn/', 
	'.arch-ids/',
	'{arch}/',
	'=RELEASE-ID',
	'=meta-update',
	'=update',
	'.bzr',
	'.bzrignore',
	'.bzrtags',
	'.hg',
	'.hgignore',
	'.hgrags',
	'_darcs' ,
	'.#',
	'*~',
	'#*#',
	'packaging'
	);
    $excludes = ' --exclude=' . implode(' --exclude=',$excludes);
    foreach ($top_mod_dirs as $mod=>$dir) {
	if ($do_versioning) {
	    $release = $last_release[$mod]['next_release'];
	} else {
	    $release = $last_release[$mod]['release'];
	}
        $tar_ball = "$tmp_dir_base/$mod-$release.tar.bz2";    
        if (!array_key_exists($mod,$created_tarballs)) {
            $created_tarballs[$mod] = array();
        }
        $created_tarballs[$mod][$mod] = $tar_ball;

        $cmd ="cd $tmp_dir && tar -cj $excludes -f $tar_ball $mod";
	echo "\t$cmd\n";
        I2CE::raiseError("Creating $tar_ball");
        exec($cmd);
        if (!array_key_exists($mod,$full_tar_balls)) {
            continue;
        }
        $tar_ball = "$tmp_dir_base/$mod-full-$release.tar.bz2";
        $created_tarballs[$mod][$mod . ' Full'] = $tar_ball;
        $cmd ="cd $tmp_dir && tar -cj --exclude=.bzr*   -f $tar_ball " . $full_tar_balls[$mod];
        I2CE::raiseError("Creating $tar_ball");
        exec ($cmd);
    }

    $tar_ball = "$tmp_dir_base/ihris-suite-$release.tar.bz2";
    $created_tarballs['i2ce'][] = $tar_ball;
    I2CE::raiseError("Creating $tar_ball");
    $cmd ="cd $tmp_dir && tar -cj $excludes  -f $tar_ball " . implode(" ", array_keys($top_mod_dirs));
    exec ($cmd);
}




if ($ask_upload && simple_prompt("Upload tarballs to launchpad?")) {
    $do_upload = null;
    foreach ($created_tarballs as $top_mod=>$tar_balls) {
        $project = strtolower($top_mod);
	if ($do_versioning) {
	    $version = $last_release[$mod]['next_release'];
	} else {
	    $version = $last_release[$top_mod]['release'];
	}
        $series = implode(".",array_slice(explode(".",$version),0,2));
        if (!createRelease($project,$series,$version)) {
            continue;
        }
        foreach ($tar_balls as $base=>$tar_ball) {
            if (!prompt("Upload tarball to launchpad ($tar_ball)?",$do_upload)) {
                continue;
            }
            $desc = str_replace('Ihris','iHRIS',ucwords(str_replace('textlayout','Text Layout Tools',str_replace('-',' ',$base)))) . ' ' .$version;
            uploadReleaseTarBall($tar_ball,$project,$series,$version,$desc);
        }
    }
}
