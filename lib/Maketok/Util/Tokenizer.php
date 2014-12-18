<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util;

use Maketok\Util\Exception\TokenizerException;

class Tokenizer implements TokenizerInterface
{
    const MODE_VAR = 1;
    const MODE_CONST = 2;

    /** @var string  */
    public $variableCharBegin = '{';
    /** @var string  */
    public $variableCharEnd = '}';
    /**
     * @var string
     */
    private $expression;
    /**
     * @var int
     */
    private $mode;
    /**
     * @var string
     */
    private $const;
    /**
     * @var string
     */
    private $var;
    /**
     * @var TokenizedBag|TokenizedBagPart[]
     */
    private $bag;

    /**
     * {@inheritdoc}
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
        $this->bag = new TokenizedBag();
    }

    /**
     * @return int|null
     */
    public function getCurrentMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getCurrentVar()
    {
        return $this->var;
    }

    /**
     * @return string
     */
    public function getCurrentConst()
    {
        return $this->const;
    }

    /**
     * {@inheritdoc}
     * @throws TokenizerException
     */
    public function tokenize()
    {
        $str = $this->expression;
        $this->changeMode(self::MODE_CONST);
        while (strlen($str) > 0) {
            $char = substr($str, 0, 1);
            $str = substr($str, 1, strlen($str)-1);
            $this->flowControl($char);
        }
        // we should have finished in const;
        // call to change mode so we're dumping remaining container
        // and throwing exception if mode was var
        $this->changeMode(self::MODE_VAR);
        return $this->bag;
    }

    /**
     * assign char to appropriate container
     * @param string $char
     */
    public function assign($char)
    {
        switch ($this->mode) {
            case self::MODE_CONST:
                $this->const .= $char;
                break;
            case self::MODE_VAR:
                $this->var .= $char;
                break;
        }
    }

    /**
     * @param string $character
     * @throws TokenizerException
     * @return self
     */
    public function flowControl($character)
    {
        if ($character === $this->variableCharBegin) {
            $this->changeMode(self::MODE_VAR);
        } elseif ($character === $this->variableCharEnd) {
            $this->changeMode(self::MODE_CONST);
        } else {
            $this->assign($character);
        }
        return $this;
    }

    /**
     * @param int $newMode
     * @throws TokenizerException
     */
    protected function changeMode($newMode)
    {
        if ($this->mode === $newMode) {
            throw new TokenizerException("Does not allow nested types.");
        }
        // next is not in our jurisdiction as it can't be called from within this implementation
        //@codeCoverageIgnoreStart
        if ($newMode !== self::MODE_CONST && $newMode !== self::MODE_VAR) {
            throw new TokenizerException("Invalid mode.");
        }
        //@codeCoverageIgnoreEnd
        $this->mode = $newMode;
        $this->flushCurrentStorage();
    }

    /**
     * flush character storage
     */
    protected function flushCurrentStorage()
    {
        if (!empty($this->const)) {
            $this->bag->addPart(new TokenizedBagPart('const', $this->const));
        }
        if (!empty($this->var)) {
            $this->bag->addPart(new TokenizedBagPart('var', $this->var));
        }
        $this->const = '';
        $this->var = '';
    }
}
