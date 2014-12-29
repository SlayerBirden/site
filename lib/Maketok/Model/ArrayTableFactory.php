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
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\Feature;

/**
 * @codeCoverageIgnore
 */
class ArrayTableFactory implements TableFactoryInterface
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
     * @param string $type
     * @param string $prototype
     * @param string $incrementIdField
     * @param string $class
     * @param AdapterInterface $adapter
     * @param string $gateway
     * @param Feature\AbstractFeature|Feature\FeatureSet|Feature\AbstractFeature[] $features
     */
    public function __construct($table,
                                $idField,
                                $type = ResultSet::TYPE_ARRAYOBJECT,
                                $incrementIdField = null,
                                $prototype = null,
                                $class = '\Maketok\Model\TableMapper',
                                AdapterInterface $adapter = null,
                                $gateway = '\Zend\Db\TableGateway\TableGateway',
                                $features = null
    ) {
        if (is_null($adapter)) {
            $adapter = $this->ioc()->get('adapter');
        }
        if ($prototype) {
            $prototype = new $prototype();
        }
        $resultSetInstance = new ResultSet($type, $prototype);
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
