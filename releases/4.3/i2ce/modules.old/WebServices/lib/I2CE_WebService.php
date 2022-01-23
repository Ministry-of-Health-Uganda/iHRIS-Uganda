<?php
/**
 * @copyright © 2014 Intrahealth International, Inc.
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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v4.2.0
 * @version v4.2.0
 */
/**
 * Handles web service actions.
 * 
 * All web service pages should be built from objects that extend this object.
 * @package I2CE
 * @abstract
 * @access public
 */
if (! class_exists('I2CE_WebService',false) ) {
    class I2CE_WebService extends I2CE_Page {

        /**
         * @var array The list of error message types to look up in magic data for translations to be displayed.
         */
        private $err_msgs;

        /**
         * @var array The data to return from this web service.
         */
        protected $data;
        
        /**
         * @var I2CE_MagicDataNode The configuration for this module.
         */
        protected $config;

        /**
         * Create a new instance of this web service page.
         * @param array $args
         * @param array $request_remainder
         * @param array $get
         * @param array $post 
         */
        public function __construct( $args, $request_remainder, $get = null, $post = null ) {
            parent::__construct( $args, $request_remainder, $get, $post );
            $this->config = I2CE::getConfig()->traverse( "/modules/web-services" );
            $this->err_msgs = array();
            //Override the permission parser template to be this object since there is no template and it 
            //should only be needed for user details.
            $this->permissionParser = new I2CE_PermissionParser($this);
        }

        /**
         * Add an error message to the results.  If it's not available in magic data then it can't be added.
         * @param string $err_msg;
         * @param array $args Any values to replace in the message.
         */
        protected function setError( $err_msg, $args=null ) {
            $msg = null;
            $this->config->setIfIsSet( $msg, "messages/" . $err_msg );
            if ( !$msg ) {
                I2CE::raiseError( "Unable to find $err_msg in /modules/web-services/messages so using unknown error." );
                $msg = "An unknown error has occurred.";
                $this->config->setIfIsSet( $msg, "messages/unknown_error" );
            }
            if ( !array_key_exists( $err_msg, $this->err_msgs ) || !is_array( $this->err_msgs[$err_msg] ) ) {
                $this->err_msgs[$err_msg] = array();
            }
            if ( $args && is_array( $args ) && count( $args ) > 0 ) {
                $this->err_msgs[$err_msg][] = vsprintf( $msg, $args );
            } else {
                $this->err_msgs[$err_msg][] = $msg;
            }
        }

        /**
         * Process the errors and replace the data with the error.
         */
        protected function processError() {
            $this->data = array( 'error' => $this->err_msgs );
        }

        /**
         * Check to see if there are any errors on this page.
         * @return boolean
         */
        public function hasError() {
            if ( is_array( $this->err_msgs ) && count( $this->err_msgs ) > 0 ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * setRedirect shouldn't really be used for web services so give a warning and then call the parent.
         * @param string $url
         */
        public function setRedirect( $url ) {
            I2CE::raiseError( "You shouldn't call setRedirect on web services.  Use setError instead to give an error message." );
            parent::setRedirect( $url );
        }


        /**
         * Calls the appropriate action for the page.  Then it 
         * processes the results as appropriate.
         * 
         * This will check to make sure the page can be seen by this user and if not send an error
         * message. 
         * @param boolean $supress_output  defaults to false.  set to true to supress the output of a webpage
         */
        public function display($supress_output = false) {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $this->displayWeb($supress_output);
            } else {
                $this->displayCommandLine();
            }
            if ( $this->hasError() ) {
                $this->processError();
            }
            $style = null;
            if ( $this->get_exists('style') ) {
                $style = $this->get('style');
            }
            if (!array_key_exists('styles',$this->args) || is_array($this->args['styles'])) {
                $this->args['styles'] = array(); //no exactly sure what you are trying to do here, but I was getting an undefined key message e if 'styles'  wasn't set. 
            }
            if ( !array_key_exists( 'styles', $this->args ) 
                 || !array_key_exists( 'allowed', $this->args['styles'] )
                 || !is_array( $this->args['styles']['allowed'] ) 
                 || !in_array( $style, $this->args['styles']['allowed'] ) ) {                
                if ( array_key_exists( 'default', $this->args['styles'] ) ) {
                    $style = $this->args['styles']['default'];
                } else {
                    $style = 'json'; // super default
                    //I2CE::raiseError( "No default style for this service.  This shouldn't happen." ); //why not?
                }
            }
            $method = 'processData_' . $style;
            if ( !method_exists( $this, $method ) ) {
                I2CE::raiseError( "No processing method available for $style so using 'json'." );
                $style = 'json';
                $method = 'processData_json';
            }
            if ( method_exists( $this, $method ) ) {
                if ( !$this->$method( $supress_output ) ) {
                    I2CE::raiseError( "Unable to process data for $style" );
                }
            } else {
                I2CE::raiseError( "Shouldn't be able to get here, but can't find $method in web service page." );
            }
        }

        /**
         * Don't initialize the template unless needed later.
         */
        protected function initializeTemplate() {
            return true;
        }

        /**
         * Force the parent to initialize the template now as we need it.
         */
        protected function forceInitializeTemplate() {
            return parent::initializeTemplate();
        }

        /**
         * Perform any actions
         * 
         * @returns boolean.  true on sucess
         */
        protected function action() {
            $this->data = array();
            return true;
        }

        /**
         * Main display method for command line interface
         */
        protected function displayCommandLine() {
            if (!$this->initPage()) {
                $this->setError( 'unknown_error' );
                I2CE::raiseError("page initialization failed");
                return ;
            }
            $this->actionCommandLine($this->args,$this->request_remainder);
        }

        /**
         * Main display method for web interface
         * @param boolean $supress_output  defaults to false.  set to true to supress the output of a webpage
         */
        protected function displayWeb($supress_output = false) {
            $i2ce_config = I2CE::getConfig()->I2CE;
            if (!$this->initPage()) {
                if ( !$this->user->logged_in() ) {
                    $this->setError('noaccess'); //defined in I2CE
                }
                return;
            }
            $permission = 'role(' . implode( ",",$this->getAccess() ) . ')';
            if (array_key_exists('tasks',$this->args) && is_array($this->args['tasks']) && count($this->args['tasks'])>0) {
                $permission .= ' | task(' . implode(',',$this->args['tasks']) . ')';
            }
            if ($this->hasPermission($permission)) {
                if ($this->action() === false) {
                    if ( !$this->hasError() ) {
                        $this->setError( 'unknown_error' );
                    }
                }
            } else{
                if ( $this->user->logged_in()) {
                    $this->setError( 'noaccess_page', array( $this->page ) );
                }
                if ( !$this->hasError() ) {
                    if (!$this->user->logged_in()) {                            
                        $this->setError( 'noaccess' );
                    }
                }
            }                   
        }
    
        /**
         * The business method if this page is called from the commmand line
         * @param array $request_remainder the remainder of the request after the page specfication.  
         * @param array $args the array of unix style command line arguments 
         * Arguements are link that in: http://us3.php.net/manual/en/features.commandline.php#78651
         * If we were called as: 
         *      index.php --page=/module/page/some/thing/else --long  -AB 2 -C -D 'ostrich' --eggs==good
         * Then $request_remainder = array('some','thing','else')
         * and $args = array('long'=>true, 'A'=>true, 'B'=>2, 'C'=>true, 'D'=>'ostrich', 'eggs'=>'good')
         */ 
        protected function actionCommandLine($args,$request_remainder) { 
            if ( $this->action() === false ) {
                if ( !$this->hasError() ) {
                    $this->setError( 'unknown_error' );
                }
            }
        }

        /**
         * Process the results data as a json array and output it.
         * @param boolean $supress_output
         * @return boolean
         */
        protected function processData_json( $supress_output=false ) {
            if ( !$supress_output ) {
                header( "Content-type: application/json" );
                echo json_encode( $this->data );
            }
            return true;
        }

        /**
         * Process the results data as xml and output it.
         * @param boolean $supress_output
         * @return boolean
         */
        protected function processData_xml( $supress_output=false ) {
            if ( !$supress_output ) {
                if ( !$this->forceInitializeTemplate() ) {
                    I2CE::raiseError( "Unable to initialize the XML templates." );
                    return false;
                }
                $this->processDataLoop_xml( $this->data, $this->template->doc->firstChild );
                header( "Content-type: text/xml" );
                echo $this->template->getDisplay();
            }
            return true;
        }

        /**
         * Loop through the passed array and recursively add all data to the XML template
         * @param array &$data
         */
        protected function processDataLoop_xml( &$data, &$parent ) {
            foreach ( $data as $key => $val ) {
                if ( is_scalar( $val ) ) {
                    $file = "web_service_value_$key.xml";
                    if ( !$this->template->findTemplate( $file, false ) ) {
                        $file = "web_service_default_value.xml";
                    }
                    $node = $this->template->appendFileByNode( $file, "data", $parent );
                    $node->setAttribute( "name", $key );
                    $this->template->appendNode( $this->template->createTextNode( $val ), $node );
                } elseif ( is_array( $val ) || is_object( $val ) ) {
                    $file = "web_service_loop_$key.xml";
                    if ( !$this->template->findTemplate( $file, false ) ) {
                        $file = "web_service_default_loop.xml";
                    }
                    $node = $this->template->appendFileByNode( $file, "values", $parent );
                    $node->setAttribute( "name", $key );
                    $this->processDataLoop_xml( $val, $node );
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
