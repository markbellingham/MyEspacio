A collection is a class for a group of a single type of model. A collection should extend the `\MyEspacio\Framework\ModelCollection` class. This class will check the values of the collection upon instantiation, thus ensuring that each of the values in the collection is always of a valid type.

A collection should always be created from an array of associative arrays. If it is passed any other data type it will throw an exception.

Create a collection by extending `ModelCollection`
```php
$data = [
    [
        'id' => '1',
        'name' => 'Joe Bloggs',
        'email' => 'joe.bloggs@domain.tld'
    ],
    [
        'id' => '2',
        'name' => 'Jane Doe',
        'jane.doe@domain.tld'
    ]
]

$userCollection = new UserCollection($data);

foreach ($userCollection as $user) {
    if (typeof $user !== User::class) {
        // error
    }
}
```

`ModelCollection` has two abstract functions, which much be created in your class

`requiredKeys`
Returns an array of strings, where each one is the name of a key that must be in each array that will create the model. If when creating the collection the data is missing any of the required keys it will throw an exception.

```php
/**
 * @return array<int, string>
 */
public function requiredKeys(): array
{
    return [
        'id',
        'name',
        'email'
    ]
}
```

`current`
Returns an instance of a class that extends `Model` .

```php
public function current(): User
{
    $data = $this->currentDataSet();
    
    return new User(
        id: $data->int('id'),
        name: $data->string('name'),
        email: $data->string('email')
    )
}
```
