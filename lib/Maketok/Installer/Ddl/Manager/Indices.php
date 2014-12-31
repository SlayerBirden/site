<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Maketok\Installer\Ddl\Manager;

use Maketok\Installer\Ddl\Directives;
use Maketok\Util\ArrayValueTrait;

class Indices implements CompareInterface
{
    use ArrayValueTrait;
    /**
     * {@inheritdoc}
     */
    public function intlCompare(array $indexA, array $indexB, $tableName, Directives $directives)
    {
        foreach ($indexB as $indexName => $indexDefinition) {
            $bInA = $this->getIfExists($indexName, $indexA);
            $new = $this->getIfExists('definition', $indexDefinition);
            $old = $this->getIfExists('definition', $bInA);
            if (is_null($bInA)) {
                $directives->addProp('addIndices', [$tableName, $indexName, $indexDefinition]);
            } elseif ($new === $old) {
                continue;
            } else {
                $directives->addProp('dropIndices', [$tableName, $indexDefinition]);
                $directives->addProp('addIndices', [$tableName, $indexName, $indexDefinition]);
            }
        }
        foreach ($indexA as $indexName => $indexDefinition) {
            $aInB = $this->getIfExists($indexName, $indexB);
            if (is_null($aInB)) {
                $directives->addProp('dropIndices', [$tableName, $indexName]);
            }
        }
    }
}
