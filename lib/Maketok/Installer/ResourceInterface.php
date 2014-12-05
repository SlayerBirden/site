<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;


interface ResourceInterface
{

    /**
     * create real DB procedures from manager directives
     * @param DirectivesInterface $directives
     * @return mixed
     */
    public function createProcedures(DirectivesInterface $directives);

    /**
     * run existing procedures
     * @return mixed
     */
    public function runProcedures();

    /**
     * @return array|\Iterator
     */
    public function getProcedures();
}
