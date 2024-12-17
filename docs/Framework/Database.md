All communication with the database is done using a repository. If the repository returns data in the form of a record or records, it should return a model or collection respectively.

To connect to the database, pass a Connection interface into the constructor
```php
use MyEspacio\Framework\Database\Connection;

final class PhotoRepository implements PhotoRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
    ) {
    }
}
```
The database class that implements this interface accepts a string for the SQL query, and an array for the parameters. The parameters can be parameterised in the query using positional placeholders or named placeholders.

The Connection interface has the following public methods available:

`fetchOne`
If a record is found it will return a single level array of keys and values.
If a record is not found it returns `null`

`fetchOneModel`
Similar to `fetchOne`, except that it takes a third parameter, which is the Fully Qualified Name of the class that will be automatically populated with the returned values.

If the class does not exist, the database class that implements this interface will throw an exception.

`fetchAll`
Returns an array of arrays, where each sub array contains the requested keys and values.

`run`
Use this method if you want to perform any changes to the database, `INSERT`, `UPDATE`, `DELETE`, etc.
It returns a PDOStatement.

`statementHasErrors`
Accepts a PDOStatement and returns a `boolean` indicating if the statement has any errors.

`lastInsertId`
Returns an integer of the last auto-incremented ID. If a record was successfully inserted, use this to get the ID of the record, if the table schema uses auto-incremented primary keys.

