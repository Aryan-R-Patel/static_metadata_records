<?php

namespace Drupal\Tests\static_metadata_records\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\static_metadata_records\Drush\Commands\MetadataRecordsCommands;

/**
 * Tests Drush command input validation.
 * @group static_metadata_records
 */
class DrushInputTest extends KernelTestBase {

  protected static $modules = ['static_metadata_records', 'system', 'user'];

    public function testUidValidation() {
        $commands = new MetadataRecordsCommands();
        $options = ['uid' => '', 'file' => 'fake_file.csv'];
        $result = $commands->metadataRecords($options);
        
        $queue = \Drupal::getContainer()->get('queue')->get('static_metadata_records_queue');
        $this->assertEquals(0, $queue->numberOfItems());
    }

    public function testFileValidation() {
        $commands = new MetadataRecordsCommands();
        $options = ['uid' => '1', 'file' => 'fake_path.csv'];
        $commands->metadataRecords($options);
        
        $queue = \Drupal::getContainer()->get('queue')->get('static_metadata_records_queue');
        $this->assertEquals(0, $queue->numberOfItems());    
    }

}