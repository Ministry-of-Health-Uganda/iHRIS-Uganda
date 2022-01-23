<?php
/*
 * © Copyright 2014 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * View a person's record.
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2014 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying user alerts
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 */
class iHRIS_PageViewUserAlerts extends I2CE_Page { 

    /**
     * @var array The list of pending alerts.
     */
    protected $pending;

    /**
     * @var I2CE_User_Form The user form for the current user.
     */
    protected $user_form;

    /**
     * @var integer The total number of alerts for this user.
     */
    protected $total_alerts;

    /**
     * @var array The pager settings for this page.
     */
    protected $jumper_opts;

    /**
     * @var I2CE_FormFactory The form factory instance
     */

    /**
     * Initialize the data for the page
     * @return boolean
     */
    protected function initPage() {
        $this->form_factory = I2CE_FormFactory::instance();
        $this->user_form = $this->form_factory->createContainer( "user|" . $this->getUser()->username );

        $pend_where = array( 
                'operator' => 'FIELD_LIMIT',
                'field' => 'time_ack',
                'style' => 'null'
                );

        //$this->pending = I2CE_FormStorage::listFields( "user_alert", array( "time_sent", "message", "alert_type" ),
                //$this->user_form->getNameId(), $pend_where, array( "time_sent" ) );
        $this->pending = $this->user_form->getChildIds( "user_alert", array( "time_sent" ), $pend_where );

        $this->jumper_opts['page_size'] = $this->args['page_size'];
        if( $this->get_exists( 'page' ) ) {
            $this->jumper_opts['page'] = $this->get('page');
        } else {
            $this->jumper_opts['page'] = 1;
        }
        $this->user_form->populateChild( "user_alert", array( "-time_sent" ), array(), 'default', array( (($this->jumper_opts['page']-1)*$this->jumper_opts['page_size']), $this->jumper_opts['page_size'] ) );
        $this->total_alerts = I2CE_FormStorage::getLastListCount( "user_alert" );

        return true;
    }

    /**
     * Handle the actions for the page.
     * @return boolean
     */
    protected function action() {
        $this->template->addHeaderLink( "user_alerts.css" );

        if ( count($this->pending) > 0 ) {
            foreach( $this->pending as $id ) {
                $alert = $this->form_factory->createContainer( "user_alert|" . $id );
                $alert->populate();
                $row = $this->template->appendFileById( "user_alerts_view_pending.html", "div", "pending_alerts" );
                $row->setAttribute( "class", $alert->getField('alert_type')->getDBValue() );
                $this->template->setForm( $alert, $row );
            }
        }


        $total_pages = max( 1, ceil( $this->total_alerts / $this->jumper_opts['page_size'] ) );
        $this->makeJumper( "all_alerts", $this->jumper_opts['page'], $total_pages, $this->page, array() );
        $this->setDisplayDataImmediate( "total_alerts", $this->total_alerts );

        $count = 1;
        foreach ( $this->user_form->children['user_alert'] as $form ) {
            $row = $this->template->appendFileById( "user_alerts_view_all_row.html", "tr", "all_alerts_list" );
            $row->setAttribute( "class", $form->getField('alert_type')->getDBValue() );
            $this->template->setForm( $form, $row );
            $count++;
        }
    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
