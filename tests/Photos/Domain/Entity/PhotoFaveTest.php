<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\User\Domain\User;
use PHPUnit\Framework\TestCase;

final class PhotoFaveTest extends TestCase
{
    public function testPhotoFave(): void
    {
        $data = new DataSet([
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
        ]);
        $photo = Photo::createFromDataSet($data);
        $data = new DataSet([
            'email' => 'mail@example.com',
            'uuid' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
            'name' => 'Mark',
            'phone' => '01234567890',
            'login_attempts' => '1',
            'login_date' => '2024-03-02 15:26:00',
            'magic_link' => '550e8400-e29b-41d4-a716-446655440000',
            'phone_code' => '9bR3xZ',
            'passcode_route' => 'email',
            'id' => '1'
        ]);
        $user = User::createFromDataSet($data);

        $photoFave = new PhotoFave(
            photo: $photo,
            user: $user
        );

        $this->assertEquals($photo, $photoFave->getPhoto());
        $this->assertEquals($user, $photoFave->getUser());
    }
}
