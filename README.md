# Code Quality

<table border="1">
  <tr>
    <td>PHPStan Level 9</td>
    <td><a href="https://gitlab.com/markbellingham/myespacio/-/commits/master"><img src="https://gitlab.com/markbellingham/myespacio/badges/master/pipeline.svg" alt="pipeline status" style="vertical-align: middle;"></a></td>
    <td>PHP <a href="https://gitlab.com/markbellingham/myespacio/-/commits/master"><img src="https://gitlab.com/markbellingham/myespacio/badges/master/coverage.svg?job=test_php" alt="PHP coverage" style="vertical-align: middle;"></a></td>
    <td>TypeScript <a href="https://gitlab.com/markbellingham/myespacio/-/commits/master"><img src="https://gitlab.com/markbellingham/myespacio/badges/master/coverage.svg?job=test_typescript" alt="TypeScript coverage" style="vertical-align: middle;"></a></td>
  </tr>
</table>

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

# Contact Me
* Form to send me a message
* Links to visit my social media profiles

![Contact Page](screenshots/Contact.png?raw=true "Title")

# Login
* Log in from your email using a magic link or a code

![Login Modal](screenshots/Login.png?raw=true "Title")
