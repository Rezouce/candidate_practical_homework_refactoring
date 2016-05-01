<?php

namespace Language\OutputRenderer;

class OutputConsole implements OutputRenderer
{

    public function render($text)
    {
        echo $text;
    }
}
