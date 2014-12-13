<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Data\Resource\Model;

use Maketok\Installer\Exception;
use Maketok\Model\TableMapper;

class DataClientType extends TableMapper
{

    /**
     * @param string $code
     * @return array|\ArrayObject|null
     * @throws \Maketok\Installer\Exception
     */
    public function getClientByCode($code)
    {
        $resultSet = $this->getGateway()->select(array('code' => $code));
        $row = $resultSet->current();
        if (!$row) {
            throw new Exception(sprintf("Could not find client with code %s.", $code));
        }
        return $row;
    }
}
