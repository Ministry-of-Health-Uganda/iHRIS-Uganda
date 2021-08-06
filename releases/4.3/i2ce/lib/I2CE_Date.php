<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
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
 * 
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v1.0.0
 * @version v2.0.0
 */
/**
 * Handles date manipulation.
 * 
 * I2CE_Dates are used to manage date and time values for form/HTML
 * display and for interacting with the database.
 *
 * @package I2CE
 * @access public
 */
class I2CE_Date {
    
    /**
     * @var integer The second value for the time.
     */
    private $second;
    /**
     * @var integer The minute value for the time.
     */
    private $minute;
    /**
     * @var integer The hour value for the time.
     */
    private $hour;
    /**
     * @var integer The day value for the date.
     */
    private $day;
    /**
     * @var integer The month value for the date.
     */
    private $month;
    /**
     * @var integer The year value for the date.
     */
    private $year;
    /**
     * The type of I2CE_Date object based on the constant values.
     * 
     * Possible date types are {@link YEAR_ONLY Year}, {@link MONTH_DAY Month and Day}, {@link DATE Date},
     * {@link DATE_TIME Date and Time}, and {@link TIME_ONLY Time}.
     * @var integer
     */
    private $type;
    
    /**
     * Constant value to signify a date only including the year.
     */
    const YEAR_ONLY = 1;
    /**
     * Constant value to signify a date only including the month and day of the month.
     */
    const MONTH_DAY = 2;
    /**
     * Constant value to signify a date only including the date.
     */
    const DATE = 3;
    /**
     * Constant value to signify a date only including the date and time.
     */
    const DATE_TIME = 4;
    /**
     * Constant value to signify a date only including the time.
     */
    const TIME_ONLY = 5;
    /**
     * Constant value to signify a date only including the month and year.
     */
    const YEAR_MONTH = 6;
    
    /**
     * Array of month names.
     * 
     * This should be modified to handle internationalization.
     * @var array The array of month names.
     */
    public static $months = array( 0 => 'None', 1 => 'January', 2 => 'February',
                                   3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 
                                   7 => 'July', 8 => 'August', 9 => 'September', 
                                   10 => 'October', 11 => 'November', 12 => 'December' );
    /** 
     * Array of formats to use to display dates.
     * @var array The array of date formats by type.
     */
    public static $date_formats = array( 
            self::YEAR_ONLY => 'Y',
            self::YEAR_MONTH => 'F Y',
            self::MONTH_DAY => 'j F',
            self::DATE_TIME => 'j F Y G:i:s',
            self::TIME_ONLY => 'G:i:s',
            self::DATE => 'j F Y',
            );
    /** 
     * Array of formats to use to display dates.
     * @var array The array of date formats by type.
     */
    public static $intl_formats = array( 
            self::YEAR_ONLY => 'y',
            self::YEAR_MONTH => 'MMMM y',
            self::MONTH_DAY => 'd MMMM',
            self::DATE_TIME => 'd MMMM y H:mm:ss',
            self::TIME_ONLY => 'H:mm:ss',
            self::DATE => 'd MMMM y',
            );
    /**
     * @var array Array of IntlDateFormatter objects per locale and display types.
     */
    protected static $formatters;

    /**
     * Return the IntlDateFormatter for the given locale and date type.
     * @param string $locale
     * @param integer $date_type
     * @return IntlDateFormatter
     */
    public function getIntlFormatter( $locale, $date_type ) {
        if ( version_compare(PHP_VERSION, '5.4.0', '<') || !class_exists( "IntlDateFormatter" ) ) {
            return null;
        }
        if ( !is_array( self::$formatters ) ) {
            self::$formatters = array();
        }
        if ( !array_key_exists( $locale, self::$formatters ) || !is_array( self::$formatters[$locale] ) ) {
            self::$formatters[$locale] = array();
        }
        if ( !array_key_exists( $date_type, self::$formatters[$locale] ) || !self::$formatters[$locale][$date_type] instanceof IntlDateFormatter ) {
            $format = self::$intl_formats[ $date_type ];
            I2CE::getConfig()->setIfIsSet( $format, "/I2CE/date/intl_format/" . $date_type );
            self::$formatters[$locale][$date_type] = new IntlDateFormatter( $locale,
                    null, null, null, null, $format );
        }
        if ( self::$formatters[$locale][$date_type] instanceof IntlDateFormatter ) {
            return self::$formatters[$locale][$date_type];
        } else {
            return null;
        }

    }
    /**
     * @var array A list of all valid types for I2CE_Date objects.
     */
    private static $types = array( self::YEAR_ONLY => "Year", self::MONTH_DAY => "Month and Day",
                                   self::DATE => "Date", self::DATE_TIME => "Date and Time", self::TIME_ONLY => "Time", self::YEAR_MONTH => "Year and Month" );

    /**
     * Constructor method to create a new I2CE_Date object.
     * @param integer $type The type of date object created.  It defaults to date and time.
     */
    private function __construct( $type = self::DATE_TIME ) {
        if ( !array_key_exists( $type, self::$types ) ) {
            I2CE::raiseError( "An invalid date type was used for I2CE_Date.", E_USER_WARNING );
            $type = self::DATE_TIME;
        }
        $this->type = $type;
        $this->blank_text = "";
    }

    /**
     * Set the type for this Date.
     * @param integer $type The type of date object created.  It defaults to date and time.
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * get the type for this Date.
     * @returns integer $type The type of date object created.  
     */
    public function getType() {
        return $this->type ;
    }


