<?php

declare(strict_types=1);

namespace Tests\Common\Infrastructure;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Common\Infrastructure\MySqlTagRepository;
use MyEspacio\Framework\Database\Connection;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class MySqlTagRepositoryTest extends TestCase
{
    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tag = new Tag(
            tag:'sunrise',
            id: 1
        );
    }

    public function testSave()
    {
        $stmt = $this->createMock(PDOStatement::class);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO project.tags (tag) VALUES :tag',
                [
                    'tag' => $this->tag->getTag()
                ]
            )
            ->willReturn($stmt);

        $connection->expects($this->once())
            ->method('statementHasErrors')
            ->with($stmt)
            ->willReturn(false);

        $connection->expects($this->once())
            ->method('lastInsertId')
            ->willReturn(1);

        $repository = new MySqlTagRepository($connection);
        $result = $repository->save($this->tag);
        $this->assertSame(1, $result);
    }

    public function testSaveStatementFail()
    {
        $stmt = $this->createMock(PDOStatement::class);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO project.tags (tag) VALUES :tag',
                [
                    'tag' => $this->tag->getTag()
                ]
            )
            ->willReturn($stmt);

        $connection->expects($this->once())
            ->method('statementHasErrors')
            ->with($stmt)
            ->willReturn(true);

        $repository = new MySqlTagRepository($connection);
        $result = $repository->save($this->tag);
        $this->assertNull($result);
    }

    public function testGetTagByName()
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOneModel')
            ->with(
                'SELECT id, tag FROM project.tags WHERE tag = :tag',
                [
                    'tag' => $this->tag->getTag()
                ],
                Tag::class
            )
            ->willReturn($this->tag);

        $repository = new MySqlTagRepository($db);
        $result = $repository->getTagByName($this->tag->getTag());
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals($this->tag->getTag(), $result->getTag());
    }

    public function testGetTagByNameNotFound()
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOneModel')
            ->with(
                'SELECT id, tag FROM project.tags WHERE tag = :tag',
                [
                    'tag' => $this->tag->getTag()
                ],
                Tag::class
            )
            ->willReturn(null);

        $repository = new MySqlTagRepository($db);
        $result = $repository->getTagByName($this->tag->getTag());
        $this->assertNull($result);
    }
}