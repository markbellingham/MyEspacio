# Email
The `MyEspacio\Framework\Messages\EmailMessage` class should not be instantiated directly, and indeed it has no constructor and protected setter methods to discourage this. Instead, when there is a need to create an email message, your class should extend this one, adding the business logic required in your specific scenario. 

This class will throw exceptions if
 * The email address is missing or not a valid email address
 * The email message has fewer than 20 characters
 * The recipient name has fewer than 3 characters
 * The subject has fewer than 3 characters

This will ensure that the email is valid and can be sent by the email handler.