    /**
     * Create a new I2CE_Date object with the current or given date and/or time.
     * @param integer $type The type of date object created.  It defaults to date and time.
     * @param integer $time_stamp.  Defaults to null in which case we use the current time stamp.  If it is an array
     * then it should be the same array structture as returned by getdate() or the one returned by I2CE_Date::getValues()
     * @param mixed $strict .  Defaults to false.  If true, does not return now.  If 'blank', returns the blank date.
     * @return mixed I2CE_Date or false if failure on strict mode
     */
    public static function now( $type = self::DATE_TIME, $time_stamp = null, $strict = false ) {
        if (is_array($time_stamp)) {
            if ( array_key_exists( 'value', $time_stamp ) ) {
                $time_stamp = I2CE_Date::fromDB( $time_stamp['value'] , $type)->getValues();
            }
            $now = $time_stamp;
        } else if ($time_stamp === null )    {
            if ($strict === 'blank') {
                return self::blank($type);
            } else if ($strict) {
                return false;
            }
            $now = getdate();
        } else {            
            if (is_numeric($time_stamp)) {
                $now = getdate($time_stamp);
            } else if ($strict === 'blank') {
                return self::blank($type);
            } else if ($strict) {
                return false;
            }
        }
        switch( $type ) {
        case self::YEAR_ONLY :
            if (!array_key_exists('year',$now) || !$now['year']) {
                if ($strict === 'blank') {
                    return self::blank($type);
                } else if ($strict) {
                    return false;
                }
            }
            return self::getYear( $now['year'] );
        case self::YEAR_MONTH :
            if (array_key_exists('month',$now) && !array_key_exists('mon',$now)) {
                $now['mon'] = $now['month'];
            }
            if (!array_key_exists('year',$now)  || !$now['year'] 
                || !array_key_exists('mon',$now) || !$now['mon']) {
                if ($strict === 'blank') {
                    return self::blank($type);
                } else if ($strict) {
                    return false;
                } else {
                    $now = getdate();
                }
            }
            return self::getYearMonth( $now['mon'], $now['year'] );
        case self::MONTH_DAY :
            if (array_key_exists('day',$now) && !array_key_exists('mday',$now)) {
                $now['mday'] = $now['day'];
            }
            if (array_key_exists('month',$now) && !array_key_exists('mon',$now)) {
                $now['mon'] = $now['month'];
            }
            if (!array_key_exists('mday',$now)  || !$now['mday'] 
                || !array_key_exists('mon',$now) || !$now['mon']) {
                if ($strict === 'blank') {
                    return self::blank($type);
                } else if ($strict) {
                    return false;
                } else {
                    $now = getdate();
                }
            }
            return self::getDay( $now['mday'], $now['mon'] );
        case self::DATE :
            if (array_key_exists('day',$now) && !array_key_exists('mday',$now)) {
                $now['mday'] = $now['day'];
            }
            if (array_key_exists('month',$now) && !array_key_exists('mon',$now)) {
                $now['mon'] = $now['month'];
            }
            if (!array_key_exists('mday',$now)  || !$now['mday'] 
                || !array_key_exists('mon',$now)  || !$now['mon'] 
                || !array_key_exists('year',$now) || !$now['year']) {
                if ($strict === 'blank') {
                    return self::blank($type);
                } else if  ($strict) {
                    return false;
                } else {
                    $now = getdate();
                }
            }
            return self::getDate( $now['mday'], $now['mon'], $now['year'] );
        case self::DATE_TIME :
            if (array_key_exists('hour',$now) && !array_key_exists('hours',$now)) {
                $now['hours'] = $now['hour'];
            }
            if (array_key_exists('minute',$now) && !array_key_exists('minutes',$now)) {
                $now['minutes'] = $now['minute'];
            }
            if (array_key_exists('second',$now) && !array_key_exists('seconds',$now)) {
                $now['seconds'] = $now['second'];
            }
            if (array_key_exists('day',$now) && !array_key_exists('mday',$now)) {
                $now['mday'] = $now['day'];
            }
            if (array_key_exists('month',$now) && !array_key_exists('mon',$now)) {
                $now['mon'] = $now['month'];
            }
            if (!array_key_exists('mday',$now) || !$now['mday'] 
                || !array_key_exists('mon',$now)  || !$now['mday'] 
                || !array_key_exists('year',$now)  || !$now['year'] 
                || !array_key_exists('seconds',$now) 
                || !array_key_exists('minutes',$now) 
                || !array_key_exists('hours',$now) ) {
                if ($strict === 'blank') {
                    return self::blank($type);
                } else if ($strict) {
                    return false;
                } else {
                    $now = getdate();
                }
            }
            return self::getDateTime( $now['seconds'], $now['minutes'], $now['hours'], $now['mday'], $now['mon'], $now['year'] );
        case self::TIME_ONLY :
            if (array_key_exists('hour',$now) && !array_key_exists('hours',$now)) {
                $now['hours'] = $now['hour'];
            }
            if (array_key_exists('minute',$now) && !array_key_exists('minutes',$now)) {
                $now['minutes'] = $now['minute'];
            }
            if (array_key_exists('second',$now) && !array_key_exists('seconds',$now)) {
                $now['seconds'] = $now['second'];
            }
            if (!array_key_exists('seconds',$now) 
                || !array_key_exists('minutes',$now) 
                || !array_key_exists('hours',$now) ) {
                if ($strict === 'blank') {
                    return self::blank($type);
                } else if ($strict) {
                    return false;
                } else {
                    $now = getdate();
                }
            }
            return self::getTime( $now['seconds'], $now['minutes'], $now['hours'] );
        default :
            if (array_key_exists('day',$now) && !array_key_exists('mday',$now)) {
                $now['mday'] = $now['day'];
            }
            if (array_key_exists('month',$now) && !array_key_exists('mon',$now)) {
                $now['mon'] = $now['month'];
            }
            if (!array_key_exists('mday',$now) || !$now['mday'] 
                || !array_key_exists('mon',$now)  || !$now['mon'] 
                || !array_key_exists('year',$now) || !$now['year']  ) {
                if ($strict === 'blank') {
                    return self::blank($type);
                } else if ($strict) {
                    return false;
                } else {
                    $now = getdate();
                }
            }
            I2CE::raiseError( "An invalid date type was used for I2CE_Date::now.  Defaulting to DATE." );
            return self::getDate( $now['mday'], $now['mon'], $now['year'] );
        }
    }

