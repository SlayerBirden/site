<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\pages\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('title', 'text')
            ->add('code', 'text')
            ->add('content', 'textarea', array('attr' => array('rows' => 20)))
            ->add('active', 'choice', array(
                'choices' => array('0' => 'Disabled', '1' => 'Enabled')
            ))
            ->add('layout', 'textarea', array('attr' => array('rows' => 15), 'required' => false))
            ->add('created_at', 'datetime', array('required' => false))
            ->add('updated_at', 'datetime', array('read_only' => true, 'required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'page';
    }
}
