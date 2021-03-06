<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use \Maketok\App\Site;

require_once 'vendor/autoload.php';

$site = new Site();
$site->run('basic_setup', Site::CONTEXT_SKIP_SESSION);
$site = new Site();
$site->run('setup', Site::CONTEXT_SKIP_SESSION);
