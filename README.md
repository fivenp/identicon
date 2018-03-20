# PHP-Identicons

PHP-Identicons is a lightweight PHP implementation of Don Park's
original identicon code for visual representation of MD5 hash values.
The program uses the PHP GD library for image processing.

The code can be used to generate unique identicons, avatars, and
system-assigned images based on a user's e-mail address, user ID, etc.


## INSTALLATION

Install using composer:

```
$ composer require fivenp/identicon
```

## USAGE

### Basic usage
The code below, will create an identicon from the string "TEST", and save it to ```identicon.png```.

```php
<?php

use Fivenp\Identicon\Identicon;

$identicon = new Identicon(md5('TEST'));
$icon = $identicon->create();
file_put_contents('identicon.png', $icon);
```

There's also a more short Version of doing this:
```php
<?php

use Fivenp\Identicon\Identicon;

file_put_contents('identicon.png', (new Identicon(md5('TEST')))->create());
```


### Advanced usage

You can overwrite some basic settings by passing an `options` array.
```php
<?php

use Fivenp\Identicon\Identicon;

$options = array(
    'size'=>2048, // a value between 16 and 2048 is accepted
    'backgroundColor'=>array( // must be in red/green/blue
        "red" => "255",
        "green" => "255",
        "blue" => "255",
    ),
);

$identicon = new Identicon(md5('TEST'),$options);
$icon = $identicon->create();
```

### Even more advanced options

You can overwrite some basic settings by passing an `options` array.
```php
<?php

use Fivenp\Identicon\Identicon;

$options = array(
    'size'=>2048, // a value between 16 and 2048 is accepted
);

$identicon = new Identicon(md5('TEST'),$options);

// A cusom color palette where the generator is using the colors randomly from
$identicon->palette = array(
    'orange' => '#ff944e',
    'red' => '#e84c3d',
    'blue' => '#3598db',
    'black' => '#000000',
    'white' => '#ffffff',
);

// A cusom color palette where the generator is using the backgroundColor randomly from
$identicon->availableBackgroundColors = array(
    'white',
    'red',
);

$icon = $identicon->create();
```

## LICENSE

PHP-Identicons is distributed under the [GPLv3 License](http://www.gnu.org/licenses/gpl-3.0.en.html).


## HISTORY

This code was forked from Timo van Neerden [project page on Github](https://github.com/timovn/identicon) which was
originnially created by Bong Costa in 2009.

It has been forked from its [project page on SourceForge](https://sourceforge.net/projects/identicons/) as I intend to enhance it a bit for personnal usage.

