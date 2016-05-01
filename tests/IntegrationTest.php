<?php

namespace Language;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{

    /** @var LanguageBatchBo */
    private $languageBatchBo;

    public function setUp()
    {
        parent::setUp();

        $this->rmdirRecursive(CACHE_PATH);

        $this->languageBatchBo = new LanguageBatchBo;
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->rmdirRecursive(CACHE_PATH);
    }

    /**
     * Remove the cache directory to ensure we're not testing the results with already existing files.
     *
     * @param $directory
     */
    private function rmdirRecursive($directory) {
        if (!is_dir($directory)) {
            return;
        }

        foreach(scandir($directory) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (is_dir("$directory/$file")) {
                $this->rmdirRecursive("$directory/$file");
            }
            else {
                unlink("$directory/$file");
            }
        }
        rmdir($directory);
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
