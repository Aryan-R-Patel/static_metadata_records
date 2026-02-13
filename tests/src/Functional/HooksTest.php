<?php

// ./vendor/bin/phpunit web/modules/custom/static_metadata_records/tests/src/Functional/HooksTest.php 

namespace Drupal\Tests\static_metadata_records\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests whether both the module hooks correctly add nodes to the queue.
 *
 * @group static_metadata_records
 */
class HooksTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field',
    'text',
    'filter',
    'user',
    'static_metadata_records',
  ];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Fixture authenticated user with no permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authUser;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero';

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // sample content type
    $this->drupalCreateContentType([
      'type' => 'islandora_object',
      'name' => 'Islandora Object',
    ]);

    // enable the hooks in the module config to simulate the tests
    $this->config('static_metadata_records.settings')
      ->set('enable_hooks', TRUE)
      ->set('excluded_content_types', []) // to make sure nothing is exluded
      ->save();
  }

  /**
   * Tests hook_node_insert.
   */
  public function testNodeInsertHook() {
    // create node and queue
    $node = $this->drupalCreateNode([
      'type' => 'islandora_object',
      'title' => 'Hook Test Node',
    ]);

    $queue = \Drupal::queue('static_metadata_records_queue');

    // check whether the hook successfully added the item to our queue
    $this->assertEquals(1, $queue->numberOfItems(), 'The node was automatically added to the queue via hook_node_insert.');
    
    // checking the data
    $item = $queue->claimItem();

    // note for debugging: in queue items are stored as 'stdClass'
    $queued_nid = NULL;
    if (isset($item->data->nid)){
      $queued_nid = $item->data->nid;
    }
    $this->assertEquals($node->id(), $queued_nid, 'The queued item has the correct Node ID.');
  }

  /**
   * Tests hook_node_update.
   */
  public function testNodeUpdateHook() {
    // create node
    $node = $this->drupalCreateNode([
      'type' => 'islandora_object',
      'title' => 'Original Title',
    ]);

    // clear the queue because it is possible that the insert hook might have added it
    $queue = \Drupal::queue('static_metadata_records_queue');
    $queue->deleteQueue();

    // update the node
    $node->setTitle('Testing Updated Title');
    $node->save();

    $this->assertEquals(1, $queue->numberOfItems(), 'The node was added to the queue via hook_node_update.');
  }

}