<?php

namespace opensooq\webservice;

class XML2Array {
    
    private static $xml = null;
    private static $encoding = 'UTF-8';
    
    /**
     * Initialize the root XML node [optional]
     * @param $version
     * @param $encoding
     * @param $format_output
     */
    public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
        self::$xml = new \DOMDocument($version, $encoding);
        self::$xml->formatOutput = $format_output;
        self::$encoding = $encoding;
    }
    
    /**
     * @param $xmlString
     * @param bool $tagName
     * @param bool $elementCount
     * @return array
     */
    public static function createArray($xmlString, $tagName = false, $elementCount = false)
    {
        $doc = new \DOMDocument();
        try {
            $doc->loadXML($xmlString);
        } catch (Exception $exc) {
            return false;
        }
        $result = [];
        
        if (is_string($tagName)) {
            $nodes = $doc->documentElement->getElementsByTagName($tagName);
            if (false == $elementCount) {
                $elementCount = $nodes->length;
            }
            for ($i = 0; $i < $elementCount; $i++) {
                $result[] = self::domNodeToArray($nodes->item($i));
            }
        } else {
            $result = self::domNodeToArray($doc->documentElement);
        }
        return $result;
    }
    
    /**
     * @param DOMNode $node
     * @return array|string
     */
    protected static function domNodeToArray(\DOMNode $node)
    {
        $output = [];
        if (!isset($node->nodeType)) return $output;
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = self::domNodeToArray($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        $tExploded = explode(':', $t);
                        $t = isset($tExploded[1]) ? $tExploded[1] : $t;
                        if (!isset($output[$t])) {
                            $output[$t] = [];
                        }
                        $output[$t][] = $v;
                    } elseif ($v || $v === '0') {
                        $output = (string)$v;
                    }
                }
                if ($node->attributes->length && !is_array($output)) { // Has attributes but isn't an array
                    $output = ['@content' => $output]; // Change output into an array.
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string)$attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }
    

    
}