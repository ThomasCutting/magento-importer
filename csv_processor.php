<?php
/**
 * Batch CSV Processor
 * @package GSImporter
 */

require_once 'autoload.php';

/**
 * Class BatchCSVProcessor
 * --
 * Processes the file stream from BatchFileReader into multi-dimensional, combined arrays.
 * @package GSImporter
 */
class BatchCSVProcessor
{
    /**
     * processResourceToArray
     * --
     * process the csv ( resource | handle ) into an array, and return it.
     * @param $file_handler
     * @param $limit
     * @param $delimiter
     * @param $enclosing
     * @return array
     */
    public static function processResourceToArray($file_handler,$limit = 0,$delimiter = ",",$enclosing = '"') {
        $row = 0;

        // Define the head array, and the temp. array ( that we will return. )
        $head = [];
        $arr = [];

        while(($data = fgetcsv($file_handler,$limit,$delimiter,$enclosing)) !== false) {
            if($row==0) {
                // Pull the head down, and set it current data
                $head = $data;
            } else {
                // Push the [head[],data[]] to current temp. array.
                $arr[] = array_combine($head,$data);
            }

            $row++;
        }

        // Return the temp. array.
        return $arr;
    }

}