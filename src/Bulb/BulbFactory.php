<?php

namespace Yeelight\Bulb;

use Socket\Raw\Factory;

class BulbFactory
{
    const LOCATION = 'Location';
    const ID = 'id';

    /**
     * @var Factory
     */
    private $socketFactory;

    /**
     * BulbFactory constructor.
     *
     * @param Factory $socketFactory
     */
    public function __construct(Factory $socketFactory)
    {
        $this->socketFactory = $socketFactory;
    }

    /**
     * @param array $data
     *
     * @return Bulb
     */
    public function create(array $data): Bulb
    {
        list($ip, $port) = $this->extractIpAndPort($data[self::LOCATION]);

        return new Bulb(
            $this->socketFactory->createTcp4(),
            $ip,
            (int) $port,
            trim($data[self::ID])
        );
    }

    /**
     * @param string $location
     *
     * @return array
     */
    private function extractIpAndPort(string $location): array
    {
        $address = explode('yeelight://', $location);

        return explode(':', end($address));
    }
}
