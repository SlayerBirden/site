<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Authentication\Resource\Validator;

use Maketok\Authentication\IdentityInterface;
use Maketok\Authentication\IdentityManagerInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserPasswordValidator extends ConstraintValidator
{
    /**
     * @var IdentityManagerInterface
     */
    private $identityManager;
    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    public function __construct(IdentityManagerInterface $identityManager, PasswordEncoderInterface $encoder)
    {
        $this->identityManager = $identityManager;
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($password, Constraint $constraint)
    {
        if (!$constraint instanceof UserPassword) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\UserPassword');
        }

        $user = $this->identityManager->getCurrentIdentity();

        if (!$user instanceof IdentityInterface) {
            throw new ConstraintDefinitionException('The User object must implement the UserInterface interface.');
        }


        if (!$this->encoder->isPasswordValid($user->getPasswordHash(), $password, false)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
