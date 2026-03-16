<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package i2ce
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_File_Base
* 
* @access public
*/


class I2CE_FormStorage_LDAP extends I2CE_FormStorage_Mechanism {
    

 
    /**
     * LDAP escaping function to prevent against injection.
     * 
     * 
     * @param string $str the string to escape
     * @param booleans $for_dn.  Defaults to false.   True if we are escaping for a dn
     * returns string
     */
    protected function ldap_escape($str, $for_dn = false) {
        //Gratefully stolen from   http://www.php.net/manual/en/function.ldap-search.php#90158            
        // see:
        // RFC2254
        // http://msdn.microsoft.com/en-us/library/ms675768(VS.85).aspx
        // http://www-03.ibm.com/systems/i/software/ldap/underdn.html       
        
        if  ($for_dn) {
            $metaChars = array(',','=', '+', '<','>',';', '\\', '"', '#');
        } else {
            $metaChars = array('*', '(', ')', '\\', chr(0));
        }
        $quotedMetaChars = array();
        foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.str_pad(dechex(ord($value)), 2, '0');
        $str=str_replace($metaChars,$quotedMetaChars,$str); //replace them
        return ($str); 
    }




    
    /**
     * @var protected resource $ldap the ldap connect;
     */
    protected $ldap = array();
    /**
     * Get the ldap connection
     * @aparm string $form.  The form we want to connect on
     * @param boolean $cached.  Defaiults to true in which case we get the cached connection
     * @returns mixed.  False on failure or resource on success
     */
    protected function getConnection($form, $cached = true) {
        if ($form instanceof I2CE_Form) {
            $form = $form->getName();
        }
        if (!is_string($form) ) {
            return false;
        }
        if (!$cached || !array_key_exists($form,$this->ldap) ) { //we only will try to make a connection once.
            $options = $this->getStorageOptions($form);
            if ( !$options instanceof I2CE_MagicDataNode ) {
                I2CE::raiseError( "Invalid storage options for $form" );
                return false;
            }
            $host = 'localhost';
            $port = 389;
            $options->setIfIsSet($port,"connection/port");
            $options->setIfIsSet($host,"connection/host");
            $ldap  = @ldap_connect($host,$port);
            if (!is_resource($ldap)) {
                I2CE::raiseError("Could not connect to ldap server on $host:$port");
                $ldap = false;
                return false;
            }
            @ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            @ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

            $user = 'admin';
            $options->setIfIsSet($user,"connection/user");
            $pass = false;
            if (! $options->setIfIsSet($pass,"connection/pass")) {
                I2CE::raiseError("No ldap password set under " . $option->getPath(false) . '/connection/pass');
                return false;
            }
            $bind_dn = 'dc=localhost';
            $options->setIfIsSet($bind_dn,"connection/bind_dn");
            if (!@ldap_bind($ldap, 'cn=' . $user . ',' . $bind_dn ,$pass)) {
                I2CE::raiseError("Could not bind to ldap server with user $user at $bind_dn ($pass)");
                $ldap = false;                
            }
            if ($cached) {
                $this->ldap[$form] = $ldap;
            } else {
                return $ldap;
            }
        }
        return $this->ldap[$form];
    }

    

    
    /**
     * Return an array of all the record ids for a given form.
     * @param string $form
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param string $parent Defaults to false.  If true, The parent field we want to restrict values to
     * @return array
     */
    public function getRecords( $form, $mod_time = -1) {
        if ($mod_time >= 0) {
            $filter_options = array('last_modified' => '>=' . $this->convertToLDAPTimestamp($mod_time));
        } else {
            $filter_options = array();
        }
        if ($parent) {
            $filter_options['parent'] = $parent;
        }
        $t_ids =  $this->getList($form,'populate',false,$filter_options, array('id'));
        $ids = array();
        foreach ($t_ids as $id) {
            if (!is_array($id) || !array_key_exists('id',$id)) {
                continue;
            }
            $ids[] = $id['id'];
        }
        return $ids;
    }



