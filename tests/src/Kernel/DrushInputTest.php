<?php

namespace Drupal\Tests\static_metadata_records\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\static_metadata_records\Drush\Commands\MetadataRecordsCommands;

/**
 * Tests Drush command input validation.
 * @group static_metadata_records
*/
class DrushInputTest extends KernelTestBase {

    // Add node and field here
    protected static $modules = ['static_metadata_records', 'system', 'user', 'node', 'field', 'text', 'filter'];
    
    protected function setUp(): void {
        parent::setUp();
        
        // installation
        $this->installEntitySchema('node');
        $this->installEntitySchema('user');
        $this->installSchema('system', ['sequences']); // Required for NIDs to increment

        \Drupal\node\Entity\NodeType::create([
            'type' => 'islandora_object',
            'name' => 'Repository Item',
        ])->save();

        $this->installConfig(['field', 'node', 'text', 'filter', 'static_metadata_records']);

        // Explicitly create the queue to ensure the table is initialized.
        $this->container->get('queue')->get('static_metadata_records_queue')->createQueue();
    }

    // uid and file invalid
    public function testUidAndFileInvalid() {
        $commands = new MetadataRecordsCommands();
        $options = ['uid' => '', 'file' => 'fake_file.csv'];
        $result = $commands->metadataRecords($options);
        
        $queue = \Drupal::getContainer()->get('queue')->get('static_metadata_records_queue');
        $this->assertEquals(0, $queue->numberOfItems());
    }

    // uid valid, file invalid
    public function testUidValid() {
        $commands = new MetadataRecordsCommands();
        $options = ['uid' => '1', 'file' => 'fake_path.csv'];
        $commands->metadataRecords($options);
        
        $queue = \Drupal::getContainer()->get('queue')->get('static_metadata_records_queue');
        $this->assertEquals(0, $queue->numberOfItems());    
    }

    // uid invalid, file valid
    public function testFileValid() {
        // creating a node so drush command can load it 
        $node = \Drupal\node\Entity\Node::create([
            'nid' => 101,
            'type' => 'islandora_object', 
            'title' => 'Test Node',
        ]);
        $node->save();

        // creating a temp file
        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_csv') . '.csv';
        file_put_contents($tempFilePath, "101");

        // run the command
        $commands = new MetadataRecordsCommands();
        $options = ['uid' => '', 'file' => $tempFilePath];
        $commands->metadataRecords($options);
        
        // final check
        $queue = \Drupal::getContainer()->get('queue')->get('static_metadata_records_queue');
        $this->assertEquals(0, $queue->numberOfItems(), 'Node 101 should not be in the queue.');

        unlink($tempFilePath); // Cleanup
    }

    // uid valid, file valid
    public function testUidAndFileValid() {
        // creating a node so drush command can load it 
        $node = \Drupal\node\Entity\Node::create([
            'nid' => 101,
            'type' => 'islandora_object', 
            'title' => 'Test Node',
        ]);
        $node->save();

        // creating a temp file
        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_csv') . '.csv';
        file_put_contents($tempFilePath, "101");

        // run the command
        $commands = new MetadataRecordsCommands();
        $options = ['uid' => 1, 'file' => $tempFilePath];
        $commands->metadataRecords($options);
        
        // final check
        $queue = \Drupal::getContainer()->get('queue')->get('static_metadata_records_queue');

        $this->assertEquals(1, $queue->numberOfItems(), 'Node 101 should be in the queue.');

        unlink($tempFilePath); // Cleanup
    }
}