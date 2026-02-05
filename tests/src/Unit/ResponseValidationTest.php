<?php

// Commands:
// if (phpunit.xml file in different directory, then provide a config file path){
//      ./vendor/bin/phpunit -c web/phpunit.xml web/modules/custom/static_metadata_records/
// }
// else{
//      ./vendor/bin/phpunit web/modules/custom/static_metadata_records/
// }

namespace Drupal\static_metadata_records\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\static_metadata_records\Plugin\MetadataExtractor\DCExtractor;
use Drupal\static_metadata_records\Plugin\MetadataExtractor\MODSExtractor;

/**
 * Test to verify the parsed body content.
 * @group static_metadata_records
 */
class ResponseValidationTest extends UnitTestCase {
	// VALID TESTS
    public function testDCResponseValidation() {
        $extractor = new DCExtractor();
        $body_to_parse = '<?xml version="1.0"?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"><responseDate>2026-01-30T18:17:14Z</responseDate><request verb="GetRecord" metadataPrefix="oai_dc">https://islandora.dev/oai/request</request><GetRecord><record><header><identifier>oai:islandora.dev:node-101</identifier><datestamp>2026-01-30T15:20:17Z</datestamp><setSpec>oai_pmh_default_set:entity_reference_1</setSpec></header><metadata><oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd"><dc:title>Custom Module Testing Page</dc:title>
                  <dc:description>This is a page created for testing the development of my Drupal Custom Module named &quot;Static Metadata Records&quot;.</dc:description>
                  <dc:date>1981-01</dc:date>
                  <dc:format>1 item</dc:format></oai_dc:dc></metadata></record></GetRecord></OAI-PMH>';
        $expected = '<oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd"><dc:title>Custom Module Testing Page</dc:title>
                  <dc:description>This is a page created for testing the development of my Drupal Custom Module named &quot;Static Metadata Records&quot;.</dc:description>
                  <dc:date>1981-01</dc:date>
                  <dc:format>1 item</dc:format></oai_dc:dc>';
        $actual = $extractor->parseBody($body_to_parse);
        $this->assertEquals($expected, $actual);
    }

