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

class Constraints implements CompareInterface
{
    /**
     * {@inheritdoc}
     */
    public function intlCompare(array $a, array $b, $tableName, Directives $directives)
    {
        foreach ($b as $constraintName => $constraintDefinition) {
            if (!array_key_exists($constraintName, $a)) {
                $directives->addProp('addConstraints',
                    [$tableName, $constraintName, $constraintDefinition]);
            } elseif ((isset($constraintDefinition['definition']) &&
                    $constraintDefinition['definition'] === $a[$constraintName]['definition']) || (
                    isset($constraintDefinition['column']) &&
                    $constraintDefinition['column'] == $a[$constraintName]['column'] &&
                    isset($constraintDefinition['reference_table']) &&
                    $constraintDefinition['reference_table'] == $a[$constraintName]['reference_table'] &&
                    isset($constraintDefinition['reference_column']) &&
                    $constraintDefinition['reference_column'] == $a[$constraintName]['reference_column'] &&
                    (!isset($constraintDefinition['on_delete']) ||
                        $constraintDefinition['on_delete'] == $a[$constraintName]['on_delete']) &&
                    (!isset($constraintDefinition['on_update']) ||
                        $constraintDefinition['on_update'] == $a[$constraintName]['on_update'])
                )) {
                // now we need to check if in fact the reference column got changed
                foreach ($directives->changeColumns as $columnDirective) {
                    if (isset($constraintDefinition['column']) &&
                        isset($columnDirective[1]) && // key 1 is old name
                        $columnDirective[1] == $constraintDefinition['reference_column']) {
                        $directives->addProp('dropConstraints', [$tableName, $constraintName, $a[$constraintName]['type']]);
                        $directives->addProp('addConstraints',
                            [$tableName, $constraintName, $constraintDefinition]);
                    }
                }
                continue;
            } else {
                $directives->addProp('dropConstraints', [$tableName, $constraintName, $a[$constraintName]['type']]);
                $directives->addProp('addConstraints',
                    [$tableName, $constraintName, $constraintDefinition]);
            }
        }
        foreach ($a as $constraintName => $constraintDefinition) {
            if (!array_key_exists($constraintName, $b)) {
                $directives->addProp('dropConstraints', [$tableName, $constraintName, $constraintDefinition['type']]);
            }
        }
    }
}
