<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Test\Unit\Gateway\Client;

use Avarda\Payments\Gateway\Client\AvardaClient;
use Avarda\Payments\Gateway\Config\Config;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\FlagManager;
use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AvardaClientTest extends TestCase
{
    protected Config|MockObject $configMock;

    protected FlagManager|MockObject $flagManagerMock;

    protected Logger|MockObject $loggerMock;

    protected AvardaClient $client;

    public function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->flagManagerMock = $this->createMock(FlagManager::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->client = new AvardaClient($this->configMock, $this->flagManagerMock, $this->loggerMock);
    }

    public function testCanBeCreatedWithoutLogger(): void
    {
        $client = new AvardaClient($this->configMock, $this->flagManagerMock);

        $this->assertTrue($client->handleErrors(new Response(200)));
    }

    /**
     * @dataProvider handledStatusProvider
     */
    #[DataProvider('handledStatusProvider')]
    public function testHandleErrorsAcceptsHandledStatus(int $status): void
    {
        $response = new Response($status);

        $this->assertTrue($this->client->handleErrors($response));
        $this->assertSame($response, $this->client->getLastResponse());
    }

    public static function handledStatusProvider(): array
    {
        return [
            'ok' => [200],
            'created' => [201],
            'accepted' => [202],
            'non-authoritative' => [203],
            'bad request' => [400],
            'unauthorized' => [401],
            'unprocessable' => [422],
        ];
    }

    /**
     * @dataProvider errorStatusProvider
     */
    #[DataProvider('errorStatusProvider')]
    public function testHandleErrorsThrowsAndLogsOnErrorStatus(int $status, string $expectedMessage): void
    {
        $this->loggerMock->expects($this->once())->method('debug');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->client->handleErrors(new Response($status));
    }

    public static function errorStatusProvider(): array
    {
        return [
            'forbidden' => [403, 'Error in request'],
            'not found' => [404, 'Error in request'],
            'method not allowed' => [405, 'Error in request'],
            'server error' => [500, 'Avarda server error'],
        ];
    }

    public function testHandleErrorsThrowsOnUnhandledStatus(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unhandled response status code');

        $this->client->handleErrors(new Response(502));
    }
}
