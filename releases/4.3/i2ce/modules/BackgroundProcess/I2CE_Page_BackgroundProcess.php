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
*/
/**
*  I2CE_Page_BackgroundProcess
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Page_BackgroundProcess extends I2CE_Page{
    

    protected $process;
    protected function action() {
        parent::action();
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuBackgroundProcess", "a[@href='BackgroundProcess']" );

        $this->template->addHeaderLink('BackgroundProcess.css');

        $this->process = '';
        if ($this->get_exists('process')) {
            $this->process = $this->get('process');
        }
        if ($this->post_exists('process')) {
            $this->process = $this->post('process');
        }
        if ($this->process && (
                !array_key_exists('BackgroundProcess', $_SESSION)
                ||!array_key_exists($this->process, $_SESSION['BackgroundProcess']))) {
            $this->template->setDisplayDataImmediate('background_status','You do not have access to this process',$mainNode);
            $this->process =  '';
        }
        if ($this->process) {
            switch ($this->page) {
            case 'tail':
                $this->tailProcess();
                break;
            case 'status':
                $this->checkStatus();
                break;
            case 'clear':
                $this->clear();
                break;
            default:
                $mainNode = $this->template->getElementById('siteContent');
                $this->template->setDisplayDataImmediate('background_tail','',$mainNode);
                $this->template->setDisplayDataImmediate('background_status_content','',$mainNode);
                //do nothing
            break;
            }
        } else {
            $mainNode = $this->template->getElementById('siteContent');
            $this->template->setDisplayDataImmediate('background_tail','',$mainNode);
            $this->template->setDisplayDataImmediate('background_status_content','',$mainNode);
        }
        $this->processMenu();
        return true;
    }


    protected function clear() {
        $mainNode = $this->template->getElementById('siteContent');
        $this->template->setDisplayDataImmediate('background_tail','',$mainNode);
        $this->template->setDisplayDataImmediate('background_status_content','',$mainNode);
        $this->template->setDisplayDataImmediate('process_number','',$mainNode);
        unset($_SESSION['BackgroundProcess'][$this->process]);
        $this->process =  '';
        return true;
    }

    protected function checkStatus() {
        $mainNode = $this->template->getElementById('siteContent');
        if (!array_key_exists('process',$_SESSION['BackgroundProcess'][$this->process]) || ! $_SESSION['BackgroundProcess'][$this->process]['process']) {
            $this->template->setDisplayDataImmediate('background_status','Do not know the OS process number associated to this process',$mainNode);
            return false;
        }
        $this->template->setDisplayDataImmediate('process_number',$this->process,$mainNode);
        $this->template->setDisplayDataImmediate('background_tail','',$mainNode);
        if (!I2CE_FileSearch::isUnixy()) {
            return true;
        }
        $process = $_SESSION['BackgroundProcess'][$this->process]['process'];
        exec("ps -Ho pid,ppid,pcpu,stime,start_time,size,vsize,comm -p $process --ppid $process", $output);
        if (count($output)>1) {
            for ($i=0; $i < count($output); $i++) {
                $this->template->setDisplayDataImmediate("background_status_content",$output[$i] . "\n",$mainNode);
            }
        } else {
            $this->template->setDisplayDataImmediate("background_status_content",'Process is no longer running',$mainNode);
        }
        return true;
    }

    protected function tailProcess() {
        $mainNode = $this->template->getElementById('siteContent');
        $this->template->setDisplayDataImmediate('background_status_content','',$mainNode);
        $this->template->setDisplayDataImmediate('process_number',$this->process,$mainNode);
        $logDir = I2CE_BackgroundProcess::getLogDir();
        $file = $logDir . DIRECTORY_SEPARATOR . 'process.' . $this->process . '.log';
        if (!file_exists($file) || !is_readable($file)) {
            $msg = "Log file not found for process " . $this->process;
            $this->template->setDisplayDataImmediate('background_status', $msg,$mainNode);
            I2CE::raiseError($msg); 
            return false;
        }
        $line = 0;
        if (array_key_exists('last_line',$_SESSION['BackgroundProcess'][$this->process])) {
            $line = $_SESSION['BackgroundProcess'][$this->process]['last_line'];
        }
        if ($this->get_exists('line')) {
            $line = $this->get('line');
        }
        if ($this->post_exists('line')) {
            $line = $this->post('line');
        }
        $contents = file($file);
        $tot = count($contents);
        if ( ($tot - $line) <= 0) {
            $this->template->setDisplayDataImmediate('background_tail','',$mainNode);
        }
        for ( $i=$line;   $i < $tot; $i++) {
            $this->template->setDisplayDataImmediate('background_tail',$contents[$i],$mainNode);
        } 
        $_SESSION['BackgroundProcess'][$this->process]['last_line'] = $tot;
        return true;
    }


    protected function processMenu() {
        $mainNode = $this->template->getElementById('siteContent');
        if (!array_key_exists('BackgroundProcess',$_SESSION) || !is_array($_SESSION['BackgroundProcess']) || count($_SESSION['BackgroundProcess']) == 0) {
            $this->template->setDisplayDataImmediate('background_status','You do not have any available processes to view',$mainNode);
            return true;
        }
        $ulNode = $this->template->getElementById('background_menu',$mainNode);
        if (!$ulNode instanceof DOMNode) {
            I2CE::raiseError("Could not find where to add the background processes");
            return false;
        }
        foreach ($_SESSION['BackgroundProcess'] as $process=>$data) {
            $html = "<li> Process Number  $process:";
            $html .= " <a href='BackgroundProcess/clear?process=$process'>Clear</a>";
            if (array_key_exists('time',$data)) {
                $html .= "<br/>Started at " . strftime("%c",$data['time']);
            }
            $html .= "<br/>";
            $html .= "<a href='BackgroundProcess/tail?process=$process&amp;line=0'>Show All Content</a>";
            if (array_key_exists('last_line',$data) && $data['last_line'] > 0) {
                $html .= " / <a href='BackgroundProcess/tail?process=$process'>Recent Content</a>";
            }
            if( array_key_exists('process',$data) && $data['process']) {
                $html .= " / <a href='BackgroundProcess/status?process=$process'>Check Status</a> for process ID {$data['process']}";
            }
            if (array_key_exists('cmd_line',$data)) {
                $html .= "<pre class='processScroll'>" . htmlspecialchars($data['cmd_line']) ;
                if (array_key_exists('working_dir',$data) && $data['working_dir']) {
                    $html .="\nWorking Directory is: {$data['working_dir']}";
                }
                if( array_key_exists('content',$data) && $data['content']) {
                    $html .="\nExecution output is: {$data['content']}";
                }
                $html .= "</pre>";
            }
            $html .= "</li>";
            $liNode = $this->template->importText($html,'li');
            $ulNode->appendChild($liNode);
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
