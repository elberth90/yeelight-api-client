<?php

namespace Yeelight\Bulb\Exceptions;

class BulbCommandException extends Exception
{
    /**
     * @var array
     */
    private $rawResponse = [];

    /**
     * BulbCommandException constructor.
     *
     * @param array $rawResponse
     * @param string $message
     * @param int $code
     */
    public function __construct(array $rawResponse, string $message, int $code)
    {
        parent::__construct($message, $code);
        $this->rawResponse = $rawResponse;
    }

    /**
     * @return array
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }
}
