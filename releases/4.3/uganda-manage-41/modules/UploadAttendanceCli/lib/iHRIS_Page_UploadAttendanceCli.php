<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
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

class iHRIS_Page_UploadAttendanceCli extends I2CE_PageFormCSV {

	protected $cli;

	/**
	 * Create a new instance of a page.
	 *
	 * The default constructor should be called by any pages extending this object.  It creates the
	 * {@link I2CE_Template} and {@link I2CE_User} objects and sets up the basic member variables.
	 * @param array $args
	 * @param array $request_remainder The remainder of the request path
	 */
	public function __construct($args, $request_remainder, $get = null, $post = null) {
		$this->cli = new I2CE_CLI();
		$this->cli->addUsage("[--csv=FILENAME]: The file to upload.\n");
		parent::__construct($args, $request_remainder, $get, $post);
		$this->cli->processArgs();
		if (!$this->cli->hasValue('csv')) {
			$this->cli->usage();
		}
	}

	/**
	 * Load the objects for the page.
	 * @return boolean
	 */
	protected function loadObjects() {
		$file = $this->cli->getValue('csv');
		if (!file_exists($file) ||
			($upload = fopen($file, "r")) === false) {
			$this->userMessage("Could not read " . $file);
			return false;
		}
		$failed_file = preg_replace('/.csv$/', '.invalid.csv', $file);
		$this->failed = fopen($failed_file, "w");
		if ($this->failed === false) {
			die("Couldn't open $failed_file for invalid rows.\n");
		}
		$this->files['clockin'] = array();
		$this->files['clockin']['file'] = $upload;
		$this->files['clockin']['header'] = true;
		return true;
	}

	/**
	 * Call the action for this page.
	 * @param array $args
	 * @param array $request_remainder
	 */
	protected function actionCommandLine($args, $request_remainder) {
		if ($this->validate()) {
			if ($this->save()) {
				$this->userMessage("The CSV file was imported.");
			} else {
				$this->userMessage("Unable to save file.");
			}
		} else {
			$this->userMessage("There was invalid data in the file.");
		}
	}

	protected function validate() {
		if (!$this->checked_validation) {
			if (!$this->processHeaderRow('clockin')) {
				echo "Unable to read headers from CSV file.\n";
				$this->invalid = true;
				return false;
			}
			$required_headers = array('enrollmentNumber', 'name', 'timeIn', 'timeOut', 'facility', 'date');
			$invalid_headers = array();
			foreach ($required_headers as $header) {
				if (!in_array($header, $this->current['clockin']['header'])) {
					$invalid_headers[] = $header;
				}
			}
			if (count($invalid_headers) > 0) {
				echo "There are missing headers in the CSV file:  " . implode(', ', $invalid_headers) . "\n";
				$this->invalid = true;
				return false;
			}
			$this->checked_validation = true;
		}
		return true;
	}

	protected function validateRow($key) {
		// Don't perform any row level validation for now.
		return true;
	}

	protected function save() {
		if (parent::save()) {
			echo "The CSV file has been uploaded.\n";
		} else {
			echo "An error occurred trying to upload your file.\n";
		}
		return true;
	}
	/*
		    protected function arrange_time( $time ) {
		        list( $hours, $min ) = explode( ':', $time );
		        return sprintf( "0000-00-00 %02d:%02d:00", $hours, $min );
		    }
	*/
	protected function arrange_time($time) {
		list($hour, $min) = explode(':', $time);
		return sprintf("%02d:%02d:00", $hour, $min);
	}

	protected function arrange_date_time($date, $time) {
		list($year, $month, $day) = explode('-', $date);
		return sprintf("%04d-%02d-%02d %s", $year, $month, $day, $this->arrange_time($time));
	}
	protected function arrange_date($date) {
		list($day, $month, $year) = explode('/', $date);
		return sprintf("%04d-%02d-%02d", $year, $month, $day);
	}

