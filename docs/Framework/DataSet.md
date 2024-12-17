A DataSet is intended to provide an easy way to convert string values to other scalar values, or to a DateTimeImmutable object. There are two methods available for each scalar type, where the class can either return a default value, or a method where it can return the converted value or null. The exception to this is the `dateTimeNull` function which doesn't have a default option.

The following data types are supported
* string - will return a trimmed string of the input value
* integer
* float
* boolean - handles type conversion, so 'true', '1', etc will return `true`, and falsy values will return `false
* DateTimeImmutable - attempts to create an instance of DateTimeImmutable, returning `null` on failure
* value - simply returns the value exactly as provided

The most common use case will be to provide a single-level array, with string keys and values.
```php
$data = [
    'name' => 'Joe Bloggs  ',
    'email' => 'joe.bloggs@domain.tld',
    'isRegistered' => 'true',
    'loginAttempts' => '1',
    'loginDate' => '2024-12-16 19:33:00',
    'someFloat' => '1.23'
]

$dataSet = new DataSet($data);

$dataSet->string('name') // returns 'Joe Bloggs'
$dataSet->bool('isRegistered') // returns true
$dataSet->int('loginAttempts') // returns 1
$dataSet->dateTimeNull('loginDate') // returns new DateTimeImmutable('2024-12-16 19:33:00')
$dataSet->float('someFloat') // returns 1.23
$dataSet->value('name') // returns 'Joe Bloggs  '
```

The most common use-case for this class will be to populate models using data from either the database or from a client request.

If the value of the property being asked for is not a compatible type and can't be converted easily, the class handles it differently depending on which function is used:

* string - will try to `json_encode` the value, and return the default or `null` if unsuccessful
* int, float - will return either the default value or `null` depending on the function used
* dateTimeNull - will return `null`
* value - will return the value as is

There is also a `toArray` method, which returns the same data that was originally passed in.