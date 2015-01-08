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

class EditUserType extends AbstractType
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
            ->add('old_password', 'password', ['label' => 'Current Password']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'edit_user';
    }
}
