.PHONY: test

test:
	docker build --rm -t cghooks .

exec:
	docker run --rm -it cghooks bash
