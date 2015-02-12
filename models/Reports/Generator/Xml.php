<?php
/**
 * @package Reports
 * @subpackage Generators
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Report generator for XML output.
 *
 * @package Reports
 * @subpackage Generators
 */
class Reports_Generator_Xml 
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
     * Creates and generates the XML report for the items in the report.
     *
     * @param string $filePath
     */
    public function generateReport($filePath) {
        $this->_file = fopen($filePath, 'w');
        ob_start(array($this, '_fileOutputCallback'), 1);
        $this->outputXML();
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
     * Generates the XML document for the report.
     */
    private function outputXML() { 
        $reportName = $this->_report->name;
        $reportDescription = $this->_report->description;
        ?>
<?xml version="1.0"?>
<ROOT>
<?php $page = 1;
    while ($items = get_db()->getTable('Item')->findBy($this->_params, 30, $page)):
        foreach ($items as $item) : ?>
        <record id="<?php echo $item->id; ?>" dt="<?php echo date('Y-m-d H:i:s O') ?>">
<?php       $sets = get_db()->getTable('ElementSet')->findByRecordType('Item');
            // Output all the metadata for all the element sets
            foreach($sets as $set) :
                $this->_outputSetElements($item, $set->name);
            endforeach;
            $tags = $item->getTags(); 
            if (count($tags)): ?>
<?php echo implode($tags, ', '); ?>

<?php       endif; ?>

<?php       release_object($item); 
        endforeach;
        $page++;
    endwhile; ?>
</record>
</ROOT>
<?php
    }
    
    /**
     * Prints XML for the elements from the given item in the given element set
     * and optionally specific element names.
     * If no element names are specified, all the elements in the set will be
     * output.
     *
     * @param Item $item The item whose metadata to output
     * @param string $setName The name of the set to output metadata for
     * @param array $elementNames Array of element names to be output
     */
    private function _outputSetElements($item, $setName, $elementNames = null)
    {
        $outputTexts = array();
        
        if (!$elementNames) {
            $elements = $item->getElementsBySetName($setName);
            foreach ($elements as $element) {
                foreach ($item->getElementTexts($setName, $element->name) as $text) {
                    $outputTexts[] = array('element' => $element->name,
                                           'text'    => $text->text);
                }
            }
        } else if (is_array($elementNames)) {
            foreach ($elementNames as $elementName) {
                $texts = $item->getElementTexts($setName, $elementName);
                foreach ($texts as $text) {
                    $outputTexts[] = array('element' => $elementName,
                                           'text'    => $text->text);
                }
            }
        }
        
        if (count($outputTexts)) {
            echo "<h3>$setName</h3>";
            echo '<table class="element-texts" cellpadding="0" cellspacing="0">';
            foreach($outputTexts as $outputText) {
                echo '<tr class="element">'
                   . '<th scope="row" class="element-name">'
                   . $outputText['element']
                   . '</th>'
                   . '<td class="element-value">'
                   . $outputText['text']
                   . '</td>'
                   . '</tr>';
            }
            echo '</table>';
        }
    }
    
    
    /**
     * Returns the readable name of this output format.
     *
     * @return string Human-readable name for output format
     */
    public static function getReadableName() {
        return 'XML';
    }
    
    /**
     * Returns the HTTP content type to declare for the output format.
     *
     * @return string HTTP Content-type
     */
    public function getContentType() {
        return 'text/XML';
    }
    
    /**
     * Returns the file extension to append to the generated report.
     *
     * @return string File extension
     */
    public function getExtension() {
        return 'xml';
    }
}
