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
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */


/**
 *  I2CE_Util
 * @package I2CE
 * @todo Better documentation
 */
class I2CE_Util {
    

    /**
     * Performs a recursive array merge which _overwrites_ values, 
     * not appends them ( the behaviour of array_merge_recursive).
     * Warning:  This is not a symmetric operation.  Any multi-index that exists
     * in $a and in $b  will result that the value in $a will be overwritten by
     * the value in $a
     * @param array &$a the array to merge into
     * @param array $b the array to merge from
     * @param boolean $addNew Defaults to true.  If false, we do not add new keys
     * @param boolean $addEmpty Defaults to true.  If false, we do not add empty values
     */
    public static function merge_recursive(&$a,$b, $addNew = true, $addEmpty = true) {
        if (is_array($b)) {
            if (is_scalar($a)) {
                $a = $b;
            } else {
                if (!is_array($a)) {
                    $a = array();
                }
                foreach ($b as $k=>$v) {
                    if (!$addNew && !array_key_exists($k,$a)) {
                        continue;
                    }
                    if (!$addEmpty && empty($v)) {
                        continue;
                    }
                    self::merge_recursive($a[$k],$b[$k]);
                }
            }
        } else { //b is a scalar.
            $a = $b;
        }
        
    }
    
    /**     
     * array unique which respects multi-dimensional arrays from:
     *              the link http://us.php.net/manual/en/function.array-unique.php#84750 sda
     *
     */
    public static function array_unique($arr) {
        if(!is_array($arr)) {
            return $arr;
        }
        foreach ($arr as &$val){
            $val=serialize($val);
        }

        $arr=array_unique($arr);

        foreach ($arr as &$val){
            $val=unserialize($val);
        }
        return $arr;
    } 



    /**
     * Flatten variables.
     * Transforms an array of variables of a nested array ['key1']['key2']['key3'] to an array with keys of the form 'key1:key2:key3' 
     * @param array $vars the variables to flatten.
     * @param array &$flat The array to store the falttened variables in
     * @param boolean $encode.  Set to true (default) if we should urlencode the values
     * @param boolean $skip_empty.  Set to true (default) skip empty values
     * @param string $prefix.  Defaults to '';
     *
     */
    public static function flattenVariables($vars, &$flat, $encode =true, $skip_empty = true, $prefix = '') {
        if (is_array($vars)) {
            if ($prefix) {
                $prefix .= ':';
            }
            foreach ($vars as $k=>$v) {
                self::flattenVariables($v,$flat,$encode,$skip_empty,$prefix . $k);
            }
        } else if (is_scalar($vars)) {
            $vars = '' . $vars; //force it to be a string
            if ($skip_empty && strlen($vars) == 0) {
                return;
            }
            $set = &$flat;
            if (array_key_exists($prefix,$flat)) {
                if (!is_array($flat[$prefix])) {
                    $flat[$prefix] = array($flat[$prefix]);
                }
                $set =&$flat[$prefix];
            }
            if ($encode) {
                $set[$prefix] = urlencode($vars);
            } else {
                $set[$prefix] = $vars;

            }
        }
    }

    /**
     * Transforms an array of variables with keys of the form 'key1:key2:key3' into a nested array ['key1']['key2']['key3']
     * @param array $vars
     * @returns array
     */
    public static function transformVariables($vars) {
        $ret = array();
        foreach ($vars as $key=>$val) {
            $keys = explode(':',$key);
            if (count($keys) == 0) {
                continue;
            }
            foreach ($keys as &$key) {
                $key = str_replace('%2F','.',$key);
            }
            unset($key);
            $data = &$ret;
            while (count($keys) > 1) {
                $k = array_shift($keys);
                if (!array_key_exists($k,$data)) {
                    $data[$k] = array();
                } else if (!is_array($data[$k])) { //make sure that it is an arrayb
                    $data[$k] = array($data[$k]);
                }
                $data = &$data[$k];
            }
            $k = array_shift($keys);
            if (is_array($val)) {
                if (array_key_exists($k,$data)) { //we have something at this reference point
                    if (is_array($data[$k])) { //we already have an array at this reference 
                        $data[$k] = array_merge($data[$k], $val);
                    } else {
                        $data[$k] = array_merge(array($data[$k]), $val);                        
                    }
                } else {
                    $data[$k] = $val;
                }
            } else { //our values  is a scalar
                if (array_key_exists($k,$data)) {
                    if (is_array($data[$k])) { //we already have an array at this reference point so add it
                        $data[$k][] = $val;
                    } else { //not sure how this would happen but OK. we have two scalar values at this point so we better make it an array
                        $data[$k] = array($data[$k],$val);
                    }
                } else {
                    $data[$k] = $val;
                }
            }
        }
        return $ret;
    }




