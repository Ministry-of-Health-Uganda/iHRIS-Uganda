<?php


class form_relationship_import_base_excel_2004_xml {

/* Set defaults*/
    var $indices = false; //will be array for header row fields indices
    var $mappings = array(); //remapping for column values
    var $max = -1;
    var $skip = 0;
    var $file = 'php://stdin';


    function do_indices($node) {
        $this->indices =array();
        $doc = new DOMDOcument();
        $i_node = $doc->importNode($node,true);
        $xpath = new DOMXPath($doc);
        $count = 0;
        foreach ($i_node->childNodes as $cell) {
            if (!$cell instanceof DOMElement
                || $cell->tagName != 'Cell'
                ) {
                //could be text node with whitespace
                continue;
            }
            $count++;
            $header = $cell->textContent;
            $this->indices[$header]= $count;
        }
    }

    //This function has only been tested on a microsoft xml export on osx
    //should really be defined per document type.
    function get_field($field,$row) {
        if (!array_key_exists($field,$this->indices)) {
            return null;
        }
        $index = $this->indices[$field];
        $count  = 0; 
        $doc = new DOMDocument();
        $i_row = $doc->importNode($row,true);
        foreach ($i_row->childNodes as $cell) {
            if (!$cell instanceof DOMElement
                || $cell->tagName != 'Cell'
                ) {
                //could be text node with whitespace
                continue;
            }
            $count++;
            if ($cell->hasAttribute('ss:Index')) {
                //excel skips empty columns
                $count = $cell->getAttribute('ss:Index');
            }
            if ($count == $index) {
                $val = $cell->textContent;
                if (array_key_exists($field,$this->mappings)
                    && is_array($this->mappings[$field])
                    && array_key_exists($val,$this->mappings[$field])
                    ) {
                    $val = $this->mappings[$field][$val];
                }
            
                return htmlspecialchars($val);
            }
            
        }
        return null;
    }

    function transform_row() {
        error_log("empty transform:  you probably should extend the form_relationship_import_base_excel_2004_xml class");
        //do nothing.  class needs to be extended
    }
    var $invalids =array();

    var $lookup_cache= array();

    function find_row_by_value($field,$field_value) {
        if (!array_key_exists($field,$this->lookup_cache)) {
            error_log("Populating lookup cache for field $field");
            $this->lookup_cache[$field] = array();

            $this->indices =false;
            if (!is_readable($this->file)) {
                error_log("unreadable file: "  . $this->file);
                return false;
            }
            $reader = new XMLReader;
            $reader->open($this->file, null, 1<<19);
            $this->indices = false;
    
            $c = 0;
            $found_row = false;
        
            while( (!$found_row && $reader->read()) || ($found_row && $reader->next())) {
                if ( !$reader->nodeType == XMLReader::ELEMENT
                     || ! ($reader->name == 'Row')
                    ){
                    continue;
                }
                $found_row = true;
                if (is_array($this->indices) && $this->skip > 0) {
                    $this->skip--;
                    continue;
                }
                $row = $reader->expand();
                if (!is_array($this->indices)) {
                    $this->do_indices($row);                
                } else {
                    $found = $this->get_field($field,$row);
                    $this->lookup_cache[$field][$found] = $row;
                }
                $reader->next();
            }
            $reader->close();
            error_log("Done populating lookup cache for field $field");
        }
        if (array_key_exists($field_value,$this->lookup_cache[$field])) {
            error_log("Using cached lookup for $field_value");
            return $this->lookup_cache[$field][$field_value];
        }
        return false;
    }

    function transform($out_stream = 'php://stdout') {
        $this->indices =false;
        if (!is_readable($this->file)) {
            error_log("unreadable file: " . $this->file);
            return false;
        }
        if (!($out= fopen($out_stream,"w"))) {
            error_log("unwritable file: " . $out_stream);
            return false;
        }
        
        $import_name = get_class($this);
        if (count($this->invalids) > 0) { 
            $header = "<?xml version='1.0'?>
<relationshipCollection name='$import_name' invalid='" . implode(',', $this->invalids) . "'>
";
        } else {
            $header = "<?xml version='1.0'?>
<relationshipCollection name='$import_name'>
";
        }
        fwrite($out,$header);

        $reader = new XMLReader;
        $reader->open($this->file, null, 1<<19);
        $this->indices = false;
    
        $c = 0;
        $found_row = false;
        while( (!$found_row && $reader->read()) || ($found_row && $reader->next())) {
            if ( !$reader->nodeType == XMLReader::ELEMENT
                 || ! ($reader->name == 'Row')
                ){
                continue;
            }
            $found_row = true;
            if (is_array($this->indices) && $this->skip > 0) {
                $this->skip--;
                continue;
            }
            $row = $reader->expand();
            if (!is_array($this->indices)) {
                $this->do_indices($row);
            } else {
                fwrite($out,$this->transform_row($row));
                $c++;
                error_log("Transformed record $c");
                if ($this->max > 0 && $c >= $this->max) {
                    break;
                }
            }
            $reader->next();
        }
        $footer = "
</relationshipCollection> 
";
        fwrite($out,$footer);


    }

}