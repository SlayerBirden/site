<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\blog\Util\Markdown;

use Aptoma\Twig\Extension\MarkdownEngineInterface;

class Engine implements MarkdownEngineInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($content)
    {
        $pd = new \ParsedownExtra();
        return $pd->text($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '\ParsedownExtra';
    }
}
