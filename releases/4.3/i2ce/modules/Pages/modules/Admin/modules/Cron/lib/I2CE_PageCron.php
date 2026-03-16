<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage admin
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1.6
* @since 4.1.6
* @filesource 
*/ 
/** 
* Class I2CE_PageCron
* 
* @access public
*/

class I2CE_PageCron extends I2CE_Page {


    /**
     * @var I2CE_CLI The CLI object for this page.
     */
    protected $cli;
    /**
     * @var array The allowed types to be run.
     */
    protected $types;
    /**
     * @var I2CE_MagicDataNode The configuration for cron details.
     */
    protected $config;

    /**
     * Create a new instance of a page.
     * 
     * The default constructor should be called by any pages extending this object.  It creates the
     * {@link I2CE_Template} and {@link I2CE_User} objects and sets up the basic member variables.
     * @param array $args
     * @param array $request_remainder The remainder of the request path
     */
    public  function __construct( $args, $request_remainder, $get=null, $post=null ) {
        $this->config = I2CE::getConfig()->modules->admin->cron;
        $this->types = $this->config->types->getAsArray();
        $this->cli = new I2CE_CLI();
        $this->cli->addUsage("[--type=XXXX]: The type of cron to run.  If not set, the system will determine\n");
        $this->cli->addUsage("               what to run based on recent run times.\n");
        $this->cli->addUsage("               Possible values are:  " . implode( ', ', array_keys( $this->types ) ) . "\n");
        $this->cli->addUsage("               If the most recently run cron hasn't finished for a type then it\n");
        $this->cli->addUsage("               will not run again.\n");
        $this->cli->addUsage("[--silent=true|false]: Determine if standard messages will be shown or not.\n");
        $this->cli->addUsage("                       Defaults to: false\n");
        parent::__construct($args,$request_remainder,$get,$post);
        $this->cli->processArgs();
    }

    
    /**
     * The business method if this page is called from the commmand line
     * @param array $request_remainder the remainder of the request after the page specfication.  
     * @param array $args the array of unix style command line arguments 
     */

    protected function actionCommandLine( $args, $request_remainder ) { 
        $silent = false;
        if ( $this->cli->hasValue( 'silent' ) ) {
            if ( strtolower( $this->cli->getValue( 'silent' ) ) == 'true' ) {
                $silent = true;
            }
        }
        $type = 'all';
        if ( $this->cli->hasValue( 'type' ) ) {
            $type = $this->cli->getValue( 'type' );
        }
        if ( $type != 'all' && !array_key_exists( $type, $this->types ) ) {
            $this->cli->usage( "An invalid type was given:  $type.\n" );
        }
        if ( $type != 'all' ) {
            $list = array( $type => $this->types[$type] );
        } else {
            $list = $this->types;
        }
        $this->config->times->volatile(true);
        $this->config->status->volatile(true);
        $this->config->pid->volatile(true);
        foreach( $list as $cron_type => $cron_data ) {
            $now = time();
            $run_it = false;
            $last_run = 0;
            $this->config->setIfIsSet( $last_run, "times/$cron_type" );
            if ( $last_run == 0 ) {
                $duration = 0;
                $run_it = true;
            } else {
                $duration = $now - $last_run;
                if ( array_key_exists( 'getdate_key', $cron_data ) ) {
                    $last_run_time = getdate( $last_run );
                    $current_time = getdate( $now );
                    if ( array_key_exists( 'getdate_value', $cron_data ) ) {
                        if ( $current_time[ $cron_data['getdate_key'] ] == $cron_data['getdate_value'] &&
                          (array_key_exists('delay', $cron_data) ? $duration > $cron_data['delay'] : true) ) {
                            $run_it = true;
                        }
                    } else {
                        if ( $current_time[ $cron_data['getdate_key'] ] > $last_run_time[ $cron_data['getdate_key'] ] ) {
                            $run_it = true;
                        } elseif( array_key_exists( 'getdate_trail', $cron_data ) ) {
                            $trail = explode( ',', $cron_data['getdate_trail'] );
                            foreach( $trail as $key ) {
                                if ( array_key_exists( $key, $last_run_time ) && $current_time[$key] > $last_run_time[$key] ) {
                                    $run_it = true;
                                    break;
                                }
                            }
                        }
                        if ( $run_it && array_key_exists( 'delay', $cron_data ) && $duration < $cron_data['delay'] ) {
                            $run_it = false;
                        }
                    }
                } elseif ( array_key_exists( 'delay', $cron_data ) ) {
                    if ( $duration > $cron_data['delay'] ) {
                        $run_it = true;
                    }
                }
            }

            $timeout = 0;
            if ( array_key_exists( 'timeout', $cron_data ) ) {
                $timeout = $cron_data['timeout'];
            }
            $status = 'done';
            $this->config->setIfIsSet( $status, "status/$cron_type" );
            if ( $status == 'in_progress' ) {
                $pid = 0;
                $this->config->setIfIsSet( $pid, "pid/$cron_type" );
                // Seeing if the current process is still running.
                $pid = (int)$pid;
                $can_kill = 0;
                if ( $pid && is_int( $pid ) ) {
                    exec( "ps h -o command -p " . escapeshellarg($pid), $output, $ret_val );
                    if ( $ret_val == 0 && array_key_exists( 0, $output ) && strpos( $output[0], "--page=/admin/cron" ) !== false ) {
                        $can_kill = $pid;
                        if (!$silent) echo "Current process is still running so double the timeout.\n";
                        // Double the timeout or set it to something if it was 0 and the process is still running.
                        $timeout = $timeout * 2;
                        if ( $timeout == 0 ) {
                            $timeout = 86400;
                        }
                    }
                } else {
                    if (!$silent) echo "Invalid previous pid ($pid) so not searching for it.\n";
                }
                if ( $duration < $timeout ) {
                    $run_it = false;
                } else {
                    if (!$silent) echo "Duration exceeds timeout ($duration > $timeout) so running again.\n";
                    if ( $can_kill ) {
                        // Try to kill the running process
                        exec( "kill -9 " . escapeshellarg( $can_kill ) );
                        if (!$silent) echo "Tried to kill existing process $can_kill\n";
                    }
                }
            }
            if ( $run_it ) {
                $this->config->status->$cron_type = 'in_progress';
                $this->config->pid->$cron_type = getmypid();
                $this->config->times->$cron_type = time();
                I2CE_ModuleFactory::callHooks( "cronjob_$cron_type" );
                $this->config->status->$cron_type = 'done';
            } else {
                if ( !$silent ) {
                    echo "Not time to run $cron_type ($duration) " . strftime( "%c %z", $last_run ) . "\n";
                }
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
