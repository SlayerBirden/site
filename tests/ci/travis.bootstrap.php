<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */
use  \Maketok\App\Site;

$site = new Site();
$site->run('travis', Site::CONTEXT_SKIP_ENVIRONMENT | Site::CONTEXT_SKIP_DISPATCH);
