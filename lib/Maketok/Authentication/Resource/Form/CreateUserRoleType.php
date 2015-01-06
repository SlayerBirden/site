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

use Maketok\Template\Symfony\Form\DataTransformer\StringToDateTimeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CreateUserRoleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new StringToDateTimeTransformer();
        $builder->add('id', 'text')
            ->add('title', 'text')
            ->add($builder->create('created_at', 'datetime')
                ->addModelTransformer($transformer));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'create_user_role';
    }
}
