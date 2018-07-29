<?php

namespace tests\Bulb;

use Yeelight\Bulb\Exceptions\BulbCommandException;
use Yeelight\Bulb\Exceptions\InvalidResponseException;
use Yeelight\Bulb\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function test_success_Response()
    {
        $response = new Response(
            [
                'id' => 1,
                'result' => ['foo']
            ]
        );

        $this->assertTrue($response->isSuccess());
        $this->assertEquals(['foo'], $response->getResult());
    }

    public function test_error_Response()
    {
        $response = new Response(
            [
                'id' => 1,
                'error' => [
                    'code' => 500,
                    'message' => 'error'
                ]
            ]
        );

        $this->assertFalse($response->isSuccess());
        $this->assertEmpty($response->getResult());
        $this->assertInstanceOf(BulbCommandException::class, $response->getException());
    }

    public function test_invalid_response_payload()
    {
        $response = new Response(
            []
        );

        $this->assertFalse($response->isSuccess());
        $this->assertInstanceOf(InvalidResponseException::class, $response->getException());
    }
}
