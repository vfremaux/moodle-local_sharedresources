<?php

class SimpleXmlDeserializer {
    var $xml;
    var $document;
    var $current;
    var $parentstack;
    
    var $parser;
    
    function __construct(){
        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "xml_start_element", "xml_end_element");
        xml_set_character_data_handler($this->parser, "xml_data");    
        $this->current = &$this->document;
        $this->parentstack = array();
    }
    
    function parse($xml){
        xml_parse($this->parser, $xml);
        return $this->document;
    }
    
    function xml_start_element($parser, $name, $attrs){
        $obj = array();
        $obj['attributes'] = $attrs;

        $this->current[$name][] = &$obj;
        $this->parentstack[] = &$this->current;
        $this->current = &$obj;
    }

    function xml_end_element($parser, $name){
        if (!preg_match("/\\S/", $this->current['value'])) $this->current['value'] = '';
        $this->current = &$this->parentstack[count($this->parentstack) - 1];
        unset($this->parentstack[count($this->parentstack) - 1]);
        $this->parentstack = array_values($this->parentstack);
    }
    
    function xml_data($parser, $data){
        $this->current['value'] = @$this->current['value'] . $data;
    }
    
    
}

?>