    protected function getEntryAttributes($connection,$entry,$attributes,$get_all_values) {
        $result = array();
        $allowed_attributes = ldap_get_attributes($connection,$entry);
        foreach ($attributes as $attribute_ref=>$attribute) {
            if (is_string($attribute)) {
                if (!array_key_exists($attribute,$allowed_attributes)) {
                    continue;
                }       
                $values  = ldap_get_values($connection,$entry,$attribute);                
                if (!is_array($values)) {
                    continue;
                }
                if ($get_all_values) {
                    unset($values['count']);
                    if ($attribute == 'modifyTimestamp' || $attribute == 'createTimestamp') {
                        foreach ($values as &$val) {
                            $val =   $this->convertToUnixTimestamp($val);
                        }
                        unset($val);
                    }

                    $result[$attribute_ref] = $values;
                } else {
                    if ($values['count'] < 1) {
                        continue;
                    }
                    if ($attribute == 'modifyTimestamp' || $attribute == 'createTimestamp') { 
                        $result[$attribute_ref] = $this->convertToUnixTimestamp( $values[0]);
                    } else {
                        $result[$attribute_ref] =  $values[0];
                    }
                }

                continue;
            } else if (!is_array($attribute)) {
                continue;
            } else   if (array_key_exists('eval',$attribute)  && is_string($attribute['eval'] = $attribute['eval']) && strlen($attribute['eval']) > 0) {
                $matches = array();
                preg_match_all('/\\$data\\[[\'"](.*?)[\'"]\\]/',$attribute['eval'],$matches,PREG_PATTERN_ORDER);
                if (!array_key_exists(1,$matches) || !  is_array($matches[1]) ) {
                    continue;
                }
                $matches = $matches[1]; //array of the LDAP attributes needed for eval
                if ($get_all_values) {
                    I2CE::raiseError("Getting all values on eval not implemented. Only returning most recent");
                }
                $data = array();
                foreach ($matches as $attr) {
                    $values  = ldap_get_values($connection,$entry,$attr);                
                    if (!is_array($values)) {
                        continue;
                    }
                    if ($values['count'] < 1) {
                        $data[$attr] = '';
                    } else {
                        if ($attr == 'modifyTimestamp' || $attr == 'createTimestamp') { 
                            $data[$attr] = $this->convertToUnixTimestamp($values[0]);
                        } else {
                            $data[$attr] = $values[0];
                        }
                    }
                }
                @eval('$result[$attribute_ref] = ' . $attribute['eval'] .';');
       
            } else  if (array_key_exists('printf',$attribute) && is_string($printf = $attribute['printf']) && strlen($printf) > 0
                        && array_key_exists('printf_args',$attribute) && is_array($printf_args = $attribute['printf_args']) && count($printf_args) > 0)  {
                if ($get_all_values) {
                    I2CE::raiseError("Getting all values on eval not implemented. Only returning most recent");
                }                
                foreach ($attribute['printf_args'] as $key=> $attr) {
                    $val = '';
                    $values  = ldap_get_values($connection,$entry,$attr);                
                    if (is_array($values) && $values['count'] >= 1) {
                        if ($attr == 'modifyTimestamp' || $attr == 'createTimestamp') { 
                            $data[$attr] = $this->convertToUnixTimestamp($values[0]);
                        } else {
                            $val  = $values[0];
                        }
                    }
                    $printf_args[$key] = $val;
                }
                ksort($printf_args);
                $result[$attribute_ref] = vsprintf($attribute['printf'],$printf_args);
            }
        }
        return $result;
    }


