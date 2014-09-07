<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\ConfigReaderInterface;
use Maketok\Installer\Resource\Model\DdlClient;

class ConfigReader implements ConfigReaderInterface
{

    /**
     * @var array
     */
    private $_tree;

    /**
     * {@inheritdoc}
     */
    public function buildDependencyTree($clients)
    {
        usort($clients, array($this, 'dependencyBubbleSort'));
        foreach ($clients as $client) {
            /** @var DdlClient $client */
            foreach ($client->config as $table => $definition) {
                $branch = [
                    'client' => $client->id,
                    'version' => $client->version,
                    'definition' => $definition,
                    'dependents' => [],
                ];
                if (isset($this->_tree[$table])) {
                    $upperBranch = $this->_tree[$table];
                    if (!in_array($upperBranch['client'], $client->dependencies)) {
                        throw new DependencyTreeException(
                            sprintf("Client %s tries to modify resource %s without declaring dependency.", $client->code, $table)
                        );
                    } else {
                        $upperBranch['dependents'][] = $branch;
                    }
                } else {
                    $this->_tree[$table] = $branch;
                }
            }
        }
    }

    /**
     * @param DdlClient $a
     * @param DdlClient $b
     * @return int
     */
    public function dependencyBubbleSort(DdlClient $a, DdlClient $b)
    {
        if (count($a->dependencies) && !count($b->dependencies)) {
            return -1;
        } elseif (!count($a->dependencies) && count($b->dependencies)) {
            return 1;
        } elseif (count($a->dependencies) && count($b->dependencies)) {
            if (in_array($a->id, $b->dependencies)) {
                return 1;
            } elseif (in_array($b->id, $a->dependencies)) {
                return -1;
            }
        }
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function validateDependencyTree()
    {
        foreach ($this->_tree as $branch) {
            $this->recursiveDependentsValidation($branch);
        }
    }

    /**
     * @param array $branch
     * @throws DependencyTreeException
     */
    public function recursiveDependentsValidation(array $branch)
    {
        if (!count($branch['dependents'])) {
            return;
        }
        // now get it :)
        $params = [];
        foreach ($branch['dependents'] as $dBranch) {
            $params[] = $dBranch['definition'];
        }
        $intersects = $this->recursiveArrayUIntersectAssoc($params, function($a, $b) {
            if (is_string($a) && !is_string($b)) {
                return 1;
            } elseif (is_string($a) && is_string($b)) {
                if ($a != $b) {
                    return 0;
                }
            }
            return -1;
        });
        if (count($intersects)) {
            throw new DependencyTreeException(
                sprintf("The clients conflicts with each other. Map: %s", print_r($intersects, true))
            );
        }
    }

    /**
     * @param array $compares
     * @param callable $sort
     * @return array
     */
    public function recursiveArrayUIntersectAssoc(array $compares, \Closure $sort)
    {
        $res = call_user_func_array('array_uintersect_assoc', ($compares + [$sort]));
        $next = [];
        foreach ($compares as $compare) {
            if (is_array($compare)) {
                foreach ($compare as $comp) {
                    $next[] = $comp;
                }
            }
        }
        return array_replace_recursive($res, $this->recursiveArrayUIntersectAssoc($next, $sort));
    }

    /**
     * {@inheritdoc}
     */
    public function mergeDependencyTree()
    {
        foreach ($this->_tree as $branch) {
            $branch['definition'] = $this->recursiveMerge($branch);
        }
    }

    /**
     * @param array $branch
     * @return array
     */
    public function recursiveMerge(array $branch)
    {
        $res = $branch['definition'];
        foreach ($branch['dependents'] as $dBranch) {
            $res = array_replace_recursive($res, $this->recursiveMerge($dBranch));
        }
        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencyTree()
    {
        return $this->_tree;
    }
}
