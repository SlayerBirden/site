<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\blog\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ArticleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('title', 'text', array('attr' => array('class' => 'long')))
            ->add('code', 'text')
            ->add('content', 'textarea', array('attr' => array('class' => 'long')))
            ->add('description', 'textarea', array('attr' => array('class' => 'long')))
            ->add($builder->create('created_at', 'datetime', array('required' => false)))
            ->add($builder->create('updated_at', 'datetime', array('read_only' => true, 'required' => false)))
            ->add('author', 'text')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'modules\blog\Model\Article',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'article';
    }
}
