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
     * @param mixed $cl1
     * @param mixed $cl2
     * @return int
     */
    public function compare($cl1, $cl2)
    {
        if (!$this->isClosure($cl1) || !$this->isClosure($cl2)) {
            return 1;
        }
        $reflectedClosure1 = new \ReflectionFunction($cl1);
        $reflectedClosure2 = new \ReflectionFunction($cl2);

        if ($reflectedClosure1->getParameters() != $reflectedClosure2->getParameters()) {
            return 1;
        }
        $a = $this->parseClosure(
            implode(array_slice(
                file($reflectedClosure1->getFileName()),
                $reflectedClosure1->getStartLine() - 1,
                ($reflectedClosure1->getEndLine() - $reflectedClosure1->getStartLine() + 1)
            ))
        );
        $b = $this->parseClosure(
            implode(array_slice(
                file($reflectedClosure2->getFileName()),
                $reflectedClosure2->getStartLine() - 1,
                ($reflectedClosure2->getEndLine() - $reflectedClosure2->getStartLine() + 1)
            ))
        );
        return (int)!($a == $b);
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
