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
        $directoryCache = __DIR__ . '/../cache/portal';
        $directoryOutput = __DIR__ . '/output';

        $this->languageBatchBo->generateLanguageFiles();

        $this->assertEquals(
            file_get_contents($directoryOutput . '/en.php'),
            file_get_contents($directoryCache . '/en.php'),
            'EN language file'
        );

        $this->assertEquals(
            file_get_contents($directoryOutput . '/hu.php'),
            file_get_contents($directoryCache . '/hu.php'),
            'HU language file'
        );
    }

    public function testItGenerateAppletLanguageXmlFiles()
    {
        $directoryCache = __DIR__ . '/../cache/flash';
        $directoryOutput = __DIR__ . '/output';

        $this->languageBatchBo->generateAppletLanguageXmlFiles();

        $this->assertEquals(
            file_get_contents($directoryOutput . '/lang_en.xml'),
            file_get_contents($directoryCache . '/lang_en.xml')
        );
    }
}
