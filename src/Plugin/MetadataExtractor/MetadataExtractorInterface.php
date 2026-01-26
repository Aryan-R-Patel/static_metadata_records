<?php

namespace Drupal\static_metadata_records\Plugin\MetadataExtractor;

/**
 * Interface for metadata extraction classes
 */
interface MetadataExtractorInterface {
    /**
     * Fetches raw XML data from an external OAI-PMH endpoint
     * 
     * @param int|string $nid
     * The Node ID to fetch metadata for
     * @param array $headers
     * Request headers (e.g. JWT Token for Authentication)
     * @return string|null
     * The extracted XML string or null on failure
     */
    public function getData($nid, array $headers);
}

?>