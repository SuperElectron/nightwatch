include make_env

NS ?= superelectron
COMPANY_REPOSITORY=superelectron
VERSION ?= latest

IMAGE_NAME ?= nightwatch
CONTAINER_NAME ?= nightwatch
CONTAINER_INSTANCE ?= default

.PHONY: testgo pull build build-company run push push-company check-dependencies

testgo:
	echo $(VERSION)

pull:
	docker pull $(NS)/$(IMAGE_NAME):latest

build:
	docker build -t $(NS)/$(IMAGE_NAME):latest .
build-company:
	docker build -t $(COMPANY_REPOSITORY)/$(IMAGE_NAME):latest .

run:
	docker run --name $(IMAGE_NAME) -d $(NS)/$(IMAGE_NAME):latest

push:
	docker push $(NS)/$(IMAGE_NAME):latest
push-company:
	docker push $(COMPANY_REPOSITORY)/$(IMAGE_NAME):latest

check-dependencies:
	docker exec nightwatch sh -c "google-chrome --version"

default: build