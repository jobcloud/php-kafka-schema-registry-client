.PHONY: clean code-style coverage help test test-unit test-integration static-analysis infection-testing install-dependencies update-dependencies
.DEFAULT_GOAL := test

PHPUNIT =  ./vendor/bin/phpunit -c ./phpunit.xml
PHPDBG =  phpdbg -qrr ./vendor/bin/phpunit -c ./phpunit.xml
PHPSTAN  = ./vendor/bin/phpstan
PHPCS = ./vendor/bin/phpcs --extensions=php -v
PHPCBF = ./vendor/bin/phpcbf ./src --standard=PSR12
INFECTION = ./vendor/bin/infection

clean:
	rm -rf ./build ./vendor

fix-code-style:
	${PHPCBF}

code-style:
	${PHPCS} --report-full --report-gitblame --standard=PSR12 ./src

coverage:
	mkdir -p build/logs/phpunit
	${PHPDBG} # && ./vendor/bin/coverage-check build/logs/phpunit/coverage/coverage.xml 100

test:
	${PHPUNIT}

test-unit:
	${PHPUNIT} --testsuite=Unit

test-integration:
	${PHPUNIT} --testsuite=Integration

static-analysis:
	mkdir -p build/logs/phpstan
	${PHPSTAN} analyse --no-progress

infection-testing:
	mkdir -p build/logs/infection
	make coverage
	cp -f build/logs/phpunit/junit.xml build/logs/phpunit/coverage/phpunit.junit.xml
	${INFECTION} --coverage=build/logs/phpunit/coverage --min-msi=65 --threads=`nproc`

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
	#   code-style          Check codestyle using phpcs
	#   coverage            Generate code coverage (html, clover)
	#   help                You're looking at it!
	#   test (default)      Run all the tests with phpunit
	#   test-unit           Run all the tests with phpunit
	#   test-integration    Run all the tests with phpunit
	#   static-analysis     Run static analysis using phpstan
	#   infection-testing   Run infection/mutation testing
	#   install-dependencies Run composerupdate
	#   update-dependencies  Run composer update