    public function testMODSResponseValidation() {
        $extractor = new MODSExtractor();
        $body_to_parse = '<?xml version="1.0"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"><responseDate>2026-01-30T18:23:56Z</responseDate><request verb="GetRecord" metadataPrefix="mods">https://islandora.dev/oai/request</request><GetRecord><record><header><identifier>oai:islandora.dev:node-101</identifier><datestamp>2026-01-30T15:20:17Z</datestamp><setSpec>oai_pmh_default_set:entity_reference_1</setSpec></header><metadata><mods xmlns="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-7.xsd"><titleInfo>
  <title lang="eng">Custom Module Testing Page</title>
  </titleInfo>



<name type="">
    <role>
      <roleTerm type="text"></roleTerm>
    </role>
    <namePart></namePart>
</name>

<typeOfResource></typeOfResource>
<genre> </genre>
<abstract>This is a page created for testing the development of my Drupal Custom Module named &amp;quot;Static Metadata Records&amp;quot;.</abstract>
<language>
      <languageTerm authority="iso639-2b" type="code"></languageTerm>
  </language>
<originInfo>
  <publisher></publisher>
  <place>
    <placeTerm type="text"></placeTerm>
    <placeTerm authority="marccountry"></placeTerm>
  </place>
  <dateCreated keyDate="yes"></dateCreated>
      <copyrightDate></copyrightDate>
</originInfo>
<physicalDescription>
  <form authority="smd"></form>
  <extent>1 item</extent>
  <reformattingQuality></reformattingQuality>
  <digitalOrigin>reformatted digital</digitalOrigin>
  <internetMediaType></internetMediaType>
  <note></note>
</physicalDescription>
<subject authority="local">
      <topic></topic>
        <geographic></geographic>
        <temporal></temporal>
   
    
    
  <name type="">
    <namePart></namePart>
  </name>
  
  <hierarchicalGeographic>
    <continent></continent>
    <country></country>
    <state></state>
    <province></province>
    <region></region>
    <county></county>
    <island></island>
    <city></city>
    <citySection></citySection>
  </hierarchicalGeographic>
  <cartographics>
    <coordinates></coordinates>
  </cartographics>
</subject>
<accessCondition type="use and reproduction"></accessCondition>
<location>
  <url usage="primary display"></url>
  </location>
<identifier type="uri"></identifier>
<identifier type="local"></identifier>
<identifier type="ark"></identifier>
<note></note>
<recordInfo>
        <languageOfCataloging>
          <languageTerm authority="iso639-2b" type="code">eng</languageTerm>
    </languageOfCataloging>
</recordInfo></mods></metadata></record></GetRecord></OAI-PMH>
';
        $expected = '<mods xmlns="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-7.xsd"><titleInfo>
  <title lang="eng">Custom Module Testing Page</title>
  </titleInfo>



<name type="">
    <role>
      <roleTerm type="text"></roleTerm>
    </role>
    <namePart></namePart>
</name>

<typeOfResource></typeOfResource>
<genre> </genre>
<abstract>This is a page created for testing the development of my Drupal Custom Module named &amp;quot;Static Metadata Records&amp;quot;.</abstract>
<language>
      <languageTerm authority="iso639-2b" type="code"></languageTerm>
  </language>
<originInfo>
  <publisher></publisher>
  <place>
    <placeTerm type="text"></placeTerm>
    <placeTerm authority="marccountry"></placeTerm>
  </place>
  <dateCreated keyDate="yes"></dateCreated>
      <copyrightDate></copyrightDate>
</originInfo>
<physicalDescription>
  <form authority="smd"></form>
  <extent>1 item</extent>
  <reformattingQuality></reformattingQuality>
  <digitalOrigin>reformatted digital</digitalOrigin>
  <internetMediaType></internetMediaType>
  <note></note>
</physicalDescription>
<subject authority="local">
      <topic></topic>
        <geographic></geographic>
        <temporal></temporal>
   
    
    
  <name type="">
    <namePart></namePart>
  </name>
  
  <hierarchicalGeographic>
    <continent></continent>
    <country></country>
    <state></state>
    <province></province>
    <region></region>
    <county></county>
    <island></island>
    <city></city>
    <citySection></citySection>
  </hierarchicalGeographic>
  <cartographics>
    <coordinates></coordinates>
  </cartographics>
</subject>
<accessCondition type="use and reproduction"></accessCondition>
<location>
  <url usage="primary display"></url>
  </location>
<identifier type="uri"></identifier>
<identifier type="local"></identifier>
<identifier type="ark"></identifier>
<note></note>
<recordInfo>
        <languageOfCataloging>
          <languageTerm authority="iso639-2b" type="code">eng</languageTerm>
    </languageOfCataloging>
</recordInfo></mods>';
        $actual = $extractor->parseBody($body_to_parse);
        
        $this->assertEquals($expected, $actual);
    }

	// EMPTY BODY 
    public function testDCParseBodyEmpty(){
        $extractor = new DCExtractor();
        $body_to_parse = "";
        $actual = $extractor->parseBody($body_to_parse);
        $this->assertNull($actual);
    }

	public function testMODSParseBodyEmpty(){
        $extractor = new MODSExtractor();
        $body_to_parse = "";
        $actual = $extractor->parseBody($body_to_parse);
        $this->assertNull($actual);
    }

	// INVALID XML 
	public function testDCInvalidXML(){
		$extractor = new DCExtractor();
		$body_to_parse = '<?xml version="1.0"?><OAI-PMH><incomplete>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertNull($actual);
	}

	public function testMODSInvalidXML(){
		$extractor = new MODSExtractor();
		$body_to_parse = '<?xml version="1.0"?><OAI-PMH><incomplete>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertNull($actual);
	}

