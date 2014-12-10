<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Data\Resource\Model;

class DataClientHistory
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
    public $initializer;
    /** @var string */
    public $created_at;
}
