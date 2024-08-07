# Code Quality

| PHPStan Level 5 | [![pipeline status](https://gitlab.com/markbellingham/myespacio/badges/master/pipeline.svg)](https://gitlab.com/markbellingham/myespacio/-/commits/master) | [![coverage report](https://gitlab.com/markbellingham/myespacio/badges/master/coverage.svg)](https://gitlab.com/markbellingham/myespacio/-/commits/master) |
|-----------------| ---------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |

# Music
* Can list and filter all music files on my computer. 
* Can add albums or individual tracks to the playlist, play file, skip previous / next, double click to another file. 
* Top artists/albums/tracks is dynamic and retrieved from LastFM once each day that the website is loaded
  * Top artists/albums/tracks are accessed using the buttons or via the URL. E.g. [/music/topalbums](url)

![Music Browser](screenshots/Music.png?raw=true "Title")
## Single album view
 * Information paragraph retrived from Wikipedia using their API. 
 * Can add individual tracks to the playlist

![Album View](screenshots/Album.png?raw=true "Title")
## Playlist 
* Playlistslides down over the website content. 
* Can double click on a track to change the currently playing one.
![Playlist View](screenshots/Playlist and Playing.png?raw=true "Title")

# Photos
* Displays all photos in the gallery. 
* Can search for photos using titles, tags, descriptions, location
* Can display most popular photos by faves and comments
* Can display search results from the URL 
  * If the search term matches an album then the view shows all photos from that album. E.g. [/photos/vietnam](url)
  * If the search term does not match an album it searches all photos. E.g. [/photos/sunset](url)
  * Multiple search terms are possible. The same rules apply, so if the first search term is an album it returns a search of the second term within that album. If no album matches then it returns results that match both terms. E.g. [/vietnam/sunset](url)

![Photo Gallery](screenshots/Photos.png?raw=true "Title")
## Single photo modal view. 
* Can show full size photo
* Add as favourite
  * Non-logged-in users are registered as anonymous faves
  * Logged-in users faves are linked to their user account
* Add comment (only if you are logged in)

![Single Photo](screenshots/Photo Detail.png?raw=true "Title")

# Games
* Shows simple JavaScript games. 
* Can switch game using the dropdown

![Games Page](screenshots/Games.png?raw=true "Title")

# Contact Me
* Form to send me a message
* Links to visit my social media profiles

![Contact Page](screenshots/Contact.png?raw=true "Title")

# Login
* Log in from your email using a magic link or a code

![Login Modal](screenshots/Login.png?raw=true "Title")
