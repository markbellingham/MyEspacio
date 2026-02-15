# RequestHandler
All controllers must require the `MyEspacio\Framework\Http\RequestHandler`  in the constructor. The `RequestHandler` will validate the request and format the response, according to the client's requirements, or some defaults if they are not set.

## `validate` 
Returns `true` if
 * The client has requested a data-only response, such as JSON, XML, or CSV
 * The request includes the CSRF token

If `validate` returns `false` the system will redirect to load the full application in HTML.

The client sets the type of response they require using the `Accept` header. If this is missing or incorrect the system will default to HTML. Other supported return types are
* application/json
* application/xml
* text/csv
If CSV is requested, the system will only return information in a relevant structure. For example, if the response includes a photo album with a list of photos, the system will only return the list of photos. All other data types will have the full structure.

## `sendResponse`
This function will both construct and return the response to the client, using the data from the `ResponseData` object below.

## `ResponseData` 
The `sendResponse`  function requires a `MyEspacio\Framework\Http\ResponseData` object, ensuring that the system has all the required components to send a valid response, even if empty. This is where you will set the 
* data - Usually data returned from the database
* status code - HTTP status code, if not `200` 
* template - template name for rendering HTML
* translation key - used when sending an Ajax response, to set the location of the required text
	* If this is a nested location the string should use dot notation - e.g. `login.already_logged_in` 
* translation variables - Some of the translation values contain a variable, such as the user's name. This is where you provide it.

All the properties have default values, so if your use case doesn't need all of them you can use named arguments to provide only the ones you require.

## Route
The application uses a Route attribute to enable HTTP routing. The attribute should be placed above the method that the particular route will execute.
The Route attribute takes 3 arguments:
* The URL itself, which uses `[nikic/FastRoute](https://github.com/nikic/FastRoute)` to parse.
* The HTTP method, which uses the HttpMethod enum
* A priority integer in the event that two or more routes clash.
```php
#[Route('/photos[/[{album:.+}]]', HttpMethod::GET, priority: 2)]
```
