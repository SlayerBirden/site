<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Http\Session\Resource\Model;

use Maketok\Model\LazyObjectPropModel;

class Session extends LazyObjectPropModel
{
    /**
     * @var string
     */
    public $session_id;
    /**
     * @var string
     */
    public $data;
    /**
     * @var \DateTime
     */
    public $updated_at;
    /**
     * @var \DateInterval
     */
    public $lifetime;
}
