<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\Photos\Infrastructure\MySql\PhotoFaveRepository;
use MyEspacio\User\Domain\User;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoFaveRepositoryTest extends TestCase
{
    private Photo $photo;
    private PhotoFave $photoFave;

    protected function setUp(): void
    {
        parent::setUp();
        $this->photo = Photo::createFromDataset(
            new DataSet([
                'country_id' => '45',
                'country_name' => 'Chile',
                'two_char_code' => 'CL',
                'three_char_code' => 'CHL',
                'geo_id' => '2559',
                'photo_id' => '2689',
                'latitude' => '-33438084',
                'longitude' => '-33438084',
                'accuracy' =>  '16',
                'width' => '456',
                'height' => '123',
                'cscore' => '4',
                'pscore' => '5',
                'date_taken' => "2012-10-21",
                'description' => "Note the spurs...",
                'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                'filename' => "P1070237.JPG",
                'title' => "Getting ready to dance",
                'town' => "Valparaiso",
                'comment_count' => '1',
                'fave_count' => '1',
                'uuid' => '8d7fb4b9-b496-478b-bd9e-14dc30a1ca71'
            ])
        );
        $this->photoFave = new PhotoFave(
            photo: $this->photo,
            user: new User(
                email: 'mail@example.com',
                uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
                name: 'Mark',
                phone: '01234567890',
                loginAttempts: 1,
                loginDate: new DateTimeImmutable('2024-03-02 15:26:00'),
                magicLink: '550e8400-e29b-41d4-a716-446655440000',
                phoneCode: '9bR3xZ',
                passcodeRoute: 'email',
                id: 1
            )
        );
    }

    public function testAdd(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.photo_faves (user_id, photo_id) VALUES (:userId, :photoId)',
                [
                    'userId' => 1,
                    'photoId' => 2689
                ]
            )
            ->willReturn($statement);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->with($statement)
            ->willReturn(false);

        $repository = new PhotoFaveRepository($db);
        $result = $repository->add($this->photoFave);
        $this->assertTrue($result);
    }

    public function testAddFail(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.photo_faves (user_id, photo_id) VALUES (:userId, :photoId)',
                [
                    'userId' => 1,
                    'photoId' => 2689
                ]
            )
            ->willReturn($statement);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->with($statement)
            ->willReturn(true);

        $repository = new PhotoFaveRepository($db);
        $result = $repository->add($this->photoFave);
        $this->assertFalse($result);
    }

    public function testAddAnonymous(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.anon_photo_faves (photo_id) VALUES (:photoId)',
                [
                    'photoId' => 2689
                ]
            )
            ->willReturn($statement);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->with($statement)
            ->willReturn(false);

        $repository = new PhotoFaveRepository($db);
        $result = $repository->addAnonymous($this->photoFave);
        $this->assertTrue($result);
    }

    public function testAddAnonymousFail(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.anon_photo_faves (photo_id) VALUES (:photoId)',
                [
                    'photoId' => 2689
                ]
            )
            ->willReturn($statement);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->with($statement)
            ->willReturn(true);

        $repository = new PhotoFaveRepository($db);
        $result = $repository->addAnonymous($this->photoFave);
        $this->assertFalse($result);
    }

    public function testGetPhotoFaveCount(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT COUNT(photo_id) AS quantity FROM pictures.photo_faves WHERE photo_id = :photoId',
                [
                    'photoId' => 2689
                ]
            )
            ->willReturn(['quantity' => '2']);

        $repository = new PhotoFaveRepository($db);
        $result = $repository->getPhotoFaveCount($this->photo);

        $this->assertSame(2, $result);
    }

    public function testGetPhotoFaveCountNotFound(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT COUNT(photo_id) AS quantity FROM pictures.photo_faves WHERE photo_id = :photoId',
                [
                    'photoId' => 2689
                ]
            )
            ->willReturn(null);

        $repository = new PhotoFaveRepository($db);
        $result = $repository->getPhotoFaveCount($this->photo);

        $this->assertSame(0, $result);
    }

    public function testGetAnonymousFaveCount(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT COUNT(photo_id) AS quantity FROM pictures.anon_photo_faves WHERE photo_id = :photoId',
                [
                    'photoId' => 2689
                ]
            )
            ->willReturn(['quantity' => '2']);

        $repository = new PhotoFaveRepository($db);
        $result = $repository->getAnonymousFaveCount($this->photo);

        $this->assertSame(2, $result);
    }

    public function testGetAnonymousFaveCountNotFound(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT COUNT(photo_id) AS quantity FROM pictures.anon_photo_faves WHERE photo_id = :photoId',
                [
                    'photoId' => 2689
                ]
            )
            ->willReturn(null);

        $repository = new PhotoFaveRepository($db);
        $result = $repository->getAnonymousFaveCount($this->photo);

        $this->assertSame(0, $result);
    }
}
