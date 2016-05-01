<?php
namespace Language;

use Language\Api\ApiCall;
use Language\OutputRenderer\OutputLog;

class LanguageBatchBoTest extends \PHPUnit_Framework_TestCase
{

    /** @var LanguageBatchBo */
    private $languageBatchBo;

    /** @var OutputLog */
    private $renderer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $cacheCreator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $apiCall;


    public function setUp()
    {
        parent::setUp();

        $this->renderer = new OutputLog;

        /** @var CacheCreator $cacheCreator */
        $this->cacheCreator = $cacheCreator
            = $this->getMockBuilder(CacheCreator::class)->disableOriginalConstructor()->getMock();

        /** @var ApiCall $apiCall */
        $this->apiCall = $apiCall
            = $this->getMockBuilder(ApiCall::class)->disableOriginalConstructor()->getMock();

        $this->languageBatchBo = new LanguageBatchBo;
        $this->languageBatchBo->setOutputRenderer($this->renderer);
        $this->languageBatchBo->setCacheCreator($cacheCreator);
        $this->languageBatchBo->setApiCall($apiCall);
    }

    private function generationSucceeded()
    {
        $this->cacheCreator->method('create')->willReturn(true);
    }

    private function mockGenerateLanguageFilesApiCall()
    {
        $this->apiCall->method('call')
            ->withConsecutive(
                [
                    $this->equalTo('system_api'),
                    $this->equalTo('language_api'),
                    $this->equalTo(array(
                        'system' => 'LanguageFiles',
                        'action' => 'getLanguageFile'
                    )),
                    $this->equalTo(array('language' => 'en'))
                ],
                [
                    $this->equalTo('system_api'),
                    $this->equalTo('language_api'),
                    $this->equalTo(array(
                        'system' => 'LanguageFiles',
                        'action' => 'getLanguageFile'
                    )),
                    $this->equalTo(array('language' => 'hu'))
                ],
                [
                    $this->equalTo('system_api'),
                    $this->equalTo('language_api'),
                    $this->equalTo(array(
                        'system' => 'LanguageFiles',
                        'action' => 'getAppletLanguages'
                    )),
                    $this->equalTo(array('language' => 'hu'))
                ]
            )
            ->will($this->onConsecutiveCalls(
                ['status' => 'OK', 'data' => 'en_php_file_translations'],
                ['status' => 'OK', 'data' => 'hu_php_file_translations'],
                ['status' => 'OK', 'data' => ['en']]
            ));
    }

    private function mockGenerateAppletLanguageXmlFiles()
    {
        $this->apiCall->method('call')
            ->withConsecutive(
                [
                    $this->equalTo('system_api'),
                    $this->equalTo('language_api'),
                    $this->equalTo(array(
                        'system' => 'LanguageFiles',
                        'action' => 'getAppletLanguages'
                    )),
                    $this->equalTo(array('applet' => 'JSM2_MemberApplet'))
                ],
                [
                    $this->equalTo('system_api'),
                    $this->equalTo('language_api'),
                    $this->equalTo(array(
                        'system' => 'LanguageFiles',
                        'action' => 'getAppletLanguageFile'
                    )),
                    $this->equalTo(array(
                        'applet' => 'JSM2_MemberApplet',
                        'language' => 'en'
                    ))
                ]
            )
            ->will($this->onConsecutiveCalls(
                ['status' => 'OK', 'data' => ['en']],
                ['status' => 'OK', 'data' => 'xml_file']
            ));
    }

    public function testOutputWhenGenerateLanguageFiles()
    {
        $this->generationSucceeded();
        $this->mockGenerateLanguageFilesApiCall();

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
        $this->mockGenerateAppletLanguageXmlFiles();

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
        $this->mockGenerateLanguageFilesApiCall();

        $this->cacheCreator->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [$this->equalTo('/portal/en.php'), 'en_php_file_translations'],
                [$this->equalTo('/portal/hu.php'), 'hu_php_file_translations']
            )
            ->willReturn(true);

        $this->languageBatchBo->generateLanguageFiles();
    }

    public function testItGenerateAppletLanguageXmlFiles()
    {
        $this->mockGenerateAppletLanguageXmlFiles();

        $this->cacheCreator->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('/flash/lang_en.xml'),
                $this->equalTo('xml_file')
            )
            ->willReturn(true);

        $this->languageBatchBo->generateAppletLanguageXmlFiles();
    }
}
