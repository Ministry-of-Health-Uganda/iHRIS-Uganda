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
*  I2CE_TaskParser
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/

if (!class_exists('I2CE_PermissionParser',false)) {
    class I2CE_PermissionParser extends I2CE_Fuzzy {

        protected $user;
        protected $template;
        protected $has_get_data;
        protected $has_get_display;
        /**
         * @var array A cached array of the task trickle down data.
         */
        static protected $task_trickle_down;
        /**
         * @var array The reverse of the trickle down array.
         */
        static protected $task_trickle_up;
        /**
         * @var array The role_trickle_up data cached for faster access.
         */
        static protected $role_trickle_up;
        /**
         * @var array A list of tasks given by a role.
         */
        static protected $role_tasks;
        /**
         * @var array A cache of tasks a role can access for repeated checkes.
         */
        static protected $role_has_task;


        public function __construct($template) {
            $this->user = new I2CE_User();
            $this->template = $template;
            $factory = I2CE_ModuleFactory::instance();
            $this->has_get_data = $factory->isEnabled('template-data');
            $this->has_get_display = $factory->isEnabled('DisplayData');
        }

        /**
         * Set up the trickle down static array if it hasn't been set.
         * @param boolean $override Override what's there if true
         */
        protected static function setupTrickleDown( $override = false ) {
            if ( $override || !is_array( self::$task_trickle_up ) 
                    || count( self::$task_trickle_up ) == 0 ) {
                self::$task_trickle_up = array();
                $task_path = "/I2CE/tasks/task_trickle_down";
                if (I2CE::getConfig()->is_parent($task_path)) {
                    self::$task_trickle_down = I2CE::getConfig()->getAsArray($task_path);
                    foreach( self::$task_trickle_down as $top => $trickle ) {
                        foreach( $trickle as $down_task ) {
                            self::$task_trickle_up[$down_task][$top] = true;
                        }
                    }
                }
                        
            }
        }

        /**
         * Add a task as permitted for a given role as well as
         * all tasks it trickles down to recursively if the
         * permission is allowed (true).
         * @param string $role
         * @param string $task
         */
        protected static function setRoleHasTask( $role, $task ) {
            if ( !is_array( self::$role_has_task ) ) {
                self::$role_has_task = array();
            }
            if ( !isset( self::$role_has_task[$role] ) || 
                    !is_array( self::$role_has_task[$role] ) ) {
                self::$role_has_task[$role] = array();
            }
            if ( !array_key_exists( $task, self::$role_has_task[$role] ) ) {
                self::$role_has_task[$role][$task] = true;
                if ( isset( self::$task_trickle_down[$task] ) ) {
                    foreach( self::$task_trickle_down[$task] as $also_task ) {
                        self::setRoleHasTask( $role, $also_task );
                    }
                }
            }
        }

        /**
         * Return the permitted value from the role/task cache.
         * @param string $role
         * @param string $task
         * @return boolean
         */
        protected static function getRoleHasTask( $role, $task ) {
            if ( is_array( self::$role_has_task ) 
                    && isset( self::$role_has_task[$role] )
                    && is_array( self::$role_has_task[$role] )
                    && array_key_exists( $task, self::$role_has_task[$role] ) ) {
                return self::$role_has_task[$role][$task];
            } else {
                return false;
            }
        }



        /**
         * Return the array of tasks that are directly given by a
         * role.
         * @param string $user_role
         * @return array
         */
        protected static function getRoleTasks( $user_role ) {
            if ( !self::$role_tasks || !is_array( self::$role_tasks ) ) {
                self::$role_tasks = array();
            }
            if ( !$user_role ) {
                return array();
            }
            if ( !array_key_exists( $user_role, self::$role_tasks ) ) {
                $checked_roles = array();
                $user_roles = array();
                if ($user_role) {
                    $user_roles[] = $user_role;
                }
                $roles = I2CE::getConfig()->I2CE->formsData->forms->role;
                while (count($user_roles) > 0) {
                    $role = array_shift($user_roles);            
                    if (!is_string($role) || strlen($role) == 0) {
                        continue;
                    }
                    if (array_key_exists($role,$checked_roles) && $checked_roles[$role]) {
                        continue; 
                    }
                    $checked_roles[$role] = true;
                    foreach ($roles as $r=>$desc) {
                        $role_trickle = self::getRoleTrickleUp($r);
                        if (in_array($role,$role_trickle)) {
                            if (($r=='any') || ($r=='all')) {
                                continue;
                            }
                            if ($r == 'admin') {
                                self::$role_tasks[ $user_role ] = 'admin';
                                return 'admin';
                            }
                            $user_roles[] = $r;
                        }
                    }
                }
                // all the possible roles the user is considered to have
                $user_roles = array_keys($checked_roles );  
                // this will store all tasks for any of the roles the user 
                // has (not each task can have a trickle down)
                $role_tasks = array();  
                foreach ($user_roles as $r) {
                    if (!is_string($r) || strlen($r) == 0) {
                        continue;
                    }
                    $t_role_tasks = array();
                    I2CE::getConfig()->setIfIsSet($t_role_tasks,"/I2CE/tasks/role_trickle_down/" . $r, true);
                    $role_tasks = array_unique(array_merge($role_tasks,$t_role_tasks));
                }
                self::$role_tasks[ $user_role ] = array_flip( $role_tasks );
            }
    
            return self::$role_tasks[ $user_role ];

        }



        /**
         * Check to see if a user has permission for a given role/task whatever
         * @param string $permissions The permisisions string.
         * Example: "role(admin) or task(can_view_page) or task(can_edit_page)"
         * Example: "ModuleName->Method(admin)  will call Method in the module ModuleName with arguemnts ('admin')
         * Exmaple: "role(admin|staff)" is the same as "role(admin,staff)" which is the same as "role(admin) role(staff)" 
         * which is the same as "role(admin) | role(staff)" which is the same as "role(admin) or role(staff)"
         * Example: "role(admin) or task(can_view_page) and role(staff)" is the same as
         *          "(role(admin) or task(can_view_page)) and role(staff)"  i.e. parenthesis are grouped left.
         * Note: You may reference variables, which is really template data, as a function argument or in a double quoted string.
         *       When referencing varaibles, you are allowed to include types, such as ${T1}z, but you are not allowed to 
         *       do so in a doulbe quoted string.  Variable names may consit of alpha-numeric character, '_', and '-'.  
         *       You may protect a variable by surrounding it in {}'s
         * Example: "func1($a,"x$a", '\' "'  "x{$a}y" , "x{$ay} ${T1}z )"  causes the (possbily fuzzy) method
         *   hasPermission_func1() with the following arguments:
         *   array( $this->template->getDisplayData('a',$node), 
         *          'x' . $this->template->getDisplayData('a',$node), 
         *          '\' "',
         *          'x' . $this->template->getDisplayData('ay',$node) ,
         *          'x' . $this->template->getDisplayData('a',$node) . 'y', 
         *          'x' . $this->template->getData('T1',z,$node,true,true))
         * There are special argument you can pass to the function which are  <TEMPLATE>, <USER> and <NODE> which will be
         * the template, user and calling node  associated with this permission.
         * Note: spaces provide an implicit comma to split arguments.  
         * Example functions: role, task which take a list or roles and task repsectively.
         * Example function:  form('person' method <USER>) -- finds the person form,relative to $node and calls $form->method($user)
         * Example function:  form(person , 'weird method;',<USER>10) -- finds the person form,relative to $node and calls $form->$method($user,10)
         *      where $method='weird method;'
         * Example function: module('mod_name',method,arg1,...,argn) works like form but for modules
         * @param DOMNode $node The node to get on set data from.
         * @returns mixed.  True/false, or null on failure.
         */
        public function hasPermission($permission, $node = null) {
            if (!$this->user instanceof I2CE_User) {
                I2CE::raiseError("No valid user for checking roles");
                return false;
            }
            $ret =$this->parsePermissionsLogic($permission, $node);
            return $ret;

        }



        public function hasRole($role, $node = null) {
            if (is_string($role)) {
                $role = trim($role);
                if (substr($role,0,5) != 'role(') {
                    $role = "role($role)";
                }
            } else if (is_array($role)) {
                $role = 'role(' . implode(',',$role) . ')';
            } else {
                I2CE::raiseError("Unrecognzied role: ". gettype($role));
                return null;
            }
            return $this->hasPermission($role,$node);
        }
        public function hasTask($task,$node = null) {
            if (is_string($task)) {
                $task = trim($task);
                if (substr($task,0,5) != 'task(') {
                    $task = "task($task)";
                }
            } else if (is_array($task)) {
                $task = 'task(' . implode(',',$task) . ')';
            } else {
                I2CE::raiseError("Unreconzied task: ". gettype($task));
                return null;
            }
            return $this->hasPermission($task,$node);
        }


        protected function hasPermsssion_module($node,$args) {
            if (count($args) < 2) {
                I2CE::raiseError("Expecting at least two arguments to method() function");
            }
            $module = array_shift($args);
            $method = array_shift($args);
            $factory = I2CE_ModuleFactory::instance();
            $class = $factory->getClass($module);
            if (!$class instanceof I2CE_Module) {
                I2CE::raiseError("Invalid module $module"); 
                return null;
            }
            if (!$class->_hasMethod($method)) {
                I2CE::raiseError("Invalid method $method for class $class:". get_class($class));
            }
            return call_user_function_array(array($class,$method),$args);
        }


        /**
         * Checks to see if the given task is defined in they system
         * @param string $task
         * @returns boolean
         */
        public static function taskExists($task) {
            return (I2CE_MagicDataNode::checkKey($task) && I2CE::getConfig()->is_scalar("/I2CE/tasks/task_description/$task"));
        }

        /**
         * Checks to see if a given task has an alternative way of being satisfied.
         * @param DOMNode $node
         * @param string $task
         */
        protected function taskHasAlternateSatisfaction($node,$task) {
            //first check to make sure the user is logged in.
            if (!$this->user->logged_in()) {
                return false;
            }

            $rules = array();

            if (I2CE::getConfig()->setIfIsSet($rules,"/I2CE/tasks/alt_satisfaction/$task",true)) {
                foreach ($rules as $rule) {
                    if (!is_scalar($rule) || !$rule) {
                        continue;
                    }
                    if ($this->parsePermissionsLogic($rule, $node)) {
                        return true;
                    }
                }            
            }
            return false;
        }

        protected function hasPermission_task($node,$tasks) {
            $user_role = $this->user->getRole();
            if ($user_role == 'admin')  {
                return true;
            }
            foreach( $tasks as $i=>$task ) {
                //make sure everything is clean
                if (!self::taskExists($task)) {
                    unset($tasks[$i]);
                }
            }            
            if (count($tasks) == 0) {
                return true;
            }

            $role_tasks = self::getRoleTasks( $user_role );
            if ( is_string( $role_tasks ) && $role_tasks == "admin" ) {
                return true;
            }

            self::setupTrickleDown();

            $checked_tasks = array();
            $alt_check = $tasks;
            while( count( $tasks ) > 0 ) {
                $task = array_shift( $tasks );
                if ( isset( $checked_tasks[$task] ) ) {
                    continue;
                }
                $checked_tasks[$task] = true;
                if ( self::getRoleHasTask( $user_role, $task ) ) {
                    return true;
                }

                if ( isset( $role_tasks[$task] ) ) {
                    self::setRoleHasTask( $user_role, $task );
                    return true;
                }

                if ( isset( self::$task_trickle_up[$task] ) ) {
                    $tasks = array_merge( $tasks, 
                            array_keys( self::$task_trickle_up[$task] ) );
                    $alt_check = array_unique( array_merge( $alt_check,
                                array_keys( self::$task_trickle_up[$task] ) ) );
                }
            }
            foreach( $alt_check as $task ) {
                if ($this->taskHasAlternateSatisfaction($node,$task)) {
                    return true;
                }
            }
            return false;

        }



        /**
         * Returns the role trickle up from the shortname
         * @param string $name the role shortname
         * @returns array (an empty array if there is no such tag name)
         */
        protected static function getRoleTrickleUp($name) {
            if ( !isset( self::$role_trickle_up[$name] ) ||
                    !is_array( self::$role_trickle_up[$name] ) ) {
                $ret = '';
                I2CE::getConfig()->setIfIsSet($ret,"/I2CE/formsData/forms/role/$name/fields/trickle_up");
                self::$role_trickle_up[$name] = explode(',', $ret);
            }
            return self::$role_trickle_up[$name];
        }



        protected function hasPermission_role($node,$roles) { //it will be an array
            if (count($roles) == 0) {
                return true; // no role specified in the string
            }
            $any = false;
            $user_role = $this->user->getRole();
            $has_role = false;
            foreach ($roles as $role) {
                $role = trim($role);
                if ($role == 'NOT_LOGGED_IN') {
                    return !$this->user->logged_in();
                } else if ($role == 'all') {
                    return true;
                } else if ($role == 'any') {
                    $any = true;
                } else if ($role == $user_role) {
                    $has_role = true;
                }
            }
            if ( !$this->user->logged_in() ){
                return false;
            }
            if ($any || $has_role) {
                return true;
            }
            //now that we did the quick checks, we check the trickle
            $checked_roles = array(); //just to check against recursion in the role trickling
            while (count($roles) > 0) {
                $name = array_pop($roles);
                $checked_roles[$name] = true;
                $trickle = self::getRoleTrickleUp($name);
                foreach ($trickle as $t) {
                    if (!is_string($t)) {
                        continue;
                    }
                    $t = trim($t);
                    if (!$t) {
                        continue;
                    }
                    if (array_key_exists($t,$checked_roles) && $checked_roles[$t] ) {
                        continue;
                    }
                    if (($t == $user_role) || ($t=='any') || ($t=='all')) {
                        return true; 
                    }
                    $roles[] = $t;
                }
            }
            return false;
        }

        protected function hasPermission_user($node,$users) { //it will be an array
            if (count($users) == 0) {
                return true; // no user specified in the string
            }
            if ( !$this->user->logged_in() ){
                return false;
            }

            if ( in_array( $this->user->username, $users ) !== false ) {
                return true;
            }

            return false;
        }



        const GROUND = 0;
        const FUNC = 10; //either a task or a function
        const FUNC_MAYBE_MODULE = 10; //maybe its a module
        const FUNC_ARGS = 20; //either a task or a function
        const FUNC_ARG_QUOTED = 21; //either a task or a function
        const FUNC_ARG_QUOTED_ESCAPED = 22; //either a task or a function
        const FUNC_ARG_UNQUOTED = 25; //either a task or a function
        const FUNC_ARG_DBL_QUOTED = 30; //either a task or a function
        const FUNC_ARG_DBL_QUOTED_ESCAPED = 31; //either a task or a function
        const FUNC_ARG_DBL_QUOTED_VARIABLE_BEGIN = 32; //either a task or a function
        const FUNC_ARG_DBL_QUOTED_VARIABLE = 33; //either a task or a function
        const FUNC_ARG_DBL_QUOTED_PROTECTED_VARIABLE_BEGIN = 34; //either a task or a function
        const FUNC_ARG_DBL_QUOTED_PROTECTED_VARIABLE = 35; //either a task or a function
        const FUNC_ARG_VARIABLE_BEGIN = 40; //either a task or a function
        const FUNC_ARG_VARIABLE_TYPED = 41; //either a task or a function
        const FUNC_ARG_VARIABLE = 42; //either a task or a function
        const FUNC_ARG_SPECIAL_VARIABLE = 50;


    


        protected function parsePermissionsLogic(&$permission,$node) {
            $state = self::GROUND;
            $orig_permission = $permission;
            $func = '';
            $module = '';
            $booleans = array();
            $i = 0;
            $arg = '';
            $args = array();
            $var = '';
            $type = 'DISPLAY'; // default type for variables is DISPLAY
            while (strlen($permission) > 0 && $i < strlen($permission) ) {
                $c = $permission[$i];
                $i++;
                switch ($state) {
                case self::FUNC_ARG_SPECIAL_VARIABLE:
                    if ($c == '>') {
                        if (strtoupper($var) == 'USER') {
                            $args[] = $this->user;
                        }  else if (strtoupper($var)== 'TEMPLATE') {
                            if ($this->template instanceof I2CE_TemplateMeister) {
                                $args[] = $this->template;
                            } else {
                                $args[] = null;
                            }
                        }  else if (strtoupper($var) == 'NODE') {
                            $args[] = $node;
                        } else {
                            I2CE::raiseError("Unrecognized special varaible ending at $i,  <$var> in $orig_permission");
                            return null;
                        }
                        $state = self::FUNC_ARGS;
                        $arg = '';
                        $var ='';
                    } else {
                        $var .= $c;
                    }
                    break;
                case self::FUNC_ARG_DBL_QUOTED_ESCAPED:
                    $state = self::FUNC_ARG_DBL_QUOTED;
                    $arg .= $c;
                    break;
                case self::FUNC_ARG_DBL_QUOTED_PROTECTED_VARIABLE_BEGIN:
                    if ($c == '$') {
                        $state = FUNC_ARG_DBL_QUOTED_PROTECTED_VARIABLE;
                        $var = '';
                    } else {
                        I2CE::raiseError("Unexpected character $c at $i in $orig_permission");
                        return null;
                    }
                    break;
                case  self::FUNC_ARG_DBL_QUOTED_PROTECTED_VARIABLE:
                    if ($c == '}') { 
                        $state = self::FUNC_ARG_DBL_QUOTED;
                        if (!$this->has_get_display) {
                            I2CE::raiseError("Template Display Data not enabled");
                            return null;
                        }
                        if ($this->template instanceof I2CE_TemplateMeister) {
                            $arg .= $this->template->getDisplayData($var,$node);
                        }
                        $var = '';
                    } else {
                        $var .= $c;
                    }
                    break;
                case self::FUNC_ARG_DBL_QUOTED:
                    switch ($c) {
                    case '{':
                        $state = self::FUNC_ARG_DBL_QUOTED_PROTECTED_VARIABLE_BEGIN;
                        break;
                    case '"':
                        $state = self::FUNC_ARGS;
                        $args[] = $arg;
                        $arg = '';
                        break;
                    case '\\':
                        $state = self::FUNC_ARG_DBL_QUOTED_ESCAPED;
                        break;
                    case '$':
                        $state = self::FUNC_ARG_DBL_QUOTED_VARIABLE;
                    default:
                        $arg .= $c;
                    }
                    break;
                case self::FUNC_ARG_DBL_QUOTED_VARIABLE:
                    if (ctype_alnum($c) || $c == '-' || $c == '_') {
                        $var .= $c;
                    } else {
                        $state = self::FUNC_ARG_DBL_QUOTED;
                        if (!$this->has_get_display) {
                            I2CE::raiseError("Template Display Data not enabled");
                            return null;
                        }
                        if ($this->template instanceof I2CE_TemplateMeister) {
                            $arg .= $this->template->getDisplayData($var,$node);
                        }
                        $var = '';
                        $type = 'DISPLAY';
                    }
                    break;
                case self::FUNC_ARG_VARIABLE: 
                    if (ctype_alnum($c) || $c == '-' || $c == '_') {
                        $var .= $c;                    
                    } else {
                        if (ctype_space($c)) {
                            $state = self::FUNC_ARGS;
                        } else {
                            $state = self::FUNC_ARGS;
                            $i--; //so we go back  and reprocess $c; we need to look for the beginning of the next argument
                        }
                        if (!$this->has_get_data) {
                            I2CE::raiseError("Template Data not enabled");
                            return null;
                        }
                        if ($this->template instanceof I2CE_TemplateMeister) {
                            $args[] = $this->template->getData($type,$var,$node,true,true); //get all data and use default if it exists.
                        } else {
                            $args[] = null;
                        }
                        $var = '';
                        $type = 'DISPLAY';                    
                    }
                    break;
                case self::FUNC_ARG_VARIABLE_BEGIN:
                    if ($c == '{') {
                        $type = '';
                        $state = self::FUNC_ARG_VARIABLE_TYPED;
                    } else {
                        $type = 'DISPLAY';
                        $var .= $c;
                        $state = self::FUNC_ARG_VARIABLE;
                    }
                    break;
                case self::FUNC_ARG_VARIABLE_TYPED:
                    if ($c == '}') {
                        $state = self::FUNC_ARG_VARIABLE;
                        $var = '';
                    } else {
                        $type .= $c;
                    }
                    break;
                case self::FUNC_ARG_QUOTED_ESCAPED:
                    $arg .= $c;
                    $state  = self::FUNC_ARG_QUOTED;
                    break;
                case self::FUNC_ARG_QUOTED:
                    if  (($c == "'")) {
                        $state = self::FUNC_ARGS;
                        $args[] = $arg;
                        $arg = '';
                    } else if ($c == '\\' ) {
                        $state=self::FUNC_ARG_QUOTED_ESCAPED;
                    } else {
                        $arg .= $c;
                    }
                    break;
                case self::FUNC_ARG_UNQUOTED:
                    if ($c == ')') {
                        $state = self::FUNC_ARGS;                    
                        $i--;
                        $args[] = $arg;
                        $arg = '';
                    } else  if  (ctype_space($c) || $c == '|' || $c==',') {
                        $state = self::FUNC_ARGS;                    
                        $args[] = $arg;
                        $arg = '';
                    }else {
                        $arg .= $c;
                    }
                    break;
                case self::FUNC_ARGS:
                    if ( $c == "'" ) {
                        $state = self::FUNC_ARG_QUOTED;
                        $arg = '';
                    } else if ($c == '"') {
                        $state = self::FUNC_ARG_QUOTED;
                        $arg = '';
                    } else  if (ctype_space($c)) {
                        //do nothing.
                    } else  if (($c == '|') || ($c == ',')){
                        $args[] = ''; //Here, $arg should be blank, we are passing an empty string as the argument.
                        $arg = '';
                    } else if ($c == ')') {
                        $state = self::GROUND;
                        $callingObj = null;
                        if ($module) {
                            //setup the args so it is node,template,arg1,arg2... function
                            array_unshift($args,$this->template);
                            array_unshift($args,$node);
                            $mod_factory = I2CE_ModuleFactory::instance();
                            if (!$mod_factory->isEnabled($module) ||   !($callingObj = $mod_factory->getClass($module)) instanceof I2CE_Module) {
                                return null;
                            }
                            $method = $func;
                        } else {
                            //setup the args so it is nodem, args... function
                            $args = array($node,$args);
                            $callingObj = $this;
                            $method = 'hasPermission_' . $func;
                        }
                        if (!$callingObj->_hasMethod($method)) {
                            I2CE::raiseError("Method $method does not exist in " . get_class($callingObj));
                            return null;
                        }     
                        $result = call_user_func_array(array($callingObj,$method),$args);               
                        if ($result !== null && !is_bool($result)) {
                            I2CE::raiseError("Invalid result from $method of " . get_class($classingObj));
                            return null;
                        }
                        $booleans[] = $result;
                        $func = '';
                        $args = array();
                        $arg = '';
                    } else if ($c=='<') {
                        $state = self::FUNC_ARG_SPECIAL_VARIABLE;
                        $var ='';
                    } else {  //presumably some printable character...                     
                        $arg .= $c;
                        $state = self::FUNC_ARG_UNQUOTED;
                    }                
                    break;
                case self::GROUND;
                if (ctype_alnum($c) ) {
                    $func .= $c;
                    $state = self::FUNC;
                } else if ($c == '(') {
                    $permission  = substr($permission,$i); //get the substring starting after the ( which is at $i-1 ($i >= 1))
                    $val = $this->parsePermissionsLogic($permission, $node);
                    if ($val !== null && !is_bool($val)) {
                        I2CE::raiseError("Unable to evaluate subexpression starting at $i of $orig_permission");
                        return null;
                    }
                    $booleans[] = $val; 
                    $i = 0;
                } else if ($c == '|') {
                    $booleans[] = '|';
                    $state= self::GROUND;
                } else if ($c == '!') {
                    $booleans[] = '!';
                    $state= self::GROUND;
                } else if ($c == '&') {
                    $booleans[] = '&';
                    $state= self::GROUND;
                } else if (ctype_space($c)) {
                    //ignore it                    
                } else  {
                    I2CE::raiseError("Runaway expression ($c/$state) at $orig_permission");
                    return null;
                }
                break;
                case self::FUNC_MAYBE_MODULE;  //we have $func = 'something-'   need to check it is is a module e.g. 'something->'
                if ( $c == '>') {
                    $module = substr($func,0,-1);
                    $func = '';
                    $state = self::GROUND;
                    break;
                }  
                //INTENTIONAL NO BREAK.. it is not a module, so let us fall back to  the func.
                case self::FUNC;
                if (ctype_alnum($c) ) {
                    $func .= $c;
                } else if ($c == '_') {
                    $func .= $c;
                } else if ($c == '.') {
                    $func .= $c;
                } else if ($c == '-') {
                    $func .= $c;
                } else if ($c == '+') {
                    $func .= $c;
                } else if ($c == '(') {
                    if (strtolower($func) == 'or') {
                        $booleans[] = '|';
                        $func = '';
                        $state= self::GROUND;
                    } else if (strtolower($func) == 'and') {
                        $booleans[] = '&';
                        $func = '';
                        $state= self::GROUND;
                    } else if (strtolower($func) == 'xor') {
                        $booleans[] = 'xor';
                        $func = '';
                        $state = self::GROUND;
                    } else if (strtolower($func) == 'true') {
                        $booleans[] = true;
                        $func = '';
                        $state = self::GROUND;
                    } else if (strtolower($func) == 'false') {
                        $booleans[] = false;
                        $func = '';
                        $state = self::GROUND;
                    } else {
                        $state = self::FUNC_ARGS;
                    }
                } else if (ctype_space($c)) {
                    if (strtolower($func) == 'or') {
                        $booleans[] = '|';
                        $func = '';
                        $state = self::GROUND;
                    } else if (strtolower($func) == 'and') {
                        $booleans[] = '&';
                        $func = '';
                        $state = self::GROUND;
                    } else if (strtolower($func) == 'xor') {
                        $booleans[] = 'xor';
                        $func = '';
                        $state = self::GROUND;
                    } else if (strtolower($func) == 'true') {
                        $booleans[] = true;
                        $func = '';
                        $state = self::GROUND;
                    } else if (strtolower($func) == 'false') {
                        $booleans[] = false;
                        $func = '';
                        $state = self::GROUND;
                    } else {
                        I2CE::raiseError("Invalid expression at ($i) $orig_permssion");
                        return null;
                    }
                    $func = '';
                    $state = self::GROUND;
                } else {
                    I2CE::raiseError("Invalid expression at ($i) $orig_permssion");
                    return null;
                    //do  nothing
                }
                break;
                }
            }
            if ($state !== self::GROUND) {
                I2CE::raiseError("Unfinised expression in $orig_permission (STATE=$state)");
                return null;
            } 
            //we now can process all the true/false  and operators
            $val = $this->evaluateBooleans($booleans);
            if ($val !== null && !is_bool($val)) {
                I2CE::raiseError("Bad expression $orig_permission");
                return null;
            }
            return $val;
        }


        protected function evaluateBooleans($booleans) {
            $not = false;
            while ( count($booleans) > 0 && !(is_bool($booleans[0]) || $booleans[0]===null)) {
                $op = array_shift($booleans);
                if ($op ===  '!') {
                    $not = !$not;
                } else {
                    I2CE::raiseError("Invalid begining operator $op");
                    return null;
                }
            }
            if (count($booleans) == 0) {
                I2CE::raiseError("No booleans to evaluate");
                return null;
            }
            //$val = array_shift($booleans);
            //we know from our while loop aboe that first element  of $booleans
            //is a boolean.  thus the first if block of the foreach block below will run
            //setting $val = false and $last_op = null then means in the if block
            // that $last_op becomes |
            // suppose $booleans[0] = false
            //    if $not = true then  $val = (false || !false) = false || true = true   -- -this happens for "!false"
            //    if $not = false then $val = (false || false) = false  ---- this happens for "false"
            // suppose $booleans[0] = true
            //    if $not = true then  $val = (false || !true) = false || false = false   -- -this happens for "!true"
            //    if $not = false then $val = (false || true) = true  ---- this happens for "true"

            $val = false;  
            $last_op = null;
            foreach ($booleans as $bool) {
                if ($bool === null) {
                    if ($last_op === null) {
                        $last_op = '|'; //we use implict or's when evaluating repeated booleans
                    }
                    $not = false; //we can ignore a not operator here b/c !null = null
                    switch($last_op) {
                    case '|':
                        if ($val === null || $val === false) {
                            $val = null;
                        } else {
                            $val = true;
                        }
                        break;
                    case '&':
                        if ($val === null || $val === true) {
                            $val = null;
                        } else {
                            $val = false;
                        }
                        break;
                    case 'xor':
                        $val = null;
                        break;
                    }
                    $last_op = null;
                } else if (is_bool($bool)) {
                    if ($last_op === null) {
                        $last_op = '|'; //we use implict or's when evaluating repeated booleans
                    }
                    if ($not) {
                        $bool = (!$bool);
                        $not = false;
                    }
                    switch($last_op) {
                    case '|':
                        if ($val === null) {
                            if ($bool) {
                                $val = true;
                            } else {
                                $val = false;
                            }
                        } else {
                            $val = ($val || $bool);
                        }
                        break;
                    case '&':
                        if ($val === null) {
                            if ($bool) {
                                $val = null;
                            } else {
                                $val = false;
                            }
                        } else {
                            $val = ($val && $val);
                        }
                        break;
                    case 'xor':
                        if ($val === null) {
                            $val = null;
                        } else {
                            $val = ($val xor $val);
                        }
                        break;
                    }
                    $last_op = null;
                } else if (is_string($bool)) {
                    if ($bool == '!') {
                        if ($not) {
                            $not = false;
                        } else {
                            $not = true;
                        }
                    }  else {
                        if ($last_op === null) {
                            $last_op = $bool;
                        } else {
                            I2CE::raiseError("Double logical operator");
                            return null;
                        }
                    }
                }
            }
            if ($last_op !== null) {
                I2CE::raiseError("Trailing logical operator");
                return null;
            }
            return $val;
        }



    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

//  LocalWords:  func
