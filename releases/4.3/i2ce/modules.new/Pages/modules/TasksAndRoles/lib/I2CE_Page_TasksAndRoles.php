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
*  I2CE_Page_TasksAndRoles
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Page_TasksAndRoles extends I2CE_Page{
    

    public function action() {
        if (!$this->hasPermission(' task(tasks_and_roles_can_access)')) {
            $this->setRedirect('noaccess');
            return false;
        }
        switch($this->page) {
        case 'tasks':
            if (!$this->hasPermission(' task(tasks_and_roles_can_edit_tasks)')) {
                $this->setRedirect('noaccess');
                return false;
            }
            if ($this->isPost()) {
                $this->actionTasksSave();
            }            
            return $this->actionTasksView();
        case 'roles':
            if (!$this->hasPermission(' task(tasks_and_roles_can_edit_roles)')) {
                $this->setRedirect('noaccess');
                return false;
            }
            if ($this->isPost()) {
                $this->actionRolesSave();
            }
            //intentionally no break here.
            return $this->actionRolesView();
        default:
        case 'home':
            return $this->actionMenu();
        }
        return true;
    }

    protected function actionMenu() {
        return ($this->template->addFile("roles_and_tasks_menu.html",'div') instanceof DOMNode);
    }

    protected static $fixed_roles = 
        array('admin'=>array('name'=>'Administator','description'=>'The administrator for this system'),
              'any'=>array('name'=>'Any','description'=>'Any user of the system, whether they are logged in or not.  May not be assigned to a specific user'),
              'NOT_LOGGED_IN'=>array('name'=>'Not Logged In','description'=>'A user who is not logged it.  May not be assigned to a specific user'),
              'all'=>array('name'=>'All','description'=>'Any user that is logged in to the system.  May not be assigned to a specific user'));
    

    protected function actionRolesSave() {
        if (count($this->request_remainder) == 0) {
            //adding new role
            if (!$this->post_exists('role_short_name')) {
                $this->userMessage("No role short name set", 'notice',false);
                return;
            }
            $role = $this->post('role_short_name');
            if (array_key_exists($role,self::$fixed_roles)) {
                $this->userMessage("Invalid Role $role short name  specified",'notice',false);
                return;
            }
            if (!$role || !I2CE_MagicDataNode::checkKey($role) || I2CE::getConfig()->is_parent("/I2CE/formsData/forms/role/$role")) {
                $this->userMessage("Bad role short name $role", 'notice',false);
                return;
            }
            if (!$this->post_exists('role_name') || !$this->post('role_name')) {
                $this->userMessage("No role display name set", 'notice',false);
                return;
            }
            //we are good to go.
            I2CE::getConfig()->I2CE->formsData->forms->role->$role->fields->name = $this->post('role_name');
            I2CE::getConfig()->I2CE->formsData->forms->role->$role->last_modified = I2CE_Date::now( I2CE_Date::DATE_TIME )->dbFormat();
        } else if (count($this->request_remainder) == 1) {
            $role = $this->request_remainder[0];
            if (!$role || array_key_exists($role,self::$fixed_roles)) {
                $this->userMessage("Invalid Role $role specified",'notice',false);
                return;
            }
            $roleBaseConfig = I2CE::getConfig()->traverse("/I2CE/formsData/forms/role/",false);
            if ( !$roleBaseConfig instanceof I2CE_MagicDataNode) {
                $this->userMessage("System Error",'notice',false);
                return;
            }
            $roleConfig = $roleBaseConfig->traverse("$role/fields",false);
            if ( !$roleConfig instanceof I2CE_MagicDataNode) {
                $this->userMessage("Invalid Role $role specified",'notice',false);
                return;
            }
            $post = $this->post();
            if (!array_key_exists('role_name', $post) ||  !$post['role_name']) {
                $this->userMessage("No role display name set", 'notice',false);
                return;
            }
            if ($roleConfig->is_translatable("name")) {
                $locale = I2CE_Locales::getPreferredLocale();
                $roleConfig->setTranslation($locale,$post['role_name'], "name");
                if ($locale == I2CE_Locales::DEFAULT_LOCALE) {
                    $roleConfig->name = $post['role_name'];
                }
            } else {
                $roleConfig->name = $post['role_name'];
            }
            $roleBaseConfig->$role->last_modified = I2CE_Date::now( I2CE_Date::DATE_TIME )->dbFormat();
            if (array_key_exists('homepage',$post) && !is_array($post['homepage'])) {
                $roleConfig->homepage = $post['homepage'];
            }

            if (array_key_exists('role_roles',$post) && is_array($post['role_roles'])) {
                //make sure the roles are valid.
                $roles = $post['role_roles'];
                foreach ($roles as $i=>$r) {
                    if (!$roleBaseConfig->is_parent("$r") 
                        || array_key_exists($r,self::$fixed_roles)) {
                        unset($roles[$i]);
                    }
                }
                $roles[] = 'admin'; //make sure that admin inherits this role.
                $trickleConfig = $roleConfig->traverse("trickle_up",true,false);
                $trickleConfig->erase();
                $roleConfig->traverse('trickle_up',true,false); //recreate what we just erased
                $roleConfig->trickle_up = implode(',',$roles);
            }
            if (array_key_exists('role_tasks',$post) && is_array($post['role_tasks'])) {
                $taskConfig =I2Ce::getConfig()->I2CE->tasks->role_trickle_down;
                if (isset($taskConfig,$role)) {
                    $taskConfig->$role->erase();
                    $taskConfig->traverse($role,true,false);
                }
                $taskConfig->$role = $post['role_tasks'];
            }
        }
        $this->setRedirect('tasks-roles/roles');
    }


    
    protected function actionRolesView() {
        $role = false;
        if (count($this->request_remainder) == 1) {
            $role = $this->request_remainder[0];
            if (!I2CE::getConfig()->is_parent("/I2CE/formsData/forms/role/$role")) {
                $role = false;
            } else if (array_key_exists($role,self::$fixed_roles)) {
                $role = false;
            }
        } 
        if ($role) {
            return $this->actionRoleView($role);
        } else {
            return $this->actionAllRolesView();
        }
    }
    
    protected function actionRoleView($role) {
        $contentNode = $this->template->addFile("roles_and_tasks_edit_role.html", 'div');
        if (!$contentNode instanceof DOMNode) {
            return false;
        }
        $meta = array();
        I2CE::getConfig()->setIfIsSet($meta,"/I2CE/formsData/forms/role/$role/fields",true);        
        $this->template->setDisplayDataImmediate('edit_role_form_action', "tasks-roles/roles/$role", $contentNode);
        $this->addFormWorm('edit_role');
        foreach (array('name'=>'') as $key=>$value) {
            if (array_key_exists($key,$meta)) {
                $value = $meta[$key];
            }
            $this->template->setDisplayDataImmediate('role_' . $key,$value);
        }
        $existing_role_tasks = array();
        I2CE::getConfig()->setIfIsSet($existing_role_tasks,"/I2CE/tasks/role_trickle_down/$role",true);
        $t_role_tasks = array();
        I2CE::getConfig()->setIfIsSet($t_role_tasks, "/I2CE/tasks/task_description",true);
        $role_tasks =array();
        foreach ($t_role_tasks as $task=>$desc) {
            $role_tasks[$task] = array('text'=>$desc);
        }
        foreach ($existing_role_tasks as $task) {
            if (array_key_exists($task,$role_tasks)) {
                $role_tasks[$task]['selected'] = true;
            }
        }
        $this->template->addOptions('role_tasks',$role_tasks);


        $t_role_roles = I2CE::getConfig()->I2CE->formsData->forms->role->getKeys();
        $t_role_roles = array_diff($t_role_roles, array_keys(self::$fixed_roles));

        $role_roles = array();
        foreach ($t_role_roles as $r) {
            if (!$r || $r == $role) {
                continue;
            }
            $displayName = $r;
            I2CE::getConfig()->setIfIsSet($displayName, "/I2CE/formsData/forms/role/$r/fields/name");
            $role_roles[$r] = array('text'=>$displayName);
        }

        
        foreach (explode(',',$meta['trickle_up']) as $r) {
            if (array_key_exists($r,$role_roles)) {
                $role_roles[$r]['selected'] = true;
            }
        }
        $this->template->addOptions('role_roles',$role_roles);
        $homepage = '';
        I2CE::getConfig()->setIfIsSet($homepage,"/I2CE/formsData/forms/role/$role/fields/homepage");
        $this->template->setDisplayData('homepage',$homepage);
    }

    protected function actionAllRolesView() {
        $roles = array();
        if (I2CE::getConfig()->is_parent("/I2CE/formsData/forms/role")) {
            foreach (I2CE::getConfig()->I2CE->formsData->forms->role as $role_id=>$role_data) {
                if (!$role_data instanceof I2CE_MagicDataNode || !$role_data->is_parent('fields')) {
                    continue;
                }
                $roles[$role_id] =$role_data->fields->getAsArray();
            }
        }
        $contentNode = $this->template->addFile("roles_and_tasks_view_all_roles.html", 'div');
        if (!$contentNode instanceof DOMNode) {
            I2CE::raiseError("Cannot get view roles template");
            return false;
        }
        $listNode = $this->template->getElementById('role_list',$contentNode);
        if (!$listNode instanceof DOMNode) {
            I2CE::raiseError("Dont knwo where to list roles");
            return false;
        }
        foreach ($roles as $role=>$meta) {
            $fixed = false;
            if (array_key_exists($role,self::$fixed_roles)) {
                $fixed = true;
                $roleNode = $this->template->appendFileByNode("roles_and_tasks_view_all_roles_no_edit.html", 'li', $listNode);
                $meta = self::$fixed_roles[$role];
            } else {
                $roleNode = $this->template->appendFileByNode("roles_and_tasks_view_all_roles_each.html", 'li', $listNode);
            }
            if (!$roleNode instanceof DOMNode) {
                I2CE::raiseError("Could not append role template for $role");
                return false;
            }
            $this->template->setDisplayDataImmediate('role_id' , $role, $roleNode);
            foreach (array('description'=>'','name'=>'') as $key=>$value) {
                if (array_key_exists($key,$meta)) {
                    $value = $meta[$key];
                }
                $this->template->setDisplayDataImmediate('role_' . $key, $value, $roleNode);
            }
            if (!$fixed) {
                $this->template->setDisplayDataImmediate('role_link' , 'tasks-roles/roles/' . $role, $roleNode);
            } else {
                $this->template->setDisplayDataImmediate('role_link' , '', $roleNode);
            }
        }       
        $addNode = $this->template->getElementById('role_short_name',$contentNode);
        if (!$addNode instanceof DOMElement) {
            return true;
        }
        $this->addFormWorm('add_user_role');
        $roles = array_keys(array_merge($roles,self::$fixed_roles));
        $role_names = json_encode(array('notinlist'=>$roles));
        $this->template->setClassValue($addNode,'validate_data',array('notinlist'=>$role_names), '%');
        return true;        
    }



    /***********************************
     * 
     *   Stuf for tasks
     *
     ***********************************/

    protected function actionTasksView() {
        $task = false;
        if (count($this->request_remainder) == 1) {
            $task = $this->request_remainder[0];
            if (!I2CE::getConfig()->__isset("/I2CE/tasks/task_description/$task")) {
                $task = false;
            }
        } 
        if ($task) {
            return $this->actionTaskView($task);
        } else {
            return $this->actionAllTasksView();
        }
    }



    
    protected function actionTaskView($task) {
        $contentNode = $this->template->addFile("roles_and_tasks_edit_task.html", 'div');
        if (!$contentNode instanceof DOMNode) {
            return false;
        }
        $this->template->setDisplayDataImmediate('task_description', I2CE::getConfig()->I2CE->tasks->task_description->$task, $contentNode);
        $this->template->setDisplayDataImmediate('edit_task_form_action', "tasks-roles/tasks/$task", $contentNode);
        $this->addFormWorm('edit_task');
 
  
        $t_task_tasks = I2CE::getConfig()->I2CE->tasks->task_description;
        $task_tasks = array();
        foreach ($t_task_tasks as $t=>$desc) {
            if (!is_scalar($desc) || !$desc) {
                continue;
            }
            if ($t == $task) {
                continue;
            }
            $task_tasks[$t] = array('text'=>$desc);
        }
        $existing_task_tasks = I2CE::getConfig()->getAsArray("/I2CE/tasks/task_trickle_down/$task");
        if (is_array($existing_task_tasks)) {
            foreach ($existing_task_tasks as $t) {
                if (array_key_exists($t,$task_tasks)) {
                    $task_tasks[$t]['selected'] = true;
                }
            }
        }
        $this->template->addOptions('task_tasks',$task_tasks);
    }





    protected function actionTasksSave() {
        if (count($this->request_remainder) == 0) {
            //adding new task
            if (!$this->post_exists('task_short_name')) {
                $this->userMessage("No task short name set", 'notice',false);
                return;
            }
            $task = $this->post('task_short_name');
            if (!$task || !I2CE_MagicDataNode::checkKey($task) 
                || I2CE::getConfig()->__isset("/I2CE/tasks/task_description/$task")
                || I2CE::getConfig()->__isset("/I2CE/tasks/task_trickle_down/$task")
                ) {
                $this->userMessage("Bad task short name", 'notice',false);
                return;
            }
            if (!$this->post_exists('task_description') || !$this->post('task_description')) {
                $this->userMessage("No task description set", 'notice',false);
                return;
            }
            //we are good to go.
            I2CE::getConfig()->I2CE->tasks->task_description->$task = $this->post('task_description');
        } else if (count($this->request_remainder) == 1) {
            $task = $this->request_remainder[0];
            $taskConfig = I2CE::getConfig()->traverse("/I2CE/tasks");
            if (!$taskConfig->__isset("task_description/$task")) {
                $this->userMessage("Invalid Task $task specified",'notice',false);
                return;
            }
            $post = $this->post();
            if (!array_key_exists('task_description', $post) ||  !$post['task_description']) {
                $this->userMessage("No task description set", 'notice',false);
                return;
            }
            if ($taskConfig->is_translatable("task_description/$task")) {
                $locale = I2CE_Locales::getPreferredLocale();
                $taskConfig->task_description->setTranslation($locale,$post['task_description'], $task);
                if ($locale == I2CE_Locales::DEFAULT_LOCALE) {
                    $taskConfig->task_description->$task = $post['task_description'];
                }
            } else {
                $taskConfig->task_description->$task = $post['task_description'];
            }
            if (array_key_exists('task_tasks',$post) && is_array($post['task_tasks'])) {
                //make sure the tasks are valid.
                $tasks = $post['task_tasks'];
                foreach ($tasks as $i=>$r) {
                    if (!$taskConfig->__isset("task_description/$r")) {
                        unset($tasks[$i]);
                    }
                }
                $taskConfig->task_trickle_down->$task->erase();
                $taskConfig->traverse("task_trickle_down/$task",true,false); //recreate what we just erased
                $taskConfig->task_trickle_down->$task = $tasks;
            }
        }
        $this->setRedirect('tasks-roles/tasks');
    }


    protected function actionAllTasksView() {
        $tasks = I2CE::getConfig()->traverse("/I2CE/tasks/task_description");
        $contentNode = $this->template->addFile("roles_and_tasks_view_all_tasks.html", 'div');
        if (!$contentNode instanceof DOMNode) {
            I2CE::raiseError("Cannot get view roles template");
            return false;
        }
        $listNode = $this->template->getElementById('task_list',$contentNode);
        if (!$listNode instanceof DOMNode) {
            I2CE::raiseError("Dont knwo where to list tasks");
            return false;
        }       
        foreach ($tasks as $task=>$desc) {
            if (!$desc) {
                continue;
            }
            $taskNode = $this->template->appendFileByNode("roles_and_tasks_view_all_tasks_each.html", 'li', $listNode);
            if (!$taskNode instanceof DOMNode) {
                I2CE::raiseError("Could not append task template for $task");
                return false;
            }
            $this->template->setDisplayDataImmediate('task_name' , $task, $taskNode);
            $this->template->setDisplayDataImmediate('task_description' , $desc, $taskNode);
            $this->template->setDisplayDataImmediate('task_link' , 'tasks-roles/tasks/' . $task, $taskNode);
        }       
        $addNode = $this->template->getElementById('task_short_name',$contentNode);
        if (!$addNode instanceof DOMElement) {
            return true;
        }
        $this->addFormWorm('add_user_task');
        $this->template->setClassValue($addNode,'validate_data',array('notinlist'=>$tasks->getKeys()), '%');
        return true;        
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
