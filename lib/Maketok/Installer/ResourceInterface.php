<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer;

interface ResourceInterface
{
    /**
     * create real DB procedures from manager directives
     * @param  DirectivesInterface $directives
     * @return mixed
     */
    public function createProcedures(DirectivesInterface $directives);

    /**
     * run existing procedures
     * return number of procedures run
     * @return int
     */
    public function runProcedures();

    /**
     * @return array|\Iterator
     */
    public function getProcedures();
}
