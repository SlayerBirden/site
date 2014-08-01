<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace modules\blog\model;

class Article
{

    /** @var  int */
    public $id;
    /** @var  string */
    public $title;
    /** @var  string data */
    public $created_at;
    /** @var  string data */
    public $update_at;
    /** @var  string */
    public $author;
    /** @var  string text */
    public $content;
}