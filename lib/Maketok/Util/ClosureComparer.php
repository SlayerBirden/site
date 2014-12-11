<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

class ClosureComparer
{

    /**
     * returns 0 if equal
     * 1 in all other cases
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    public function compare($a, $b)
    {
        $r1 = new \ReflectionFunction($a);
        $r2 = new \ReflectionFunction($b);

        if ($r1->getParameters() != $r2->getParameters()) {
            return 1;
        }

        $a = $this->parseClosure(implode(array_slice(file($r1->getFileName()),
            $r1->getStartLine() - 1,
            ($r1->getEndLine() - $r1->getStartLine() + 1)
        )));
        $b = $this->parseClosure(implode(array_slice(file($r2->getFileName()),
            $r2->getStartLine() - 1,
            ($r2->getEndLine() - $r2->getStartLine() + 1)
        )));
        return (int) !($a == $b);
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public function isClosure($var)
    {
        return is_callable($var) && is_object($var) && ($var instanceof \Closure);
    }

    /**
     * return Closure body
     *
     * @param string $contents
     * @return string
     */
    public function parseClosure($contents)
    {
        preg_match('/\)\s*\{([^}]*)\}/', $contents, $matches);

        return $this->filterBody(trim($matches[1]));
    }

    /**
     * filter spaces
     *
     * @param string $body
     * @return string
     */
    public function filterBody($body)
    {
        return preg_replace(['/\s+/', '/\s+;/'], [' ', ';'], $body);
    }
}
