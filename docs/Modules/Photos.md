## Default view
if no other type of view is requested, the system will show the photos from the Favourites album
## Search photos
Users can search photos by entering search terms into the search box, or by editing the URL. The former will automatically update the latter. This means that if you search, view an album, view a single photo in an album, or view a single photo in a search result, then copy the URL, you will be able to paste that URL into a new tab and open the same result in the same context. Likewise, you will be able to share that URL and the person you shared it with will see the same context.

The system is designed so that searching via the web interface or the API will work in the same way and return the same results.

If the user selects a photo album from the drop-down, or if the URL search matches an album name, the system will return that album's photos. If this is combined with a search term, the system will search within that album's photos.

The format of the URL is that albums appear in the path, and search terms should be added  as  a parameter with the key "search".

The search feature will match with tags, titles, descriptions, towns, and countries. Results will be sorted by relevance (most matches).

`/photos?search=sunset`
Search all photos that match with "sunset"

`/photos/mexico`
Show all photos from the "Mexico" album

`/photos/mexico?search=sunset`
Search photos within the "Mexico" album that also match with "sunset"

`/photos?search=sunset trees`
Search all photos that match with both "sunset" and "trees"
## My favourites
`/photos/my-favourites`
Returns my (the author's) favourite photos. This is the same as the default view.
## Most popular
`/photos/most-popular`
Returns photos with the most faves and comments.
## Single photo view
Click on a photo to show a larger version. The photo will appear at the top of the screen with the photo grid below. Along with the photo will be a heart icon to select that photo as a favourite, comments from other users related to that photo. 
Click the "Close" button in the top right corner or press the "Esc" key to close the single photo view and restore the grid to fill the view.
## Faves
Anybody can click to select a photo as a "fave", you don't need to be logged in. This is only available to the website users, because the server will check the CSRF token before completing the action.

If the user is logged in, the fave will be recorded against their user ID, and it will be possible to return a list of the user's favourite photos. If the user is not logged in, the fave will be recorded against the anonymous user. It will not be possible to extract this out later, although the system does also store their faves in local storage, so that users who are and aren't logged in will have a similar experience whilst their local browser data is still valid.

Clicking the heart icon makes it turn red and registers the fave.
## Comments
Comments can only be added by logged in users, although all users will be able to see and read comments. This feature is only available to the website users because the system will check the CSRF token before submitting.

The system will check that the comment meets certain guidelines - no profanity, no hyperlinks or HTML.
## Entity relationship diagram
[[pictures_erd.png]]