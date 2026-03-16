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
* @package iHRIS
* @subpackage Common
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1.6
* @since v4.1.6
* @filesource 
*/ 
/** 
* Class iHRIS_PageCalendar
* 
* @access public
*/


abstract class iHRIS_PageCalendar extends I2CE_Page {

    /**
     * @var integer The month to display.
     */
    protected $month;

    /**
     * @var integer The year to display.
     */
    protected $year;
    
    /**
     * @var boolean Set if this page is in list view
     */
    protected $list_view;

    /**
     * @var array The current date information from getdate()
     */
    protected $now;

    /**
     * @var array The date information from getdate() for the first day of the displayed month
     */
    protected $cal_display;


    /**
     * Return the href to use for any links on this page.
     * @return string
     */
    abstract protected function getHref();


    /**
     * Set the month to start with if not in the GET array
     * @param integer $month
     */
    protected function setMonth( $month ) {
        if ( !is_numeric( $month ) ) {
            $month = $this->now['mon'];
        }
        if ( $month < 1 ) {
            $month = 1;
        } elseif ( $month > 12 ) {
            $month = 12;
        }
        $this->month = $month;
    }

    /**
     * Set the year to start with if not in the GET array
     * @param integer $year
     */
    protected function setYear( $year ) {
        if ( !is_numeric( $year ) ) {
            $year = $this->now['year'];
        }
        if ( $year < 0 ) {
            $year = 2000;
        }
        $this->year = $year;
    }

    /**
     * Initialize the page.
     */
    protected function initPage() {
        $this->now = getdate();

        if ( $this->get_exists( 'month' ) ) {
            $this->setMonth( $this->get( 'month' ) );
        } else {
            $this->setMonth( $this->now['mon'] );
        }
        if ( $this->get_exists( 'year' ) ) {
            $this->setYear( $this->get( 'year' ) );
        } else {
            $this->setYear( $this->now['year'] );
        }
        if ( $this->get_exists( 'list_view' ) && $this->get('list_view') == 1 ) {
            $this->list_view = true;
        } else {
            $this->list_view = false;
        }

        $this->cal_display = getdate( mktime( 0, 0, 0, $this->month, 1, $this->year ) );
        return true;
    }


