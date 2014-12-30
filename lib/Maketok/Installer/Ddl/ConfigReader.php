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
    private $tree;
    /**
     * @var bool
     */
    private $isMerged = false;

    /**
     * {@inheritdoc}
     * validation is happening here
     * @throws DependencyTreeException
     */
    public function buildDependencyTree($clients)
    {
        if (is_array($this->tree) && !empty($this->tree)) {
            throw new DependencyTreeException("Invalid context of calling method. The tree is already built.");
        }
        usort($clients, array($this, 'dependencyBubbleSortCallback'));
        foreach ($clients as $client) {
            foreach ($client->getConfig() as $table => $definition) {
                $branch = [
                    'client' => $client->code,
                    'version' => $client->version,
                    'definition' => $definition,
                    'dependents' => [],
                ];
                if (isset($this->tree[$table])) {
                    if (!in_array($this->tree[$table]['client'], $client->getDependencies())) {
                        throw new DependencyTreeException(
                            sprintf("Client %s tries to modify resource %s without declaring dependency.",
                                $client->code,
                                $table)
                        );
                    } else {
                        $this->tree[$table]['dependents'][] = $branch;
                    }
                } else {
                    foreach ($client->getDependencies() as $dependency) {
                        if (!isset($this->tree[$dependency])) {
                            throw new DependencyTreeException(
                                sprintf("Unresolved dependency '%s' for client %s.", $dependency, $client->code));
                        }
                    }
                    $this->tree[$table] = $branch;
                }
            }
        }
    }

    /**
     * the client with more dependencies move up
     * *on practice moving up means it's displayed down (because greater numbers are down in ascending sort)
     *
     * scenarios:
     *  - client a has dependency, client b doesn't -> a goes up
     *  - client a in listed in clients' b dependencies -> b goes up
     *  - both clients depend on each other -> the greater id goes up
     *      - if one id is null -> null goes up
     * @param  DdlClient $clientA
     * @param  DdlClient $clientB
     * @return int
     */
    public function dependencyBubbleSortCallback(DdlClient $clientA, DdlClient $clientB)
    {
        $aGotDependencies = count($clientA->getDependencies()) > 0;
        $bGotDependencies = count($clientB->getDependencies()) > 0;
        $aInb = in_array($clientA->code, $clientB->getDependencies());
        $bIna = in_array($clientB->code, $clientA->getDependencies());

        // next block is to make sort stable
        $isNullAid = is_null($clientA->id);
        $isNullBid = is_null($clientB->id);
        $clientAgreaterB = $clientA->id > $clientB->id;

        switch ((string) ((int) $aGotDependencies . (int) $bGotDependencies . (int) $aInb . (int) $bIna)) {
            case '1000':// a got dependencies, b does not
            case '1001':
            case '1010':
            case '1011':
            case '1101':// b in a
                return 1;
            case '0100':// b got dependencies, a does not
            case '0101':
            case '0110':
            case '0111':
            case '1110':// a in b
                return -1;
            default:// all edge cases
                break;
        }
        switch ((string) ((int) $isNullAid . (int) $isNullBid . (int) $clientAgreaterB)) {
            case '100':// a is null
            case '101':
            case '001':// a > b
                return 1;
            case '010':// b is null
            case '011':
            case '000':// b > a
                return -1;
            default:// all edge cases
                break;
        }
        return 0;
    }

    /**
     * {@inheritdoc}
     * @throws DependencyTreeException
     */
    public function mergeDependencyTree()
    {
        if ($this->isMerged) {
            return;
        }
        if (is_null($this->tree)) {
            throw new DependencyTreeException("Invalid context of calling method. The tree is not built yet.");
        }
        foreach ($this->tree as &$branch) {
            $branch['definition'] = $this->recursiveMerge($branch);
            if (isset($branch['dependents'])) {
                unset($branch['dependents']);
            }
        }
        $this->isMerged = true;
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
     * internal getter
     * @param  null|string $table
     * @return array|null
     */
    private function getTree($table = null)
    {
        if (!isset($this->tree)) {
            return [];
        }
        if (is_string($table)) {
            if (isset($this->tree[$table])) {
                return $this->tree[$table];
            } else {
                return null;
            }
        }

        return $this->tree;
    }

    /**
     * {@inheritdoc}
     */
    public function getMergedConfig()
    {
        if (!$this->isMerged) {
            $this->mergeDependencyTree();
        }
        $config = [];
        foreach ($this->getTree() as $table => $branch) {
            $config[$table] = $branch['definition'];
        }

        return $config;
    }
}
