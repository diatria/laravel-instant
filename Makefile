.PHONY: test analyse validate check docker-check

test:
	composer test

analyse:
	composer analyse

validate:
	composer validate --strict

check: validate test analyse

docker-check:
	docker compose run --rm package-test
