Controllers live inside `MyEspacio/{ModuleName}/Presentation`

A controller should receive the request and return the response. It might also handle some simple business logic, but business logic that becomes complex should be moved to another class inside Application.

The controller must take the `MyEspacio/Framework/Http/RequestHandler` as a constructor argument. 

The controller must call the `validate` method on each request. If `validate` returns `false` it must return the full HTML website.

The controller must use the `sendResponse` method to return the data to the client for each request. This will ensure that the data is formatted to the client's requirements (HTML, JSON, etc).

If the controller is routable, then it must extend `MyEspacio\Framework\BaseController` , and any public methods must use the `#[Route]` attribute.