<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\Ddl\Resource\Model\DdlClient;

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
     * validation is happening here
     * @throws DependencyTreeException
     */
    public function buildDependencyTree($clients)
    {
        if (is_array($this->_tree) && !empty($this->_tree)) {
            throw new DependencyTreeException("Invalid context of calling method. The tree is already built.");
        }
        try {
            usort($clients, array($this, 'dependencyBubbleSortCallback'));
        } catch (\Exception $e) {
            // for now suppress the error. It will be revealed later
        }
        foreach ($clients as $client) {
            if (!is_object($client)) {
                throw new DependencyTreeException(sprintf("Invalid Client. Type: %s", gettype($client)));
            }
            if (!($client instanceof DdlClient)) {
                throw new DependencyTreeException(sprintf("Invalid Client. Class: %s", get_class($client)));
            }
            /** @var DdlClient $client */
            foreach ($client->config as $table => $definition) {
                $branch = [
                    'client' => $client->code,
                    'version' => $client->version,
                    'definition' => $definition,
                    'dependents' => [],
                ];
                if (isset($this->_tree[$table])) {
                    if (!in_array($this->_tree[$table]['client'], $client->dependencies)) {
                        throw new DependencyTreeException(
                            sprintf("Client %s tries to modify resource %s without declaring dependency.",
                                $client->code,
                                $table)
                        );
                    } else {
                        $this->_tree[$table]['dependents'][] = $branch;
                    }
                } else {
                    if ($client->dependencies) {
                        foreach ($client->dependencies as $dependency) {
                            if (!isset($this->_tree[$dependency])) {
                                throw new DependencyTreeException(
                                    sprintf("Unresolved dependency '%s' for client %s.", $dependency, $client->code));
                            }
                        }
                    }
                    $this->_tree[$table] = $branch;
                }
            }
        }
    }

    /**
     * @param  DdlClient $a
     * @param  DdlClient $b
     * @return int
     */
    public function dependencyBubbleSortCallback(DdlClient $a, DdlClient $b)
    {
        if (count($a->dependencies) && !count($b->dependencies)) {
            return 1;
        } elseif (!count($a->dependencies) && count($b->dependencies)) {
            return -1;
        } elseif (count($a->dependencies) && count($b->dependencies)) {
            if (in_array($a->code, $b->dependencies)) {
                return -1;
            } elseif (in_array($b->code, $a->dependencies)) {
                return 1;
            }
        }
        // this makes sort stable
        if (is_null($a->id) && !is_null($b->id)) {
            return 1;
        }
        if (is_null($b->id) && !is_null($a->id)) {
            return -1;
        }
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
        // not in use now
    }

    /**
     * {@inheritdoc}
     * @throws DependencyTreeException
     */
    public function mergeDependencyTree()
    {
        if ($this->_isMerged) {
            return;
        }
        if (is_null($this->_tree)) {
            throw new DependencyTreeException("Invalid context of calling method. The tree is not built yet.");
        }
        foreach ($this->_tree as &$branch) {
            $branch['definition'] = $this->recursiveMerge($branch);
            if (isset($branch['dependents'])) {
                unset($branch['dependents']);
            }
        }
        $this->_isMerged = true;
    }

    /**
     * @param  array $branch
     * @return array
     */
    public function recursiveMerge(array $branch)
    {
        $res = $branch['definition'];
        if (isset($branch['dependents'])) {
            foreach ($branch['dependents'] as $dBranch) {
                $res = array_replace_recursive($res, $this->recursiveMerge($dBranch));
            }
        }

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencyTree()
    {
        return $this->getTree();
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
     * @param  null|string $table
     * @return array|null
     */
    private function getTree($table = null)
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
        foreach ($this->getTree() as $table => $branch) {
            $config[$table] = $branch['definition'];
        }

        return $config;
    }
}
