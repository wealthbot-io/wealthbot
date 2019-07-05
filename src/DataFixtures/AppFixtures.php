<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;

class AppFixtures extends ContainerAwareLoader
{
    public function loadFromDirectory($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('"%s" directory does not exist', $dir));
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        return $this->load($iterator);
    }

    public function loadFromFile($file)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('"%s" file does not exist', $file));
        }

        $iterator = new \ArrayIterator([new \SplFileInfo($file)]);

        return $this->load($iterator);
    }

    private function load(\Traversable $iterator)
    {
        $fixtures = [];
        $includedFiles = [];

        foreach ($iterator as $file) {
            if (($fileName = $file->getBasename('.php')) === $file->getBasename()) {
                continue;
            }
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }
        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $reflClass = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();

            if (in_array($sourceFile, $includedFiles) && !$this->isTransient($className)) {
                $fixture = new $className();
                $fixtures[] = $fixture;
                $this->addFixture($fixture);
            }
        }

        return $fixtures;
    }
}
