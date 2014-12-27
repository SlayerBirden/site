<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Maketok\Module\Resource\Form;

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
        $builder->add('module_code', 'text', array('read_only' => true))
            ->add('version', 'text', array('read_only' => true))
            ->add('active', 'choice', array(
                'choices' => array('0' => 'Disabled', '1' => 'Enabled')
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'module';
    }
}
