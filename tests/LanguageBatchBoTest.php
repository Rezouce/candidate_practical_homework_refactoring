<?php
namespace Language;

use Language\Api\ApiCall;
use Language\Config\Config;
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

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $config;


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

        /** @var Config $config */
        $this->config = $config
            = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->mockConfig();

        $this->languageBatchBo = new LanguageBatchBo;
        $this->languageBatchBo->setOutputRenderer($this->renderer);
        $this->languageBatchBo->setCacheCreator($cacheCreator);
        $this->languageBatchBo->setApiCall($apiCall);
        $this->languageBatchBo->setConfig($config);
    }

    private function mockGenerationSucceeded()
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
                    $this->equalTo(array('language' => 'fr'))
                ],
                [
                    $this->equalTo('system_api'),
                    $this->equalTo('language_api'),
                    $this->equalTo(array(
                        'system' => 'LanguageFiles',
                        'action' => 'getLanguageFile'
                    )),
                    $this->equalTo(array('language' => 'de'))
                ]
            )
            ->will($this->onConsecutiveCalls(
                ['status' => 'OK', 'data' => 'en_php_file_translations'],
                ['status' => 'OK', 'data' => 'hu_php_file_translations']
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

    private function mockConfig()
    {
        $this->config->method('get')
            ->withConsecutive(
                [$this->equalTo('system.translated_applications')],
                [$this->equalTo('system.paths.root')]
            )
            ->will($this->onConsecutiveCalls(
                ['appletId' => ['fr', 'de']],
                ['systemPathsRoot']
            ));
    }

    public function testOutputWhenGenerateLanguageFiles()
    {
        $this->mockGenerationSucceeded();
        $this->mockGenerateLanguageFilesApiCall();

        $this->languageBatchBo->generateLanguageFiles();

        $logs = [
            "\nGenerating language files.\n",
            "[APPLICATION: appletId]\n",
            "\t[LANGUAGE: fr] OK\n",
            "\t[LANGUAGE: de] OK\n",
        ];

        $this->assertEquals($logs, $this->renderer->getLogs());
    }

    public function testOutputWhenGenerateAppletLanguageXmlFiles()
    {
        $this->mockGenerationSucceeded();
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
                [$this->equalTo('/appletId/fr.php'), 'en_php_file_translations'],
                [$this->equalTo('/appletId/de.php'), 'hu_php_file_translations']
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

    public function testApiCallFailRetrievingFileDuringGenerateLanguageFiles()
    {
        $this->apiCall->method('call')
            ->willReturn(['status' => 'error']);

        $this->setExpectedException(LanguageBatchException::class, '', LanguageBatchException::FAIL_RETRIEVING_FILE);

        $this->languageBatchBo->generateLanguageFiles();
    }

    public function testApiCallFailSavingFileDuringGenerateLanguageFiles()
    {
        $this->mockGenerateLanguageFilesApiCall();
        $this->cacheCreator->method('create')->willReturn(false);

        $this->setExpectedException(LanguageBatchException::class, '', LanguageBatchException::FAIL_SAVING_FILE);

        $this->languageBatchBo->generateLanguageFiles();
    }

    public function testApiCallFailDuringGenerateAppletLanguageXmlFiles()
    {
        $this->apiCall->method('call')
            ->willReturn(['status' => 'error']);

        $this->setExpectedException(LanguageBatchException::class, '', LanguageBatchException::FAIL_RETRIEVING_FILE);

        $this->languageBatchBo->generateAppletLanguageXmlFiles();
    }

    public function testApiCallFailDuringGenerateAppletLanguageXmlFiles2()
    {
        $this->apiCall->method('call')
            ->with(
                $this->equalTo('system_api'),
                $this->equalTo('language_api'),
                $this->equalTo(array(
                    'system' => 'LanguageFiles',
                    'action' => 'getAppletLanguages'
                )),
                $this->equalTo(array('applet' => 'JSM2_MemberApplet'))
            )
            ->willReturn(['status' => 'OK', 'data' => '']);

        $this->setExpectedException(
            LanguageBatchException::class,
            '',
            LanguageBatchException::NO_AVAILABLE_LANGUAGE_FOR_APPLET
        );

        $this->languageBatchBo->generateAppletLanguageXmlFiles();
    }

    public function testApiCallFailSavingFileDuringGenerateAppletLanguageXmlFiles()
    {
        $this->mockGenerateAppletLanguageXmlFiles();
        $this->cacheCreator->method('create')->willReturn(false);

        $this->setExpectedException(LanguageBatchException::class, '', LanguageBatchException::FAIL_SAVING_FILE);

        $this->languageBatchBo->generateAppletLanguageXmlFiles();
    }
}