    /**
     * Return array which is a list of the specified typ
     * @param string $form
     * @param string $type
     * @param bool $get_all_values.  Defaults to false in which we only get the most recent value of each attribute  . If true we return the array of all values/
     * @param array $filter_options.  Defaults to empty array.  Keys are attribute references (same keys as under the attributes MDN) and values are the values of the attribute to limit to
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @return array or arrays.  Each sub-array is an array with keys attribute references and values dependent on {@get_all_values}. if the attribute is not set the attribute reference will not be present
     */
    protected function getList($form,$type,$get_all_values = false, $filter_options = array(), $attribute_refs = null, $limit = false )  {
        $results = array();
        if (!$connection = $this->getConnection($form)) {
            I2CE::raiseError("No connection");
            return $results;
        }
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid storage options for $form" );
            return $results;
        }
        $base_dn = null;
        $filter = null;
        if ( !$options->setIfIsSet( $base_dn, "list/" . $type . "/base_dn" ) ||  strlen((string)$base_dn) == 0) {
            I2CE::raiseError("No base_dn  set under " . $options->getPath(false) . '/list/' . $type . '/base_dn');
            return $results;
        }        
        $attributes = array();
        $attribute_printfs = array();
        $options->setIfIsSet($attribute_printfs,"list/" . $type . "/attribute_printfs",true);
        if ( !$options->setIfIsSet( $attributes, "list/" . $type . "/attributes" ,true) || ! is_array($attributes) || count($attributes) == 0) {
            I2CE::raiseError("No attributes  set under " . $options->getPath(false) . '/list/' . $type . '/attributes');
            return $results;
        }   
        if (!array_key_exists('last_modified',$attributes)) {
            $attributes['last_modified'] = array('eval'=>'date("Y-m-d H:m:s",$data["modifyTimestamp"]);');
        }
        if (!array_key_exists('created',$attributes)) {
            $attributes['created'] = array('eval'=>'date("Y-m-d H:m:s",$data["createTimestamp"]);');
        }
        if ( !$options->setIfIsSet( $filter, "list/" . $type . "/filter" ) ||  strlen((string)$filter) == 0) {
            I2CE::raiseError("No filter  set under " . $options->getPath(false) . '/list/' . $type . '/filter');
            return $results;
        }
        $filters = array($filter);
        if (!is_array($filter_options)) {
            $filter_options = array();
        }
        $parent_filter = false;
        if (array_key_exists('parent',$filter_options)) {
            $parent_filter = $filter_options['parent'];
            unset($filter_options['parent']);
        }

        foreach ($filter_options as $attribute_ref=>$val) {
            if ($attribute_ref == 'where_data') {
                $filters[] = $val;
            } else {
                if (!array_key_exists($attribute_ref,$attributes)) {
                    I2CE::raiseError("Filter reference $attribute_ref is not found in " . implode(",", array_keys($attributes)));
                    return $results;
                }
                $filters[] = $attributes[$attribute_ref] .  $this->ldap_escape($val);
            }
        }
        if (count($filters) == 1) {
            reset($filters);
            $filter = current($filters);
        } else {
            foreach ($filters as &$filter) {
                $filter = trim($filter);
                while (strlen($filter) > 1 && $filter[0] == '(' && $filter[strlen($filter)-1] == ')') {
                    $filter = trim(substr($filter,1,-1));
                }
            }
            unset($filter);
            $filter =  '(&(' . implode(')(',$filters) . '))';
        }
        I2CE::raiseError("Using filter = " . $filter);
        $parent = false;
        if (is_array($attribute_refs)) {
            $t_attributes = array();
            foreach ($attribute_refs as $attribute_ref) {
                if (!array_key_exists($attribute_ref,$attributes)) {
                    continue;
                }
                $t_attributes[$attribute_ref] = $attributes[$attribute_ref];
            }
            $attributes = $t_attributes;
        }
        if (array_key_exists('parent',$attributes)) {
            $parent = $attributes['parent'];
            unset($attributes['parent']);
        }

        $limit_offset = 0;
        $limit_amount = -1;
        if (is_numeric($limit)) {
            $limit_offset = max(0,(int) $limit);
        } else if  (is_array($limit) && count($limit) == 2) {
            list($limit_offset, $limit_amount) = $limit;
            $limit_offset = max(0,$limit_offset);
            $limit_amount = max(-1,$limit_offset);            
        }

        $scope = 'onelevel';
        $options->setIfIsSet($scope,"list/$type/scope");
        $scope = strtoupper($scope);
               
