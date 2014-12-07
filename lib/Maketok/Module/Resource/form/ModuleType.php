<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Module\Resource\form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ModuleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('module_code', 'text', array('read_only' => true))
            ->add('version', 'text', array('read_only' => true))
            ->add('active', 'choice', array(
                'choices'   => array('0' => 'Disabled', '1' => 'Enabled'),
            ))
            ->add('updated_at', 'text', array('read_only' => true))
            ->add('area', 'text', array('read_only' => true))
        ;
    }
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Maketok\Module\Resource\Model\Module',
        ));
    }
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'module';
    }
}
