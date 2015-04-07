<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Worker;

use Maketok\Shell\Installer;
use Maketok\Shell\NoArgumentException;
use Maketok\Shell\Resource\Dumper\DumperInterface;

class Config extends AbstractWorker
{
    /**
     * @var DumperInterface
     */
    private $dumper;

    protected $parameters = [
        'db_user' => 'root',
        'db_passw' => '',
        'db_host' => 'localhost',
        'db_database' => 'maketok',
        'db_driver' => 'pdo_mysql',
        'base_url' => false,
    ];

    /**
     * @param Installer $installer
     * @param DumperInterface $dumper
     */
    public function __construct(Installer $installer, DumperInterface $dumper)
    {
        $this->installer = $installer;
        $this->dumper = $dumper;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $baseLocalFilePath = AR . '/config/di/local.parameters.' . $this->dumper->getExtension();
        $adminLocalFilePath = AR . '/config/di/local.admin.parameters.' . $this->dumper->getExtension();
        if (!file_exists($baseLocalFilePath)) {
            foreach ($this->parameters as $key => $default) {
                $value = $this->installer->getArg($key, $default);
                if ($value !== false) {
                    $this->parameters[$key] = $value;
                } else {
                    throw new NoArgumentException("No argument given for key '$key'");
                }
            }
            #base site
            $baseParams = $this->parameters;
            $contents = $this->dumper->write([
                'parameters' => $baseParams
            ]);
            file_put_contents($baseLocalFilePath, $contents);
        }
        if (!file_exists($adminLocalFilePath)) {
            $value = $this->installer->getArg('admin_url');
            if ($value !== false) {
                $this->parameters['admin_url'] = $value;
            } else {
                throw new NoArgumentException("No argument given for key 'admin_url'");
            }
            #admin
            $adminParams = ['base_url' => $value];
            $contents = $this->dumper->write([
                'parameters' => $adminParams
            ]);
            file_put_contents($adminLocalFilePath, $contents);
        }
    }

    /**
     * @return string representation
     */
    public function __toString()
    {
        return 'config';
    }
}