	// NO XML TAG 
	public function testDCNoXmlTag(){
		$extractor = new DCExtractor();
		$body_to_parse = '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><responseDate>2026-01-30T18:17:14Z</responseDate><request verb="GetRecord" metadataPrefix="oai_dc">https://islandora.dev/oai/request</request><GetRecord><record><header><identifier>oai:islandora.dev:node-101</identifier></header><metadata><oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>Test</dc:title></oai_dc:dc></metadata></record></GetRecord></OAI-PMH>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertNull($actual);
	}

	public function testMODSNoXmlTag(){
		$extractor = new MODSExtractor();
		$body_to_parse = '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><responseDate>2026-01-30T18:23:56Z</responseDate><request verb="GetRecord" metadataPrefix="mods">https://islandora.dev/oai/request</request><GetRecord><record><header><identifier>oai:islandora.dev:node-101</identifier></header><metadata><mods xmlns="http://www.loc.gov/mods/v3"><titleInfo><title>Test</title></titleInfo></mods></metadata></record></GetRecord></OAI-PMH>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertNull($actual);
	}

	// NO OAI-PMG TAG
	public function testDCNoOaiPmhTag(){
		$extractor = new DCExtractor();
		$body_to_parse = '<?xml version="1.0"?><some><xml><metadata><oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>Test</dc:title></oai_dc:dc></metadata></xml></some>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertNull($actual);
	}

	public function testMODSNoOaiPmhTag(){
		$extractor = new MODSExtractor();
		$body_to_parse = '<?xml version="1.0"?><some><xml><metadata><mods xmlns="http://www.loc.gov/mods/v3"><titleInfo><title>Test</title></titleInfo></mods></metadata></xml></some>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertNull($actual);
	}

	// NO METADATA TAG
	public function testDCNoMetadataTags(){
		$extractor = new DCExtractor();
		$body_to_parse = '<?xml version="1.0"?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><responseDate>2026-01-30T18:17:14Z</responseDate><request verb="GetRecord" metadataPrefix="oai_dc">https://islandora.dev/oai/request</request><GetRecord><record><header><identifier>oai:islandora.dev:node-101</identifier></header></record></GetRecord></OAI-PMH>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertNull($actual);
	}

	public function testMODSNoMetadataTags(){
		$extractor = new MODSExtractor();
		$body_to_parse = '<?xml version="1.0"?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><responseDate>2026-01-30T18:23:56Z</responseDate><request verb="GetRecord" metadataPrefix="mods">https://islandora.dev/oai/request</request><GetRecord><record><header><identifier>oai:islandora.dev:node-101</identifier></header></record></GetRecord></OAI-PMH>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertNull($actual);
	}

	// EMPTY METADATA TAGS
	public function testDCEmptyMetadata(){
		$extractor = new DCExtractor();
		$body_to_parse = '<?xml version="1.0"?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><responseDate>2026-01-30T18:17:14Z</responseDate><request verb="GetRecord" metadataPrefix="oai_dc">https://islandora.dev/oai/request</request><GetRecord><record><header><identifier>oai:islandora.dev:node-101</identifier></header><metadata></metadata></record></GetRecord></OAI-PMH>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertEquals('', $actual);
	}

	public function testMODSEmptyMetadata(){
		$extractor = new MODSExtractor();
		$body_to_parse = '<?xml version="1.0"?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"><responseDate>2026-01-30T18:23:56Z</responseDate><request verb="GetRecord" metadataPrefix="mods">https://islandora.dev/oai/request</request><GetRecord><record><header><identifier>oai:islandora.dev:node-101</identifier></header><metadata></metadata></record></GetRecord></OAI-PMH>';
		$actual = $extractor->parseBody($body_to_parse);
		$this->assertEquals('', $actual);
	}
}

?>