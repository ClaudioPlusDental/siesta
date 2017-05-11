<?php

declare(strict_types = 1);

namespace Siesta\Database\Exception;

use Siesta\Database\ConnectionData;

/**
 * @author Gregor Müller
 */
class ConnectException extends SQLException
{

    /**
     * @var ConnectionData
     */
    protected $connectionData;

    /**
     * @param ConnectionData $connectionData
     * @param string $message
     * @param $code
     */
    public function __construct(ConnectionData $connectionData, string $message = "", $code = 0)
    {
        parent::__construct($message, $code);
        $this->connectionData = $connectionData;
    }

    /**
     * @return ConnectionData
     */
    public function getConnectionData()
    {
        return $this->connectionData;
    }

    /**
     * @param ConnectionData $connectionData
     */
    public function setConnectionData($connectionData)
    {
        $this->connectionData = $connectionData;
    }

}

