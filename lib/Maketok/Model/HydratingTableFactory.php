<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Model;

use Maketok\App\Helper\ContainerTrait;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\Feature;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * @codeCoverageIgnore
 */
class HydratingTableFactory implements TableFactoryInterface
{
    use ContainerTrait;

    /**
     * @var TableGatewayInterface
     */
    protected $gateway;
    /**
     * @var string
     */
    private $class;
    /**
     * @var mixed
     */
    private $idField;
    /**
     * @var null|string
     */
    private $incrementIdField;

    /**
     * @param string $table
     * @param mixed $idField
     * @param string $prototype
     * @param string $incrementIdField
     * @param string $class
     * @param string $resultSet
     * @param HydratorInterface $hydrator
     * @param AdapterInterface $adapter
     * @param string $gateway
     * @param Feature\AbstractFeature|Feature\FeatureSet|Feature\AbstractFeature[] $features
     */
    public function __construct($table,
                                $idField,
                                $prototype,
                                $incrementIdField = null,
                                $class = '\Maketok\Model\TableMapper',
                                $resultSet = null,
                                HydratorInterface $hydrator = null,
                                AdapterInterface $adapter = null,
                                $gateway = '\Zend\Db\TableGateway\TableGateway',
                                $features = null
    ) {
        if (is_null($resultSet)) {
            $resultSet = $this->ioc()->getParameter('hydrating_resultset');
        }
        if (is_null($hydrator)) {
            $hydrator = $this->ioc()->get('object_prop_hydrator');
        }
        if (is_null($adapter)) {
            $adapter = $this->ioc()->get('adapter');
        }
        $resultSetInstance = new $resultSet($hydrator, new $prototype());
        $this->gateway = new $gateway($table, $adapter, $features, $resultSetInstance);
        $this->class = $class;
        $this->idField = $idField;
        $this->incrementIdField = $incrementIdField;
    }

    /**
     * {@inheritdoc}
     */
    public function spawnTable()
    {
        return new $this->class($this->gateway, $this->idField, $this->incrementIdField);
    }
}
