<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use  \Maketok\App\Site;

$site = new Site();
$site->run('travis', Site::CONTEXT_SKIP_ENVIRONMENT | Site::CONTEXT_SKIP_DISPATCH);