    /**
     * Get the array of values associated to this date.  
     * @returns array The keys of which match the names for generated html elements
     */
    public function getValues() {
        switch( $this->type ) {
        case self::YEAR_ONLY :
            return array('year'=>$this->year);
        case self::YEAR_MONTH :
            return array('year'=>$this->year, 'month'=>$this->month);
        case self::MONTH_DAY :
            return array('day'=>$this->day, 'month'=>$this->month);
        case self::DATE :
            return array('day'=>$this->day, 'month'=>$this->month, 'year'=>$this->year);
        case self::DATE_TIME :
            return array('day'=>$this->day, 'month'=>$this->month, 'year'=>$this->year,'hour'=>$this->hour,'minute'=>$this->minute,'second'=>$this->second);
        case self::TIME_ONLY :
            return array('hour'=>$this->hour,'minute'=>$this->minute,'second'=>$this->second);
        default :
            I2CE::raiseError( "An invalid date type was used for I2CE_Date::now.  Defaulting to DATE." );
            return array('day'=>$this->day, 'month'=>$this->month, 'year'=>$this->year);
        }        
    }

    /**
     * Create a blank date object with no values set for anything.
     * @param integer $type The type of date object created.  It defaults to date and time.
     * @return I2CE_Date
     */
    public static function blank( $type = self::DATE_TIME ) {
        switch( $type ) {
        case self::YEAR_ONLY :
            return self::getYear( 0 );
        case self::YEAR_MONTH :
            return self::getYearMonth( 0, 0 );
        case self::MONTH_DAY :
            return self::getDay( 0, 0 );
        case self::DATE :
            return self::getDate( 0, 0, 0 );
        case self::DATE_TIME :
            return self::getDateTime( 0, 0, 0, 0, 0, 0 );
        case self::TIME_ONLY :
            return self::getTime( 0, 0, 0 );
        default :
            I2CE::raiseError( "An invalid date type was used for I2CE_Date::blank.  Defaulting to DATE.", E_USER_WARNING );
            return self::getDate( 0, 0, 0 );
        }        
    }

    /**
     * Create a date object of the type Year only.
     * @param integer $year Sets the year to be used.
     * @return I2CE_Date
     */
    public static function getYear( $year=-1 ) {
        $date = new I2CE_Date( self::YEAR_ONLY );
        $date->year = (integer)$year;
        return $date;
    }
    /**
     * Create a date object of the type Month/Day
     * @param integer $day Sets the day of the month to be used
     * @param integer $month Sets the month to be used
     * @return I2CE_Date
     */
    public static function getYearMonth( $month=-1, $year=-1 ) {
        $date = new I2CE_Date( self::YEAR_MONTH );
        $date->month = (integer)$month;
        $date->year = (integer)$year;
        return $date;
    }
     /**
     * Create a date object of the type Month/Day
     * @param integer $day Sets the day of the month to be used
     * @param integer $month Sets the month to be used
     * @return I2CE_Date
     */
    public static function getDay( $day=-1, $month=-1 ) {
        $date = new I2CE_Date( self::MONTH_DAY );
        $date->day = (integer)$day;
        $date->month = (integer)$month;
        return $date;
    }
    /**
     * Creates a date object of the type Date.
     * @param integer $day Sets the day of the month to be used
     * @param integer $month Sets the month to be used
     * @param integer $year Sets the year to be used.
     * @return I2CE_Date
     */
    public static function getDate( $day=-1, $month=-1, $year=-1 ) {
        $date = new I2CE_Date( self::DATE );
        $date->day = (integer)$day;
        $date->month = (integer)$month;
        $date->year = (integer)$year;
        return $date;
    }
    /**
     * Creates a date object of the type Date/Time.
     * @param integer $sec Sets the seconds to be used
     * @param integer $min Sets the minutes to be used
     * @param integer $hour Sets the hour to be used.
     * @param integer $day Sets the day of the month to be used
     * @param integer $month Sets the month to be used
     * @param integer $year Sets the year to be used.
     * @return I2CE_Date
     */
    public static function getDateTime( $sec=-1, $min=-1, $hour=-1, $day=-1, $month=-1, $year=-1 ) {
        $date = new I2CE_Date( self::DATE_TIME );
        $date->second = (integer)$sec;
        $date->minute = (integer)$min;
        $date->hour = (integer)$hour;
        $date->day = (integer)$day;
        $date->month = (integer)$month;
        $date->year = (integer)$year;
        return $date;
    }
    /**
     * Creates a date object of the type Time.
     * @param integer $sec Sets the seconds to be used
     * @param integer $min Sets the minutes to be used
     * @param integer $hour Sets the hour to be used.
     * @return I2CE_Date
     */
    public static function getTime( $sec=-1, $min=-1, $hour=-1 ) {
        $date = new I2CE_Date( self::TIME_ONLY );
        $date->second = (integer)$sec;
        $date->minute = (integer)$min;
        $date->hour = (integer)$hour;
        return $date;
    }
    
