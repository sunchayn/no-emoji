## No Emoji

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mazentouati/no-emoji/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mazentouati/no-emoji/?branch=master) [![StyleCI](https://styleci.io/repos/168873915/shield)](https://styleci.io/repos/168873915) [![Maintainability](https://api.codeclimate.com/v1/badges/acab377b68b3ce930708/maintainability)](https://codeclimate.com/github/mazentouati/no-emoji/maintainability) [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](./LICENSE)

No Emoji is simple package dedicated to generate a RegEx pattern that detect if a string has an Emoji or not. The generated pattern is based on Unicode V11 reference published in this [File](https://unicode.org/Public/emoji/11.0/emoji-test.txt).

## Installation

Installation through [composer](http://getcomposer.org/) :

```bash
composer create-project mazentouati/no-emoji no-emoji
```

you can execute the code using your preferred local server (eg: [Laragon](https://laragon.org))

or using PHP built-in server :

```bash
cd no-emoji
php -S localhost:80 -t . index.php
```