	protected function arrange_date1($date) {
		list($year, $month, $day) = explode('-', $date);
		return sprintf("%04d-%02d-%02d", $year, $month, $day);
	}
	/*
		    protected function arrange_date_time( $date_time ) {
		    list( $date, $time ) = explode( ' ', $date_time );
		    list( $day, $month, $year ) = explode( '/', $date );
		    return sprintf( "%04d-%02d-%02d %s", $year, $month, $day, $time );
			}
	*/
	protected function saveRow($key) {
		ob_start();
		//Check if row has ID Number
		if (!$this->current[$key]['row']['enrollmentNumber']) {
			echo "Unable to enter data without ID Number.\n";
			return;
		}

		$id_type = $this->lookupList("id_type", "Employee /IPPS Number");
		//$date = $this->arrange_date1($this->current[$key]['row']['date']);
		$date = $this->current[$key]['row']['date'];

		$person_id = false;
		if ($id_type && $this->current[$key]['row']['enrollmentNumber']) {
			$find_id = array(
				'operator' => 'AND',
				'operand' => array(0 => array(
					'operator' => 'FIELD_LIMIT',
					'style' => 'lowerequals',
					'field' => 'id_num',
					'data' => array('value' => $this->current[$key]['row']['enrollmentNumber']),
				),
					1 => array(
						'operator' => 'FIELD_LIMIT',
						'style' => 'equals',
						'field' => 'id_type',
						'data' => array('value' => $id_type),
					),
				),
			);
			$pers_id = I2CE_FormStorage::search("person_id", false, $find_id, array(), true);
			if ($pers_id) {
				$per_id = $this->factory->createContainer("person_id|$pers_id");
				$per_id->populate();
				$person_id = $per_id->getParent();
			}
		}
		if (!$person_id) {
			echo "Unable to find user for " . $this->current[$key]['row']['enrollmentNumber'] . "\n";
			return true;
		}

		if ($this->current[$key]['row']['timeIn'] || $this->current[$key]['row']['timeOut']) {
			$where = array(
				'operator' => 'FIELD_LIMIT',
				'style' => 'equals',
				'field' => 'date',
				'data' => array('value' => $date),
			);

			$clockin_id = I2CE_FormStorage::search("clockin", $person_id, $where, array(), true);
			if (!$clockin_id) {
				//echo "Clockin does not Exist ".$this->current[$key]['row']['name']. " ". $date . "\n" ;
				$clockin = $this->factory->createContainer("clockin");
				$clockin->setParent($person_id);
				$clockin->getField('date')->setFromDB($date);

				if (!$this->current[$key]['row']['timeIn']) {
					$time_in = $this->arrange_date_time($date, $this->current[$key]['row']['timeOut']);
					$time_out = $this->arrange_date_time($date, $this->current[$key]['row']['timeOut']);
					$clockin->getField('time_in')->setFromDB($time_in);
					$clockin->getField('time_out')->setFromDB($time_out);
				} elseif (!$this->current[$key]['row']['timeOut']) {
					$time_in = $this->arrange_date_time($date, $this->current[$key]['row']['timeIn']);
					$time_out = $this->arrange_date_time($date, $this->current[$key]['row']['timeIn']);
					$clockin->getField('time_in')->setFromDB($time_in);
					$clockin->getField('time_out')->setFromDB($time_out);
					//echo "If loop for No Time Out ". $time_out. " ". $date ."\n" ;
				} else {
					$time_in = $this->arrange_date_time($date, $this->current[$key]['row']['timeIn']);
					$time_out = $this->arrange_date_time($date, $this->current[$key]['row']['timeOut']);
					$clockin->getField('time_in')->setFromDB($time_in);
					$clockin->getField('time_out')->setFromDB($time_out);
				}
				$clockin->validate();
				if ($clockin->hasInvalid()) {
					$this->userMessage("Unable to validate row for: " . $this->current[$key]['row']['name'] . " at "
						. $this->current[$key]['row']['enrollmentNumber']);
					echo "Clockin Times not Valid for  " . $this->current[$key]['row']['name'] . "  On Date " . $date . "\n";
				} else {
					$clockin->save($this->user);
				}

				$clockin->cleanup();
				unset($clockin);
			} else {
				echo "Clockin Existed " . $clockin_id . " " . $this->current[$key]['row']['name'] . " on " . $date . "\n";
				$clockin = $this->factory->createContainer("clockin|$clockin_id");
				$clockin->populate();
				$old_time_in = $clockin->getField('time_in')->getDBValue();
				$old_time_out = $clockin->getField('time_out')->getDBValue();

				if (!$this->current[$key]['row']['timeIn']) {
					$time_in = $this->arrange_date_time($date, $this->current[$key]['row']['timeOut']);
					$time_out = $this->arrange_date_time($date, $this->current[$key]['row']['timeOut']);
					if ($time_in < $old_time_in) {
						$clockin->getField('time_in')->setFromDB($time_in);
					}
					if ($old_time_out < $time_out) {
						$clockin->getField('time_out')->setFromDB($time_out);
					}
				} elseif (!$this->current[$key]['row']['timeOut']) {
					$time_in = $this->arrange_date_time($date, $this->current[$key]['row']['timeIn']);
					$time_out = $this->arrange_date_time($date, $this->current[$key]['row']['timeIn']);
					if ($time_in < $old_time_in) {
						$clockin->getField('time_in')->setFromDB($time_in);
					}
					if ($old_time_out < $time_out) {
						$clockin->getField('time_out')->setFromDB($time_out);
					}
				} else {
					$time_in = $this->arrange_date_time($date, $this->current[$key]['row']['timeIn']);
					$time_out = $this->arrange_date_time($date, $this->current[$key]['row']['timeOut']);
					if ($time_in < $old_time_in) {
						$clockin->getField('time_in')->setFromDB($time_in);
					}
					if ($old_time_out < $time_out) {
						$clockin->getField('time_out')->setFromDB($time_out);
					}
				}
				$clockin->validate();
				if ($clockin->hasInvalid()) {
					$this->userMessage("Unable to validate row for: " . $this->current[$key]['row']['name'] . " at "
						. $this->current[$key]['row']['enrollmentNumber']);
					echo "Clockin Times not Valid for  " . $this->current[$key]['row']['name'] . "  On Date " . $date . "\n";
				} else {
					$clockin->save($this->user);
				}
				$clockin->cleanup();
				unset($clockin);
			}
		}
		$output = ob_get_contents();
		$outputFile = 'OutputFile.txt';
		ob_end_clean();
		file_put_contents($outputFile, $output, FILE_APPEND);
		return true;
	}
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
