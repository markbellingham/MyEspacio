The system uses language files for any text that will be seen by the end user. This includes labels (inputs, buttons, etc), headings, paragraphs, and Ajax messages.

The localisation files are located at `MyEspacio\Locale\{two character country code}/file.php`. The file should return an array, grouped by section or type, and then a key describing the particular item.

There are unit tests which check that each of the language files have the same structure, ensuring that once a language is implemented it must be complete.

The language is set via the URL, with the two character country code being the first segment of the URL. E.g. `https://www.domain.tld/en/photos` If the country code is missing or does not match any of the supported languages, the system will default to English. Supported languages are listed in `MyEspacio\Framework\Localisation\TranslationIdentifier` , and are currently
* English
* French
* Spanish

The filenames should use the following standards:
* `messages.php`  - Messages returned from an Ajax request
* `components.php`  - Labels, button text, and other reusable items
* `copy.php`  - Headings, paragraphs, etc