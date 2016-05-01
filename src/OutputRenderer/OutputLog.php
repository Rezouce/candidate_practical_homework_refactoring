<?php

namespace Language\OutputRenderer;

class OutputLog implements OutputRenderer
{

    private $logs = [];
    
    public function render($text)
    {
        $this->logs[] = $text;
    }

    public function getLogs()
    {
        return $this->logs;
    }
}
