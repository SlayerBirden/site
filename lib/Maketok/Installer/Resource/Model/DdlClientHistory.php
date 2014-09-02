<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Resource\Model;

class DdlClientHistory
{

    /** @var int */
    public $id;
    /** @var int */
    public $client_id;
    /** @var string */
    public $prev_version;
    /** @var string */
    public $version;
    /** @var string */
    public $launcher;
    /** @var string */
    public $config;
}
