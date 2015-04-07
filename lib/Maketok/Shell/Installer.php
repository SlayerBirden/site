<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell;

use Maketok\Observer\SubjectInterface;
use Maketok\Shell\Provider\ProviderInterface;
use Maketok\Shell\Worker\WorkerInterface;
use Maketok\Util\RequestInterface;
use Monolog\Logger;

class Installer
{
    /**
     * @var ProviderInterface[]
     */
    protected $providers = [];

    /**
     * @var WorkerInterface[]
     */
    protected $workers = [];
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * run the installer
     * @param RequestInterface $request
     * @param SubjectInterface $subject
     */
    public function run(RequestInterface $request, SubjectInterface $subject)
    {
        foreach ($this->workers as $worker) {
            try {
                fputs(STDOUT, sprintf("Worker %s starts execution.\n", $worker));
                $worker->run();
            } catch (\Exception $e) {
                $this->logger->err($e);
            }
        }
        $subject->setShouldStopPropagation(1);
    }

    /**
     * @param ProviderInterface $provider
     * @return self
     */
    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
        return $this;
    }

    /**
     * @param WorkerInterface $worker
     * @return self
     */
    public function addWorker(WorkerInterface $worker)
    {
        $this->workers[] = $worker;
        return $this;
    }

    /**
     * @param string $key
     * @param bool $default
     * @return false|mixed
     */
    public function getArg($key, $default = false)
    {
        foreach ($this->providers as $provider) {
            if (($arg = $provider->getArg($key, $default)) !== $default) {
                return $arg;
            }
        }
        return $default;
    }
}
