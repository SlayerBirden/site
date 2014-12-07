<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module\Resource\Model;

use Maketok\App\Site;

class Module
{

    /** @var  string */
    public $module_code;
    /** @var  string */
    public $version;
    /** @var  int */
    public $active;
    /** @var  string data */
    public $updated_at;
    /** @var  string data */
    public $created_at;
    /** @var  string $area */
    public $area;

    /**
     * @return string
     */
    public function getUrl()
    {
        return Site::getBaseUrl() . '/modules/' . $this->area . '/' . $this->module_code;
    }
}
