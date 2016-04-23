<?php

namespace Pas;

use Model\Pas\Repository\BaseRepository;

class Pas
{
    /**
     * @var array
     */
    protected $repositories;

    /**
     * Add repository
     *
     * @param BaseRepository $repository
     */
    public function addRepository(BaseRepository $repository)
    {
        $reflect = new \ReflectionClass($repository);
        $name = str_replace('Repository', '', $reflect->getShortName());

        $this->repositories[$name] = $repository;
    }

    /**
     * Get repository
     *
     * @param string $name
     * @return BaseRepository
     */
    public function getRepository($name)
    {
        if (isset($this->repositories[$name])) {
            return $this->repositories[$name];
        }
        return null;
    }
}