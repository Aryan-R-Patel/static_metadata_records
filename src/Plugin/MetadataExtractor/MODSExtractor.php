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
        // $url = "https://islandora.dev/oai/request?identifier=oai%3Aislandora.dev%3Anode-$nid&metadataPrefix=mods&verb=GetRecord";
        // // $xmlSchema = "https://www.loc.gov/standards/mods/v3/mods-3-8.xsd";

        // Dynamically construct the OAI-PMH URL and identifier based on the current site.
        $host = \Drupal::request()->getHttpHost();
        // Remove port from hostname if present.
        if (strpos($host, ':') !== FALSE) {
            $host_parts = explode(':', $host);
            $host = $host_parts[0];
        }
        // $scheme = \Drupal::request()->getScheme();
        $scheme = "https";
        $url = $scheme . '://' . $host . '/oai/request?identifier=oai%3A' . urlencode($host) . '%3Anode-' . $nid . '&metadataPrefix=mods&verb=GetRecord';

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
                return null;
            }
            if ($response->getStatusCode() !== 200) {
                \Drupal::logger('static_metadata_records')->error("HTTP request failed for node $nid. \nStatus: $response->getStatusCode() - $response->getReasonPhrase().");
                return null;
            }

            $body = (string) $response->getBody();

            return $this->parseBody($body);
        } 
        catch (Exception $e) {
            \Drupal::logger('static_metadata_records')->error("Exception in MODS Records for node $nid. \nError: $e->getMessage().");
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

        libxml_use_internal_errors(true); // disable libxml errors and allow the user to fetch error information as needed
        if (simplexml_load_string($body) === false) {
            // \Drupal::logger('static_metadata_records')->error("Invalid XML for node $nid.");
            return NULL;
        }
        if (!empty(libxml_get_errors())){
            libxml_clear_errors();
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
        $refined = trim(substr($body, $start_index + $tag_length, $length - $tag_length)); 

        if (empty($refined)){
            return '';
        }

        // schema validation
        $xmlSchema = "https://www.loc.gov/standards/mods/v3/mods-3-8.xsd";
        $dom = new \DOMDocument();
        if ($dom->loadXML($refined)) {
            libxml_use_internal_errors(true);
            if ($dom->schemaValidate($xmlSchema)) {
                \Drupal::logger('static_metadata_records')->info("MODS Schema validated.");
            } 
            else {
                $errors = libxml_get_errors();
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        \Drupal::logger('static_metadata_records')->error("MODS Schema validation error: " . $error->message);
                    }
                    libxml_clear_errors();
                } 
                else {
                    \Drupal::logger('static_metadata_records')->error("MODS Schema validation failed.");
                }
            }
        } 
        else {
            \Drupal::logger('static_metadata_records')->error("Failed to load MODS XML for validation.");
        }

        // remove extra white spaces between tags
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false; // ignore extra spaces
        $dom->loadXML($refined);
        $dom->formatOutput = true;       // set to true because we want pretty printing for our display
        $refined = $dom->saveXML($dom->documentElement);
        
        return $refined;
    }
}

?>