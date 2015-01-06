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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CreateAdminUserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text')
            ->add('password', 'password')
            ->add('confirm', 'password', ['label' => 'Confirm Password'])
            ->add('firstname', 'text')
            ->add('lastname', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'create_admin';
    }
}
