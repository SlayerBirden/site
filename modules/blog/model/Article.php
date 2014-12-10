<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace modules\blog\model;

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

}
