<?php

namespace Drupal\static_metadata_records\Plugin\MetadataExtractor;

use GuzzleHttp\Client;
use Drupal\static_metadata_records\Plugin\MetadataExtractor\MetadataExtractorInterface;

/**
 * Extracts MODS metadata via OAI-PMH
 */
class MODSExtractor implements MetadataExtractorInterface{
    /**
     * {@inheritdoc}
     */
    public function getData($nid, array $headers){
        $client = new Client();
        $link_to_mods_record = "https://islandora.dev/oai/request?identifier=oai%3Aislandora.dev%3Anode-$nid&metadataPrefix=mods&verb=GetRecord";
        // $xmlSchema = "https://www.loc.gov/standards/mods/v3/mods-3-8.xsd";

        try {
            // send request
            $mods_response = $client->request(
                'GET', 
                $link_to_mods_record, 
                $headers,
            );

            // validate response object
            if (!$mods_response) {
                \Drupal::logger('static_metadata_records')->error("No response received for node $nid.");
                return null;
            }
            if ($mods_response->getStatusCode() !== 200) {
                \Drupal::logger('static_metadata_records')->error("HTTP request failed for node $nid. \nStatus: $mods_response->getStatusCode() - $mods_response->getReasonPhrase().");
                return null;
            }

            $body = (string) $mods_response->getBody();

            // validate the body and xml
            if (empty($body)) {
                \Drupal::logger('static_metadata_records')->error("Empty response body for node $nid.");
                return null;
            }
            if (strpos($body, '<?xml') === false || strpos($body, '<OAI-PMH') === false) {
                \Drupal::logger('static_metadata_records')->error("Response body does not appear to be XML for node $nid.");
                return null;
            }
            if (simplexml_load_string($body) === false) {
                \Drupal::logger('static_metadata_records')->error("Invalid XML for node $nid.");
                return null;
            }

            // get the content between the metadata tags
            $start_tag = "<metadata>";
            $end_tag = "</metadata>";
            $tag_length = 10; // 10 is the length of <metadata>, and we dont want to display it
            $start_index = strpos($body, $start_tag);
            $end_index = strpos($body, $end_tag);
            
            if (!$start_index || !$end_index){
                \Drupal::logger('static_metadata_records')->error("Start or ending index not found XML for node $nid.");
                return null;
            }    

            $length = $end_index - $start_index;
            $refined = substr($body, $start_index + $tag_length, $length - $tag_length); 
            
            // schema validation (need to fix)
            // $dom = new \DOMDocument();
            // $dom->loadXML($refined);
            // if (!$dom->schemaValidate($xmlSchema)) {
            //     \Drupal::logger('static_metadata_records')->error("MODS Schema is wrong.");
            //     return null;
            // }
            // else{
            //     \Drupal::logger('static_metadata_records')->notice("MODS Schema validated.");
            // }

            return $refined;
        } 
        catch (Exception $e) {
            \Drupal::logger('static_metadata_records')->error("Exception in MODS Records for node $nid. \nError: $e->getMessage().");
            return null;
        }
    }
}

?>