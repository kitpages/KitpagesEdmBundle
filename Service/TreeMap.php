<?php

namespace Kitpages\EdmBundle\Service;

/**
 * Holds references to all declared edms
 * and allows to access them through their name
 */
class TreeMap
{
    /**
     * Map of edms indexed by their name
     *
     * @var array
     */
    private $map;

    /**
     * Instanciates a new edm map
     *
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param string $name name of a edm
     * @throw \InvalidArgumentException if the edm does not exist
     * @return Edm
     */
    public function getEdm($name)
    {
        if (!isset($this->map[$name])) {
            throw new \InvalidArgumentException(sprintf('No edm register for name "%s"', $name));
        }

        return $this->map[$name];
    }

    public function getTreeList()
    {
        return $this->map;
    }
}
