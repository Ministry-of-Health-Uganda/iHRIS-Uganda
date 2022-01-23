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
 * Tutorial class for importing education details from a CSV file.
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2014 IntraHealth International, Inc. 
 * @since v4.2.0
 * @version v4.2.0
 */

/**
 * The page class for uploading education details for a person.
 * @package iHRIS
 * @subpackage Common
 * @access public
 */
class iHRIS_PageFormUpload_Education extends I2CE_PageFormCSV {

    /**
     * @var iHRIS_Person The person object that the education is being added to.
     */
    protected $person;

    /**
     * Run the action from the command line to load data.
     * @param array $args
     * @param array $request_remainder
     */
    protected function actionCommandLine( $args, $request_remainder ) {
        $cli = new I2CE_CLI();
        $cli->addUsage("--csv=FILENAME: The CSV file to upload.\n");
        $cli->addUsage("--id=person|XXXX: The person ID of the parent form.\n");
        $cli->processArgs();
        if ( !$cli->hasValue('csv') || !$cli->hasValue('id') ) {
            $cli->usage();
            return;
        }
        $this->person = $this->factory->createContainer( $cli->getValue('id' ) );
        if ( !$this->person instanceof iHRIS_Person ) {
            echo "Invalid id passed to education upload page.\n";
            return false;
        }
        $this->person->populate();

        $file = $cli->getValue('csv');
        if ( !file_exists( $file ) ||
                ($upload = fopen( $file, "r" )) === false ) {
            echo "Could not read: " . $file . "\n";
            return;
        }
        $this->files['education'] = array();
        $this->files['education']['file'] = $upload;
        $this->files['education']['header'] = true;

        if ( $this->validate() ) {
            if ( $this->save() ) {
                echo "The CSV file was imported.\n";
            } else {
                echo "Unable to load file.\n";
            }
        } else {
            echo "There was invalid data in the file.\n";
        }
    }

    /**
     * Load the objects for the page.
     * This loads the person object based on the ID passed to the page.
     * @return boolean
     */
    protected function loadObjects() {
        parent::loadObjects();
        if ( array_key_exists( 'HTTP_HOST', $_SERVER ) ) {
            if ( !$this->get_exists('id') ) {
                $this->userMessage("Invalid person id provided.");
                return false;
            }
            $this->person = $this->factory->createContainer( $this->get('id') );
            if ( !$this->person instanceof iHRIS_Person ) {
                $this->userMessage("Invalid id passed to education upload page.");
                return false;
            }
            $this->person->populate();
        }
        return true;
    }

    /**
     * Set the form on the page.
     * This makes sure the person object is accessible from anywhere in the template.
     */
    protected function setForm() {
        $this->template->setForm( $this->person );
    }

    /**
     * Validate the CSV file to make sure everything is required.
     * We can check to be sure the required headers are there or check the number of columns in the file
     * if there are no headers.
     * @return boolean
     */
    protected function validate() {
        if ( !$this->checked_validation ) {
            if ( !$this->processHeaderRow( 'education' ) ) {
                $this->userMessage("Unable to read headers from CSV file.");
                $this->invalid = true;
                return false;
            }
            $required_headers = array( 'Education Type', 'Degree', 'Major', 'Year', 'Institution' );
            $invalid_headers = array();
            foreach( $required_headers as $header ) {
                if ( !in_array( $header, $this->current['education']['header'] ) ) {
                    $invalid_headers[] = $header;
                }
            }
            if ( count( $invalid_headers ) > 0 ) {
                $this->userMessage( "There are missing headers in the CSV file:  " . implode( ', ', $invalid_headers ) );
                $this->invalid = true;
                return false;
            }
            $this->checked_validation = true;
        }
        return true;
    }

    /**
     * Validate the current row for the given key
     * @param string $key
     * @return boolean
     */
    protected function validateRow( $key ) {
        // Don't perform any row level validation for now.
        return true;
    }

 
    /**
     * Lookup a degree with the given edu_type
     * @param string $degree
     * @param string $edu_type
     * @return string
     */
    protected function lookupDegree( $degree, $edu_type ) {
        $degree = strtolower( trim( $degree ) );
        if ( !array_key_exists( "degree", $this->cache ) ) {
            $this->cache["degree"] = array();
        }
        if ( !array_key_exists( $edu_type, $this->cache["degree"] ) ) {
            $this->cache["degree"][$edu_type] = array();
        }
        if ( !array_key_exists( $degree, $this->cache["degree"][$edu_type] ) ) {
            $where = array(
                    'operator' => 'AND',
                    'operand' => array(
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'edu_type',
                            'style' => 'equals',
                            'data' => array( 'value' => $edu_type ),
                            ),
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'style' => 'lowerequals',
                            'field' => 'name',
                            'data' => array( 'value' => $degree )
                            ),
                        )
                    );
            $id = I2CE_FormStorage::search( "degree", false, $where, array(), true );
            if ( $id ) {
                $this->cache["degree"][$edu_type][$degree] = "degree|$id";
            } else {
                $this->cache["degree"][$edu_type][$degree] = "";
            }
        }
        return $this->cache["degree"][$edu_type][$degree];
    }


    /**
     * Save the CSV for this page.
     */
    protected function save() {
        if ( parent::save() ) {
            $this->userMessage("The CSV file has been uploaded.");
        } else {
            $this->userMessage("An error occurred trying to upload your file.");
        }
        $this->setRedirect( "view?id=" . $this->person->getNameId() );
        return true;
    }

   /**
     * Save the current row for the given key.
     * @param string $key
     * @return boolean
     */
    protected function saveRow( $key ) {
        // First lookup the education type that was given.
        $edu_type = $this->lookupList( "edu_type", $this->current[$key]['row']['Education Type'] );
        // Then lookup the degree with the education type.
        $degree = $this->lookupDegree( $this->current[$key]['row']['Degree'], $edu_type );
        if ( !$degree ) {
            $this->userMessage( "Unable to load row for: " . $this->current[$key]['row']['Degree'] . " at " 
                    . $this->current[$key]['row']['Institution'] );
            // Don't try to load this one, but continue.
            return true;
        }
        $education = $this->factory->createContainer( "education" );
        $education->institution = $this->current[$key]['row']['Institution'];
        if ( array_key_exists( 'Location', $this->current[$key]['row'] ) ) {
            $education->location = $this->current[$key]['row']['Location'];
        }
        $education->getField('year')->setFromDB( $this->current[$key]['row']['Year']."-00-00 00:00:00" );
        $education->getField('degree')->setFromDB( $degree );
        $education->major = $this->current[$key]['row']['Major'];

        $education->setParent( $this->person->getNameId() );
        $education->validate();
        if ( $education->hasInvalid() ) {
            $this->userMessage( "Unable to validate row for: " . $this->current[$key]['row']['Degree'] . " at " 
                    . $this->current[$key]['row']['Institution'] );
        } else {
            $education->save( $this->user );
        }

        $education->cleanup();
        unset( $education );
        return true;
    }

}

?>
