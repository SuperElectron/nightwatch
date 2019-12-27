include make_env

NS ?= superelectron
COMPANY_REPOSITORY=superelectron
VERSION ?= latest

IMAGE_NAME ?= nightwatch
CONTAINER_NAME ?= nightwatch
CONTAINER_INSTANCE ?= default

.PHONY: show-version pull pull-company build build-company run run-company push push-company check-dependencies

show-version:
	echo $(VERSION)

pull:
	docker pull $(NS)/$(IMAGE_NAME):$(VERSION)
pull-company:
	docker pull $(COMPANY_REPOSITORY)/$(IMAGE_NAME):$(VERSION)

build:
	docker build -t $(NS)/$(IMAGE_NAME):$(VERSION) .
build-company:
	docker build -t $(COMPANY_REPOSITORY)/$(IMAGE_NAME):$(VERSION) .

run:
	docker run --name $(IMAGE_NAME) -d $(NS)/$(IMAGE_NAME):$(VERSION)
run-company:
	docker run --name $(IMAGE_NAME) -d $(COMPANY_REPOSITORY)/$(IMAGE_NAME):$(VERSION)

push:
	docker push $(COMPANY_REPOSITORY)/$(IMAGE_NAME):$(VERSION)
push-company:
	docker push $(IMAGE_NAME) -d $(NS)/$(IMAGE_NAME):$(VERSION)

check-dependencies:
	docker exec $(IMAGE_NAME) sh -c "google-chrome --version"

default: build