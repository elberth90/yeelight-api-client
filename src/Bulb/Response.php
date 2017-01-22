<?php

namespace Yeelight\Bulb;

use Yeelight\Bulb\Exceptions\BulbCommandException;

class Response
{
    /**
     * @var int
     */
    private $deviceId;

    /**
     * @var array
     */
    private $result = [];

    /**
     * @var BulbCommandException|null
     */
    private $exception = null;

    /**
     * Response constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->deviceId = $response['id'];
        if (isset($response['error'])) {
            $this->exception = new BulbCommandException(
                $response['error']['message'],
                $response['error']['code'],
                $response['id']
            );
        } else {
            $this->result = $response['result'];
        }
    }

    /**
     * @return int
     */
    public function getDeviceId(): int
    {
        return $this->deviceId;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @return null|BulbCommandException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return is_null($this->exception);
    }
}
