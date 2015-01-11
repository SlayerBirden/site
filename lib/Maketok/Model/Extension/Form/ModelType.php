<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Model\Extension\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ModelType extends AbstractType
{
    /**
     * @var array
     */
    private $choiceListCache = array();

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * init defaults
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'model';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choiceListCache = & $this->choiceListCache;
        $propertyAccessor = $this->propertyAccessor;
        $choiceList = function (Options $options) use (&$choiceListCache, $propertyAccessor) {
            // Support for closures
            $propertyHash = is_object($options['property'])
                ? spl_object_hash($options['property'])
                : $options['property'];
            $idHash = $options['id_field'];
            $choiceHashes = $options['choices'];
            // Support for recursive arrays
            if (is_array($choiceHashes)) {
                // A second parameter ($key) is passed, so we cannot use
                // spl_object_hash() directly (which strictly requires
                // one parameter)
                array_walk_recursive($choiceHashes, function (&$value) {
                    $value = spl_object_hash($value);
                });
            } elseif ($choiceHashes instanceof \Traversable) {
                $hashes = array();
                foreach ($choiceHashes as $value) {
                    $hashes[] = spl_object_hash($value);
                }
                $choiceHashes = $hashes;
            }
            $preferredChoiceHashes = $options['preferred_choices'];
            if (is_array($preferredChoiceHashes)) {
                array_walk_recursive($preferredChoiceHashes, function (&$value) {
                    $value = spl_object_hash($value);
                });
            }

            $hash = hash('sha256', json_encode(array(
                spl_object_hash($options['table']),
                $propertyHash,
                $idHash,
                $choiceHashes,
                $preferredChoiceHashes,
            )));
            if (!isset($choiceListCache[$hash])) {
                $choiceListCache[$hash] = new ModelChoiceList(
                    $options['table'],
                    $options['property'],
                    $options['id_field'],
                    $options['choices'],
                    $options['preferred_choices'],
                    $propertyAccessor
                );
            }
            return $choiceListCache[$hash];
        };
        $resolver->setDefaults(array(
            'property' => null,
            'choices' => null,
            'choice_list' => $choiceList,
            'id_field' => null,
        ));
        $resolver->setRequired(['table']);
        $resolver->setAllowedTypes(array(
            'table' => array('null', 'string', 'Maketok\Model\TableMapper'),
        ));
    }
}
