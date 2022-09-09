#! /bin/bash

#check_all() {
#  coding_standard_fix --dry-run
#  if [[ "$?" -ne 0 ]]; then return 1; fi
#
#  psalm
#  if [[ "$?" -ne 0 ]]; then return 1; fi
#
#  psalm --taint-analysis
#  if [[ "$?" -ne 0 ]]; then return 1; fi
#
#  phpunit
#  if [[ "$?" -ne 0 ]]; then return 1; fi
#
#  check_deps_vulnerabilities
#  if [[ "$?" -ne 0 ]]; then return 1; fi
#
#  deptrac_table_all --quiet
#  if [[ "$?" -ne 0 ]]; then return 1; fi
#}

sphinx_build() {

    sphinx-build -b linkcheck -a docs/source ./var/tmp/mydocs
    STATUS=$?

    if [[ "$STATUS" -eq 0 ]]; then
      sphinx-build -a docs/source ./var/tmp/mydocs
    fi
}

operations["php_staged_files"]="php_staged_files"
#operations["check-deps-vulnerabilities"]="check_deps_vulnerabilities"
#operations["setup-test"]="APP_RUNTIME_ENV='test' setup"
#operations["setup-dev"]="APP_RUNTIME_ENV='dev' setup"
operations["phpunit"]='phpunit "$@"'
operations["coverage"]='coverage; exit $?'
operations["psalm"]='psalm "$@"; exit $?'
operations["psalm-taint"]='psalm --taint-analysis "$@"; exit $?'
operations["type-coverage"]='psalm --shepherd; exit $?'
operations["coding-standard-fix"]='coding_standard_fix "$@"; exit $?'
operations["coding-standard-check"]='coding_standard_fix --dry-run "$@"; exit $?'
operations["coding-standard-fix-all"]='coding_standard_fix; exit $?'
operations["coding-standard-fix-staged"]='coding_standard_fix $(git diff --name-only --cached --diff-filter=ACMR -- "*.php" | sed "s| |\\ |g"); exit $?'
operations["coding-standard-check-staged"]='coding_standard_fix --dry-run $(git diff --name-only --cached --diff-filter=ACMR -- "*.php" | sed "s| |\\ |g"); exit $?'
operations["coding-standard-check-all"]='coding_standard_fix --dry-run; exit $?'
operations["build-docs"]='sphinx_build; exit $?'
#operations["deptrac-table"]='deptrac_table "$@"; exit $?'
#operations["deptrac-table-all"]='deptrac_table_all "$@"; exit $?'
#operations["deptrac-image"]='deptrac_image "$1"; exit $?'
#operations["deptrac-image-all"]='for f in $DEPTRAC_CONFIG_FILES; do deptrac_image "$f"; done; exit $?'
#operations["infection"]='infection "$@"; exit $?'
#operations["check-all"]='check_all; exit $?'
operations["shortlist"]='printf "%s\n" "${!operations[@]}"'