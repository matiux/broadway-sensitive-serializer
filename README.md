Sensitization support for [broadway/broadway](https://github.com/broadway/broadway)
===

![check dependencies](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/check-dependencies.yml/badge.svg)
![test](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/tests.yml/badge.svg)
[![codecov](https://codecov.io/gh/matiux/broadway-sensitive-serializer/branch/master/graph/badge.svg)](https://codecov.io/gh/matiux/broadway-sensitive-serializer)
[![type coverage](https://shepherd.dev/github/matiux/broadway-sensitive-serializer/coverage.svg)](https://shepherd.dev/github/matiux/broadway-sensitive-serializer)
[![psalm level](https://shepherd.dev/github/matiux/broadway-sensitive-serializer/level.svg)](https://shepherd.dev/github/matiux/broadway-sensitive-serializer)
![security analysis status](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/security-analysis.yml/badge.svg)
![coding standards status](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/coding-standards.yml/badge.svg)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/matiux/broadway-sensitive-serializer?color=blue)
![Read the Docs (version)](https://img.shields.io/readthedocs/broadway-sensitive-serializer/latest)

The idea behind this project is to make a CQRS+ES system compliant, specifically implemented through
the [Broadway](https://github.com/broadway/broadway) library, with the General Data Protection Regulation (GDPR),
in particular with the right to be forgotten.

Normal Broadway event payload
```json
{
    "class": "SensitiveUser\\User\\Domain\\Event\\UserRegistered",
    "payload": {
        "id": "b0fce205-d816-46ac-886f-06de19236750",
        "name": "Matteo",
        "surname": "Galacci",
        "email": "m.galacci@gmail.com",
        "occurred_at": "2022-01-08T14:22:38.065+00:00"
    }
}
```

Example of a payload with the extension active
```json
{
  "class": "SensitiveUser\\User\\Domain\\Event\\UserRegistered",
  "payload": {
    "id": "b0fce205-d816-46ac-886f-06de19236750",
    "name": "Matteo",
    "surname": "#-#2Iuofg4NKKPLAG2kdJrbmQ==:bxQo+zXfjUgrD0jHuht0mQ==",
    "email": "#-#OFLfN9XDKtWrmCmUb6mhY0Iz2V6wtam0pcqs6vDJFRU=:bxQo+zXfjUgrD0jHuht0mQ==",
    "occurred_at": "2022-01-08T14:22:38.065+00:00"
  }
}
```

The symfony bundle exists to simplify integration with the framework [here](https://github.com/matiux/broadway-sensitive-serializer-bundle)

Read the [doc](https://broadway-sensitive-serializer.readthedocs.io/en/latest/) for more information.

## Install
```shell
composer require matiux/broadway-sensitive-serializer
```
## Setup for development

```shell
git clone https://github.com/matiux/broadway-sensitive-serializer.git && cd broadway-sensitive-serializer
cp docker/docker-compose.override.dist.yml docker/docker-compose.override.yml
rm -rf .git/hooks && ln -s ../scripts/git-hooks .git/hooks
```

This repository uses GitHub actions to perform some checks. If you want to test the actions locally you can use [act](https://github.com/nektos/act).
For example if you want to check the action for static analysis
```
act -P ubuntu-latest=shivammathur/node:latest --job static-analysis
```


### Interact with the PHP container
This is a bash script that wrap major docker-compose function. You can find it [here](./docker/dc.sh) and there is a symbolic link in project root.

Some uses:
```shell
./dc up -d
./dc enter
./dc phpunit
./dc psalm
./dc coding-standard-fix-staged
./dc build php --no-cache
./dc build-docs
```
Check out [here](./docker/dc.sh) for all the options.

### Install dependencies to run test or execute examples
```shell
./dc up -d
./dc enter
composer install
```

### Run test
```shell
./dc up -d
./dc enter
project phpunit
```

## Example code
Read the [examples section](https://broadway-sensitive-serializer.readthedocs.io/en/latest/examples.html)