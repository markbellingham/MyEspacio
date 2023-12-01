<?php

namespace Tests\photos\infrastructure;

use PDO;
use PDOStatement;
use Personly\Framework\Database\Connection;
use Personly\Framework\Database\PdoConnection;
use Personly\Photos\Domain\Photo;
use Personly\Photos\Infrastructure\MySqlPhotoRepository;
use PHPUnit\Framework\TestCase;

class MySqlPhotoRepositoryTest extends TestCase
{
    private Photo $photo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->photo = new Photo();
    }

    public function testFindOne()
    {
        // Arrange
        $id = 1;

        // Create a mock PDOStatement
        $mockStmt = $this->getMockBuilder(PDOStatement::class)
            ->onlyMethods(['execute', 'setFetchMode', 'fetch'])
            ->getMock();

        $mockStmt->expects($this->once())
            ->method('execute')
            ->with([$id]);

        $mockStmt->expects($this->once())
            ->method('setFetchMode')
            ->with(PDO::FETCH_CLASS, Photo::class);

        $mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($this->photo);

        // Create a mock database connection
        $mockDb = $this->getMockBuilder(PdoConnection::class)
            ->onlyMethods(['prepare'])
            ->getMock();

        $mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        // Set the mocked database connection in your repository
        $repository = new MySqlPhotoRepository($mockDb);

        // Act
        $result = $repository->findOne($id);

        // Assert
        $this->assertInstanceOf(Photo::class, $result);
        // Add more assertions based on the expected properties of $result and $expectedPhoto
        $this->assertEquals(0, $result->getCommentCount());
    }
}