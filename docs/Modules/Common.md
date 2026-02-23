The `/common` section contains various bits that are used by more than 1 module, such as captcha, icons, and high-level classes for tags, comments, and faves.

## Fave
The base class for creating faves. This class should not be instantiated on its own, but instead the different modules should create a class that extends this one, for example `PhotoFave`.

## Comment
Similar to `Fave` this is the base class for user comments. This class should not be instantiated on its own, but instead the different modules should create a class that extends this one, for example `PhotoComment`.

## Tag
Another base class, this time for tags. This class is to be extended by the different modules, for example `PhotoTag`.