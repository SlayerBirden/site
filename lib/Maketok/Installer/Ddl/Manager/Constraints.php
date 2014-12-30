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
use Maketok\Installer\Exception;
use Maketok\Util\ArrayValueTrait;

class Constraints implements CompareInterface
{
    use ArrayValueTrait;

    const CONDITION_AND = 'and';
    const CONDITION_OR = 'or';

    /**
     * {@inheritdoc}
     * @throws \Maketok\Installer\Exception
     */
    public function intlCompare(array $constraintA, array $constraintB, $tableName, Directives $directives)
    {
        foreach ($constraintB as $constraintName => $constraintDefinition) {
            $bInA = $this->getIfExists($constraintName, $constraintA);
            if (is_null($bInA)) {
                $directives->addProp('addConstraints',
                    [$tableName, $constraintName, $constraintDefinition]);
            } elseif ($this->getCompareCondition(['definition'], $constraintDefinition, $bInA)
                || $this->getCompareCondition([
                    'column',
                    'reference_table',
                    'reference_column',
                    'on_delete',
                    'on_delete',
                ], $constraintDefinition, $bInA)) {
                // now we need to check if in fact the reference column got changed
                foreach ($directives->changeColumns as $columnDirective) {
                    $refCol = $this->getIfExists('reference_column', $constraintDefinition); // if fk
                    $col = $this->getIfExists(1, $columnDirective, '');
                    if ($refCol === $col) {
                        $oldType = $this->getIfExists('type', $bInA, function () {
                            throw new Exception("Old Constraint definition doesn't have type.");
                        });
                        $directives->addProp('dropConstraints', [$tableName, $constraintName, $oldType]);
                        $directives->addProp('addConstraints', [$tableName, $constraintName, $constraintDefinition]);
                    }
                }
                continue;
            } else {
                $directives->addProp('dropConstraints', [$tableName, $constraintName, $constraintA[$constraintName]['type']]);
                $directives->addProp('addConstraints',
                    [$tableName, $constraintName, $constraintDefinition]);
            }
        }
        foreach ($constraintA as $constraintName => $constraintDefinition) {
            $aInB = $this->getIfExists($constraintName, $constraintB);
            $type = $this->getIfExists('type', $constraintDefinition, function () {
                throw new Exception("Old Constraint definition doesn't have type.");
            });
            if (is_null($aInB)) {
                $directives->addProp('dropConstraints', [$tableName, $constraintName, $type]);
            }
        }
    }

    /**
     * @param string[] $keys
     * @param array $oldDef
     * @param array $newDef
     * @param string $condition
     * @return bool|mixed
     */
    protected function getCompareCondition(array $keys, array $oldDef, array $newDef, $condition = self::CONDITION_AND)
    {
        $conditions = [];
        foreach ($keys as $key) {
            $old = $this->getIfExists($key, $oldDef);
            $new = $this->getIfExists($key, $newDef);
            $conditions[] = $old == $new;
        }
        if ($condition === self::CONDITION_AND) {
            $result = true;
            while (($res = array_pop($conditions)) !== null) {
                $result &= $res;
            }
        } else {
            $result = false;
            while (($res = array_pop($condition)) !== null) {
                $result |= $res;
            }
        }
        return $result;
    }
}
