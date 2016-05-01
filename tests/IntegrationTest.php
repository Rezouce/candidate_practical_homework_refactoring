<?php

namespace Language;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{

    /** @var LanguageBatchBo */
    private $languageBatchBo;

    public function setUp()
    {
        parent::setUp();

        $this->languageBatchBo = new LanguageBatchBo;
    }

    public function testItGenerateLanguageFiles()
    {
        $this->languageBatchBo->generateLanguageFiles();

        $this->assertEquals(
            file_get_contents(CACHE_PATH . '/portal/en.php'),
            file_get_contents(TESTS_PATH . '/output/en.php'),
            'EN language file'
        );

        $this->assertEquals(
            file_get_contents(CACHE_PATH . '/portal/hu.php'),
            file_get_contents(TESTS_PATH . '/output/hu.php'),
            'HU language file'
        );
    }

    public function testItGenerateAppletLanguageXmlFiles()
    {
        $this->languageBatchBo->generateAppletLanguageXmlFiles();

        $this->assertEquals(
            file_get_contents(CACHE_PATH . '/flash/lang_en.xml'),
            file_get_contents(TESTS_PATH . '/output/lang_en.xml')
        );
    }
}
