<?php

class iHRIS_Module extends I2CE_Module {


    /**
     * Return the list of hooks provided by this module.
     * @return array
     */
    public static function getHooks() {
        return array( 
                'set_active_menu_module_I2CE_page_lists' => 'set_active_menu_lists',
                'set_active_menu_module_I2CE_page_auto_list' => 'set_active_menu_auto_list',
                'set_active_menu_module_I2CE_page_auto_list_edit' => 'set_active_menu_auto_list',
                'post_page_prepare_display' => 'set_left_nav_menu_options',
                );
    }

    /**
     * Set the left nav menu options based on configuration.
     * @param $page I2CE_Page
     */
    public function set_left_nav_menu_options( $page ) {
        $template = $page->getTemplate();
        $outer = $template->getElementById("siteOuterWrap");
        if ( $outer instanceof DOMElement ) {
            $outer_class = $outer->getAttribute("class");
            if ( preg_match( '/\bautoHideNav\b/', $outer_class ) == 1 ) {
                $template->addHeaderLink( 'mootools-core.js' );
                $template->addHeaderLink( 'mootools-more.js' );
                $lock = $template->getElementById("inlineNavMenuLock");
                if ( $lock instanceof DOMElement ) {
                    $template->addHeaderLink( 'iHRIS_NavMenu.js' );
                } else {
                    $template->addHeaderLink( 'iHRIS_NavMenuFloat.js' );
                }
            }
        }
    }

    /**
     * Set the active menu for the lists page.
     * @param $page I2CE_PageFormLists
     */
    public function set_active_menu_lists( $page ) {
        $this->set_active_menu_href( $page, 'lists' );
    }

    /**
     * Set the active menu for the auto_list page.
     * @param $page I2CE_PageFormLists
     */
    public function set_active_menu_auto_list( $page ) {
        $this->set_active_menu_href( $page, 'auto_list' );
    }

    /**
     * Set the active menu for either lists page by the href to set active
     * @param $page I2CE_PageFormLists
     * @param $href string
     */
    public function set_active_menu_href( $page, $href ) {
        $template = $page->getTemplate();
        $template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $template->setAttribute( "class", "active", "menuLists", "a[@href='$href']" );
    }


    public function showUserNames($node,$template) {
        if ($template instanceof I2CE_Template && $node instanceof DOMNode) {
            $user = $template->getUser();        
            $namesNode = $template->appendFileByNode('user_names.html','span',$node);
            $template->setDisplayDataImmediate('user_firstname', $user->firstname,$namesNode);
            $template->setDisplayDataImmediate('user_lastname', $user->lastname,$namesNode);
            return;
        }
    }


    public function getUserNames() {
        $user = new I2CE_User();
        return $user->displayName();
    }
            


    public function welcomeNamedUser($node,$template,$named_welcome_str,$unamed_welcome_str = '') {
        if (!$template instanceof I2CE_Template && !$node instanceof DOMNode) {
            return;
        }
        $user = new I2CE_User();
        if ($named_welcome_str && $user->logged_in() && (  $name = $user->displayName())) {
            $text =  sprintf($named_welcome_str,$name);
        } else {
            $text = $unamed_welcome_str;
        }
        $t_node = $template->createTextNode($text);
        $node->appendChild($t_node);
    }
            

    
    public function showUserRole($node ,$template) {
        if ($template instanceof I2CE_Template && $node instanceof DOMNode) {
            $node->appendChild($template->createTextNode($this->getUserRole()));
        }
    }

    public function getUserRole() {
        $user = new I2CE_User();
        if ($user->role) {
            return I2CE_User_Form::getRoleNameFromShortName($user->role);
        } else {
            return null;
        }
    }
     

        
     
    public function showModuleVersion($node,$template, $module) {
        if ($template instanceof I2CE_Template && $node instanceof DOMNode) {

            $node->appendChild($template->createTextNode($this->getModuleVersion($module)));
        }
    }

    public function getModuleVersion($module) {
        $version = '';
        I2CE::getConfig()->setIfIsSet($version,"/config/data/$module/version");
        return $version;
    }


}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
