.PHONY: test testnocov examples

testnocov:
	php vendor/bin/phpunit

test:
	php vendor/bin/phpunit --coverage-text

examples:
	php examples/pheanstalk.php

travis: test examples
