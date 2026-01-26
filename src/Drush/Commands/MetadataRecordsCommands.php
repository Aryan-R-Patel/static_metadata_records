<?php

namespace Drupal\static_metadata_records\Drush\Commands;

use Drush\Commands\DrushCommands;

class MetadataRecordsCommands extends DrushCommands {
    /**
     * Imports node IDs from a CSV and adds them to the metadata processing queue.
     *
     * @command metadata-records:metadataRecords
     * @param array $options
     * @option file The path to CSV file containing the node ID's
     * @option uid the Drupal User ID to associate the queue items with
     * @usage metadata-records:metadataRecords --file=/path/to/csv --uid=uid
     */
    public function metadataRecords($options = ["file" => "", "uid" => ""]) { 
        // validate uid
        $uid = $options["uid"];
        if (empty($uid)) {
            \Drupal::logger('static_metadata_records')->error(">You must provide a user ID using the --uid option.");
            return;
        }
        if (!is_numeric($uid)){
            \Drupal::logger('static_metadata_records')->error(">You must provide a numeric value for the uid.");
            return;
        }

        // validate file
        $file_path = $options["file"];
        if (empty($file_path)) {
            \Drupal::logger('static_metadata_records')->error(">You must provide a file path using the --file option.");
            return;
        }
        if (!file_exists($file_path)) {
            \Drupal::logger('static_metadata_records')->error(">File not found: $file_path.");
            return;
        }
        if (!is_readable($file_path)) {
            \Drupal::logger('static_metadata_records')->error(">File is not readable: $file_path.");
            return;
        }
        // file extension
        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
        if (strtolower($file_extension) !== 'csv') {
            \Drupal::logger('static_metadata_records')->error("File must be a CSV file: $file_path.");
            return;
        }

        // load the csv file
        $handle = fopen($file_path, "r");
        if (!$handle){
            \Drupal::logger('static_metadata_records')->error("Error opening file: $file_path.");
            return;
        }

        // intialize a drupal queue
        $queueFactory = \Drupal::service('queue');
        $queue = $queueFactory->get('static_metadata_records_queue');        
                    
        // read the csv file and add the data to the queue
        while (($data = fgetcsv($handle)) !== FALSE) {
            // check whether current line has data
            if (empty($data) || !isset($data[0])) {
                \Drupal::logger('static_metadata_records')->warning("Empty or invalid CSV line. Skipping to next line.");
                continue;
            }

            // get the node id from the current line
            $nid = $data[0];

            // validate nid
            if (!is_numeric($nid)) {
                \Drupal::logger('static_metadata_records')->error("Invalid node ID '$nid'. Node ID must be numeric.");
                continue;
            }
            else{
                // create an item in the queue with the uid and nid
                $item = new \stdClass();
                $item->nid = $nid;
                $item->uid = $uid;
                $queue->createItem($item);
                \Drupal::logger('static_metadata_records')->info("Node ID $nid successfully added to the queue.");
            }
        }

        fclose($handle);
    }
}