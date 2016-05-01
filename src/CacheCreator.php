<?php

namespace Language;

use League\Flysystem\FilesystemInterface;

class CacheCreator
{

    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function create($path, $content)
    {
         return $this->filesystem->put($path, $content);
    }
}
