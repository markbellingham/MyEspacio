Repositories must implement an interface. The interface lives in `MyEspacio/{ModuleName}/Domain/Repository`. 

The repository itself lives in the `MyEspacio/{ModuleName}/Infrastructure` directory. 

The repository should take a `MyEspacio/Framework/Database/Connection` interface as a constructor argument, which will provide access to all the databases.

A repository should be concerned with a single model, even if this means it only has a single method. This will make the code easier to browse through and find what you are looking for.

## Databases
### games
There is only one table, which is a list of all the games currently added to the website.

### music
**albums**
A list of all music albums that can contain one or more tracks.

**artists**
A list of all artists that have at least one track attributed to them. This will include artists that have their own albums, artists that have created compilation albums, and artists that appear on compilation albums.

**extra**
Some metadata, such as the last time the playback count was retrieved from the music scrobbler.

**genres**
A list of possible music genres that tracks have been categorised as.

**tracks**
A list of music tracks. Each track may belong to a single album (duplicates will have their own entries). Each track may be attributed to a single artist.

### pictures
**albums**
Has a list of all photo albums

**anon_photo_faves**
Photos added as a fave where the user is not known

**countries**
A list of all countries with their 2 and 3 digit ISO country code

**geo**
One entry per image, contains the latitude and longitude of the image

**photo_album**
A link table between photos and albums, showing which photos belong to which albums. A photo can belong to multiple albums. An album will have multiple photos.

**photo_comments**
Comments that belong to a photo. A photo can have multiple comments. A comment will belong to only one photo.

**photo_faves**
Photos added as a fave where the user is known. Has the user ID and the Photo ID. A user can only fave a photo once.

**photo_tags**
Tags associated with a photo. The actual tag values are stored in the `projects` database, because they can also be added to blog entries (planned feature). A tag can belong to more than one photo and a photo can have more than one tag.

**photos**
The main metadata table for the list of photos.

### project
**icons**
A list of icons used by the project. Mainly used by the captcha feature.

**sections**
A list of sections (modules) implemented by the website, with settings to show whether the section is currently available. If the section is marked as unavailable, the system will not load the code associated with it, thus making the size of the site smaller.

**tags**
A list of tags assigned to photos and blog entries.

**users**
A list of users that are able to log in to the website. Also contains metadata such as the magic link and login code.
