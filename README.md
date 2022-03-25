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

The idea behind this project is to make a CQRS + ES system compliant, specifically implemented through
the [Broadway](https://github.com/broadway/broadway) library, with the General Data Protection Regulation (GDPR),
in particular with the right to be forgotten.

Read the [wiki](https://github.com/matiux/broadway-sensitive-serializer/wiki) for more information.

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
Read the [wiki](https://github.com/matiux/broadway-sensitive-serializer/wiki/04.Examples)