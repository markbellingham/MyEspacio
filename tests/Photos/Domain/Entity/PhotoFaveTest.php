<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\User\Domain\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoFaveTest extends TestCase
{
    #[DataProvider('modelDataProvider')]
    public function testModel(
        Photo $photo,
        User $user,
    ): void {
        $photoFave = new PhotoFave($photo, $user);

        $this->assertSame($photo, $photoFave->getPhoto());
        $this->assertSame($user, $photoFave->getUser());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'photo' => Photo::createFromDataSet(
                    new DataSet([
                        'country_id' => '45',
                        'country_name' => 'Chile',
                        'two_char_code' => 'CL',
                        'three_char_code' => 'CHL',
                        'geo_id' => '2559',
                        'photo_uuid' => '8d7fb4b9-b496-478b-bd9e-14dc30a1ca71',
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
                ),
                'user' => new User(
                    email: 'mail@example.com',
                    uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
                    name: 'Mark',
                    phone: '01234567890',
                    loginAttempts: 1,
                    loginDate: new \DateTimeImmutable('2024-03-02 15:26:00'),
                    magicLink: '550e8400-e29b-41d4-a716-446655440000',
                    phoneCode: '9bR3xZ',
                    passcodeRoute: 'email',
                    id: 1
                )
            ],
            'test_2' => [
                'photo' => Photo::createFromDataSet(
                    new DataSet([
                        'country_id' => '12',
                        'country_name' => 'France',
                        'two_char_code' => 'FR',
                        'three_char_code' => 'FRA',
                        'geo_id' => '1661',
                        'photo_uuid' => 'caa1fb28-9aa8-41fa-9b28-4c92e0325247',
                        'photo_id' => '1234',
                        'latitude' => '32309405',
                        'longitude' => '77175694',
                        'accuracy' =>  '14',
                        'width' => '1920',
                        'height' => '1080',
                        'cscore' => '2',
                        'pscore' => '3',
                        'date_taken' => "2014-12-23",
                        'description' => "Top of the tower",
                        'directory' => "RTW Trip\/12France\/10 - Paris",
                        'filename' => "P1070123.JPG",
                        'title' => "Tower top",
                        'town' => "Paris",
                        'comment_count' => '3',
                        'fave_count' => '4',
                        'uuid' => 'bfd42416-75ae-4f41-af76-df148ea990c5'
                    ])
                ),
                'user' => new User(
                    email: 'joe@bloggs.com',
                    uuid: Uuid::fromString('e7efcf15-8766-4ab5-a0ca-dfc517a2725e'),
                    name: 'Mark',
                    phone: '01234567890',
                    loginAttempts: 1,
                    loginDate: new \DateTimeImmutable('2025-08-06 21:13:00'),
                    magicLink: 'cac4a5f5-9eae-461e-86cd-3d148f42e0f6',
                    phoneCode: 'abc123',
                    passcodeRoute: 'phone',
                    id: 5
                )
            ],
        ];
    }
}