        if ($parent_filter) {
            //there is a parent filter.  We need to get a new base dn to perform our search on
            if ( !$parent) {
                I2CE::raiseError("Trying to filter the form $form on parent which is not a defined attribute ($parent_filter)");
                return $results;
            }

            $p_base_dn = null;
            $p_filter = null;
            $p_attribute = null;
            $p_attribute_printf = false;
            if ( !$options->setIfIsSet( $p_base_dn, "list/" . $type . "/parent/base_dn" ) ||  strlen((string)$p_base_dn) == 0) {
                I2CE::raiseError("No parent base_dn  set under " . $options->getPath(false) . '/list/' . $type . '/parent/base_dn');
                return $results;
            }        
            if ( !$options->setIfIsSet( $p_filter, "list/" . $type . "/parent/filter" ) ||  strlen((string)$p_filter) == 0) {
                I2CE::raiseError("No parent filter  set under " . $options->getPath(false) . '/list/' . $type . '/parent/filter');
                return $results;
            }
            if ( !$options->setIfIsSet( $p_attribute, "list/" . $type . "/parent/attribute" ) ||  strlen((string)$p_attribute) == 0) {
                I2CE::raiseError("No parent attribute  set under " . $options->getPath(false) . '/list/' . $type . '/parent/attribute');
                return $results;
            }

            $p_filter = '(&(' . $p_filter .')(' . $p_attribute . $parent_filter . '))';
            $p_filter =  $p_attribute . $parent_filter;
            
            if (  ! ($sr = @ldap_list(  $connection, $p_base_dn , $p_filter,array()))) {
                I2CE::raiseError("Cannot lst parent");
                return $results;
            }
            if ( !(  $p_entry = @ldap_first_entry($connection,$sr))) {
                I2CE::raiseError("Cannot list parent -- no entry");
                return $results;
            }
            //we found the parent entry and we have to do the search relative to this node
            $base_dn = ldap_get_dn($connection,$p_entry);
        }
        

        $needed_attributes = array();
        foreach ($attributes as $attribute_ref => $attribute_data) {
            if (is_string($attribute_data) && strlen($attribute_data= trim($attribute_data)) > 0) {
                $needed_attributes[] = $attribute_data;
                continue;
            } else  if (!is_array($attribute_data)) {
                continue;
            } else   if (array_key_exists('eval',$attribute_data)
                && is_string($attribute_data['eval'])
                && strlen($attribute_data['eval']  =trim($attribute_data['eval'] )) > 0) {
                $matches = array();
                preg_match_all('/\\$data\\[[\'"](.*?)[\'"]\\]/',$attribute_data['eval'],$matches,PREG_PATTERN_ORDER);
                if (array_key_exists(1,$matches) &&  is_array($matches[1])) {
                    foreach ($matches[1] as $match) {
                        if (!is_string($match) || strlen($match = trim($match)) == 0) {
                            continue;
                        }
                        $needed_attributes[] = $match;
                    }
                }
            } else if (
                array_key_exists('printf',$attribute_data) 
                && is_string($attribute_data['printf'])
                && strlen($attribute_data['printf'] = trim($attribute_data['printf'])) > 0
                && array_key_exists('printf_args',$attribute_data) 
                && is_array($attribute_data['printf_args'])
                && count($attribute_data['printf_args']) > 0) {
                foreach ($attribute_data['printf_args'] as $printf_arg) {
                    if (!is_string($printf_arg) || strlen($printf_arg = trim($printf_arg)) == 0) {
                        continue;
                    }
                    $needed_attributes[] = $printf_arg;
                }
            }
        }
        $needed_attributes = array_unique($needed_attributes);
        
        
        switch ($scope) {
        case 'SUBTREE':
            $r1 = @ldap_search(  $connection, $base_dn , $filter,array_values($needed_attributes));
            break;
        case 'BASE':
            $r1 = @ldap_read(  $connection, $base_dn , $filter,array_values($needed_attributes));
            break;
        default:
            $r1 = @ldap_list(  $connection, $base_dn , $filter,array_values($needed_attributes));
            break;
        }

