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

class TokenizedBagPart
{
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $value;

    /**
     * @param string $type
     * @param string $value
     */
    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => $this->type,
            'value' => $this->value,
        ];
    }
}
