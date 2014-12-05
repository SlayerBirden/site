<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;


use Maketok\Util\Exception\ParserException;
// @TODO fix parse method
class ExpressionParser implements ExpressionParserInterface
{

    /**
     * @var string
     */
    protected $_expression;

    /**
     * @var array
     */
    protected $_parameters;
    /**
     * @var array
     */
    protected $_restrictions;

    /** @var string  */
    protected $_variableCharBegin = '{';
    /** @var string  */
    protected $_variableCharEnd = '}';

    /**
     * @param string $expression
     * @param null|array $parameters
     * @param null|array $restrictions
     */
    public function __construct($expression, $parameters = null, $restrictions = null)
    {
        $this->_expression = $expression;
        $this->_parameters = $parameters;
        $this->_restrictions = $restrictions;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate($parameters = null, $restrictions = null)
    {
        if (is_null($this->_parameters) && is_null($parameters)) {
            throw new ParserException("No Params to evaluate.");
        }
        $parameters = (is_null($parameters)) ? $this->_parameters : $parameters;
        $restrictions = (is_null($restrictions)) ? $this->_restrictions : $restrictions;
        if (is_array($restrictions)) {
            foreach ($parameters as $key => $value) {
                if (isset($restrictions[$key])) {
                    $res = preg_match('#' . $restrictions[$key] . '#', $value);
                    if (!$res) {
                        throw new ParserException(sprintf("One of the parameters (%s) failed to satisfy requirements.", $key));
                    }
                }
            }
        }
        $returnString = $this->_expression;
        foreach ($parameters as $key => $param) {
            $returnString = str_replace($this->_variableCharBegin . $key . $this->_variableCharEnd, $param, $returnString);
        }
        return $returnString;
    }

    /**
     * Check if $newString satisfies the Expression
     * If it does, set the parameters
     *
     * @param string $newString
     * @param null|array $restrictions
     * @throws ParserException
     * @return bool|array
     */
    public function parse($newString, $restrictions = null)
    {
        $restrictions = (is_null($restrictions)) ? $this->_restrictions : $restrictions;
        $returnVar = array();
        // first of all compare strings
        // if no variables exist
        if (strcmp($this->_expression, $newString) === 0) {
            return $returnVar;
        }
        $tokenized = $this->tokenize();
        // parse new string against tokenized
        $lastVar = '';
        foreach ($tokenized as $part) {
            if ($part['type'] == 'const' && (strpos($newString, $part['val']) === false)) {
                return false;
            } elseif ($part['type'] == 'const') {
                $pos = strpos($newString, $part['val']);
                if ($pos === 0) {
                    $newString = substr($newString, strlen($part['val']));
                } else {
                    if (strlen($lastVar) == 0) {
                        throw new ParserException("Wrong logic of parsing.");
                    }
                    $varString = substr($newString, 0, $pos);
                    if (isset($restrictions[$lastVar])) {
                        $res = preg_match('#' . $restrictions[$lastVar] . '#', $varString);
                        if (!$res) {
                            return $res;
                        }
                    }
                    $returnVar[$lastVar] = $varString;
                    // now clean up string
                    $newString = substr($newString, strlen($varString));
                    $newString = substr($newString, strlen($part['val']));
                    // if string ended
                    if ($newString === false) {
                        $newString = '';
                    }
                    $lastVar = '';
                }
            } elseif ($part['type'] == 'var') {
                $lastVar = $part['val'];
            }
        }
        // we need to account for last var
        if (strlen($lastVar) != 0 && strlen($newString) != 0) {
            if (isset($restrictions[$lastVar])) {
                $res = preg_match('#' . $restrictions[$lastVar] . '#', $newString);
                if ($res === 0) {
                    return false;
                } elseif ($res === false) {
                    throw new ParserException(sprintf("Error in Restriction regexp for '%s'.", $lastVar));
                }
            }
            $returnVar[$lastVar] = $newString;
        } elseif (strlen($lastVar) != 0) {
            // empty variable placeholder
            return false;
        }
        return $returnVar;
    }

    /**
     * tries to tokenize the expression
     * @param null|string $string
     * @throws ParserException
     * @return array
     */
    public function tokenize($string = null)
    {
        if (is_null($string)) {
            $string = $this->_expression;
        }
        $const = '';
        $var = '';
        $res = array();
        $isVar = false;
        $len = strlen($string);
        for ($i = 0; $i < $len; ++$i) {
            if ($string{$i} == $this->_variableCharBegin) {
                // dump constant
                if (!empty($const)) {
                    $res[] = array(
                        'val' => $const,
                        'type' => 'const',
                    );
                    $const = '';
                }
                if ($isVar) {
                    throw new ParserException("Does not allow nested variables.");
                } else {
                    $isVar = true;
                    continue;
                }
            }
            if ($string{$i} == $this->_variableCharEnd) {
                // dump var
                if (!empty($var)) {
                    $res[] = array(
                        'val' => $var,
                        'type' => 'var',
                    );
                    $var = '';
                }
                if (!$isVar) {
                    throw new ParserException("Error in expression syntax.");
                } else {
                    $isVar = false;
                    continue;
                }
            }
            if ($isVar) {
                $var .= $string{$i};
            } else {
                $const .= $string{$i};
            }
        }
        // dump what's left
        if ($isVar) {
            throw new ParserException("Can not end up in variable.");
        }
        if (!empty($const)) {
            $res[] = array(
                'val' => $const,
                'type' => 'const',
            );
        }
        return $res;
    }
}
