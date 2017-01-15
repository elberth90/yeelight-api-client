<?php

namespace Yeelight\Bulb\Exceptions;

class BulbCommandException extends Exception
{
    /**
     * @var int
     */
    private $bulbId;

    /**
     * BulbCommandException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param int    $bulbId
     */
    public function __construct(string $message, int $code, int $bulbId)
    {
        parent::__construct($message, $code);
        $this->bulbId = $bulbId;
    }

    /**
     * @return int
     */
    public function getBulbId(): int
    {
        return $this->bulbId;
    }
}
