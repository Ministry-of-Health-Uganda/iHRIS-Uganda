<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 12/28/12
 * Time: 10:38 AM
 * To change this template use File | Settings | File Templates.
 */
class iHRIS_Module_DHISDashboard extends I2CE_Module
{
    /** register a fuzzy method to display dependents on the view person page   */
    public static function getMethods() {
        return array('iHRIS_PageView->action_DHISDashboard'=>'show_DHISDashboard');
    }
    function show_DHISDashboard($pageObject) {
        $pageObject->addChildForms('Dashboard');
        return true;
    }

}
