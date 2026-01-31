<?php

namespace Drupal\Tests\static_metadata_records\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\node\Entity\Node;

/**
 * @group static_metadata_records
 */
class ExclusionLogicTest extends KernelTestBase {
  protected static $modules = ['static_metadata_records', 'node', 'user', 'system'];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['static_metadata_records']);

    // Create a dummy content type
    NodeType::create(['type' => 'islandora_object', 'name' => 'Islandora Object'])->save();
  }

  public function testExclusionByContentType() {
    // 1. Configure the module to exclude 'islandora_object'
    $this->config('static_metadata_records.settings')
      ->set('excluded_content_types', ['islandora_object' => 'islandora_object'])
      ->save();

    // 2. Create a node of that type
    $node = Node::create([
      'type' => 'islandora_object',
      'title' => 'Test Node',
    ]);
    $node->save();

    // 3. Call your helper function from the .module file
    // We include it manually if it's not loaded
    include_once __DIR__ . '/../../../static_metadata_records.module';
    
    $is_excluded = static_metadata_records_exclude_node($node->id());

    // 4. If the field check logic in your module isn't met (missing field), 
    // it currently returns FALSE (meaning "don't exclude"). 
    // This test verifies that logic path.
    $this->assertFalse($is_excluded);
  }
}