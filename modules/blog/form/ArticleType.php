<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\blog\form;


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
            ->add('created_at', 'text')
            ->add('updated_at', 'text')
            ->add('author', 'text')
            ->add('reset', 'reset', array('attr' => array('class' => 'button')))
            ->add('save', 'submit', array('label' => 'Save', 'attr' => array('class' => 'button')))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'modules\blog\model\Article',
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
