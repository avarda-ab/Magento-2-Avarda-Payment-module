<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Test\Unit\Gateway\Client;

use Avarda\Payments\Gateway\Client\AvardaClient;
use Avarda\Payments\Gateway\Client\AvardaClientFactory;
use Avarda\Payments\Gateway\Client\AvardaGatewayClient;
use GuzzleHttp\Psr7\Response;
use LogicException;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AvardaGatewayClientTest extends TestCase
{
    protected AvardaClient|MockObject $avardaClientMock;

    protected AvardaClientFactory|MockObject $avardaClientFactoryMock;

    protected ConverterInterface|MockObject $converterMock;

    protected Logger|MockObject $loggerMock;

    protected AvardaGatewayClient $gatewayClient;

    public function setUp(): void
    {
        $this->avardaClientMock = $this->createMock(AvardaClient::class);
        $this->avardaClientMock->method('buildHeader')->willReturn(['Authorization' => 'Bearer token']);

        $this->avardaClientFactoryMock = $this->createMock(AvardaClientFactory::class);
        $this->avardaClientFactoryMock->method('create')->willReturn($this->avardaClientMock);

        $this->converterMock = $this->createMock(ConverterInterface::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->gatewayClient = new AvardaGatewayClient(
            $this->avardaClientFactoryMock,
            $this->loggerMock,
            $this->converterMock
        );
    }

    public function createTransfer(string $method, string $uri, array $body): TransferInterface|MockObject
    {
        $transfer = $this->createMock(TransferInterface::class);
        $transfer->method('getMethod')->willReturn($method);
        $transfer->method('getUri')->willReturn($uri);
        $transfer->method('getBody')->willReturn($body);

        return $transfer;
    }

    public function testCanBeCreatedWithoutConverter(): void
    {
        $this->avardaClientMock->method('get')->willReturn('{"state":0}');
        $gatewayClient = new AvardaGatewayClient($this->avardaClientFactoryMock, $this->loggerMock);

        $result = $gatewayClient->placeRequest(
            $this->createTransfer('GET', 'https://avarda.test/authorization/{authorization_id}', [
                'additional' => ['authorization_id' => 'abc123'],
            ])
        );

        $this->assertSame(['state' => 0], $result);
    }

    public function testPostReplacesUriPlaceholdersAndSendsBodyWithoutAdditional(): void
    {
        $response = new Response(202);
        $this->avardaClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                'https://avarda.test/authorization/direct-invoice',
                ['orderReference' => '000000001'],
                ['Authorization' => 'Bearer token']
            )
            ->willReturn($response);
        $this->converterMock
            ->expects($this->once())
            ->method('convert')
            ->with($response)
            ->willReturn(['authorizationId' => 'abc123']);
        $this->loggerMock->expects($this->once())->method('debug');

        $result = $this->gatewayClient->placeRequest(
            $this->createTransfer('POST', 'https://avarda.test/authorization/{payment_method}', [
                'orderReference' => '000000001',
                'additional' => ['payment_method' => 'direct-invoice'],
            ])
        );

        $this->assertSame(['authorizationId' => 'abc123'], $result);
    }

    public function testGetDecodesJsonResponse(): void
    {
        $this->avardaClientMock
            ->expects($this->once())
            ->method('get')
            ->with('https://avarda.test/authorization/abc123', ['Authorization' => 'Bearer token'])
            ->willReturn('{"state":99}');

        $result = $this->gatewayClient->placeRequest(
            $this->createTransfer('GET', 'https://avarda.test/authorization/{authorization_id}', [
                'additional' => ['authorization_id' => 'abc123'],
            ])
        );

        $this->assertSame(['state' => 99], $result);
    }

    public function testClientRuntimeExceptionIsConvertedToClientException(): void
    {
        $this->avardaClientMock
            ->method('post')
            ->willThrowException(new RuntimeException('Avarda server error'));

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Avarda server error');

        $this->gatewayClient->placeRequest(
            $this->createTransfer('POST', 'https://avarda.test/authorization/{payment_method}', [
                'additional' => ['payment_method' => 'direct-invoice'],
            ])
        );
    }

    public function testUnsupportedMethodThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);

        $this->gatewayClient->placeRequest(
            $this->createTransfer('PUT', 'https://avarda.test/authorization', [])
        );
    }
}
