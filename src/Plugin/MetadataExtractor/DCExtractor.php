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
        $link_to_dc_record = "https://islandora.dev/oai/request?identifier=oai%3Aislandora.dev%3Anode-$nid&metadataPrefix=oai_dc&verb=GetRecord";
        // $xmlSchema = "https://www.dublincore.org/schemas/xmls/simpledc20021212.xsd";
        // $xmlSchema = "http://www.openarchives.org/OAI/2.0/oai_dc.xsd";

        try {
            // send request
            $dc_response = $client->request(
                'GET', 
                $link_to_dc_record, 
                $headers,
            );

            // validate response object
            if (!$dc_response) {
                \Drupal::logger('static_metadata_records')->error("No response received for node $nid.");
                return null;
            }
            if ($dc_response->getStatusCode() !== 200) {
                \Drupal::logger('static_metadata_records')->error("HTTP request failed for node $nid. \nStatus: $dc_response->getStatusCode() - $dc_response->getReasonPhrase().");
                return null;
            }

            $body = (string) $dc_response->getBody();

            // validate the body and xml
            if (empty($body)) {
                \Drupal::logger('static_metadata_records')->error("Empty response body for Node ID $nid.");
                return null;
            }
            if (strpos($body, '<?xml') === false || strpos($body, '<OAI-PMH') === false) {
                \Drupal::logger('static_metadata_records')->error("Response body does not appear to be XML for Node ID $nid.");
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
            //     \Drupal::logger('static_metadata_records')->error("DC Schema is wrong.");
            //     return null;
            // }
            // else{
            //     \Drupal::logger('static_metadata_records')->notice("DC Schema validated.");
            // }

            return $refined;
        } 
        catch (Exception $e) {
            \Drupal::logger('static_metadata_records')->error("Exception in DC Records for node $nid. \nError: $e->getMessage().");
            return null;
        }
    }
}

?>