    /**
     * Creates a date from a MySQL database formatted string
     * 
     * The type of this date object is determined by the string.  The possible types are
     * Month/Day ('0000-MM-DD'), Year Only ('YYYY-00-00'), Date ('YYYY-MM-DD'),
     * Date/Time ('YYYY-MM-DD HH:MM:SS'), and Time Only ('HH:MM:SS').  If the string is blank
     * a {@link blank() blank date} is returned.  If none of thse formats match the 
     * {@link now() current date} is returned.
     * @param string $dateString Date formatted string from MySQL
     * @param int $type.   Defaults to null in which the type is guessed from the {$dateString}.   
     *    Otherwise it is one of I2CE_DATE::DATE, I2CE_DATE::YEAR_ONLY, etc.
     * @return I2CE_Date
     */
    public static function fromDB( $dateString, $type = null ) {
        if ( !$dateString || !isset( $dateString) || $dateString == "" || $dateString == '0000-00-00 00:00:00' ) {
            if ($type !== null) {
                return self::blank($type);
            } else {
                return self::blank();
            }
        }
        $data = array();
        if ( preg_match( '/^(\d+)-(\d+)-(\d+)( 00:00:00){0,1}$/', $dateString, $data ) ) {            
            if ($type === self::DATE_TIME) {
                $date = new I2CE_Date( self::DATE_TIME );
                $date->year = $data[1];
                $date->month = $data[2];
                $date->day = $data[3];
                $date->hour = 0;
                $date->minute = 0;
                $date->second = 0;
            } else if ( $type === self::YEAR_MONTH || $data[3] == '00' ) {
                $date = new I2CE_Date( self::YEAR_MONTH );
                $date->year = $data[1];
                $date->month = $data[2];
            } else if ( $type === self::MONTH_DAY || $data[1] == '0000' ) {
                $date = new I2CE_Date( self::MONTH_DAY );
                $date->month = $data[2];
                $date->day = $data[3];
            } elseif ( $type === self::YEAR_ONLY || $data[2] == '00' ) {
                $date = new I2CE_Date( self::YEAR_ONLY );
                $date->year = $data[1];
            } else {
                if ($type === null || $type == self::DATE) {
                    $date = new I2CE_Date( self::DATE );
                    $date->year = $data[1];
                    $date->month = $data[2];
                    $date->day = $data[3];
                } else {
                    if ($type !== null) {
                        return self::blank($type);
                    } else {
                        return self::blank();
                    }
                }
            }
        } elseif (  ($type === null || $type === self::TIME_ONLY) && preg_match( '/^0000-00-00 (\d+):(\d+):(\d+)$/', $dateString, $data ) ) {
            $date = new I2CE_Date( self::TIME_ONLY );
            $date->hour = $data[1];
            $date->minute = $data[2];
            $date->second = $data[3];
        } elseif (  ($type === null || $type === self::DATE_TIME) && preg_match( '/^(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)$/', $dateString, $data ) ) {
            $date = new I2CE_Date( self::DATE_TIME );
            $date->year = $data[1];
            $date->month = $data[2];
            $date->day = $data[3];
            $date->hour = $data[4];
            $date->minute = $data[5];
            $date->second = $data[6];
        } else {
            //make this consistent with the above
            //$date = self::now();
            if ($type !== null) { 
                return self::blank($type); 
            } else {
                return self::blank();
            }
        }
        return $date;
    }
    
    /**
     * Checks to see if the I2CE_Date object is valid
     * 
     * This method will check to see if the date object has valid entries based on the type.
     * @return boolean
     */
    public function isValid() {
        switch( $this->type ) {
        case self::YEAR_ONLY :
            return $this->year > 0;
        case self::YEAR_MONTH :
            return checkdate( $this->month, 1, $this->year );
        case self::MONTH_DAY :
            // let any valid date be good even for leap years
            return checkdate( $this->month, $this->day, 2000 );
        case self::DATE :
            return checkdate( $this->month, $this->day, $this->year );
        case self::DATE_TIME :
            return 
                (checkdate( $this->month, $this->day, $this->year )
                 && $this->hour >= 0 && $this->hour <= 23 && $this->minute >= 0 && $this->minute <= 59 && $this->second >= 0 && $this->second <= 59);
        case self::TIME_ONLY :
            return $this->hour >= 0 && $this->hour <= 23 && $this->minute >= 0 && $this->minute <= 59 && $this->second >= 0 && $this->second <= 59;
        default :
            I2CE::raiseError( "An invalid date type was used for I2CE_Date::isValid.", E_USER_WARNING );
            return false;
        }
    }

