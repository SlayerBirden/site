<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Resource\Model;

use Maketok\Model\LazyObjectPropModel;

class DdlClient extends LazyObjectPropModel
{
    /** @var int */
    public $id;
    /** @var string */
    public $code;
    /** @var string */
    public $version;
    /** @var array */
    public $config;
    /** @var array */
    public $dependencies;
}