    /**
     * Display the calendar on the page.
     * @return boolean
     */
    protected function action() {
        // Names from I2CE::getConfig()->modules->DatePicker->options->days->0-6
        // Names from I2CE::getConfig()->modules->DatePicker->options->months->0-11
        
        $date_opts = I2CE::getConfig()->modules->DatePicker->options;
        $this->template->addHeaderLink( "calendar.css" );
        $this->template->addFile( "calendar_base.html" );
        if ( !$this->list_view ) {
            $this->template->addFile( "calendar_header_days.html", "tr" );
        }


        // Set up the date selector
        $min_year = 2000;
        if ( array_key_exists( 'min_year', $this->args ) ) {
            $min_year = $this->args['min_year'];
        } elseif ( array_key_exists( 'min_year_increment', $this->args ) ) {
            $min_year = $this->now['year'] - $this->args['min_year_increment'];
        }
        $max_year = $this->now['year'] + 5;
        if ( array_key_exists( 'max_year', $this->args ) ) {
            $max_year = $this->args['max_year'];
        } elseif ( array_key_exists( 'max_year_increment', $this->args ) ) {
            $max_year = $this->now['year'] + $this->args['max_year_increment'];
        }
        for( $year = $min_year; $year <= $max_year; $year++ ) {
            $year_select = $this->template->getElementById( 'year_select' );
            $attr = array( 'value' => $year );
            if ( $year == $this->year ) {
                $attr['selected'] = 'selected';
            }
            $opt = $this->template->createElement( 'option', $attr, $year );
            $year_select->appendChild( $opt );
        }
        for( $month = 1; $month <= 12; $month++ ) {
            $month_select = $this->template->getElementById( 'month_select' );
            $attr = array( 'value' => $month );
            if ( $month == $this->month ) {
                $attr['selected'] = 'selected';
            }
            $mon = getdate( mktime( 0, 0, 0, $month, 1, $this->year ) );
            $mon_name = $mon['month'];
            $date_opts->setIfIsSet( $mon_name, "months/" . ($month-1) );
            $opt = $this->template->createElement( 'option', $attr, $mon_name );
            $month_select->appendChild( $opt );
        }
        $this->template->setDisplayDataImmediate( 'list_view', ($this->list_view ? 1 : 0) );
        $this->template->setDisplayDataImmediate( 'calendar_current', $this->getHref() .'?month=' . $this->now['mon']. '&year=' . $this->now['year'] . ( $this->list_view ? "&list_view=1" : "" ) );


        // Set up the month name and weekdays
        $month_name = $this->cal_display['month'];
        $date_opts->setIfIsSet( $month_name, "months/" . ( $this->cal_display['mon'] - 1 ) );
        $this->template->setDisplayDataImmediate( 'month_name', $month_name );
        $this->template->setDisplayDataImmediate( 'year_name', $this->year );

        $this->template->setDisplayDataImmediate( 'calendar_list_view',
                $this->getHref() . '?month=' . $this->month .'&year=' . $this->year . '&list_view=1' );
        $this->template->setDisplayDataImmediate( 'calendar_month_view',
                $this->getHref() . '?month=' . $this->month .'&year=' . $this->year );

        $prev_mon = $this->month - 1;
        $prev_year = $this->year;
        if ( $prev_mon < 1 ) {
            $prev_mon = 12;
            $prev_year--;
        }
        $this->template->setDisplayDataImmediate( 'calendar_previous_month', $this->getHref() . '?month=' . $prev_mon. '&year=' . $prev_year . ($this->list_view ? "&list_view=1" : "" ) );
        $next_mon = $this->month + 1;
        $next_year = $this->year;
        if ( $next_mon > 12 ) {
            $next_mon = 1;
            $next_year++;
        }
        $this->template->setDisplayDataImmediate( 'calendar_next_month', $this->getHref() . '?month=' . $next_mon. '&year=' . $next_year . ( $this->list_view ? "&list_view=1" : "" ) );
        $this->template->setDisplayDataImmediate( 'calendar_previous_year', $this->getHref() . '?month=' . $this->month . '&year=' . ($this->year-1) . ($this->list_view ? "&list_view=1" : "" ) );
        $this->template->setDisplayDataImmediate( 'calendar_next_year', $this->getHref() . '?month=' . $this->month . '&year=' . ($this->year+1) . ($this->list_view ? "&list_view=1" : "" )  );
        if ( $this->list_view ) {
            $this->template->addFile( "calendar_list.html", 'tbody' );
        } else {
            for( $i = 1; $i <= 7; $i++ ) {
                $day = getdate( mktime( 0, 0, 0, $this->month, $i, $this->year ) );
                $day_name = $day['weekday'];
                $date_opts->setIfIsSet( $day_name, "days/" . $day['wday'] );
                $this->template->setDisplayDataImmediate( 'day_name_' . $day['wday'], $day_name );
            }

            $days = $this->template->getElementById( 'calendar_days' );

            $beginning = true;

            $cur_date = $this->cal_display;
            while ( $cur_date['mon'] == $this->month ) {
                if ( $beginning || $cur_date['wday'] == 0 ) {
                    $row = $this->template->createElement( 'tr' );
                    $days->appendChild( $row );
                }
                if ( $beginning ) {
                    $beginning = false;
                    if ( $cur_date['wday'] > 0 ) {
                        $blank = $this->template->appendFileByNode( 'calendar_blank.html', 'td', $row );
                        $blank->setAttribute( "colspan", $cur_date['wday'] );
                    }
                }
                $mday = $cur_date['mday'];
                $day = $this->template->appendFileByNode( 'calendar_day.html', 'td', $row );
                $this->setDisplayDataImmediate( 'calendar_date', $cur_date['mday'], $day );
                $this->template->setAttribute( 'id', sprintf( "%04d_%02d_%02d", $this->year, $this->month, $mday), 
                        null, 'div[@name="calendar_text"]', $day );
                $cur_date = getdate( mktime( 0, 0, 0, $this->month, $mday+1, $this->year ) );
            }
            if ( $cur_date['wday'] > 0 ) {
                $blank = $this->template->appendFileByNode( 'calendar_blank.html', 'td', $row );
                $blank->setAttribute( "colspan", 7 - $cur_date['wday'] );
            }
        }

        return true;

    }

    /**
     * Add the form details to the calendar for each day between
     * the start and end date (unless on the list view).
     * @param I2CE_Form $form
     * @param DateTime $start_date
     * @param DateTime $end_date
     * @param string $template_file
     * @param array $color Optional color (RGB) style to set
     * @param string $color_style Which color to set.  Defaults to: background-color
     * @param string $template_root The root element in the template.  Defaults to:  p
     */
    protected function addForm( $form, $start_date, $end_date, $template_file,
            $color = null, $color_style = 'background-color', $template_root = 'p' ) {
        if ( $this->list_view ) {
            $node = $this->template->appendFileById( $template_file, $template_root, 'calendar_list' );
            $this->setForm( $form, $node );
            $this->template->setAttribute( "style", "display: inline;", 'calendar_list',
                    ".//span[@name='date_range']" );
        } else {
            $curr_disp = $start_date;
            while ( $curr_disp->format('n') < $this->month ) {
                $curr_disp->add( new DateInterval( 'P1D' ) );
            }

            while ( $curr_disp <= $end_date 
                    && $curr_disp->format('n') == $this->month ) {
                $date_id = $curr_disp->format('Y_m_d');
                $node = $this->template->appendFileById( $template_file, $template_root, $date_id );
                $this->setForm( $form, $node );
                if ( $color && is_array( $color ) && count($color) == 3 ) {
                    $this->template->setAttribute( "style", 
                            $color_style . ": rgb(" . implode(',', $color). ");", 
                            null, null, $node );
                }
                $curr_disp->add( new DateInterval( 'P1D' ) );
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
