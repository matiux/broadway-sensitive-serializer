Sensitization support for [broadway/event-store-dbal](https://github.com/broadway/event-store-dbal)
===

![Continuous Integration](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/ci.yml/badge.svg)
![Test](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/test.yml/badge.svg)
[![codecov](https://codecov.io/gh/matiux/broadway-sensitive-serializer/branch/develop/graph/badge.svg)](https://codecov.io/gh/matiux/broadway-sensitive-serializer)
![Type coverage](https://shepherd.dev/github/matiux/broadway-sensitive-serializer/coverage.svg)
![Psalm level](https://shepherd.dev/github/matiux/broadway-sensitive-serializer/level.svg)

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