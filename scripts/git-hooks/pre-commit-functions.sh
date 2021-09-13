#!/usr/bin/env bash

check_code_style() {

    # Formattazione del codice con PHP CS Fixer
    ./dc php-cs-fixer-fix $PHP_STAGED_FILES --show-progress=dots --dry-run
    STATUS=$?

    if [[ "$STATUS" -eq 0 ]]; then
        echo ""
        echo -e "\e[42mCode style is OK\e[m"
        return 0 # true
    fi

    while true; do
        echo -e "\e[41mInvalid code style\e[m"
        read -p $'\e[31mDo you really want to commit ignoring code style warnings? y/n/f[Fix] \e[0m: ' yn < /dev/tty
        case $yn in
            [Yy]* ) echo ""; echo "Please consider fixing code style"; return 0;;
            [Nn]* ) echo "Run './dc php-cs-fixer-fix \$(git diff --name-only --cached --diff-filter=ACMR -- '*.php') --show-progress=dots' to fix"; return 1;;
            [Ff]* ) ./dc php-cs-fixer-fix "$(git diff --name-only --cached --diff-filter=ACMR -- '*.php')" --show-progress=dots; return 1;;
            * ) echo "Please answer y, n or f.";;
        esac
    done
}

check_psalm() {

  # Analisi statica del codice con Psalm
  ./dc psalm --no-cache
  STATUS=$?

  if [[ "$STATUS" -eq 0 ]]; then
      echo -e "\e[42mPHP Static Analysis is OK\e[m"
      ./dc badge "static analysis" passed psalm
      return 0 # true
  fi

  while true; do
      read -p $'\e[31mDo you really want to commit ignoring psalm errors? y/n \e[0m: ' yn < /dev/tty
      case $yn in
          [Yy]* ) echo ""; echo "Please consider fixing psalm errors"; return 0;;
          [Nn]* ) return 1;; # No commit
          * ) echo "Please answer y or n.";;
      esac
  done

}

check_phpunit() {

  # Esecuzione dei test con phpunit
  ./dc phpunit --testdox --exclude-group learning -vvv
  STATUS=$?

  if [[ "$STATUS" -eq 0 ]]; then
      echo -e "\e[42mPHP Unit Tests Suite is OK\e[m"
      ./dc badge test passed phpunit
      return 0 # true
  fi

  echo "Pay attention! Unit Tests are broken."
  return 1
}

check_deptrac() {

  FILES="config/deptrac/*"
  for f in $FILES
  do
    ./dc deptrac "$f"
    STATUS=$?

    if [[ "$STATUS" -eq 0 ]]; then
        echo ""
        echo -e "\e[42mDeptrac $f is OK\e[m"
    else
      echo -e "\e[31m Deptrac $f if failed\e[m"
      return 1
    fi
  done

  return 0 # true
}