<?php

namespace Drupal\Tests\static_metadata_records\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the module configuration.
 * @group static_metadata_records
 */
class MetadataConfigTest extends KernelTestBase {

  /**
   * Modules to enable.
   */
  protected static $modules = ['static_metadata_records', 'system', 'user', 'node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Install the default configuration for our module.
    $this->installConfig(['static_metadata_records']);
  }

  /**
   * Test that the default 'enable_hooks' is false.
   */
  public function testDefaultConfig() {
    $config = $this->container->get('config.factory')->get('static_metadata_records.settings');
    $enable_hooks = $config->get('enable_hooks');
    
    // Assert that hooks are disabled by default
    $this->assertFalse($enable_hooks, 'Hooks should be disabled by default upon installation.');
  }
}