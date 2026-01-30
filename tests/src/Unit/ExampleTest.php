<?php

// Commands:
// if (phpunit.xml file in web directory){
//      ./vendor/bin/phpunit -c web/phpunit.xml web/modules/custom/static_metadata_records/
// }
// else{
//      ./vendor/bin/phpunit web/modules/custom/static_metadata_records/
// }

namespace Drupal\static_metadata_records\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Simple test to verify PHPUnit is working.
 * @group static_metadata_records
 */
class ExampleTest extends UnitTestCase {
    public function testTrueIsTrue() {
        $this->assertTrue(TRUE);
    }
}

?>