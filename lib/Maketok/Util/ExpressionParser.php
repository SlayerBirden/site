<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

use Maketok\Util\Exception\ParserException;

class ExpressionParser implements ExpressionParserInterface
{

    /**
     * @var string
     */
    protected $expression;
    /**
     * @var array
     */
    protected $parameters;
    /**
     * @var array
     */
    protected $restrictions;
    /**
     * @var TokenizerInterface
     */
    private $tokenizer;

    /**
     * {@inheritdoc}
     */
    public function __construct($expression, TokenizerInterface $tokenizer, array $parameters = [], array $restrictions = [])
    {
        $this->expression = $expression;
        $this->parameters = $parameters;
        $this->restrictions = $restrictions;
        $this->tokenizer = $tokenizer;
    }

    /**
     * {@inheritdoc}
     * @throws ParserException
     */
    public function evaluate()
    {
        $this->validate($this->parameters);
        $result = [];
        foreach ($this->tokenizer->tokenize() as $part) {
            switch ($part->type) {
                case 'const':
                    $result[] = $part->value;
                    break;
                case 'var':
                    if (isset($this->parameters[$part->value])) {
                        $result[] = $this->parameters[$part->value];
                    } else {
                        $result[] = $part->value;
                    }
            }
        }
        return implode($result);
    }

    /**
     * @throws ParserException
     */
    protected function validate($params)
    {
        foreach ($params as $key => $value) {
            if (isset($this->restrictions[$key])) {
                $res = preg_match('#' . $this->restrictions[$key] . '#', $value);
                if (!$res) {
                    throw new ParserException(sprintf("One of the parameters (%s) failed to satisfy requirements.", $key));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws ParserException
     */
    public function parse($newString)
    {
        // first of all compare strings
        // if no variables exist
        if (strcmp($this->expression, $newString) === 0) {
            return [];
        }
        try {
            $tokenized = $this->tokenize($newString);
            $this->validate($tokenized);
            return $tokenized;
        } catch (ParserException $e) {
            // flow control exception
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tokenize($string)
    {
        $tokenizedExpression = $this->tokenizer->tokenize();
        $constants = [];
        $variables = [];
        $i = 0;
        foreach ($tokenizedExpression as $part) {
            if ($i == 0) {
                $firstPart = $part;
            }
            if ($part->type == 'const') {
                $constants[] = $part->value;
            } else {
                $variables[] = $part->value;
            }
            ++$i;
        }
        if (isset($firstPart) && $firstPart->type == 'const' && ((strpos($string, $firstPart->value) !== 0))) {
            // this is the case when there's wrong constant starts in new String
            throw new ParserException("String stats from wrong constant.");
        }
        $vParts = self::strSplit($constants, $string);
        if (count($variables) != count($vParts)) {
            throw new ParserException("Can't combine variables, wrong number of placeholders.");
        }
        return array_combine($variables, $vParts);
    }

    /**
     * @param string|string[] $delimiter
     * @param string $string
     * @throws ParserException
     * @return string[]
     */
    public static function strSplit($delimiter, $string)
    {
        if (!is_scalar($delimiter) && !is_array($delimiter)) {
            throw new ParserException(sprintf("Wrong delimiter type: %s.", gettype($delimiter)));
        } elseif (is_scalar($delimiter)) {
            return array_values(array_filter(explode($delimiter, $string)));
        }
        if (empty($delimiter)) {
            return [];
        }
        $safeDelimiter = self::getSafeDelimiter($string);
        foreach ($delimiter as $d) {
            if (($pos = strpos($string, (string) $d)) === false) {
                throw new ParserException(sprintf("Delimiter %s is not present", $d));
            }
            $string = substr_replace($string, $safeDelimiter, $pos, strlen($d));
        }
        return array_values(array_filter(explode($safeDelimiter, $string)));
    }

    /**
     * @param $string
     * @return string
     * @throws ParserException
     * @codeCoverageIgnore
     */
    public static function getSafeDelimiter($string)
    {
        $roundsAllowed = 100;
        do {
            $delimiter = md5(uniqid());
            --$roundsAllowed;
        } while ((strpos($string, $delimiter) !== false) || !$roundsAllowed);
        if (!$roundsAllowed) {
            throw new ParserException("Could not find safe delimiter.");
        }
        return $delimiter;
    }
}
