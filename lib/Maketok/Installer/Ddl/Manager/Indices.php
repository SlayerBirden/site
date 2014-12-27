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

class Indices implements CompareInterface
{
    /**
     * {@inheritdoc}
     */
    public function intlCompare(array $a, array $b, $tableName, Directives $directives)
    {
        foreach ($b as $indexName => $indexDefinition) {
            if (!array_key_exists($indexName, $a)) {
                $directives->addProp('addIndices', [$tableName, $indexName, $indexDefinition]);
            } elseif ($indexDefinition['definition'] === $a[$indexName]['definition']) {
                continue;
            } else {
                $directives->addProp('dropIndices', [$tableName, $indexDefinition]);
                $directives->addProp('addIndices',
                    [$tableName, $indexName, $indexDefinition]);
            }
        }
        foreach ($a as $indexName => $indexDefinition) {
            if (!array_key_exists($indexName, $b)) {
                $directives->addProp('dropIndices', [$tableName, $indexName]);
            }
        }
    }
}
