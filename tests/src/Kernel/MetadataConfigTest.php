<?php

// Note to self: move repititive logic to setUp method

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
  public function testHooks() {
    $config = $this->container->get('config.factory')->get('static_metadata_records.settings');
    $enable_hooks = $config->get('enable_hooks');
    $this->assertFalse($enable_hooks, 'Hooks should be disabled by default.');
  }

  public function testContentTypes(){
    $config = $this->container->get('config.factory')->get('static_metadata_records.settings');
    $excluded_content_types = $config->get('excluded_content_types');
    $this->assertEquals([], $excluded_content_types, 'None of the content types should be checked by default.');
  }
  
  public function testFieldKey(){
    $config = $this->container->get('config.factory')->get('static_metadata_records.settings');
    $excluded_field_machine_name = $config->get('excluded_field_machine_name');
    $this->assertEquals("", $excluded_field_machine_name, 'Field Machine Name should be empty by default.');
  }

  public function testFieldValue(){
    $config = $this->container->get('config.factory')->get('static_metadata_records.settings');
    $excluded_field_value = $config->get('excluded_field_value');
    $this->assertEquals("", $excluded_field_value, 'Field Value should be empty by default.');
  }
}