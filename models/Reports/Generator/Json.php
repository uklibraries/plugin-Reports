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
        $reportName = $this->_report->name;
        $reportDescription = $this->_report->description;
        ?>

        <?php echo date('Y-m-d H:i:s O') ?>
<?php $page = 1;
    while ($items = get_db()->getTable('Item')->findBy($this->_params, 30, $page)):
        foreach ($items as $item) : ?>
            <?php echo $item->id; ?>

<?php       $sets = get_db()->getTable('ElementSet')->findByRecordType('Item');
            // Output all the metadata for all the element sets
            foreach($sets as $set) :
                $this->_outputSetElements($item, $set->name);
            endforeach;
            $tags = $item->getTags(); 
            if (count($tags)): ?
            <?php echo implode($tags, ', '); ?>
<?php       endif; ?>

<?php       release_object($item); 
        endforeach;
        $page++;
    endwhile; ?>
}
<?php
    }
    
    /**
     * Prints JSON for the elements from the given item in the given element set
     * and optionally specific element names.
     * If no element names are specified, all the elements in the set will be
     * output.
     *
     * @param Item $item The item whose metadata to output
     * @param string $setName The name of the set to output metadata for
     * @param array $elementNames Array of element names to be output
     */

private function outputJSON()
{
    $metadata = array(
        /* fields like name, description, and date go here */
        'name' => $this->_report->name;
    );

    $page = 1;
    while ($items = get_db()->getTable('Item')->findBy(/* blah */)) {
        foreach ($items as $item) {
            $item_metadata = array(
                'id'   => $item->id,
                'sets' => array(),
                'tags' => $item->getTags(),
            );

            /* fill out sets metadata */
            $sets = get_db()->getTable('ElementSet')->findByRecordType('Item');
            foreach ($sets as $set) {
                /* _outputSetElements should return an array,
                 * not print anything.  Consider renaming it. */
                $item_metadata['sets'][] = $this->_outputSetElements($item, $set->name);
            }
            release_object($item);
        }
        $page++;
    }

    print json_encode($metadata);
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
        return 'text/json';
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
