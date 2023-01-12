.PHONY: test testnocov examples

testnocov:
	php vendor/bin/phpunit -v

test:
	php vendor/bin/phpunit -v --coverage-text

examples:
	php examples/pheanstalk.php
	php examples/retrying.php
