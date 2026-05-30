# Code Quality

<table>
  <tr>
    <td>PHPStan Level 9</td>
    <td><a href="https://gitlab.com/markbellingham/myespacio/-/commits/master"><img src="https://gitlab.com/markbellingham/myespacio/badges/master/pipeline.svg" alt="pipeline status"></a></td>
    <td>PHP <a href="https://gitlab.com/markbellingham/myespacio/-/commits/master"><img src="https://gitlab.com/markbellingham/myespacio/badges/master/coverage.svg?job=test_php" alt="PHP coverage"></a></td>
    <td>TypeScript <a href="https://gitlab.com/markbellingham/myespacio/-/commits/master"><img src="https://gitlab.com/markbellingham/myespacio/badges/master/coverage.svg?job=test_typescript" alt="TypeScript coverage"></a></td>
  </tr>
</table>

# Photos

* Displays all photos in the gallery.
* Can search for photos using titles, tags, descriptions, location
* Can open a large version of the photo
* Can re-open the same view from the URL
  * If this is a search result then the URL format will be `/photos?search={search terms}`
  * If this is a photo album then the URL format will be `/photos/{album name}`
  * If this is a single photo then the URL format will be `/photo/{photo uuid}`
  * If this is a single photo with album context then the URL will be `/photos/{album name}/photo/{photo uuid}`
  * If this is a single photo with search context then the URL will be `/photo/{photo uuid}?search={search terms}`

![Photo Gallery](screenshots/Photos.png?raw=true "Photo Gallery")

## Single photo view

* Can show full size photo
* Add as favourite
  * Non-logged-in users are registered as anonymous faves
  * Anonymous faves are saved to local storage
  * Logged-in users faves are linked to their user account
* Add comment, if you are logged in
* View a sharable link, either as a direct link to the photo or a link that retains the current context

![Single Photo](screenshots/Photo%20Detail.png?raw=true "Single Photo")

# Contact Me

* Form to send me a message
* Links to visit my social media profiles

![Contact Page](screenshots/Contact.png?raw=true "Contact Page")

# Login

* Log in from your email using a magic link or a code

<p align="center">
  <strong>Login modal</strong><br>
  <img src="screenshots/Login.png?raw=true" alt="Login modal" title="Login modal">
  <br><br>
  <strong>Login Email</strong><br>
  <img src="screenshots/Login%20Email.png?raw=true" alt="Login Email" title="Login Email">
</p>
