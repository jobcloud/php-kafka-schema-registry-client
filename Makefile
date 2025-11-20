.PHONY: clean code-style coverage help test test-unit test-integration static-analysis infection-testing install-dependencies update-dependencies
.DEFAULT_GOAL := test

PHPUNIT =  ./vendor/bin/phpunit -c ./phpunit.xml
PHPSTAN  = ./vendor/bin/phpstan --no-progress
PHPCS = ./vendor/bin/phpcs --extensions=php
PHPCBF = ./vendor/bin/phpcbf
INFECTION = ./vendor/bin/infection
COVCHK = ./vendor/bin/coverage-check

clean:
	rm -rf ./build ./vendor

fix-code-style:
	${PHPCBF}

code-style:
	mkdir -p build/logs/phpcs
	${PHPCS}

coverage:
	${PHPUNIT} && ${COVCHK} build/logs/phpunit/coverage/coverage.xml 100

test:
	${PHPUNIT}

test-unit:
	${PHPUNIT} --testsuite=Unit

static-analysis:
	mkdir -p build/logs/phpstan
	${PHPSTAN} analyse

infection-testing:
	mkdir -p build/logs/infection
	make coverage
	cp -f build/logs/phpunit/junit.xml build/logs/phpunit/coverage/phpunit.junit.xml
	${INFECTION} --coverage=build/logs/phpunit/coverage --min-msi=93 --threads=`nproc`

install-dependencies:
	composer install

update-dependencies:
	composer update

help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	#   clean               Cleans the coverage and the vendor directory
	#   code-style          Check code style using phpcs
	#   coverage            Generate code coverage (html, clover)
	#   help                You're looking at it!
	#   test (default)      Run all the tests with phpunit
	#   test-unit           Run all the tests with phpunit
	#   test-integration    Run all the tests with phpunit
	#   static-analysis     Run static analysis using phpstan
	#   infection-testing   Run infection/mutation testing
	#   install-dependencies Run composer install
	#   update-dependencies  Run composer update
