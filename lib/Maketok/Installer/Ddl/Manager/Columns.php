<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Installer\Ddl\Manager;

use Maketok\Installer\Ddl\Directives;

class Columns implements CompareInterface
{

    /**
     * {@inheritdoc}
     */
    public function intlCompare(array $a, array $b, $tableName, Directives $directives)
    {
        $_changeMap = [];
        foreach ($b as $columnName => $columnDefinition) {
            if (!array_key_exists($columnName, $a) && !isset($columnDefinition['old_name'])) {
                $directives->addProp('addColumns', [$tableName, $columnName, $columnDefinition]);
            } elseif (isset($columnDefinition['old_name']) && is_string($columnDefinition['old_name'])) {
                $directives->addProp('changeColumns', [
                    $tableName,
                    $columnDefinition['old_name'],
                    $columnName,
                    $columnDefinition,
                ]);
                $_changeMap[$columnDefinition['old_name']] = $tableName;
            } elseif ($columnDefinition == $a[$columnName]) { // not strict compare because scalar types may differ
                continue;
            } else {
                // now we need to make sure new definitions contain same keys as old ones
                $newDefinition = $columnDefinition;
                $oldDefinition = $a[$columnName];
                foreach ($oldDefinition as $key => $value) {
                    if (!isset($newDefinition[$key])) {
                        unset($oldDefinition[$key]);
                    }
                }
                // not strict compare because scalar types may differ
                if (!($oldDefinition == $newDefinition)) {
                    $directives->addProp('changeColumns',
                        [$tableName, $columnName, $columnName, $newDefinition]);
                }
            }
        }
        foreach ($a as $columnName => $columnDefinition) {
            if (!array_key_exists($columnName, $b) && !isset($_changeMap[$columnName])) {
                $directives->addProp('dropColumns', [$tableName, $columnName]);
            }
        }
    }
}