    /**
     * Executes a script
     * @param string $file.  The SQL file to execute -- it must lie in the fileSearch's SQL category
     * (this is ensured by addinging it to a <path name='sql'> node in the configuration XML
     * @param string $database.  If non-null it will connect to the named database.  
     * @param mixed $transact defaults to true meaning that the whole script is executed in a transaction.  If a string, it is the name of
     * a save point to use (assumes you are already in a transaction)
     * @param string $dsn.  An option DSN to connect on.  If set $database is ignored.
     * it will use whatever database is refered to by the DSN
     * @param string $delimiter.  Defaults to ';' Needs to be exactly one character
     * @return boolean  -- true on sucess, false on failure
     */
    public static function runSQLScript($file,$database=null,$transact = true,$dsn=null, $delimiter = ';') {
        $t_file = I2CE::getFileSearch()->search('SQL',$file);
        if (!$t_file){
            I2CE::raiseError( "Couldn't find SQL script: $file.\nSearch Path is:\n" 
                              . print_r(I2CE::getFileSearch()->getSearchPath('SQL'), true), E_USER_NOTICE );
            return false;
        }
        $file = $t_file;
        if ($dsn !== null) {
            $database = null;
            $db = I2CE::pdoConnect( null, $dsn );
            if ( !$db ) {
                return false;
            }
        } else if ($database!==null) {
            $db = I2CE::PDO();
            $oldDB = I2CE_PDO::details('dbname');
        } else {
            $db = I2CE::PDO();
        }      
        if ($database !== null) {
            try {
                $db->exec( 'USE `' . $database . '`' );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Cannot use database $database" );
                return false;
            }
        } 
        $transact_msg = '';
        $savepoint = null;
        if ($transact === true) {
            /*
            if (!$db->supports('transactions')) {
                $transact = false;
                $transact_msg = 'No Transactions used (Not supported)';
            } else */ if ( $db->inTransaction() ) {
                $transact = 'SCRIPT_EXECUTE_' . rand(1000,9999);
                $transact_msg = "Using savepoint $transact (Already in transaction)";
            } else {
                $transact_msg = "Transaction on";
            }
        } else if (is_string($transact)) {
            if ($db->inTransaction()) {
                $transact_msg = "Using savepoint $transact";
            } else {
                $transact_msg = "Transaction on (Savepoint $transact ignored because not in transaction)";
                $transact = true;
            }
        } else {
            $transact =false;
            $transact_msg = 'No Transactions used (None specified)';
        }
        I2CE::raiseError("Running SQL Script at $file: " . $transact_msg);

        $result =  self::explodeAndExecuteSQLQueries(file_get_contents($file),$db,$transact,$delimiter);
        if ($database !== null) {
            try {
                $db->exec( 'USE `' . $oldDB . '`' );
            } catch ( PDOException $e ) {
                I2CE::pdoError($e, "Cannot use database $oldDB");
                return false;
            }
        } 
        if (!$result) {
            I2CE::raiseError("Script excution failed for $file");
            return false;
        } 
        return true;
    }

