<?php
/**
 * @package Reports
 * @subpackage Generators
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Report generator for JSON output.
 *
 * @package Reports
 * @subpackage Generators
 */
class Reports_Generator_Json 
    extends Reports_Generator
    implements Reports_GeneratorInterface
{
    /**
     * The file handle to output to
     *
     * @var resource
     */
    private $_file;
    
    /**
     * Creates and generates the JSON report for the items in the report.
     *
     * @param string $filePath
     */
    public function generateReport($filePath) {
        $this->_file = fopen($filePath, 'w');
        ob_start(array($this, '_fileOutputCallback'), 1);
        $this->outputJSON();
        ob_end_flush();
        fclose($this->_file);
        return $filePath;
    }
    
    /**
     * Callback to redirect PHP output to a file, using output buffering.
     *
     * @param string $buffer The data to be output
     */
    private function _fileOutputCallback($buffer) {
        fwrite($this->_file, $buffer);
    }
    
    /**
     * Generates the JSON document for the report.
     */
    private function outputJSON() {
        $metadata = array();
        $page = 1;
        while ($items = get_db()->getTable('Item')->findBy($this->_params, 30, $page) {
          foreach ($items as $item) {
            $item_metadata = array();
            $sets = get_db()->getTable('ElementSet')->findByRecordType('Item');
            foreach ($sets as $set) {
                $item_metadata = array_merge(
                    $item_metadata,
                    $this->_getSetElements($item, $set->name)m
                );
            }
            $metadata[] = $item_metadata;
            release_object($item);
          }
          $page++;
        }
        print json_encode($metadata);
    }

    /**
     * Returns the elements from the given item in the given element set
     * and optionally specific element names.
     * If no element names are specified, all the elements in the set will be
     * output.
     *
     * @param Item $item The item whose metadata to output
     * @param string $setName The name of the set to output metadata for
     * @param array $elementNames Array of element names to be output
     */

    private function _getSetElements($item, $setName, $elementNames = null) {
        if (!isset($elementNames) or !is_array($elementNames)) {
            $elementNames = $item->getElementsBySetName($setName);
        }
        $metadata = array();
        foreach ($elementNames as $elementName) {
            $texts = $item->getElementTexts($setName, $elementName);
            foreach ($texts as $text) {
                $metadata[$elementName] = $text->text;
            }
        }
        return $metadata;
    }

    /**
     * Returns the readable name of this output format.
     *
     * @return string Human-readable name for output format
     */
    public static function getReadableName() {
        return 'JSON';
    }
    
    /**
     * Returns the HTTP content type to declare for the output format.
     *
     * @return string HTTP Content-type
     */
    public function getContentType() {
        return 'application/json';
    }
    
    /**
     * Returns the file extension to append to the generated report.
     *
     * @return string File extension
     */
    public function getExtension() {
        return 'json';
    }
}
