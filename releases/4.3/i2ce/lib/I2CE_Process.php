<?php
/**
 * @copyright © 2009 IntraHealth International, Inc.
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
 * @author Mark A. Hershberger <mhershberger@intrahealth.org>
 * @filesource
 */

require_once("I2CE.php");

/**
 * Handles processes.
 * @package I2CE
 */
class I2CE_Process
{

    /**
      * @var string $cmd The command being executed.
      */
    private $cmd;

    /**
      * @var string $res Currently executing resource
      */
    private $res;

    /**
      * @var array $pipes Stdin, Stdout, etc.
      */
    private $pipes;

    /**
      * @var integer $exit holds the exit code of the program
      */
    private $exit;

    /**
      * @var boolean $done Flag so that we only execute this once.
      */
    private $done;


    /**
      * Constructor for the I2CE_Process
      * @params string $cmd The full command line to execute.
      */
    public function __construct($cmd) {
        $this->cmd = $cmd;

        $descriptorspec =
            array(0 => array('pipe', 'r'),
                  1 => array('pipe', 'w'),
                  2 => array('pipe', 'w'));

        $this->res = proc_open($this->cmd, $descriptorspec, $this->pipes);
        if(is_resource($this->res)) {
            return $this;
        }
    }

    /**
      * Slurp up the output of the process
      * @params string $cmd The full command line to execute.
      * @todo implement stdin
      */
    private function slurp() {
        if($this->done) {
            return;
        }

        fclose($this->pipes[0]); /* FIXME: Haven't done stdin yet */
        $this->stdout = stream_get_contents($this->pipes[1]);
        $this->stderr = stream_get_contents($this->pipes[2]);

        fclose($this->pipes[1]);
        fclose($this->pipes[2]);

        $this->exit = proc_close($this->res);
        $this->done = TRUE;
    }

    /**
      * Returns a boolean indicating if an error code occurred.  Will
      * run the Process if it hasn't been run.
      *
      * @returns boolean TRUE if an error occurred.
      */
    public function is_error() {
        $this->slurp();

        if($this->exit === 0) {
            return FALSE;
        }
        return $this->exit;
    }

    /**
      * Provides access to what the command spewed out on STDOUT
      *
      * @returns string The output.
      */
    public function stdout() {
        $this->slurp();
        return $this->stdout;
    }

    /**
      * Provides access to what the command spewed out on STDERR
      *
      * @returns string
      */
    public function stderr() {
        $this->slurp();
        return $this->stderr;
    }
}

# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
