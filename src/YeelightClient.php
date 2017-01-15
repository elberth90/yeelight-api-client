<?php

namespace Yeelight;

use Socket\Raw\Factory;
use Yeelight\Bulb\Bulb;
use Yeelight\Bulb\BulbFactory;
use Yeelight\Exceptions\SocketException;

class YeelightClient
{
    /**
     * @var YeelightRawClient
     */
    private $client;

    /**
     * YeelightClient constructor.
     *
     * @param int $readTimeout
     */
    public function __construct(int $readTimeout = 1)
    {
        $socketFactory = new Factory();
        $bulbFactory = new BulbFactory($socketFactory);
        $this->client = new YeelightRawClient(
            $readTimeout,
            $socketFactory->createUdp4(),
            $bulbFactory
        );
    }

    /**
     * @return Bulb[]
     * @throws SocketException
     */
    public function search()
    {
        return $this->client->search();
    }
}
