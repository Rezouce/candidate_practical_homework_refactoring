<?php

namespace Language;

use League\Flysystem\FilesystemInterface;

class CacheGenerator
{

    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function create($path, $content)
    {
         $this->filesystem->put($path, $content);
    }
}
