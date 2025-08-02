<?php

declare(strict_types=1);

namespace Tests\Common\Infrastructure\MySql;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Common\Infrastructure\MySql\TagRepository;
use MyEspacio\Framework\Database\Connection;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TagRepositoryTest extends TestCase
{
    #[DataProvider('saveDataProvider')]
    public function testSave(
        Tag $tag,
        string $tagName,
        bool $statementHasErrors,
        int $lastInsertIdInvocationCount,
        int $lastInsertId,
        ?int $expectedFunctionResult
    ): void {
        $stmt = $this->createMock(PDOStatement::class);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO project.tags (tag) VALUES :tag',
                [
                    'tag' => $tagName
                ]
            )
            ->willReturn($stmt);

        $connection->expects($this->once())
            ->method('statementHasErrors')
            ->with($stmt)
            ->willReturn($statementHasErrors);

        $connection->expects($this->exactly($lastInsertIdInvocationCount))
            ->method('lastInsertId')
            ->willReturn($lastInsertId);

        $repository = new TagRepository($connection);
        $result = $repository->save($tag);
        $this->assertSame($expectedFunctionResult, $result);
    }

    /** @return array<string, array<string, mixed>> */
    public static function saveDataProvider(): array
    {
        return [
            'test_success' => [
                'tag' => new Tag(
                    tag:'sunrise',
                    id: 1
                ),
                'tagName' => 'sunrise',
                'statementHasErrors' => false,
                'lastInsertIdInvocationCount' => 1,
                'lastInsertId' => 1,
                'expectedFunctionResult' => 1
            ],
            'test_failure' => [
                'tag' => new Tag(
                    tag: 'Mexico',
                    id: 2
                ),
                'tagName' => 'Mexico',
                'statementHasErrors' => true,
                'lastInsertIdInvocationCount' => 0,
                'lastInsertId' => 0,
                'expectedFunctionResult' => null
            ],
        ];
    }

    #[DataProvider('fetchTagByNameDataProvider')]
    public function testFetchTagByName(
        string $tagName,
        ?Tag $expectedResult,
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOneModel')
            ->with(
                'SELECT id, tag FROM project.tags WHERE tag = :tag',
                [
                    'tag' => $tagName
                ],
                Tag::class
            )
            ->willReturn($expectedResult);

        $repository = new TagRepository($db);
        $result = $repository->fetchTagByName($tagName);
        $this->assertSame($expectedResult, $result);
    }

    /** @return array<string, array<string, mixed>> */
    public static function fetchTagByNameDataProvider(): array
    {
        return [
            'test_success' => [
                'tagName' => 'sunrise',
                'expectedResult' => new Tag(
                    tag: 'sunrise',
                    id: 1
                ),
            ],
            'test_failure' => [
                'tagName' => 'Mexico',
                'expectedResult' => null,
            ],
        ];
    }
}
