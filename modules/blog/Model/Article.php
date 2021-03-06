<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\blog\Model;

use Maketok\Model\LazyObjectPropModel;

class Article extends LazyObjectPropModel
{
    /** @var int */
    public $id;
    /** @var string */
    public $title;
    /** @var string */
    public $code;
    /** @var \DateTime data */
    public $created_at;
    /** @var \DateTime data */
    public $updated_at;
    /** @var string */
    public $author;
    /** @var string text */
    public $content;
    /** @var string text */
    public $description;
}
