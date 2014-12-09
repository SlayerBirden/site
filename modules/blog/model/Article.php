<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\blog\model;

use Maketok\App\Site;

class Article
{

    /** @var  int */
    public $id;
    /** @var  string */
    public $title;
    /** @var  string */
    public $code;
    /** @var  string data */
    public $created_at;
    /** @var  string data */
    public $updated_at;
    /** @var  string */
    public $author;
    /** @var  string text */
    public $content;

    /**
     * @return string
     */
    public function getUrl()
    {
        return Site::getBaseUrl() . '/blog/' . $this->code;
    }
}
