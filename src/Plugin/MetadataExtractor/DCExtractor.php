<?php

namespace Drupal\static_metadata_records\Plugin\MetadataExtractor;

use GuzzleHttp\Client;
use Drupal\static_metadata_records\Plugin\MetadataExtractor\MetadataExtractorInterface;

/**
 * Extracts Dublin Core (DC) metadata via OAI-PMH
 */
class DCExtractor implements MetadataExtractorInterface {
    /**
     * {@inheritdoc}
     */
    public function getData($nid, array $headers){
        $client = new Client();
        $url = "https://islandora.dev/oai/request?identifier=oai%3Aislandora.dev%3Anode-$nid&metadataPrefix=oai_dc&verb=GetRecord";
        // $xmlSchema = "https://www.dublincore.org/schemas/xmls/simpledc20021212.xsd";
        // $xmlSchema = "http://www.openarchives.org/OAI/2.0/oai_dc.xsd";

        try {
            // send request
            $response = $client->request(
                'GET', 
                $url, 
                $headers,
            );

            // validate response object
            if (!$response) {
                \Drupal::logger('static_metadata_records')->error("No response received for node $nid.");
                return NULL;
            }
            if ($response->getStatusCode() !== 200) {
                \Drupal::logger('static_metadata_records')->error("HTTP request failed for node $nid. \nStatus: $response->getStatusCode() - $response->getReasonPhrase().");
                return NULL;
            }

            $body = (string) $response->getBody();

            return parseBody($body);
            // // validate the body and xml
            // if (empty($body)) {
            //     \Drupal::logger('static_metadata_records')->error("Empty response body for Node ID $nid.");
            //     return NULL;
            // }

            // if (strpos($body, '<?xml') === false || strpos($body, '<OAI-PMH') === false) {
            //     \Drupal::logger('static_metadata_records')->error("Response body does not appear to be XML for Node ID $nid.");
            //     return NULL;
            // }
            // if (simplexml_load_string($body) === false) {
            //     \Drupal::logger('static_metadata_records')->error("Invalid XML for node $nid.");
            //     return NULL;
            // }

            // // get the content between the metadata tags
            // $start_tag = "<metadata>";
            // $end_tag = "</metadata>";
            // $tag_length = 10; // 10 is the length of <metadata>, and we dont want to display it
            // $start_index = strpos($body, $start_tag);
            // $end_index = strpos($body, $end_tag);
            
            // if (!$start_index || !$end_index){
            //     \Drupal::logger('static_metadata_records')->error("Start or ending index not found XML for node $nid.");
            //     return NULL;
            // }    

            // $length = $end_index - $start_index;
            // $refined = substr($body, $start_index + $tag_length, $length - $tag_length); 
            
            // return $refined;
        } 
        catch (Exception $e) {
            \Drupal::logger('static_metadata_records')->error("Exception in DC Records for node $nid. \nError: $e->getMessage().");
            return NULL;
        }
    }

    public function parseBody($body){
        // validate the body and xml
        if (empty($body)) {
            // \Drupal::logger('static_metadata_records')->error("Empty response body for Node ID $nid.");
            return NULL;
        }

        if (strpos($body, '<?xml') === false || strpos($body, '<OAI-PMH') === false) {
            // \Drupal::logger('static_metadata_records')->error("Response body does not appear to be XML for Node ID $nid.");
            return NULL;
        }
        if (simplexml_load_string($body) === false) {
            // \Drupal::logger('static_metadata_records')->error("Invalid XML for node $nid.");
            return NULL;
        }

        // get the content between the metadata tags
        $start_tag = "<metadata>";
        $end_tag = "</metadata>";
        $tag_length = 10; // 10 is the length of <metadata>, and we dont want to display it
        $start_index = strpos($body, $start_tag);
        $end_index = strpos($body, $end_tag);
        
        if (!$start_index || !$end_index){
            // \Drupal::logger('static_metadata_records')->error("Start or ending index not found XML for node $nid.");
            return NULL;
        }    

        $length = $end_index - $start_index;
        $refined = substr($body, $start_index + $tag_length, $length - $tag_length); 
        
        return $refined;
    }
}

?>