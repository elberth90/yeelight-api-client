<?php

namespace Yeelight\Bulb;

use Yeelight\Bulb\Exceptions\Exception;
use Yeelight\Bulb\Exceptions\BulbCommandException;
use Yeelight\Bulb\Exceptions\InvalidResponseException;

class Response
{
    /**
     * @var array
     */
    private $result = [];

    /**
     * @var Exception|null
     */
    private $exception = null;

    /**
     * Response constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        if (empty($response)) {
            $this->exception = new InvalidResponseException('empty response');
            return;
        }

        if (
            isset($response['error'])) {
            $this->exception = new BulbCommandException(
                $response,
                $response['error']['message'],
                $response['error']['code']
            );
            return;
        }

        $this->result = $response['result'];        
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @return Exception
     */
    public function getException(): Exception
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
