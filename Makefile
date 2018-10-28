.PHONY: build exec

build:
	docker build --rm -t cghooks .

exec:
	docker run --rm -it cghooks bash