        if  ( !($r1 )) {
            return $results;
        }      
        $entry = ldap_first_entry($connection,$r1);
        $e = 0;
        $count = 0;
        while (is_resource($entry) && $limit_offset > 0) {            
            $entry = ldap_next_entry($connection,$entry);
            $limit_offset--;
        }
        if (!is_resource($entry)) {
            return $results;
        }
        while ($entry) {
            $result = $this->getEntryAttributes($connection,$entry,$attributes,$get_all_values);
            $entry_dn = ldap_get_dn($connection,$entry);
            if ($parent ) {
                $parent_path = explode('/',$parent);
                $parent_dn = ldap_explode_dn($entry_dn,0);
                unset($parent_dn['count']);
                $parent_attr = false;
                foreach ($parent_path as $path) {
                    if ($path == '' || $path == '.') {
                        continue;
                    } else if ($path == '..') {
                        array_shift($parent_dn);
                    } else {
                        $parent_attr = $path;
                        break;
                    }
                }
                if ($parent_attr ) {
                    
                    //$result['parent'] = implode(',',$parent);
                    $parent_dn = implode(",",$parent_dn);
                    if (  ($sr = @ldap_read($connection,$parent_dn,"(objectclass=*)",array($parent_attr))) &&
                          ($p_entry = ldap_first_entry($connection,$sr))) {                  
                        $p_result = $this->getEntryAttributes($connection,$p_entry,array('parent'=>$parent_attr),false);
                        if (array_key_exists('parent',$p_result)) {
                            $result['parent'] = $p_result['parent'];
                        }
                    }
                }
            }

            foreach ($result as $attribute_ref => &$dbval) {
                if (!array_key_exists($attribute_ref,$attribute_printfs)) {
                    continue;
                }
                $dbval = sprintf($attribute_printfs[$attribute_ref],$dbval);
            }
            unset($dbval);
            $result['dn'] = $entry_dn     ;
            $results[] = $result;
            if (($limit === true) 
                || ($limit_amount > 0 && count($results) >= $limit_amount)) {
                break; 
            }
            $entry = ldap_next_entry($connection,$entry);
        }
        return $results;
    } 
    


    protected function convertToLDAPTimestamp($unix_stamp) {
        $date_format = "YmdHise";
        try {
            $time = new DateTime('@' . $unix_stamp);
        } catch (Exception $e) {
            I2CE::raiseError("Invalid unix stamp $unix_stamp");
            return false;
        }
        $time->setTimeZone(new DateTimeZone('GMT'));
        return  substr($time->format($date_format),0,-3)  . 'Z';
    }

    protected function convertToUnixTimestamp($ldap_stamp) {
        $date_format = "YmdHise";
        if (!is_string($ldap_stamp) || strlen($ldap_stamp) < 1) {
            I2CE::raiseError("Invalid ldap_stamp $ldap_stamp");
            return false;
        }
        $ldap_stamp = substr($ldap_stamp,0,-1) . 'GMT';
        if (! ($parsed = DateTime::createFromFormat($date_format,$ldap_stamp)) instanceof DateTime) {
            I2CE::raiseError("Bad ldap_stamp $ldap_stamp");
            return false;
        }
        return $parsed->getTimestamp();
    }

    protected function createFilter($where_data,$attributes) {
        if (!is_array($where_data) || count($where_data) == 0 || !array_key_exists('operator',$where_data)) {
            throw new Exception("Invalid Operator:" . print_r($where_data,true));
        }
        if ($where_data['operator']  == 'FIELD_LIMIT') {
            if ( array_key_exists('field',$where_data) 
                 && is_string($where_data['field'])
                 && $where_data['field'] != 'parent'
                 && array_key_exists($where_data['field'],$attributes)
                 && array_key_exists('data',$where_data)
                 && is_array($where_data['data'])
                 && count($where_data['data']) == 1) {
                if ($where_data['style']  == 'equals' || $where_data['style'] == 'lowerequals') {
                    return $attributes[$where_data['field']]  .'='  .$this->ldap_escape(current($where_data['data']));
                } else if ($where_data['style'] == 'contains') {
                    return $attributes[$where_data['field']]  .'=*'  . $this->ldap_escape(current($where_data['data'])) . '*';
                }
            }
            throw new Exception("Invalid field limit filter from" . print_r($where_data,true));            
        } 
        if (!array_key_exists('operand',$where_data) || !is_array($where_data['operand'])) {
            return false;
        }
        $filters = array();
        foreach ($where_data['operand'] as $sub_where_data) {
            if (! ($filter = $this->createFilter($sub_where_data,$attributes))) {
                //might have been blank/empty
                continue;
            }
            $filters[] = $filter;
        }
        //ldap does not like many parens
        foreach ($filters as &$filter) {
            $filter = trim($filter);
            while (strlen($filter) > 1 && $filter[0] == '(' && $filter[strlen($filter)-1] == ')') {
                $filter = trim(substr($filter,1,-1));
            }
        }
        unset($filter);

        if ($where_data['operator']  == 'AND') {
            if (count($filters) == 0) {
                return false;
            }
            if (count($filters) == 1) {
                return current($filters);
            }
            return '(&(' .implode(')(',$filters) . '))';
        }
        if ($where_data['operator']  == 'OR') {
            if (count($filters) == 0) {
                return false;
            }
            if (count($filters) == 1) {
                return current($filters);
            }
            return '(|(' .implode(')(',$filters) . '))';
            
        }
        if ($where_data['operator']  == 'NOT') {
            if (count($filters) != 1) {
                throw new Exception ("Not operator applied to more than one result");
            }
            return '(!' . current($filters) . ')';
        }
        throw new Exception("Invalid Operator:" . print_r($where_data,true));
    }

    
    /**
     * @param string $form  The form name.
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id.
     * @param mixed $where_data array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @returns mixed an array of matching form ids.  However, ff $limit_one is true or 1 or 
     * array ($offset,1) then then we return either the id or false,  if none found or there was an error.
     */
    public  function search($form, $parent=false, $where_data=array(), $ordering=array(), $limit = false) {                                      
        $list = $this->listFields($form,array(),$parent,$where_data,$ordering,$limit);
        $limit_one = false;
        if (is_array($limit)) {
            if (count($limit) == 2) {
                end($limit);
                if (current($limit) == 1) {
                    $limit_one = true;
                }
            }
        } else {
            $limit_one = (($limit === true ) || (is_numeric($limit) && $limit == 1 ));
        }
        reset($list);
        if ($limit_one === true ) {
            if (count($list) != 1) {
                return false;
            } else {
                return key($list);
            }
        } else {
            return array_keys($list);
        }

    }

    /**
     * @param string $form.  THe form name
     * @param array $fields of string. The fields we want returned.  Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id. If true, we return the parent as one of the fields.
      *@param array $where_data.  contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.
     */
    public  function listFields($form, $fields, $parent = false, $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1) { 
        $filter_options = array();
        if ($parent === true) {
            if (!in_array('parent',$fields)) {
                $fields[] = 'parent';
            }            
        } else if (is_string($parent) && strlen($parent) > 0) {
            $filter_options['parent'] = '=' . $parent;
        }
        if (!in_array('id',$fields)) {
            $fields[] = 'id';
        }            
        if ($mod_time > 0) {
            $filter_options['last_modified'] = '>=' . $this->convertToLDAPTimestamp($mod_time);
        }
        if (is_array($where_data) && count($where_data) > 0) {
            $options = $this->getStorageOptions($form);
            if ( !$options instanceof I2CE_MagicDataNode ) {
                I2CE::raiseError( "Invalid storage options for $form" );
                return array();
            }
            $attributes = array();
            if ( !$options->setIfIsSet( $attributes, "list/populate/attributes" ,true) || ! is_array($attributes) || count($attributes) == 0) {
                I2CE::raiseError("No attributes  set under " . $options->getPath(false) . '/list/' . $type . '/attributes');
                return array();
            }   
            try {
                $where_filter = $this->createFilter($where_data,$attributes);
                I2CE::raiseError("Created filter " . $where_filter . "\nfrom:" . print_r($where_data,true));
                $filter_options['where_data'] = $where_filter; 
                $where_data = array();
            } catch (Exception $e) {
                I2CE::raiseError("Could not create specialized filter: " . $e->getMessage());
            }
        }
        $t_list =  $this->getList($form,'populate',false,$filter_options, $fields, $limit );
        I2CE::raiseError(print_r($t_list,true));
        $list = array();
        foreach ($t_list as $data) {
            if (!is_array($data) || !array_key_exists('id',$data)) {
                continue;
            }
            $id = $data['id'];
            unset($data['id']);
            unset($data['dn']);
            $list[$id] = $data;
        }
        if (is_array($where_data) && count($where_data) > 0) {
            $ff = I2CE_FormFactory::instance();
            if  ( ! ($formObj  = $ff->createContainer($form)) instanceof  I2CE_Form 
                  ||   !@is_callable($checkFunc = $formObj->createCheckFunction($where_data))) {
                I2CE::raiseError("cannot create check function from:" . print_r($where_data,true));
                return array();
            }
            foreach ($list as $id=>$data) {
                if (!call_user_func($checkFunc,$data)) {                    
                    unset($list[$id]);                    
                }
            }
        }
        if (is_array($ordering) && count($ordering) > 0) {
            I2CE::raiseError("ordering data not supported");
            //do something
        }
        return $list;
    }


    /**
     * Populate the member variables of the object from the database.
     * @param I2CE_Form $form
     */
    public function populate( $formObj) {
        $formName = $formObj->getName();
        $id = $formObj->getId();
        $vals =  $this->getList($formName,'populate',false,array('id'=>'=' . $id));
        if (!is_array($vals) || count($vals) != 1) {
            return false;
        }
        $vals = current($vals);
        foreach ($formObj->getFieldNames() as $field) {
            if (!array_key_exists($field,$vals)) {
                continue;
            }
            $dbval = $vals[$field];            
            if ($field == 'parent') {
                continue;
            } 
            if (! ($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField) {
                continue;
            }
            $fieldObj->setFromDB($dbval);        
        }
        if (array_key_exists('parent',$vals) && $vals['parent'] != '0' ) {
            $formObj->setParent($vals['parent']);
        }
        $formObj->setAttribute('ldap_dn', $vals['dn']);
        return true;
    
    }
    

    /**
     * Gets the id's for the given child for this form.
     * @param string $form_name
     * @param   mixed $parent_form_id the prent form id
     * @param  array/string: an optional orderBy array of fields
     * @param array  where
     * @param integer: A limit of the number of children ids to return
     * @return array
     */
    public function getIdsAsChild($form_name, $parent_form_id,$order_by, $where, $limit) { 
        list($parent_form,$parent_form_id) =array_pad(explode("|",$parent_form_id,2),2,'');
        $vals =  $this->getList($form_name,'populate',false,array('parent'=>'=' . $parent_form_id), array('id','parent'),$limit);
        $ids = array();
        foreach ($vals as $val) {
            if (!is_array($val) || !array_key_exists('id',$val)) {
                continue;
            }
            $ids[] = $val['id'];
        }
        
        return $ids;
    }
    

    /**
     * Checks to see if this storage mechansim implements the writing methods.
     * You need to override this in a subclass that implements writable
     * @returns boolean
     */
    public function isWritable() {
        return true;
    }




    /**
     * Save a form object into entry tables.
     * If this functio is over-written, it should include the fuzzy method call
     * foreach ($form as $field) {
     *      $field->save(true/false, $user)
     * }
     * 
     * See compatibility issue: http://www.php.net/manual/en/function.ldap-rename.php#57521 
     *
     * 
     * 
     * @param I2CE_Form $form
     * @param I2CE_User $user
     * @param boolean $transact
     */
    public function save( $formObj, $user, $transact ) {        
        $formName = $formObj->getName();
        $id = $formObj->getId();
        if (!$connection = $this->getConnection($formName)) {
            I2CE::raiseError("No connection");
            return false;
        }
        $options = $this->getStorageOptions($formName);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid storage options for $formName" );
            return false;
        }
        $base_dn = false;
        $parent_dn = false;
        $printf =false;
        $printf_args =false;        
        $options->setIfIsSet($base_dn,"save/dn");
        $options->setIfIsSet($parent_dn,"save/parent_dn");
        if (!$options->setIfIsSet($printf,"save/rdn/printf")) {
            I2CE::raiseError("No printf");
            return false;
        }
        //need to get the read id attribute
        $read_id =false;
        if ( !  $options->setIfIsSet($read_id,"list/populate/attributes/id")) {
            I2CE::raiseError("No read id attribute set");
            return false;
        }
        $objectClass =false;
        if ( !  $options->setIfIsSet($objectClass,"save/objectClass")) {
            I2CE::raiseError("No object class attribute set");
            return false;
        }

        if (!$options->setIfIsSet($printf_args,"save/rdn/printf_args",true)) {
            I2CE::raiseError("No printf args");
            return false;
        }

        ksort($printf_args);
        $printf_vals = array();
        foreach ($printf_args as $arg=>$field) {
            if ( ($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField) {
                $val = $fieldObj->getDBValue();
            } else {
                $val = '';
            }
            $printf_vals[$arg] = $this->ldap_escape($val);
        }
        $old_dn = $formObj->getAttribute('ldap_dn');
        //if $parent_dn is true, we need to look at the parent form and see if it is stored in LDAP, then set the DN from that.
        if ($parent_dn) {
            $parent_dn = 'NLAH'; 	//example:  cn=Caij Sluvothaecre+nid=3679883,ou=Providers, dc=moh, dc=gov, dc=rw 
            if (! ($parentFormObj = I2CE_FormFactory::instance()->createContainer($formObj->getParent())) instanceof I2CE_Form) {
                I2CE::raiseError("Trying to save a child node in LDAP where parent is not in LDAP");
                return false;
            }
            $parentFormObj->populate();
            if ( ! ($base_dn = $parentFormObj->getAttribute('ldap_dn'))) {
                I2CE::raiseError("No DN for parent");
                return false;
            }
        }
        if (!$base_dn) {
            I2CE::raiseError("No base dn is set");
            return false;
        }
        $dn =  vsprintf($printf, $printf_vals) .','. $base_dn;        

        $attributes = array();
        if ( !$options->setIfIsSet( $attributes, "save/attributes" ,true) || ! is_array($attributes) || count($attributes) == 0) {
            I2CE::raiseError("No attributes  set under  " . $options->getPath(false) . '/save/attributes');
            return false;
        }   
        $details = array();
        foreach ($attributes as $attribute=>$attribute_def) {
            $val = false;
            if (is_string($attribute_def)) {
                if ( ($fieldObj = $formObj->getField($attribute_def)) instanceof I2CE_FormField) {
                    $val = $fieldObj->getDBValue();
                }
            } else if (!is_array($attribute_def)) {
                continue;
            } else   if (array_key_exists('eval',$attribute_def)  && is_string($attribute_def['eval'] = $attribute_def['eval']) && strlen($attribute_def['eval']) > 0) {
                $data = array();
                foreach ($formObj->getFieldNames() as $field) {
                    if (! ($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField) {
                        $data[$field] = null;
                        continue;
                    }
                    $data[$field] = $fieldObj->getDBValue();            
                }
                @eval('$val = ' . $attribute_def['eval'] .';');
            } else  if (array_key_exists('printf',$attribute_def) && is_string($printf = $attribute_def['printf']) && strlen($printf) > 0
                        && array_key_exists('printf_args',$attribute_def) && is_array($printf_args = $attribute_def['printf_args']) && count($printf_args) > 0)  {
                $printf_vals  = array();
                foreach ($printf_args as $arg=>$field) {
                    if ( ($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField) {
                        $dbval = $fieldObj->getDBValue();
                    } else {
                        $dbval = '';
                    }
                    $printf_vals[$arg] = $dbval;
                }
                $val =  vsprintf($printf, $printf_vals);        
            }
            if ($val === false || !is_scalar($val) ||  (is_string($val) && strlen(trim($val)) == 0) ) {
                continue;
            }
            $details[$attribute] = $val;
        }


        
        if ($id != '0') {
            if ($old_dn != $dn) {
                $new_basedn  = ldap_explode_dn($dn,0);
                unset($new_basedn['count']);
                $new_rdn = array_shift($new_basedn);
                $new_basedn = implode(",",$new_basedn);
                if ( !@(ldap_rename($connection,$old_dn,$new_rdn,$new_basedn,false))) {                     
                    I2CE::raiseError("Could not rename $old_dn to $dn with $new_rdn  and $new_basedn");
                    return false;
                }
            }
            if (!@ldap_modify($connection,$dn,$details)) {
                I2CE::raiseError("Could not modify $dn with detail: " .print_r($details,true));
                return false;
            }       
        } else {
            $details['objectClass'] = $objectClass;
            if (!@ldap_add($connection,$dn,$details)) {
                I2CE::raiseError("Could not add  $dn with detail: " .print_r($details,true));
                return false;
            }                    

            $r1 = @ldap_read(  $connection, $dn , 'objectClass=' . $objectClass ,array($read_id));
            if  ( !($r1 )) {
                I2CE::raiseError("Could not read newly saved form under $dn");
                return false;
            }      
            if ( ! ($entry = ldap_first_entry($connection,$r1))) {
                I2CE::raiseError("no entry under $dn for reading id after save");
                return false;
            }
            $result = $this->getEntryAttributes($connection,$entry,array('id'=>$read_id),false);
            if (!array_key_exists('id',$result) || !$result['id']) {
                I2CE::raiseError("Could not read id attribute $read_id after save");
                return false;
            }
            $formObj->setId($result['id']);
        }
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
