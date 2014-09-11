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
    /** @var bool */
    private $_isMerged = false;

    /**
     * {@inheritdoc}
     */
    public function buildDependencyTree($clients)
    {
        usort($clients, array($this, 'dependencyBubbleSortCallback'));
        foreach ($clients as $client) {
            /** @var DdlClient $client */
            foreach ($client->config as $table => $definition) {
                $branch = [
                    'client' => $client->id,
                    'version' => $client->version,
                    'definition' => $definition,
                    'dependents' => [],
                ];
                if ($upperBranch = &$this->_getTree($table)) {
                    if (!in_array($upperBranch['client'], $client->dependencies)) {
                        throw new DependencyTreeException(
                            sprintf("Client %s tries to modify resource %s without declaring dependency.",
                                $client->code,
                                $table)
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
    public function dependencyBubbleSortCallback(DdlClient $a, DdlClient $b)
    {
        if (count($a->dependencies) && !count($b->dependencies)) {
            return 1;
        } elseif (!count($a->dependencies) && count($b->dependencies)) {
            return -1;
        } elseif (count($a->dependencies) && count($b->dependencies)) {
            if (in_array($a->id, $b->dependencies)) {
                return -1;
            } elseif (in_array($b->id, $a->dependencies)) {
                return 1;
            }
        }
        // this makes sort stable
        if ($a->id > $b->id) {
            return 1;
        } elseif ($b->id > $a->id) {
            return -1;
        }
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function validateDependencyTree()
    {
        // not needed now
    }

    /**
     * {@inheritdoc}
     */
    public function mergeDependencyTree()
    {
        if ($this->_isMerged) {
            return;
        }
        foreach ($this->_getTree() as &$branch) {
            $branch['definition'] = $this->recursiveMerge($branch);
            if (isset($branch['dependents'])) {
                unset($branch['dependents']);
            }
        }
        $this->_isMerged = true;
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
        return $this->_getTree();
    }

    /**
     * @return bool
     */
    public function getIsMerged()
    {
        return $this->_isMerged;
    }

    /**
     * internal getter
     * @param null|string $table
     * @return array|null
     */
    private function &_getTree($table = null)
    {
        if (!isset($this->_tree)) {
            return [];
        }
        if (is_string($table)) {
            if (isset($this->_tree[$table])) {
                return $this->_tree[$table];
            } else {
                return null;
            }
        }
        return $this->_tree;
    }

    /**
     * {@inheritdoc}
     */
    public function getMergedConfig()
    {
        if (!$this->_isMerged) {
            $this->mergeDependencyTree();
        }
        $config = [];
        foreach ($this->_getTree() as $table => $branch) {
            $config[$table] = $branch['definition'];
        }
        return $config;
    }
}
