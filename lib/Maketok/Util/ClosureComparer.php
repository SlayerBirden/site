<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util;

/**
 * this is pretty much useless because it tries to compare the actual code of the closure
 * unless we can come up with better idea
 * there's no point in this component existence
 */
class ClosureComparer
{
    /**
     * returns 0 if equal
     * 1 in all other cases
     *
     * @param  mixed $cl1
     * @param  mixed $cl2
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
        $a = $this->getClosureContents($cl1);
        $b = $this->getClosureContents($cl2);

        return (int) !($a == $b);
    }

    /**
     * @param \Closure $closure
     * @return string
     */
    public function getClosureContents(\Closure $closure)
    {
        $reflectedClosure = new \ReflectionFunction($closure);
        return $this->parseClosure(
            implode(array_slice(
                file($reflectedClosure->getFileName()),
                $reflectedClosure->getStartLine() - 1,
                ($reflectedClosure->getEndLine() - $reflectedClosure->getStartLine() + 1)
            ))
        );
    }

    /**
     * @param  mixed $var
     * @return bool
     */
    public function isClosure($var)
    {
        return is_callable($var) && is_object($var) && ($var instanceof \Closure);
    }

    /**
     * return Closure body
     *
     * @param  string $contents
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
     * @param  string $body
     * @return string
     */
    public function filterBody($body)
    {
        return preg_replace(['/\s+/', '/\s+;/'], [' ', ';'], $body);
    }
}
