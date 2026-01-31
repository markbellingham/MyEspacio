<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Http;

use InvalidArgumentException;
use MyEspacio\Framework\Http\ResponseData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class ResponseDataTest extends TestCase
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $translationVariables
     */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        array $data,
        int $statusCode,
        ?string $template,
        ?string $translationKey,
        array $translationVariables,
    ): void {
        $model = new ResponseData(
            $data,
            $statusCode,
            $template,
            $translationKey,
            $translationVariables
        );

        $this->assertEquals($data, $model->getData());
        $this->assertSame($statusCode, $model->getStatusCode());
        $this->assertEquals($template, $model->getTemplate());
        $this->assertEquals($translationKey, $model->getTranslationKey());
        $this->assertEquals($translationVariables, $model->getTranslationVariables());
    }

    /** @return array<string, array<int, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                [],
                Response::HTTP_OK,
                null,
                null,
                []
            ],
            'test_2' => [
                ['name' => 'Mark'],
                Response::HTTP_REQUEST_TIMEOUT,
                '',
                '',
                []
            ],
            'test_3' => [
                ['message' => 'A message'],
                Response::HTTP_NOT_FOUND,
                'photos.error.html.twig',
                'photos',
                []
            ],
            'test_4' => [
                [],
                Response::HTTP_BAD_REQUEST,
                null,
                'login.logged_in',
                ['name' => 'Mark']
            ]
        ];
    }

    public function testBadStatusCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP status code 299');

        new ResponseData(
            [],
            299,
            null,
            null,
            []
        );
    }

    #[DataProvider('setDataDataProvider')]
    public function testSetData(
        string $key,
        mixed $value
    ): void {
        $responseData = new ResponseData();

        $responseData->setData($key, $value);

        $this->assertArrayHasKey($key, $responseData->getData());
        $this->assertSame($value, $responseData->getData()[$key]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function setDataDataProvider(): array
    {
        return [
            'test_1' => [
                'key1',
                'value1'
            ],
            'test_2' => [
                'key2',
                2,
            ],
            'test_3' => [
                'key3',
                [
                    '123',
                    '456'
                ]
            ]
        ];
    }
}
