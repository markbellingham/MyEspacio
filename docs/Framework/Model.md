All models should extend the `\MyEspacio\Framework\Model` class. All models must be declared `final` unless there is a specific and documented reason for extending the class.

This class has two methods, both of which should be implemented or overridden in the extended class:

`createFromDataSet`
Provide an easy way to populate the properties of the model by passing in a [[DataSet]], which itself is created from an associative array. It should return an instance of the class or `null`.

`jsonSerialize`
If this method is not overridden, then performing `json_encode` on a model will return an empty array.

This method should specify which properties are to be returned when the model is `json_encode`d. 

The model should never display an auto-incremented ID value to the client, keeping this value server-side at all times. If it is required for a client to specify a single record, a `uuid` value should be provided for this purpose instead.

DateTimeImmutable fields should be returned as
```php
$dateTimeImmutable->format(DateTimeInterface::ATOM);
```

Properties which are themselves instances of another class should call the `jsonSerialize` function to ensure they are properly formatted, and that properties which are not needed or desired to be shown client side are removed.

```php
final class User(
    public function __construct(
        private int $id,
        private UuidInterface $uuid,
        private string $name,
        private DateTimeImmutable $loginDate,
        private Country $country
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid->toString(),
            'name' => $this->name,
            'loginDate' => $this->loginDate->format(DateTimeInterface::ATOM),
            'country' => $this->country->jsonSerialize()
        ]
    }
)
```
This will return data in the format
```php
[
    'uuid' => '9c001dd7-7921-4944-bc17-52b890aa51fb',
    'name' => 'Joe Bloggs',
    'loginDate' => '2024-06-17T12:34:56+00:00',
    'country' => [
        'name' => 'United Kingdom',
        'twoCharShortCode' => 'GB',
        'threeCharShortCode' => 'GBR'
    ]
]
```
