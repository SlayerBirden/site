<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Util;


class ExpressionParser
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
     * @param $expression
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
     * @param null|array $parameters
     * @param null|array $restrictions
     * @return mixed|string
     * @throws \Exception
     */
    public function evaluate($parameters = null, $restrictions = null)
    {
        if (is_null($this->_parameters) && is_null($parameters)) {
            throw new \Exception("No Params to evaluate.");
        }
        $parameters = (is_null($parameters)) ? $this->_parameters : $parameters;
        $restrictions = (is_null($restrictions)) ? $this->_restrictions : $restrictions;
        if (is_array($restrictions)) {
            foreach ($parameters as $key => $value) {
                if (isset($restrictions[$key])) {
                    $res = preg_match('#' . $restrictions[$key] . '#', $value);
                    if (!$res) {
                        throw new \Exception(sprintf("One of the parameters (%s) failed to satisfy requirements.", $key));
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
     * @return bool|array
     */
    public function parse($newString)
    {

    }

    /**
     * tries to tokenize the expression
     * @param null|string $string
     * @throws \Exception
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
        for ($i = 0; $i < strlen($string); ++$i) {
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
                    throw new \Exception("Does not allow nested variables.");
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
                    throw new \Exception("Error in expression syntax.");
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
            throw new \Exception("Can not end up in variable.");
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