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

use Maketok\Model\TableMapper;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\StringCastException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ModelChoiceList extends ObjectChoiceList
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * {@inheritdoc}
     */
    protected $labelPath;
    /**
     * Contains the table mapper that builds the query for fetching the
     * entities.
     *
     * @var TableMapper
     */
    private $tableMapper;
    /**
     * Creates a new entity choice list.
     *
     * @param TableMapper               $table             TableMapper - for building result set
     * @param string                    $labelPath         The property path used for the label
     * @param string                    $valuePath         The property path used for the value
     * @param array|\Traversable|null   $entities          An array of choices or null to lazy load
     * @param array                     $preferredEntities An array of preferred choices
     * @param PropertyAccessorInterface $propertyAccessor  The reflection graph for reading property paths.
     */
    public function __construct(TableMapper $table, $labelPath = null, $valuePath = null, $entities = null, array $preferredEntities = array(), PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->tableMapper = $table;
        if (empty($entities)) {
            // required for traversing the entities later (ResultSet doesn't support 'rewind')
            $resultSet = $this->tableMapper->fetchAll();
            foreach ($resultSet as $entity) {
                $entities[] = $entity;
            }
        } else {
            $entities = [];
        }
        $this->preferredEntities = $preferredEntities;
        if (is_null($valuePath)) {
            $valuePath = $this->tableMapper->getIdField();
        }
        parent::__construct($entities, $labelPath, $preferredEntities, null, $valuePath, $propertyAccessor);
    }

    /**
     * Make sure to access associative arrays as well
     * @param $choices
     * @param array $labels
     */
    protected function extractLabels($choices, array &$labels)
    {
        foreach ($choices as $i => $choice) {
            if (is_array($choice) && $this->labelPath) {
                $labels[$i] = $this->propertyAccessor->getValue($choice, $this->labelPath);
            } elseif (is_array($choice)) {
                $labels[$i] = array();
                $this->extractLabels($choice, $labels[$i]);
            } elseif ($this->labelPath) {
                $labels[$i] = $this->propertyAccessor->getValue($choice, $this->labelPath);
            } elseif (method_exists($choice, '__toString')) {
                $labels[$i] = (string) $choice;
            } else {
                throw new StringCastException(sprintf('A "__toString()" method was not found on the objects of type "%s" passed to the choice field. To read a custom getter instead, set the argument $labelPath to the desired property path.', get_class($choice)));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function addChoices(array &$bucketForPreferred, array &$bucketForRemaining, $choices, array $labels, array $preferredChoices)
    {
        // Add choices to the nested buckets
        foreach ($choices as $group => $choice) {
            if (!array_key_exists($group, $labels)) {
                throw new InvalidArgumentException('The structures of the choices and labels array do not match.');
            }
            // do not support groups
            $this->addChoice(
                $bucketForPreferred,
                $bucketForRemaining,
                $choice,
                $labels[$group],
                $preferredChoices
            );
        }
    }
}