    /**
     * Checks to see if the I2CE_Date object is the blank value
     * 
     * This method will check to see if the date object has blank entries based on the type.
     * @return boolean
     */
    public function isBlank() {
        switch( $this->type ) {
        case self::YEAR_ONLY :
            return $this->year === 0;
        case self::YEAR_MONTH :
            return (($this->year === 0) && ($this->month === 0));
        case self::MONTH_DAY :
            return (($this->year === 0) && ($this->month === 0));
        case self::DATE :
            return (($this->year === 0) && ($this->month === 0) && ($this->day  === 0));
        case self::DATE_TIME :
            return (
                ($this->year === 0) && ($this->month === 0) && ($this->day  === 0)
                && ($this->hour === 0) && ($this->minute === 0) && ( $this->second == 0 )
                );
        case self::TIME_ONLY :
            return (
                ($this->hour === 0) && ($this->minute === 0) && ( $this->second == 0 )
                );
        default :
            I2CE::raiseError( "An invalid date type was used for I2CE_Date::isValid.", E_USER_WARNING );
            return false;
        }
    }

    
    /**
     * @return integer The year part of the date.
     */
    public function year() { return $this->year; }
    /**
     * @return integer The month part of the date.
     */
    public function month() { return $this->month; }
    /**
     * @return integer The day part of the date.
     */
    public function day() { return $this->day; }
    /**
     * @return integer The hour part of the time.
     */
    public function hour() { return $this->hour; }
    /**
     * @return integer The minute part of the time.
     */
    public function minute() { return $this->minute; }
    /**
     * @return integer the second part of the time.
     */
    public function second() { return $this->second; }

    /**
     * The month name text to be displayed for the given month number.
     * @param integer $mon
     * @return string
     */
    public function getMonthName( $mon ) {
        if ( $mon === 0 && I2CE_Validate::checkString( $this->blank_text ) ) {
            return $this->blank_text;
        } else {
            return self::$months[$mon];
        }
    }

    /**
     * Set the blank string to be used if this date is empty.
     * @param string $text The text to use.
     */
    public function setBlank( $text ) {
        $this->blank_text = $text;
    }
        
