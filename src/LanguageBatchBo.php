<?php

namespace Language;

use Language\Api\ApiCall;
use Language\Api\ApiCallCheck;
use Language\Api\ApiCallException;
use Language\OutputRenderer\OutputConsole;
use Language\OutputRenderer\OutputRenderer;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo
{

    private $cacheCreator;

    private $renderer;

    private $apiCall;


    private function render($text)
    {
        if (null === $this->renderer) {
            $this->renderer = new OutputConsole;
        }

        $this->renderer->render($text);
    }

    public function setOutputRenderer(OutputRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function generateLanguageFiles()
    {
        $this->render("\nGenerating language files.\n");

        $availableLanguages = Config::get('system.translated_applications');

        foreach ($availableLanguages as $application => $languages) {
            $this->generateLanguageFilesForApplication($application, $languages);
        }
    }

    private function generateLanguageFilesForApplication($applicationName, $languages)
    {
        $this->render("[APPLICATION: " . $applicationName . "]\n");

        foreach ($languages as $language) {
            $content = $this->getPhpFileWithTranslationsForLanguage($language);

            $this->createPhpFileWithTranslationFor($applicationName, $language, $content);

            $this->render("\t[LANGUAGE: " . $language . "] OK\n");
        }
    }

    private function getPhpFileWithTranslationsForLanguage($language)
    {
        try {
            return $this->getResultFromApi(
                array(
                    'system' => 'LanguageFiles',
                    'action' => 'getLanguageFile'
                ),
                array('language' => $language)
            );
        } catch (ApiCallException $e) {
            throw new LanguageBatchException(
                "Error during API call when trying to retrieve the translation file for language: $language",
                LanguageBatchException::FAIL_RETRIEVING_FILE,
                $e
            );
        }
    }

    private function createPhpFileWithTranslationFor($cachePath, $language, $content)
    {
        $filePath = "/$cachePath/$language.php";

        $result = $this->getCacheCreator()->create($filePath, $content);

        if (!$result) {
            throw new LanguageBatchException(
                "Unable to generate language file: $filePath",
                LanguageBatchException::FAIL_SAVING_FILE
            );
        }
    }

    private function getCacheCreator()
    {
        if (null === $this->cacheCreator) {
            $cacheDirectory = Config::get('system.paths.root') . '/cache';

            $this->cacheCreator = new CacheCreator(
                new Filesystem(new Local($cacheDirectory))
            );
        }

        return $this->cacheCreator;
    }
    
    public function setCacheCreator(CacheCreator $cacheCreator)
    {
        $this->cacheCreator = $cacheCreator;
    }

    public function generateAppletLanguageXmlFiles()
    {
        // List of the applets [directory => applet_id].
        $applets = array(
            'memberapplet' => 'JSM2_MemberApplet',
        );

        $this->render("\nGetting applet language XMLs.\n");

        foreach ($applets as $directory => $appletId) {
            $this->generateAppletLanguageXmlFilesForApplet($appletId, $directory);
        }

        $this->render("\nApplet language XMLs generated.\n");
    }

    private function generateAppletLanguageXmlFilesForApplet($appletId, $appletDirectory)
    {
        $this->render(" Getting > $appletId ($appletDirectory) language xmls.\n");

        $availableLanguages = $this->getAvailableLanguagesForApplet($appletId);

        $this->render(' - Available languages: ' . implode(', ', $availableLanguages) . "\n");

        foreach ($availableLanguages as $language) {
            $content = $this->getXmlFileForApplet($appletId, $language);

            $this->createXmlFileForApplet($appletId, $language, $content);

            $this->render(" OK saving applet: ($appletId) language: ($language) was successful.\n");
        }

        $this->render(" < $appletId ($appletDirectory) language xml cached.\n");
    }

    private function getAvailableLanguagesForApplet($appletId)
    {
        try {
            $languages = $this->getResultFromApi(
                array(
                    'system' => 'LanguageFiles',
                    'action' => 'getAppletLanguages'
                ),
                array('applet' => $appletId)
            );

            if (empty($languages)) {
                throw new LanguageBatchException(
                    "There is no available languages for the $appletId applet.",
                    LanguageBatchException::NO_AVAILABLE_LANGUAGE_FOR_APPLET
                );
            }

            return $languages;
        } catch (ApiCallException $e) {
            throw new LanguageBatchException(
                "Getting language for applet: ($appletId) was unsuccessful.",
                LanguageBatchException::FAIL_RETRIEVING_FILE,
                $e
            );
        }
    }

    private function getXmlFileForApplet($appletId, $language)
    {
        try {
            return $this->getResultFromApi(
                array(
                    'system' => 'LanguageFiles',
                    'action' => 'getAppletLanguageFile'
                ),
                array(
                    'applet' => $appletId,
                    'language' => $language
                )
            );
        } catch (ApiCallException $e) {
            throw new LanguageBatchException(
                "Getting language xml for applet: ($appletId) on language: ($language) was unsuccessful.",
                LanguageBatchException::FAIL_RETRIEVING_LANGUAGE_FOR_APPLET,
                $e
            );
        }
    }

    private function createXmlFileForApplet($applet, $language, $content)
    {
        $filePath = "/flash/lang_$language.xml";

        $result = $this->getCacheCreator()->create($filePath, $content);

        if (!$result) {
            throw new LanguageBatchException(
                "Unable to save applet: ($applet) language: ($language) xml ($filePath)!",
                LanguageBatchException::FAIL_SAVING_FILE
            );
        }
    }

    private function getResultFromApi($getParameters, $postParameters)
    {
        if (null === $this->apiCall) {
            $this->apiCall = new ApiCall;
        }
        
        $languageResponse = $this->apiCall->call('system_api', 'language_api', $getParameters, $postParameters);

        $this->checkForApiErrorResult($languageResponse);

        return $languageResponse['data'];
    }

    public function setApiCall(ApiCall $apiCall)
    {
        $this->apiCall = $apiCall;
    }

    private function checkForApiErrorResult($result)
    {
        (new ApiCallCheck($result))->check();
    }
}
