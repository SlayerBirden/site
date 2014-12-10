<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Installer\Ddl\Manager;

use Maketok\Installer\Ddl\Directives;

interface CompareInterface
{

    /**
     * @param array $a
     * @param array $b
     * @param $tableName
     * @param Directives $directives
     * @return void
     */
    public function intlCompare(array $a, array $b, $tableName, Directives $directives);
}
