<?php

namespace Language;

use Language\Api\ApiCallCheck;
use Language\Api\ApiCallException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo
{

    private $cacheGenerator;


    public function generateLanguageFiles()
    {
        $availableLanguages = Config::get('system.translated_applications');

        echo "\nGenerating language files\n";

        foreach ($availableLanguages as $cachePath => $languages) {
            echo "[APPLICATION: " . $cachePath . "]\n";

            foreach ($languages as $language) {
                $data = $this->getLanguageFile($language);

                $this->createLanguageFile("/$cachePath/$language.php", $data);

                echo "\t[LANGUAGE: " . $language . "] OK\n";
            }
        }
    }

    protected function getLanguageFile($language)
    {
        try {
            return $this->getApiCallResult(
                array(
                    'system' => 'LanguageFiles',
                    'action' => 'getLanguageFile'
                ),
                array('language' => $language)
            );
        } catch (ApiCallException $e) {
            throw new LanguageBatchException("Error during API call for retrieving language file: $language", 0, $e);
        }
    }

    private function createLanguageFile($filePath, $content)
    {
        $result = $this->getCacheGenerator()->create($filePath, $content);

        if (!$result) {
            throw new LanguageBatchException('Unable to generate language file!');
        }
    }

    private function getCacheGenerator()
    {
        if (null === $this->cacheGenerator) {
            $this->cacheGenerator = new CacheGenerator(
                new Filesystem(new Local(Config::get('system.paths.root') . '/cache'))
            );
        }

        return $this->cacheGenerator;
    }

    public static function generateAppletLanguageXmlFiles()
	{
		// List of the applets [directory => applet_id].
		$applets = array(
			'memberapplet' => 'JSM2_MemberApplet',
		);

		echo "\nGetting applet language XMLs..\n";

		foreach ($applets as $appletDirectory => $appletLanguageId) {
			echo " Getting > $appletLanguageId ($appletDirectory) language xmls..\n";
			$languages = self::getAppletLanguages($appletLanguageId);
			if (empty($languages)) {
				throw new \Exception('There is no available languages for the ' . $appletLanguageId . ' applet.');
			}
			else {
				echo ' - Available languages: ' . implode(', ', $languages) . "\n";
			}
			$path = Config::get('system.paths.root') . '/cache/flash';
			foreach ($languages as $language) {
				$xmlContent = self::getAppletLanguageFile($appletLanguageId, $language);
				$xmlFile    = $path . '/lang_' . $language . '.xml';
				if (strlen($xmlContent) == file_put_contents($xmlFile, $xmlContent)) {
					echo " OK saving $xmlFile was successful.\n";
				}
				else {
					throw new \Exception('Unable to save applet: (' . $appletLanguageId . ') language: (' . $language
						. ') xml (' . $xmlFile . ')!');
				}
			}
			echo " < $appletLanguageId ($appletDirectory) language xml cached.\n";
		}

		echo "\nApplet language XMLs generated.\n";
	}


    /**
	 * Gets the available languages for the given applet.
	 *
	 * @param string $applet The applet identifier.
	 * @return array The list of the available applet languages.
	 * @throws \Exception
	 */
	protected static function getAppletLanguages($applet)
	{
		$result = ApiCall::call(
			'system_api',
			'language_api',
			array(
				'system' => 'LanguageFiles',
				'action' => 'getAppletLanguages'
			),
			array('applet' => $applet)
		);

		try {
			self::checkForApiErrorResult($result);
		}
		catch (\Exception $e) {
			throw new \Exception('Getting languages for applet (' . $applet . ') was unsuccessful ' . $e->getMessage());
		}

		return $result['data'];
	}

    /**
	 * Gets a language xml for an applet.
	 *
	 * @param string $applet The identifier of the applet.
	 * @param string $language The language identifier.
	 * @return false|string The content of the language file or false if weren't able to get it.
	 * @throws \Exception
	 */
	protected static function getAppletLanguageFile($applet, $language)
	{
		$result = ApiCall::call(
			'system_api',
			'language_api',
			array(
				'system' => 'LanguageFiles',
				'action' => 'getAppletLanguageFile'
			),
			array(
				'applet' => $applet,
				'language' => $language
			)
		);

		try {
			self::checkForApiErrorResult($result);
		}
		catch (\Exception $e) {
			throw new \Exception('Getting language xml for applet: (' . $applet . ') on language: (' . $language . ') was unsuccessful: '
				. $e->getMessage());
		}

		return $result['data'];
	}

    /**
	 * Checks the api call result.
	 *
	 * @param mixed $result The api call result to check.
	 *
	 * @throws \Exception
	 */
	protected static function checkForApiErrorResult($result)
	{
        (new ApiCallCheck($result))->check();
	}

    protected function getApiCallResult($getParameters, $postParameters)
    {
        $languageResponse = ApiCall::call(
            'system_api',
            'language_api',
            $getParameters,
            $postParameters
        );

        self::checkForApiErrorResult($languageResponse);

        return $languageResponse['data'];
    }
}
