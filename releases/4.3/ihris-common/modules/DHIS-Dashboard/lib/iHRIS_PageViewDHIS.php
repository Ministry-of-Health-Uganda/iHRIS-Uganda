<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 12/29/12
 * Time: 5:08 PM
 * To change this template use File | Settings | File Templates.
 */
class iHRIS_PageViewDHIS extends I2CE_PageForm
{
    /**
     * Perform the main actions of the page.
     */
    protected function setDisplayData() {
        parent::setDisplayData();
        $config = I2CE::getConfig();

        $ReportViewMagicData = $config->traverse("/modules/CustomReports/reportViews/");
        if (!$ReportViewMagicData instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Bad Magic node");
            return;
        }


        $AllReportViews = $ReportViewMagicData->getKeys();
        $selectOptions = array();
        foreach ($AllReportViews as $ReportView){
            $selectOptions[$ReportView] = $ReportView;
        }
        $this->template->setDisplayData( "ReportViews", $selectOptions );

    }

}
