<?php

namespace Language;

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

    private $cacheGenerator;

    private $renderer;


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
                0,
                $e
            );
        }
    }

    private function createPhpFileWithTranslationFor($cachePath, $language, $content)
    {
        $filePath = "/$cachePath/$language.php";

        $result = $this->getCacheCreator()->create($filePath, $content);

        if (!$result) {
            throw new LanguageBatchException("Unable to generate language file: $filePath");
        }
    }

    private function getCacheCreator()
    {
        if (null === $this->cacheGenerator) {
            $cacheDirectory = Config::get('system.paths.root') . '/cache';

            $this->cacheGenerator = new CacheCreator(
                new Filesystem(new Local($cacheDirectory))
            );
        }

        return $this->cacheGenerator;
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
                throw new LanguageBatchException("There is no available languages for the $appletId applet.");
            }

            return $languages;
        } catch (ApiCallException $e) {
            throw new LanguageBatchException("Getting language for applet: ($appletId) was unsuccessful.", 0, $e);
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
                0,
                $e
            );
        }
    }

    private function createXmlFileForApplet($applet, $language, $content)
    {
        $filePath = "/flash/lang_$language.xml";

        $result = $this->getCacheCreator()->create($filePath, $content);

        if (!$result) {
            throw new LanguageBatchException("Unable to save applet: ($applet) language: ($language) xml ($filePath)!");
        }
    }

    protected function getResultFromApi($getParameters, $postParameters)
    {
        $languageResponse = ApiCall::call(
            'system_api',
            'language_api',
            $getParameters,
            $postParameters
        );

        $this->checkForApiErrorResult($languageResponse);

        return $languageResponse['data'];
    }

    protected function checkForApiErrorResult($result)
    {
        (new ApiCallCheck($result))->check();
    }
}
