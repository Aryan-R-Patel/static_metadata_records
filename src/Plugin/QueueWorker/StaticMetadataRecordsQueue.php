<?php

namespace Drupal\static_metadata_records\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\static_metadata_records\Plugin\MetadataExtractor\MODSExtractor;
use Drupal\static_metadata_records\Plugin\MetadataExtractor\DCExtractor;

/**
 * Queue worker for the static metadata records queue.
 *
 * @QueueWorker(
 *   id = "static_metadata_records_queue",
 *   title = @Translation("Static Metadata Records Queue Worker"),
 *   cron = {"time" = 60}
 * )
 */
class StaticMetadataRecordsQueue extends QueueWorkerBase {
    /**
     * Processes a single queue item
     * 
     * @param $data
     * The data passed to the queue item, contains the 'nid' and 'uid'
     */
    public function processItem($data){
        $nid = $data->nid;
        $uid = $data->uid;
        
        // node validation
        $node = Node::load($nid);
        if (!$node) {
            return; // add debug msg here
        }

        // user validation and then change user accounts using the account switcher
        $user = User::load($uid);
        if (!$user){
            \Drupal::logger('static_metadata_records')->error("User not found with uid $uid.");
            return;
        }
        // validate that the user is not blocked
        if ($user->isBlocked()){
            \Drupal::logger('static_metadata_records')->error("User is blocked with uid $uid.");
            return;
        }

        // switch accounts
        $account_switcher = \Drupal::service("account_switcher");
        if (!$account_switcher) {
            \Drupal::logger('static_metadata_records')->error("Account Switcher does not exist.");
            return;
        }
        $account_switcher->switchTo($user);

        // core logic
        try {
            // jwt authentication
            // generate JWT token and construct headers to be sent with the request
            $jwt_service = \Drupal::service("jwt.authentication.jwt");
            if (!$jwt_service){
                \Drupal::logger('static_metadata_records')->error("Cannot initialize JWT authentication.");
                return;
            }

            // note: default expiry of a JWT token is 1 hour (see: https://plugins.miniorange.com/drupal-rest-api-authentication#features)
            $token = $jwt_service->generateToken();
            if (empty($token)){
                \Drupal::logger('static_metadata_records')->error("Failed to generate JWT token.");
                return;
            }
            $headers = [
                "headers" => [
                    "Authorization" => "Bearer " . $token,
                ],
            ];

            // validate config exists, and extract the required field names
            $config = \Drupal::config('static_metadata_records.settings');
            if (!$config) {
                \Drupal::logger('static_metadata_records')->error("Configuration 'static_metadata_records.settings' not found.");
                return;
            }
            $dc_field_name = $config->get("dc_field_selection");
            $mods_field_name = $config->get("mods_field_selection");


            // validate at least one field is configured
            if (empty($dc_field_name) && empty($mods_field_name)) {
                \Drupal::logger('static_metadata_records')->error("No DC or MODS fields configured. Please configure atleast one field in the admin settings.");
                return;
            }

            $dc_extractor_object = new DCExtractor();
            $mods_extractor_object = new MODSExtractor();

            // send a request (internally inside the objects) and extract data
            $raw_dc_data = null;
            $raw_mods_data = null;

            if (!empty($dc_field_name)) {
                $raw_dc_data = $dc_extractor_object->getData($nid, $headers);
            }
            
            if (!empty($mods_field_name)) {
                $raw_mods_data = $mods_extractor_object->getData($nid, $headers);
            }

            // null check - only fail if the configured field's data is null
            if ((!empty($dc_field_name) && is_null($raw_dc_data)) || 
                (!empty($mods_field_name) && is_null($raw_mods_data))) {
                \Drupal::logger('static_metadata_records')->error("Extraction failed for node $nid.");
                return;
            }

            $need_to_save = false;
            // dc
            if (!empty($dc_field_name) && !is_null($raw_dc_data)){
                if ($node->hasField($dc_field_name)) {
                    $node->set($dc_field_name, $raw_dc_data);
                    $need_to_save = true;
                } 
                else {
                    \Drupal::logger('static_metadata_records')->error("'$dc_field_name' Does Not Exist");
                }
            }

            // mods 
            if (!empty($mods_field_name) && !is_null($raw_mods_data)){
                if ($node->hasField($mods_field_name)) {
                    $node->set($mods_field_name, $raw_mods_data);
                    $need_to_save = true;
                } 
                else {
                    \Drupal::logger('static_metadata_records')->error("'$mods_field_name' Does Not Exist");
                }
            }

            // save only if you need to, otherwise we dont need to
            // because it is an expensive operation
            if ($need_to_save) {
                // save_flag is used in the .module file to prevent infinite loops
                $node->save_flag = TRUE; 
                $node->save();
            } 
        }
        catch (Exception $e) {
            \Drupal::logger('static_metadata_records')->error("Queue processing failed for node $nid: " . $e->getMessage());
        }
        finally {
            \Drupal::logger('static_metadata_records')->notice("Finished processing node $nid.");
            $account_switcher->switchBack();
        }
    }
}
?>