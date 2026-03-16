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
 * The page wrangler
 * 
 * This page loads the main HTML template for the home page of the site.
 * @package iHRIS
 * @subpackage DemoManage
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since Demo-v2.a
 * @version Demo-v2.a
 */

class I2Ce_Page_ModDocumentor extends I2CE_Page {





    /**
     * Gets a "scheme" used to describe module documentation options.
     * @returns array
     */
    protected function getSchemeDetails($scheme) {
        $details = array();
        I2CE::getConfig()->setIfIsSet($details,"/modules/modDocumentor/schemes/$scheme",true);
        return $details;
    }



    /**
     * Produces  a .txt file for the given modules as a string
     * @param array $modules of string the modules
     */
    public function text($modules ) {
        return '';
    }

    /**
     * Produces  a wiki page for the given modules as a string
     * @param array $modules of string the modules
     */
    public function wiki($modules) {
        return '';
    }



    /**
     * Produces  a .dot file for the given modules as a string
     * @param array $modules of string the modules
     */
    public function dot($modules) {        
        $mod_factory = I2CE_ModuleFactory::instance();
        $nodes = array();
        $paths = array();
        $scheme_details  = $this->getSchemeDetails('dot');    
        $config = I2CE::getConfig();
        if (array_key_exists('colors',$scheme_details) && is_array($scheme_details['colors'])) {
            $mod_groups = $scheme_details['colors'];
        } else {
            $mod_groups = array();
        }
        $node_groups = array();
        $config = I2Ce::getConfig();
        $mod_config = $config->config->data;
        foreach ($modules as $module) {
            if (!$mod_config->is_parent($module)) {
                continue;
            }
            $color = 'ivory3';
            foreach ($mod_groups as $m_module=>$m_color) {
                if (strpos($module,$m_module) !== false) {
                    $color = $m_color;
                    break;
                }            
            }
            $className = $mod_factory->getClassName($module);
            if ($className) {
                $className = ' (' . $className . ')';
            }
            $mod_data = array();
            foreach (array('displayName') as $key) {
                if (!$mod_config->is_scalar("$module/$key")) {
                    continue;
                }
                $mod_data[] =  $mod_config->$module->$key;
            }
            foreach ($mod_data as &$d) {
                $d = '<tr><td ALIGN=\'LEFT\'>' . $d  .  '</td></tr>';
            }
            $label = '<table border=\'0\' cellborder=\'0\'><tr><td BGCOLOR=\'white\' BORDER=\'1\'>' . $module  .  $className . '</td></tr>'
                . implode('',$mod_data) . '</table>';
            if (!array_key_exists($color,$node_groups) || !is_array($node_groups[$color])) {
                $node_groups[$color] = array();
            }
            $node_groups[$color][$module] = "\"$module\" [style=filled fillcolor = $color   label =<$label> shape = \"Mrecord\"   ];";
            if ($mod_config->is_parent("$module/requirement")) {
                $requirements = array_intersect($modules,$mod_config->getKeys("$module/requirement"));
            } else {
                $requirements = array();
            }
            if ($mod_config->is_parent("$module/conflict")) {
                $conflicts = array_intersect($modules,$mod_config->getKeys("$module/conflict"));
            } else {
                $conflicts = array();
            }            
            if ($mod_config->is_parent("$module/enable")) {
                $enabled = array_intersect($modules,$mod_config->getKeys("$module/enable"));
            } else {
                $enabled = array();
            }
            foreach ($requirements as $req) {
                $paths[] = "\"{$module}\" -> \"$req\";";
            }
            foreach ($enabled as $end) {
                $paths[] = "\"{$module}\" -> \"$end\" [color=forestgreen];";
            }
            foreach ($conflicts as $con) {
                $paths[] = "\"{$module}\" -> \"$con\" [color=yellow];";
            }
        }


        $config = I2CE::getConfig();
        $module = $config->config->site->module;
        if (!$module) {
            I2CE::raiseError("No site module");
            return $graph;
        }
        if (array_key_exists('graph_options',$scheme_details) && is_array($scheme_details['graph_options'])) {
            $graph_options = $scheme_details['graph_options'];
        } else {
            $graph_options = array();
        }

        if (!array_key_exists('label',$graph_options) || !$graph_options['label'] || $graph_options['label'] == "''" ||  $graph_options['label'] == '""') {
            $title = 'Module Documentor';
            $version = '';
            $u_version = '';
            if ($config->setIfIsSet($title,"/config/data/$module/displayName")) {
                $title = str_ireplace('Demonstration','',$title);
                $title = str_ireplace('Demo','',$title);
                $title = trim($title);
                if ($config->setIfIsSet($version,"/config/data/$module/version")) {
                    $title .= ' - ' . $version;
                    $u_version = '_' . strtr($version,'.','_');
                }            
            }
            $graph_options['label'] = '"' . $title . '"';
        }
        $bgcolor = 'white';        
        if (array_key_exists('bgcolor',$graph_options)) {
            $bgcolor = $graph_options['bgcolor'];
        }
        $graph_details =   "graph [";
        foreach ($graph_options as $key=>$val) {
            $graph_details .= "\n\t\t" .  $key .'=' . $val;
        }
        $graph_details .=  "\n\t];\n\tratio = auto;\n";
        foreach ($node_groups as $colors=>$ns) {
            foreach ($ns as $n) {
                $nodes[] = $n;
            }
        }
        $graph =  "digraph g {\n\t$graph_details\n\t" . implode( "\n\t",$nodes) . implode("\n\t",$paths) . "\n}\n";

        $dot = trim(`which dot`);
        $unflatten = trim(`which unflatten`);
        if (!$dot || !$unflatten) {
            I2CE::raiseError("the dot utility was not found on your system.  cannot create the imate. try sudo apt-get install dot");
            return $graph;
        }
        

        $output_file = '/tmp/modules_' . $module . $u_version . '.gif';
        $dot = "$unflatten -f -l 2 -c 2 | $dot -T gif ";
        $composite = trim(`which composite`);
        $composite = false;
        if ($composite) {            
            $watermark_file = I2CE::getFileSearch()->search('IMAGES','module_documentor_legend.gif');
            $watermark = '';
            if ($watermark_file) {
                $bgcolor_change = '';
                if (strtolower($bgcolor) != 'white') {
                    $bgcolor_change =  "-fuzz 5% -fill $bgcolor -opaque white";
                }
                $watermark  = "  |$composite gif:-  -gravity SouthEast  $bgcolor_change $watermark_file ";
            }
            $exec = $dot  . $watermark . $output_file;
        } else {
            I2CE::raiseError("Imagemagick utitilies were not found on your system.  cannot watermark the file. try sudo apt-get isntall imagemagick");
            $exec = $dot . '-o ' . $output_file;
        }
        I2CE::raiseError("Attempting to execute:\n\t" . $exec);
        $proc = popen ($exec , "w");
        if (!is_resource($proc)) {
            I2CE::raiseError("Could not start execute");
        } else {
            fwrite($proc,$graph);
            fclose($proc);
        }
        return $graph;
    }


    /**
     * The business method if this page is called from the commmand line
     * @param array $request_remainder the remainder of the request after the page specfication.  
     * @param array $args the array of unix style command line arguments 
     */
    protected function actionCommandLine($args,$request_remainder) { 
        $config = I2CE::getConfig();
        if (!$config->is_parent("/config/data")) {
            I2CE::raiseError("No Modules", E_USER_ERROR);
        }
        $modConfig = $config->config->data;
        if ($this->request('only_enabled')) {
            $modules = I2CE_ModuleFactory::instance()->getEnabled();
        } else {
            $modules  = $modConfig->getKeys();
        }
        sort($modules);
        switch ($this->page) {
        case 'wiki':
            echo $this->wiki($modules);
            break;
        case 'dot':
            echo $this->dot($modules);
            break;
        case 'text':
        default:
            echo $this->text($modules);
            break;
        }
    }


  }








# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