    /**
     * Explode and execute a 
     * @param string $sql a nuch of sql queries
     * @param boolean $transact defaults to true meaning that the whole script is executed in a transaction.  If a string, it is the name of a savepoint to rollback to/release
     * @param PDO $db
     * @param string $delimiter.  Defaults to ';'. Needs to be exactly one character
     * @returns boolean true on sucess or false
     */
    public static function explodeAndExecuteSQLQueries($sql,$db,$transact=true, $delimiter = ';') {
        if (!is_string($sql)) {
            I2CE::raiseError("Invalid SQL script");
            return false;
        }
        I2CE::longExecution();
        try {
            if ($transact===true) {
                $db->beginTransaction(); 
                I2CE::raiseError("Beginning transaction");
            } else if (is_string($transact)) {
                if ($db->inTransaction()) { 
                    $db->exec('SAVEPOINT $transact');
                } else { //we aren't in a transaction, so don't use the savepoint
                    $transact =true;
                    $db->beginTransaction();
                    I2CE::raiseError("Beginning transaction");
                }
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Unable to begin transactions" );
        }
        $len  = strlen($sql);
        $in_string_single = false;
        $in_string_double = false;
        $in_string_back = false;
        $in_comment = false;
        $in_comment_ml = false;
        $beg_qry = 0;
        $t_qry = '';
        for ($i=0; $i < $len; $i++) {
            switch($sql[$i]) {
            case "\n":
                if ($in_comment) {
                    $in_comment = false;
                    $beg_qry = $i+1; //beginning of query is next line
                }
                if ($in_comment || $in_comment_ml ||$in_string_single !== false || $in_string_double || $in_string_back) {
                    continue;
                }
                if (preg_match('/^DELIMITER (.)$/',trim(substr($sql,$beg_qry,$i - $beg_qry )), $matches)) {
                    $delimiter = $matches[1];
                    $beg_qry = $i+1; //beginning of query is next line
                }
                continue;
            case "-":
                if ($in_comment || $in_comment_ml ||$in_string_single !== false || $in_string_double || $in_string_back) {
                    continue;
                }
                if ($i > 0 && $sql[$i-1] == '-') {
                    $in_comment = true;
                    $t_qry .= $t_qry . trim(substr($sql,$beg_qry,$i-1 - $beg_qry));
                    $beg_qry = -1;
                }
                break;
            case "#":
                if ($in_comment || $in_comment_ml ||$in_string_single !== false|| $in_string_double || $in_string_back) {
                    continue;
                }
                $in_comment = true;
                $t_qry .= $t_qry . trim(substr($sql,$beg_qry,$i - $beg_qry ));
                $beg_qry = $i +1;
                break;
            case '*':
                if ($in_comment || $in_string_single !== false|| $in_string_double || $in_string_back) {
                    continue;
                }
                if ($in_comment_ml) {
                    if ($i +2 < $len && $sql[$i+1] == '/') {
                        $in_comment_ml = false; 
                        $beg_qry = $i+2;
                    }
                }else {
                    if ($i > 0 && $sql[$i-1] == '/') {
                        $in_comment_ml = true;  
                        $t_qry .= $t_qry . trim(substr($sql,$beg_qry,$i-1 - $beg_qry ));
                        $beg_qry = $i +1;
                    }
                }
                continue;
                break;
            case "`":
                if ($in_comment || $in_comment_ml) {
                    continue;
                }
                if ($in_string_back) {
                    $in_escape_slash = false;
                    $in_escape_back = false;
                    if ($i > 0) {
                        if ($sql[$i-1] == '\\') {
                            $in_escape_slash =true;
                            //can't have a \ apearing in a table or field name
                        }
                        $j= $i-1;
                        while($sql[$j] == '`') {
                            $in_escape_back = !$in_escape_back;
                            $j--;
                        }
                    }
                    if ( !$in_escape_slash && !$in_escape_back){ 
                        $in_string_back = false;
                    }
                } else if ($in_string_single ===FALSE && !$in_string_double) {
                    $in_string_back = true;
                }
                continue;
                break;
            case '"':
                if ($in_comment || $in_comment_ml) {
                    continue;
                }
                if ($in_string_double) {
                    $in_escape_slash = false;
                    $in_escape_double = false;
                    if ($i > 9) {
                        $j= $i-1;
                        while($sql[$j] == '\\') {
                            $in_escape_slash = !$in_escape_slash;
                            $j--;
                        }
                        $j= $i-1;
                        while($sql[$j] == '"') {
                            $in_escape_double = !$in_escape_double;
                            $j--;
                        }
                    }
                    if ( !$in_escape_slash && !$in_escape_double){ 
                        $in_string_double = false;
                    }
                } else  if ($in_string_single=== false && !$in_string_back) {
                    $in_string_double = true;
                }
                continue;
                break;
            case "'":
                if ($in_comment || $in_comment_ml) {
                    continue;
                }
                if ($in_string_single !== false) {
                    $in_escape_slash = false;
                    if ($i > 0) {
                        $j= $i-1;
                        while($j > $in_string_single) {
                            if ($sql[$j] == '\\') {
                                $in_escape_slash = !$in_escape_slash;
                                $j--;
                            } else {
                                break;
                            }
                        }
                    }
                    if ( !$in_escape_slash ) {
                        $in_string_single = false;
                    }
                } else if (!$in_string_back && !$in_string_double) {
                    $in_string_single = $i;
                }
                continue;
                break;
            case $delimiter: 
                if ($in_string_single !== false || $in_string_double || $in_string_back || $in_comment || $in_comment_ml) {
                    continue;
                }
                $qry = trim($t_qry . trim(substr($sql,$beg_qry,$i - $beg_qry )));
                $beg_qry = $i +1;
                $t_qry = '';
                if (!$qry) {
                    continue;
                }
                try {
                    $db->exec($qry);
                } catch ( PDOException $e ) {
                    I2CE::pdoError($e,"Cannot execute  query:\n$qry");
                    if ($db->inTransaction()) { 
                        try {
                            if ($transact===true)  {
                                I2CE::raiseError("Rolling Back");
                                $db->rollback();
                            } elseif (is_string($transact)) {
                                I2CE::raiseError("Rolling Back to savepoint $transact");
                                $db->exec("ROLLBACK TO SAVEPOINT $transact");
                            }
                        } catch ( PDOException $e ) {
                            I2CE::pdoError( $e, "Failed to rollback transaction" );
                        }
                    }
                    if ($transact) {
                        I2CE::getConfig()->clearCache();
                    }
                    return false;
                }

                continue 2;
                break;
            default:
                break;
            }
        }
        //now make sure we pick up the last query
        $qry = trim($t_qry . trim(substr($sql,$beg_qry)));
        if ($qry) {
            try {
                $db->exec($qry);
            } catch( PDOException $e ) {
                I2CE::pdoError($e,"Cannot execute  query:\n$qry");
                if ($db->inTransaction()) {
                    try {
                        if ($transact===true) {
                            I2CE::raiseError("Rolling Back");
                            $db->rollback();
                        } else if (is_string($transact)) {
                            I2CE::raiseError("Rolling Back to savepoint $transact");
                            $db->exec("ROLLBACK TO SAVEPOINT $transact");                        
                        }
                    } catch ( PDOException $e ) {
                        I2CE::pdoError( $e, "Failed to rollback transaction" );
                    }
                } 
                if ($transact) {
                    I2CE::getConfig()->clearCache();
                }
                return false;
            }
        }
        //we are done
        if ($db->inTransaction()) { 
            try {
                if ($transact === true) {
                    I2CE::raiseError("Commiting");
                    return $db->commit();
                } 
                if (is_string($transact))  {
                    $db->exec("RELEASE SAVEPOINT $transact");                                        
                    I2CE::raiseError("Released savepoint $transact");
                } 
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to commit transaction" );
            }
            return true;
        } else{
            return true;  
        }
    }


    /**
     * Convers a sql like statement to a regular expression
     * @param string $like
     * @param string $escape.  The escape character.  Defaults to \'
     * @returns string
     */

    public static function convertLikeToRegExp($like,$escape ='\\') {
        $regexp = '';
        $part = '';
        $in_escape = false;
        $l = strlen($like);
        for ($i=0; $i < $l; $i++) {
            $c = $like[$i];
            if ($in_escape) {
                $part .= $c;
                $in_escape = false;
            } else {
                if ($c == $escape) {
                    $in_escape = true;
                } else if ($c == '%') { //wildcard
                    $regexp .= preg_quote($part) . '.*';
                    $part = '';
                } else if ($c == '_') { //single character
                    $regexp .= preg_quote($part) . '.' ;
                    $part = '';
                } else {
                    $part .= $c;
                }
            }
        }
        $regexp .= preg_quote($part);
        return $regexp;
    }

  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
