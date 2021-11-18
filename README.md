Sensitization support for [broadway/broadway](https://github.com/broadway/broadway)
===

![check dependencies](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/check-dependencies.yml/badge.svg)
![test](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/tests.yml/badge.svg)
[![codecov](https://codecov.io/gh/matiux/broadway-sensitive-serializer/branch/master/graph/badge.svg)](https://codecov.io/gh/matiux/broadway-sensitive-serializer)
[![type coverage](https://shepherd.dev/github/matiux/broadway-sensitive-serializer/coverage.svg)](https://shepherd.dev/github/matiux/broadway-sensitive-serializer)
[![psalm level](https://shepherd.dev/github/matiux/broadway-sensitive-serializer/level.svg)](https://shepherd.dev/github/matiux/broadway-sensitive-serializer)
![security analysis status](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/security-analysis.yml/badge.svg)
![coding standards status](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/coding-standards.yml/badge.svg)
![continuous Integration](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/ci.yml/badge.svg)

## Setup for development

```shell
git clone https://github.com/matiux/broadway-sensitive-serializer.git && cd broadway-sensitive-serializer
cp docker/docker-compose.override.dist.yml docker/docker-compose.override.yml
rm -rf .git/hooks && ln -s ../scripts/git-hooks .git/hooks
```

## TODO

* Whole payload strategy
* Documentazione
  * Check test names
* Coverage
* Badge PHP version