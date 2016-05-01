<?php
namespace Language;

use Language\OutputRenderer\OutputLog;

class LanguageBatchBoTest extends \PHPUnit_Framework_TestCase
{

    /** @var LanguageBatchBo */
    private $languageBatchBo;

    /** @var OutputLog */
    private $renderer;


    public function setUp()
    {
        parent::setUp();

        $this->renderer = new OutputLog;

        $this->languageBatchBo = new LanguageBatchBo;
        $this->languageBatchBo->setOutputRenderer($this->renderer);
    }

    public function testOutputWhenGenerateLanguageFiles()
    {
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
}