    /**
     * Formats the date to be saved to MySQL
     * 
     * Formats the date object to a string that MySQL will recognize based on the date type.
     * @param boolean $allow_blank.  Defaults to false. If true we allow blank values in which case we return  "0000-00-00 00:00:00"
     * @return string
     */
    public function dbFormat($allow_blank = false) {

        if ($this->isBlank()) {            
            if ($allow_blank) {
                return "0000-00-00 00:00:00";
            } else {
                return null;
            }
        } else  if (!$this->isValid() ) {
            return null;
        } 
        
        switch( $this->type ) {
        case self::YEAR_ONLY :
            return sprintf( "%04d-%02d-%02d", $this->year, 0, 0 );
        case self::YEAR_MONTH :
            return sprintf( "%04d-%02d-%02d", $this->year, $this->month, 0 );
        case self::MONTH_DAY :
            return sprintf( "%04d-%02d-%02d", 0, $this->month, $this->day );
        case self::DATE :
            return sprintf( "%04d-%02d-%02d", $this->year, $this->month, $this->day );
        case self::DATE_TIME :
            return sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second );
        case self::TIME_ONLY :
            return sprintf( "0000-00-00 %02d:%02d:%02d", $this->hour, $this->minute, $this->second );
        default :
            I2CE::raiseError( "An invalid date type was used for I2CE_Date::dbFormat.", E_USER_WARNING );
            return "0000-00-00 00:00:00";
        }
    }

    /**
     * Return a DateTime object to use for formatting this I2CE_Date object.
     * @return DateTime
     */
    public function getDateTimeObj() {
        try {
            // DateTime objects need a month/day so handle separately for
            // DATE_Y
            if ( $this->type == self::YEAR_ONLY ) {
                $obj = new DateTime( $this->year . "-01-01" );
            } elseif ( $this->type == self::YEAR_MONTH ) {
                $obj = new DateTime( $this->year . "-" . $this->month . "-01" );
            } elseif ( $this->type == self::MONTH_DAY ) {
                $now = I2CE_Date::now();
                $obj = new DateTime( $now->year() . "-" . $this->month . "-" . $this->day );
            } else {
                $obj = new DateTime( $this->dbFormat( true ) );
            }
        } catch( Exception $e ) {
            I2CE::raiseError( "Invalid DateTime object: " . $e->getMessage() );
            return false;
        }
        return $obj;
    }
    
    /**
     * Displays the date in a readable format.
     */
    public function displayDate() {
        if ( !$this->isValid() ) {
            return "";
        }
        $dateTimeObj = $this->getDateTimeObj();
        if ( $dateTimeObj === false ) {
            return "";
        }
        $formatter = self::getIntlFormatter( I2CE_Locales::getPreferredLocale(), $this->type );
        if ( $formatter !== null ) {
            return $formatter->format( $dateTimeObj->getTimestamp() );
        } else {
            $format = self::$date_formats[ $this->type ];
            I2CE::getConfig()->setIfIsSet( $format, "/I2CE/date/format/" . $this->type );
            return $dateTimeObj->format( $format );
        }
        /*
        switch( $this->type ) {
        case self::YEAR_ONLY :
            return $this->year;
            break;
        case self::MONTH_DAY :
            if ( (int)$this->month == 0 ) {
                return "";
            } else {
                return sprintf( "%d %s", $this->day, self::$months[ (int)$this->month ] );
            }
            break;
        case self::DATE_TIME :
            if ( (int)$this->month == 0 ) {
                return "";
            } else { 
                return sprintf( "%d %s %d %d:%02d:%02d", $this->day, self::$months[ (int)$this->month ], $this->year, $this->hour, $this->minute, $this->second );
            }
            break;
        case self::TIME_ONLY :

            return sprintf( "%d:%02d:%02d", $this->hour, $this->minute, $this->second );                
            break;
        case self::DATE :
            if ( (int)$this->month == 0 ) {
                return "";
            } else { 
                return sprintf( "%d %s %d", $this->day, self::$months[ (int)$this->month ], $this->year );
            }
            break;
        default :
            I2CE::raiseError( "An invalid date type was used for I2CE_Date::displayDate.", E_USER_WARNING );
            return "";
        }
        */
    }
    
    /**
     * Checks to see if the date's month matches the given month.
     * @param integer $month
     * @return boolean
     */
    public function isMonth( $month ) {
        return (int)$month == (int)$this->month;
    }
    /**
     * Checks to see if the date's day matches the given day.
     * @param integer $day
     * @return boolean
     */
    public function isDay( $day ) {
        return (int)$day == (int)$this->day;
    }
    /**
     * Checks to see if the date's year matches the given year.
     * @param integer $year
     * @return boolean
     */
    public function isYear( $year ) {
        return (int)$year == (int)$this->year;
    }
    /**
     * Checks to see if the date's hour matches the given hour.
     * @param integer $hour
     * @return boolean
     */
    public function isHour( $hour ) {
        return (int)$hour == (int)$this->hour;
    }
    /**
     * Checks to see if the date's minute matches the given minute.
     * @param integer $minute
     * @return boolean
     */
    public function isMinute( $minute ) {
        return (int)$minute == (int)$this->minute;
    }
    /**
     * Checks to see if the date's second matches the given second.
     * @param integer $second
     * @return boolean
     */
    public function isSecond( $second ) {
        return (int)$second == (int)$this->second;
    }
    
    /**
     * Determines if a date is before the given date.
     * @param I2CE_Date $date
     * @return boolean
     */
    public function before( $date ) {
        if ( $this->compare( $date ) > 0 ) return true;
        else return false;
    }

    /**
     * Determines if a date is before the given date.
     * @param I2CE_Date $date
     * @return boolean
     */
    public function after( $date ) {
        if ( $this->compare( $date ) < 0 ) return true;
        else return false;
    }
    
    /**
     * Determines if one date is identical to another.
     * @param I2CE_Date $date
     * @return boolean
     */
    public function equals( $date ) {
        if ( $this->compare( $date ) == 0 )
            return true;
        else
            return false;       
    }
    /**
     * Compares a date to this one and returns -1 if it is before, 0 if the same and 1 if after
     * this date.
     * @param I2CE_Date $date
     * @return integer
     */
    public function compare( $date ) {
        if (!$date  instanceof I2CE_Date) {
            I2CE::raiseError("Invalid date comparison");
            return null;
        }
        switch( $this->type ) {
        case self::YEAR_ONLY :
            return bccomp( $date->year(), $this->year );
            break;
        case self::YEAR_MONTH :
            $year_cmp = bccomp( $date->year(), $this->year );
            if ( $year_cmp == 0 ) return bccomp( $date->month(), $this->month );
            else return $year_cmp;
            break;
         case self::MONTH_DAY :
            $month_cmp = bccomp( $date->month(), $this->month );
            if ( $month_cmp == 0 ) return bccomp( $date->day(), $this->day );
            else return $month_cmp;
            break;
        case self::DATE :
            $year_cmp = bccomp( $date->year(), $this->year );
            if ( $year_cmp == 0 ) {
                $month_cmp = bccomp( $date->month(), $this->month );
                if ( $month_cmp == 0 ) return bccomp( $date->day(), $this->day );
                else return $month_cmp;
            } else {
                return $year_cmp;
            }
            break;
        case self::DATE_TIME :
            $year_cmp = bccomp( $date->year(), $this->year );
            if ( $year_cmp == 0 ) {
                $month_cmp = bccomp( $date->month(), $this->month );
                if ( $month_cmp == 0 ) {
                    $day_cmp = bccomp( $date->day(), $this->day );
                    if ( $day_cmp == 0 ) {
                        $hour_cmp = bccomp( $date->hour(), $this->hour );
                        if ( $hour_cmp == 0 ) {
                            $min_cmp = bccomp( $date->minute(), $this->minute );
                            if ( $min_cmp == 0 ) return bccomp( $date->second, $this->second );
                            else return $min_cmp;
                        } else {
                            return $hour_cmp;
                        }                                       
                    } else {
                        return $day_cmp;
                    }
                } else {
                    return $month_cmp;
                }
            } else {
                return $year_cmp;
            }
            break;
        case self::TIME_ONLY :
            $hour_cmp = bccomp( $date->hour(), $this->hour );
            if ( $hour_cmp == 0 ) {
                $min_cmp = bccomp( $date->minute(), $this->minute );
                if ( $min_cmp == 0 ) return bccomp( $date->second, $this->second );
                else return $min_cmp;
            } else {
                return $hour_cmp;
            }
            break;
        default :
            I2CE::raiseError( "An invalid date type was used for I2CE_Date::compare.", E_USER_WARNING );
            return 0;
            break;
        }       
    }
    
    /**
     * Adds the month values to a {@link I2CE_Template} object.
     * @param I2CE_Template $template
     * @param string $selectId The id of the element in the page to add the selections to.
     */
    public static function listMonths( $template, $selectId ) {
        foreach( self::$months as $num => $mon ) {
            $template->setData(array('text'=>$mon,'value'=>$num), $selectId,'OPTION',$selectId);
        }
    }
    /**
     * Adds a sequence of years to a {@link I2CE_Template} object.
     * 
     * It takes two optional arguments to limit the years to use.  The initial year to start with is 1990 and it will end
     * with the current year.
     * @param I2CE_Template $template
     * @param string $selectId The id of the element in the page to add the selections to.
     * @param integer $start The year to start with
     * @param integer $end The year to end with.
     * @see I2CE_Template::setData()
     */
    public static function listYears( $template, $selectId, $start = 1990, $end = 0 ) {
        if ( $end == 0 ) {
            $now = getdate();
            $end = $now['year'];
        }   
        for( $i = $start; $i <= $end; $i++ ) {
            $template->setData(array('text'=>$i,'value'=>$i), $selectId,'OPTION',$selectId);
        }
    }



    /**
     * Add a selection drop down for the year to be selected.
     * @param string $name The name of the selection element
     * @param I2CE_Date $default The default I2CE_Date object to use to preset the value.
     * @param boolean $showError A flag if this field is currently invalid to mark it as such.
     * @param DOMNode $node The node to append the element to.
     * @param array $year_range The range of years to use for the drop down.
     * @param boolean $hidden Set to true if the form element should be hidden. Defaults to false.
     * @param boolean $blank Set to true if this element should have a blank entry option. Defaults to false.
     */
    public static function addYearElement( $template, $name, $default, $showError, $node, $year_range, $hidden = false, $blank = false ) {
        if ( !$default instanceof I2CE_Date ) {
            $default = self::blank();
        }
        if ( $hidden ) {
            $year = $template->createElement( "input", array( "name" => $name . "[year]", "type" => "hidden", "value" => $default->year() ) );
        } else {
            $year = $template->createElement( "select", array( "name" => $name . "[year]", "class" => "date_year" . ( $showError ? "_error" : "" ) ) );
            if ( $blank ) {
                $blank_opt = $template->createElement( "option", array( "value" => "", "class" => "blank_opt" ), "Select" );
                $year->appendChild( $blank_opt );
            } else {
                $blank_opt = $template->createElement( "option", array( "value" => "", "class" => "blank_opt" ), "0000" );
                $year->appendChild( $blank_opt );
            }
            for( $i = $year_range[0]; $i <= $year_range[1]; $i++ ) {
                $year_opt = $template->createElement( "option", array( "value" => $i ), $i );
                if ( $default->isYear( $i ) ) {
                    $year_opt->setAttribute( "selected", "selected" );
                }
                $year->appendChild( $year_opt );
            }
        }
        $node->appendChild( $year );
    }
    /**
     * Add a selection drop down for the year and month to be selected.
     * @param string $name The name of the selection element
     * @param I2CE_Date $default The default I2CE_Date object to use to preset the value.
     * @param boolean $showError A flag if this field is currently invalid to mark it as such.
     * @param DOMNode $node The node to append the element to.
     * @param array $year_range The range of years to use for the drop down.
     * @param boolean $hidden Set to true if the form element should be hidden. Defaults to false.
     * @param boolean $blank Set to true if this element should have a blank entry option. Defaults to false.
     */
    public static function addYearMonthElement( $template, $name, $default, $showError, $node, $year_range, $hidden = false, $blank = false ) {
        if ( !$default instanceof I2CE_Date ) {
            $default = self::blank();
        }
        if ( $hidden ) {
            $year = $template->createElement( "input", array( "name" => $name . "[year]", "type" => "hidden", "value" => $default->year() ) );
            $month = $template->createElement( "input", array( "name" => $name . "[month]", "type" => "hidden", "value" => $default->month() ) );                                
        } else {
            $year = $template->createElement( "select", array( "name" => $name . "[year]", "class" => "date_year" . ( $showError ? "_error" : "" ) ) );
            if ( $blank ) {
                $blank_opt = $template->createElement( "option", array( "value" => "", "class" => "blank_opt" ), "Select" );
                $year->appendChild( $blank_opt );
            } else {
                $blank_opt = $template->createElement( "option", array( "value" => "", "class" => "blank_opt" ), "0000" );
                $year->appendChild( $blank_opt );
            }
            for( $i = $year_range[0]; $i <= $year_range[1]; $i++ ) {
                $year_opt = $template->createElement( "option", array( "value" => $i ), $i );
                if ( $default->isYear( $i ) ) {
                    $year_opt->setAttribute( "selected", "selected" );
                }
                $year->appendChild( $year_opt );
            }
            $month = $template->createElement( "select", array( "name" => $name . "[month]", "id" => $name, "class" => "date_month" . ( $showError ? "_error" : "" ) ) );
            if ( $blank ) {
                $blank_opt = $template->createElement( "option", array( "value" => "", "class" => "blank_opt" ), "Select" );
                $month->appendChild( $blank_opt );
            } 
            foreach( I2CE_Date::$months as $mon => $def_name ) {
                $mon_name = $default->getMonthName( $mon );                     
                $opt = $template->createElement( "option", array( "value" => $mon, "class" => ($mon == "" ? "blank_opt" : "" ) ), $mon_name );
                if ( $default->isMonth( $mon ) )
                    $opt->setAttribute( "selected", "selected" );
                $month->appendChild( $opt );
            }
        }
        $node->appendChild( $month );
        $node->appendChild( $year );
    }
    /**
     * Add a selection drop down for the month and date to be selected.
     * @param string $name The name of the selection element
     * @param I2CE_Date $default The default I2CE_Date object to use to preset the value.
     * @param boolean $showError A flag if this field is currently invalid to mark it as such.
     * @param DOMNode $node The node to append the element to.
     * @param boolean $hidden Set to true if the form element should be hidden. Defaults to false
     * @param boolean $blank Set to true if this element should have a blank entry option. Defaults to false.
     */
    public static function addMonthDayElement( $template, $name, $default, $showError, $node, $hidden = false, $blank = false ) {
        if ( !$default instanceof I2CE_Date ) {
            $default = self::blank();
        }
        if ( $hidden ) {
            $day = $template->createElement( "input", array( "name" => $name . "[day]", "type" => "hidden", "value" => $default->day() ) );
            $month = $template->createElement( "input", array( "name" => $name . "[month]", "type" => "hidden", "value" => $default->month() ) );                                
        } else {
            $day = $template->createElement( "select", array( "name" => $name . "[day]", "class" => "date_day" . ( $showError ? "_error" : "" ) ) );
            if ( $blank ) {
                $blank_opt = $template->createElement( "option", array( "value" => "", "class" => "blank_opt" ), "Select" );
                $day->appendChild( $blank_opt );
            } 
            for( $i = 1; $i <= 31; $i++ ) {
                $opt = $template->createElement( "option", array( "value" => $i ), $i );
                if ( $i == $default->isDay( $i ) ) {
                    $opt->setAttribute( "selected", "selected" );
                }
                $day->appendChild( $opt );                      
            }
            $month = $template->createElement( "select", array( "name" => $name . "[month]", "id" => $name, "class" => "date_month" . ( $showError ? "_error" : "" ) ) );
            if ( $blank ) {
                $blank_opt = $template->createElement( "option", array( "value" => "", "class" => "blank_opt" ), "Select" );
                $month->appendChild( $blank_opt );
            } 
            foreach( I2CE_Date::$months as $mon => $def_name ) {
                $mon_name = $default->getMonthName( $mon );                     
                $opt = $template->createElement( "option", array( "value" => $mon, "class" => ($mon == "" ? "blank_opt" : "" ) ), $mon_name );
                if ( $default->isMonth( $mon ) )
                    $opt->setAttribute( "selected", "selected" );
                $month->appendChild( $opt );
            }
        }
        $node->appendChild( $day );
        $node->appendChild( $month );
    }

        
    /**
     * Add a selection drop down for the hour, minute and second to be selected.
     * @param string $name The name of the selection element
     * @param I2CE_Date $default The default I2CE_Date object to use to preset the value.
     * @param boolean $showError A flag if this field is currently invalid to mark it as such.
     * @param DOMNode $node The node to append the element to.
     * @param boolean $hidden Set to true if the form element should be hidden.
     */
    public static function addTimeElement( $template, $name, $default, $showError, $node, $hidden = false ) {
        if ( !$default instanceof I2CE_Date ) {
            $default = self::blank();
        }
        if ( $hidden ) {
            $hour = $template->createElement( "input", array( "name" => $name . "[hour]", "type" => "hidden", "value" => $default->hour() ) );
            $minute = $template->createElement( "input", array( "name" => $name . "[minute]", "type" => "hidden", "value" => $default->minute() ) );                             
            $second = $template->createElement( "input", array( "name" => $name . "[second]", "type" => "hidden", "value" => $default->second() ) );
        } else {
            $hour = $template->createElement( "select", array( "name" => $name . "[hour]", "class" => "date_hour" . ( $showError ? "_error" : "" ) ) );
            for( $i = 0; $i <= 23; $i++ ) {
                $opt = $template->createElement( "option", array( "value" => $i ), sprintf( "%02d", $i ) );
                if ( $i == $default->isHour( $i ) ) {
                    $opt->setAttribute( "selected", "selected" );
                }
                $hour->appendChild( $opt );                     
            }
            $minute = $template->createElement( "select", array( "name" => $name . "[minute]", "class" => "date_minute" . ( $showError ? "_error" : "" ) ) );
            for( $i = 0; $i <= 59; $i++ ) {
                $opt = $template->createElement( "option", array( "value" => $i ), sprintf( "%02d", $i ) );
                if ( $i == $default->isMinute( $i ) ) {
                    $opt->setAttribute( "selected", "selected" );
                }
                $minute->appendChild( $opt );                   
            }
            $second = $template->createElement( "select", array( "name" => $name . "[second]", "class" => "date_second" . ( $showError ? "_error" : "" ) ) );
            for( $i = 0; $i <= 59; $i++ ) {
                $opt = $template->createElement( "option", array( "value" => $i ), sprintf( "%02d", $i ) );
                if ( $i == $default->isSecond( $i ) ) {
                    $opt->setAttribute( "selected", "selected" );
                }
                $second->appendChild( $opt );                   
            }
        }
        $node->appendChild( $hour );
        $node->appendChild( $minute );
        $node->appendChild( $second );
    }




}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
