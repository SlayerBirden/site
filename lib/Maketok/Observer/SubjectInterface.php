<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Observer;

interface SubjectInterface
{
    /**
     * @return bool
     */
    public function getShouldStopPropagation();

    /**
     * @param bool | int $flag
     * @return mixed
     */
    public function setShouldStopPropagation($flag);
}
