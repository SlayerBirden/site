<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Maketok\Authentication\Resource\Form;

use Maketok\App\Helper\ContainerTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CreateUserType extends AbstractType
{
    use ContainerTrait;
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text')
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('roles', 'model', [
                'table' => $this->ioc()->get('auth_role_table'),
                'property' => '[title]',
                'id_field' => '[id]',
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'create_user';
    }
}
