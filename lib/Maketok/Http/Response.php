<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Http;


use Maketok\Util\ResponseInterface;

class Response extends AbstractMessage implements ResponseInterface
{

    public static $codes = [
        // INFORMATIONAL
        100 => 'Continue',
        101 => 'Switching Protocols',
        // SUCCESS
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // REDIRECT
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Unused',
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];

    /** @var  string */
    protected $_statusCode;


    /**
     * @return string
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * @param $code
     * @return $this
     * @throws \Exception
     */
    public function setStatusCode($code)
    {
        if (!array_key_exists($code, self::$codes)) {
            throw new \Exception(sprintf("Trying to set invalid status code %d.", $code));
        }
        $this->_statusCode = $code;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOK()
    {
        return ($this->_statusCode >= 200 && $this->_statusCode < 300);
    }

    /**
     * @return bool
     */
    public function isRedirect()
    {
        return ($this->_statusCode >= 300 && $this->_statusCode < 400);
    }

    /**
     * @return bool
     */
    public function isClientError()
    {
        return ($this->_statusCode >= 400 && $this->_statusCode < 500);
    }

    /**
     * @return bool
     */
    public function isServerError()
    {
        return ($this->_statusCode >= 500);
    }

    /**
     * @return bool
     */
    public function isInformational()
    {
        return ($this->_statusCode >= 100 && $this->_statusCode < 200);
    }
}