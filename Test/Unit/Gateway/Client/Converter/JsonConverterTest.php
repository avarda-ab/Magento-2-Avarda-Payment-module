<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Test\Unit\Gateway\Client\Converter;

use Avarda\Payments\Gateway\Client\Converter\JsonConverter;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Exception as WebapiException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JsonConverterTest extends TestCase
{
    protected JsonConverter $converter;

    public function setUp(): void
    {
        $this->converter = new JsonConverter();
    }

    public function testConvertReturnsDecodedBody(): void
    {
        $response = new Response(202, [], '{"authorizationId":"abc123","forced":false}');

        $this->assertSame(
            ['authorizationId' => 'abc123', 'forced' => false],
            $this->converter->convert($response)
        );
    }

    public function testUnauthorizedThrowsAuthorizationException(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->converter->convert(new Response(401, [], '{}'));
    }

    /**
     * @dataProvider errorBodyProvider
     */
    #[DataProvider('errorBodyProvider')]
    public function testErrorBodyThrowsWebapiException(int $status, string $body, string $expectedMessage): void
    {
        $this->expectException(WebapiException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->converter->convert(new Response($status, [], $body));
    }

    public static function errorBodyProvider(): array
    {
        return [
            'reason message' => [422, '{"reasonMessage":"Credit not approved"}', 'Credit not approved'],
            'error message' => [422, '{"errorMessage":"Invalid request"}', 'Invalid request'],
            'validation errors' => [
                400,
                '{"errors":{"IdentificationNumber":["The IdentificationNumber field is required."]}}',
                'The IdentificationNumber field is required.',
            ],
            'bad request without details' => [
                400,
                '{}',
                'Something went wrong with Avarda payments. Please try again later.',
            ],
        ];
    }
}
