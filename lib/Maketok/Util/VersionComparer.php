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

class VersionComparer
{
    /**
     * the recursive compare function
     * should compare versions
     * for strings only!
     *
     * @param  string                    $versionA
     * @param  string                    $versionB
     * @throws \InvalidArgumentException
     * @return int
     */
    public static function natRecursiveCompare($versionA, $versionB)
    {
        if (!is_string($versionA) || !is_string($versionB)) {
            throw new \InvalidArgumentException("Compared arguments must be strings.");
        }
        // if version has dot it's directed here
        if (strpos($versionA, '.') || strpos($versionB, '.')) {
            $versionAlist = explode('.', $versionA);
            $versionBlist = explode('.', $versionB);
            self::castEqualLength($versionAlist, $versionBlist);
            do {
                $versionA = array_shift($versionAlist);
                $versionB = array_shift($versionBlist);
            } while ($versionA == $versionB && !is_null($versionA) && !is_null($versionB));
            return self::natRecursiveCompare((string) $versionA, (string) $versionB);
        }
        // this is for plain numbers
        return self::numberCmp((int) $versionA, (int) $versionB);
    }

    /**
     * @param int  $elementA
     * @param int  $elementB
     * @return int
     */
    public static function numberCmp($elementA, $elementB)
    {
        if ($elementA == $elementB) {
            return 0;
        }
        return $elementA > $elementB ? 1 : -1;
    }

    /**
     * cast both array to equal length
     * @param string[] $aList
     * @param string[] $bList
     * @param mixed    $placeholder
     */
    public static function castEqualLength(array &$aList, array &$bList, $placeholder = '0')
    {
        $countA = count($aList);
        $countB = count($bList);
        if ($countA > $countB) {
            for ($i = $countB; $i < $countA; $i++) {
                $bList[] = $placeholder;
            }
        } elseif ($countB > $countA) {
            for ($i = $countA; $i < $countB; $i++) {
                $aList[] = $placeholder;
            }
        }
    }
}
