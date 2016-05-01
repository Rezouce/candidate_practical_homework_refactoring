<?php
namespace Language;

class LanguageBatchException extends \Exception
{
    const FAIL_RETRIEVING_FILE = 1;
    const FAIL_SAVING_FILE = 2;
    const FAIL_RETRIEVING_LANGUAGE_FOR_APPLET = 3;
    const NO_AVAILABLE_LANGUAGE_FOR_APPLET = 4;
}
