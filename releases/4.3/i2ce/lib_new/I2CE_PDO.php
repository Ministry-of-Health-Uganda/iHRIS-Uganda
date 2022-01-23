<?php
/**
 * @copyright © 2016 Intrahealth International, Inc.
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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since v4.3.0
 * @version v4.3.0
 */

/**
 * Static class with helper methods for I2CE PDO (database) handling.
 * 
 */


/**
 * @package I2CE
 */
class I2CE_PDO   {

    /**
     * @var static protected PDO PDO object
     */
    protected static $pdo;

    /**
     * @var static protected array The database connection details.
     */
    protected static $details = array ( 'scheme' => 'mysql', 'charset' => 'utf8' );

    /**
     * Initialize the DSN for this connection.
     * @param string $dsn
     */
    public static function initialize( $dsn ) {
        self::$details = self::parseDSN( $dsn );
    }

    /**
     * Return the connected PDO object.
     * @return PDO
     */
    public static function PDO() {
        return self::$pdo;
    }

    /**
     * Set up the internal PDO refernece to a connected database.
     * @return boolean
     */
    public static function setup() {
        // Especially when writing PHP scripts for use on different
        // servers, it is a very good idea to explicitly set the internal
        // encoding somewhere on top of every document served, e.g.

        // mb_internal_encoding("UTF-8");

        // This, in combination with mysql-statement "SET NAMES
        // 'utf8'", will save a lot of debugging trouble.

        // Also, use the multi-byte string functions instead of the
        // ones you may be used to, e.g. mb_strlen() instead of
        // strlen(), etc.

        // -- From Joachim Kruyswijk 25-May-2006 on mb_internal_encoding
        mb_internal_encoding( "UTF-8" );


        self::$pdo = self::connect();

        if ( self::$pdo instanceof PDO ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Try to reconnect to the database
     * @return PDO
     */
    public static function reconnect() {
        self::$pdo = null;
        if ( !self::setup() ) {
            return false;
        }
        return self::PDO();
    }

    /**
     * Connect to database with PDO and return that object.
     * @param array $details database connection details
     * @param string $dsn optional DSN URL.
     * @return PDO
     */
    public static function connect( $details = null, $dsn = null ) {
        $dbDefaults = array( 'scheme' => 'mysql', 'charset' => 'utf8', 
                //'dbname' => null, 'host' => null, 'port' => null, 
                //'user' => null, 'pass' => null, 
                //'unix_socket' => null,
                'opt' => array(
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_CASE               => PDO::CASE_LOWER,
                    ) );
        if ( !$details || count($details) == 0 ) {
            if ( !empty($dsn) ) {
                $details = self::parseDSN( $dsn );
            } else {
                $details = self::details();
            }
        }
        foreach ( $dbDefaults as $key => $val ) {
            if ( !array_key_exists( $key, $details ) ) {
                $details[$key] = $val;
            }
        }
        foreach( $dbDefaults['opt'] as $key => $val ) {
            if ( !array_key_exists( $key, $details['opt'] ) ) {
                $details['opt'][$key] = $val;
            }
        }

        $db_args = array( 'user' => null, 'pass' => null );
        foreach( $details as $key => $val ) {
            if ( !$val || $key == 'opt' || $key == 'scheme' ) continue;
            if ( $key == 'user' || $key == 'pass' ) {
                if ( $details['scheme'] == 'mysql' ) {
                    $db_args[$key] = $val;
                } else {
                    $dsn[] = "$key=$val";
                }
            } else {
                $dsn[] = "$key=$val";
            }
        }

        $dsn_str = $details['scheme'] . ':' . implode( ';', $dsn );
        try {
            return new PDO( $dsn_str, $db_args['user'], $db_args['pass'], $details['opt'] );
        } catch( PDOException $e ) {
            I2CE::raiseError( $e->getMessage() );
            return false;
        }

    }

    /**
     * Return the database details being used.
     * @param string $key the detail data to return.
     * @return mixed
     */
    public static function details( $key = null ) {
        if ( $key ) {
            if ( array_key_exists( $key, self::$details ) ) {
                return self::$details[ $key ];
            } else {
                return null;
            }
        } else {
            return self::$details;
        }
    }

    /**
     * Duplicate the MDB2 extended execParam function to make it easier to
     * convert code without adding many lines.
     * Should be run in a try/catch statement as errors are not caught here and
     * only with DML statements.
     *
     * @param string $qry The query to be run.
     * @param array $params The parameters to be replaced.
     * @return int
     */
    public static function execParam( $qry, $params=array() ) {
        $stmt = self::$pdo->prepare( $qry );
        $stmt->execute( $params );
        $result = $stmt->rowCount();
        $stmt->closeCursor();
        unset( $stmt );
        return $result;
    }

    /**
     * Return a single row from a query.
     * Duplicate the MDB2 extended getRow function to make it easier to
     * convert code without adding many lines.
     *
     * @param string $qry The query to be run.
     * @param array $params The parameters to be replaced.
     * @return array
     */
    public static function getRow( $qry, $params=array() ) {
        $stmt = self::$pdo->prepare( $qry );
        $stmt->execute( $params );
        $result = $stmt->fetch();
        $stmt->closeCursor();
        unset( $stmt );
        return $result;
    }

    /**
     * Replacement for mysql_real_escape_string since it is deprecated or removed.
     * Ideally prepare/execute would be used, but this isn't always easily possible
     * for migrating current code.
     *
     * @param string $var
     * @return string
     */
    public static function escape_string( $var ) {
        return substr( self::$pdo->quote( $var ), 1, -1 );
    }

    /**  
     * Parse a DSN into the database details array.  This can either be an MDB2 or PDO compliant DSN.
     * @param string $dsn
     * @return array
     */
    public static function parseDSN( $dsn ) {
        $dbDetails = array();

        $dsn_data = parse_url( $dsn );

        if ( count($dsn_data) == 2 && array_key_exists('scheme', $dsn_data) && array_key_exists('path', $dsn_data) ) {
            $dbDetails['scheme'] = $dsn_data['scheme'];
            $chunks = array_chunk(preg_split('/(.*[^\\\])[=;]/U', $dsn_data['path'],-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE), 2);
            $result = array_combine(array_map('stripslashes',array_column($chunks, 0)), array_map('stripslashes',array_column($chunks, 1)));
            foreach( $result as $key => $val ) {
                $dbDetails[$key] = $val;
            }
        } else {
            if ( array_key_exists( 'scheme', $dsn_data ) ) {
                $dbDetails['scheme'] = $dsn_data['scheme'];
            }
            if ( array_key_exists( 'host', $dsn_data ) ) {
                if ( $dsn_data['host'] == 'unix(' && array_key_exists( 'path', $dsn_data ) ) {
                        list( $socket, $dbname ) = explode( ')/', $dsn_data['path'], 2 ); 
                $dbDetails['unix_socket'] = $socket;
                $dbDetails['dbname'] = $dbname;
                } else {
                    $dbDetails['host'] = $dsn_data['host'];
                    if ( array_key_exists( 'path', $dsn_data ) ) {
                        $dbDetails['dbname'] = substr( $dsn_data['path'], 1 ); 
                    }
                    if ( array_key_exists( 'port', $dsn_data ) ) {
                        $dbDetails['port'] = $dsn_data['port'];
                    }
                }
            }
            if ( array_key_exists( 'user', $dsn_data ) ) {
                $dbDetails['user'] = $dsn_data['user'];
            }
            if ( array_key_exists( 'pass', $dsn_data ) ) {
                $dbDetails['pass'] = $dsn_data['pass'];
            }
        }

        return $dbDetails;

 
    }  


}

# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
