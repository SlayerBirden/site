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

class Columns implements CompareInterface
{
    use ArrayValueTrait;
    /**
     * {@inheritdoc}
     */
    public function intlCompare(array $columnA, array $columnB, $tableName, Directives $directives)
    {
        $changeMap = [];
        foreach ($columnB as $columnName => $columnDefinition) {
            $bInA = $this->getIfExists($columnName, $columnA);
            $oldName = $this->getIfExists('old_name', $columnDefinition);
            if (is_null($bInA) && is_null($oldName)) {
                $directives->addProp('addColumns', [$tableName, $columnName, $columnDefinition]);
            } elseif (is_string($oldName)) {
                $directives->addProp('changeColumns', [
                    $tableName,
                    $oldName,
                    $columnName,
                    $columnDefinition,
                ]);
                $changeMap[$oldName] = $tableName;
            } elseif ($columnDefinition == $bInA) { // not strict compare because scalar types may differ
                continue;
            } else {
                // now we need to make sure new definitions contain same keys as old ones
                $newDefinition = $columnDefinition;
                $oldDefinition = $bInA;
                foreach ($oldDefinition as $key => $value) {
                    if (!isset($newDefinition[$key])) {
                        unset($oldDefinition[$key]);
                    }
                }
                // not strict compare because scalar types may differ
                if (!($oldDefinition == $newDefinition)) {
                    $directives->addProp('changeColumns', [$tableName, $columnName, $columnName, $newDefinition]);
                }
            }
        }
        foreach ($columnA as $columnName => $columnDefinition) {
            $aInB = $this->getIfExists($columnName, $columnB);
            if (is_null($aInB) && !isset($changeMap[$columnName])) {
                $directives->addProp('dropColumns', [$tableName, $columnName]);
            }
        }
    }
}
