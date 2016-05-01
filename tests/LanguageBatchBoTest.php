<?php
namespace Language;

use Language\OutputRenderer\OutputLog;

class LanguageBatchBoTest extends \PHPUnit_Framework_TestCase
{

    /** @var LanguageBatchBo */
    private $languageBatchBo;

    /** @var OutputLog */
    private $renderer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $cacheCreator;


    public function setUp()
    {
        parent::setUp();

        $this->renderer = new OutputLog;

        /** @var CacheCreator $cacheCreator */
        $this->cacheCreator = $cacheCreator
            = $this->getMockBuilder(CacheCreator::class)->disableOriginalConstructor()->getMock();

        $this->languageBatchBo = new LanguageBatchBo;
        $this->languageBatchBo->setOutputRenderer($this->renderer);
        $this->languageBatchBo->setCacheCreator($cacheCreator);
    }

    private function generationSucceeded()
    {
        $this->cacheCreator->method('create')->willReturn(true);
    }

    public function testOutputWhenGenerateLanguageFiles()
    {
        $this->generationSucceeded();

        $this->languageBatchBo->generateLanguageFiles();

        $logs = [
            "\nGenerating language files.\n",
            "[APPLICATION: portal]\n",
            "\t[LANGUAGE: en] OK\n",
            "\t[LANGUAGE: hu] OK\n",
        ];

        $this->assertEquals($logs, $this->renderer->getLogs());
    }

    public function testOutputWhenGenerateAppletLanguageXmlFiles()
    {
        $this->generationSucceeded();

        $this->languageBatchBo->generateAppletLanguageXmlFiles();

        $logs = [
            "\nGetting applet language XMLs.\n",
            " Getting > JSM2_MemberApplet (memberapplet) language xmls.\n",
            " - Available languages: en\n",
            " OK saving applet: (JSM2_MemberApplet) language: (en) was successful.\n",
            " < JSM2_MemberApplet (memberapplet) language xml cached.\n",
            "\nApplet language XMLs generated.\n",
        ];

        $this->assertEquals($logs, $this->renderer->getLogs());
    }

    public function testItGenerateLanguageFiles()
    {
        $this->cacheCreator->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [$this->equalTo('/portal/en.php'), $this->equalTo(file_get_contents(TESTS_PATH . '/output/en.php'))],
                [$this->equalTo('/portal/hu.php'), $this->equalTo(file_get_contents(TESTS_PATH . '/output/hu.php'))]
            )
            ->willReturn(true);

        $this->languageBatchBo->generateLanguageFiles();
    }

    public function testItGenerateAppletLanguageXmlFiles()
    {
        $this->cacheCreator->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('/flash/lang_en.xml'),
                $this->equalTo(file_get_contents(TESTS_PATH . '/output/lang_en.xml'))
            )
            ->willReturn(true);

        $this->languageBatchBo->generateAppletLanguageXmlFiles();
    }
}
