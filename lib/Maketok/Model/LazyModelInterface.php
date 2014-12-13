<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Model;

interface LazyModelInterface
{
    /**
     * define strategy of getting/setting data
     * for each concrete implementation
     * this is combined setter/getter
     * @param array|null $data
     * @return mixed
     */
    public function processOrigin(array $data = null);
}
