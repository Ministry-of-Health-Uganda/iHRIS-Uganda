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
*  I2CE_Page_LocaleAdmin
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Page_LocaleAdmin extends I2CE_Page {

    public function action() {
        parent::action();
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuLocaleSelector", "a[@href='localeSelector/edit']" );

        if ($this->page == 'userPreferred') {
            return $this->setUserPreferred();
        }
        if (!$this->hasPermission('task(locales_can_edit_site_locales)')) {
            $this->userMessage("You don't have permission to view set the site locale",'notice');
            $this->setRedirect("./");
            return false;
        }
        if ($this->page == 'sitePreferred') {
            return $this->setSitePreferred();
        }
        $init_options = array(
            'root_path'=>'/locales/selectable' ,
            'root_url_postfix'=>'selectable',
            'root_path_create'=>true,                    
            'root_type'=>'Locales');
        try {
            $swiss_factory = new I2CE_SwissMagicFactory($this,$init_options);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create swissmagic for selectable" . $e->getMessage());
            return false;
        }
        try {
            $swiss_factory->setRootSwiss();
        } catch (Exception $e) {
            I2CE::raiseError("Could not create root swissmagic for selectable" . $e->getMessage());
            return false;
        }
        $swiss_path = $this->request_remainder;
        array_shift($swiss_path); 
        $action = $this->page;        
        if ($action == 'update' && $this->isPost()) {
            if ($this->get('noRedirect')) {
                $redirect = false;
            } else {
                $redirect = true;
            }
            if ( $swiss_factory->updateValues($this->post(),$redirect)) {                        
                $msg = "Updated Values";
            } else {
                $msg = "Unable to Update Values";
            }
            if ($redirect) {
                $this->userMessage($msg, 'notice',true);
                $swiss = $swiss_factory->getSwiss($swiss_path);
                if ($swiss instanceof I2CE_Swiss) { 
                    $redirect = $swiss->getURLRoot('edit') . $swiss->getPath() . $swiss->getURLQueryString();
                } else {
                    $redirect ='localeSelector/';
                }
                $this->setRedirect($redirect);
                return true;
            }
        }
        if ($action == 'update') {
            $action = 'edit';
        }
        return $swiss_factory->displayValues( $this->template->getElementById('siteContent'),$swiss_path, $action);
    }

    protected function setUserPreferred() {
        if  ($this->request_exists('locale')) {
            $this->user->setPreferredLocale($this->request('locale'));
        }
        if ($this->get_exists('redirect')) {
            $this->setRedirect($this->get('redirect'));
        } else {
            $this->setRedirect('./'); //go home as I don't know what to do
        }
        return true;
    }

    protected function setSitePreferred() {
        if  ($this->request_exists('locale')) {
            I2CE_Locales::setSitePreferredLocale($this->request('locale'));            
        }
        if ($this->get_exists('redirect')) {
            $this->setRedirect($this->get('redirect'));
        } else {
            $this->setRedirect('./'); //go home as I don't know what to do
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
