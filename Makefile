include make_env

NS ?= superelectron
COMPANY_REPOSITORY=superelectron
VERSION ?= latest

IMAGE_NAME ?= nightwatch
CONTAINER_NAME ?= nightwatch
CONTAINER_INSTANCE ?= default

.PHONY: version 
.PHONY: pull-latest pull-date 
.PHONY: build-latest build-date build-company-latest build-company-date
.PHONY: run-latest run-date
.PHONY: push-latest push-date push-company-latest push-company-date 
.PHONY: check-dependencies

version:
	echo $(VERSION)

pull-latest:
	docker pull $(NS)/$(IMAGE_NAME):latest
pull-date:
	docker pull $(NS)/$(IMAGE_NAME):$(VERSION)

build-latest:
	docker build -t $(NS)/$(IMAGE_NAME):latest .
build-date:
	docker build -t $(NS)/$(IMAGE_NAME):$(VERSION) .
build-company-latest:
	docker build -t $(COMPANY_REPOSITORY)/$(IMAGE_NAME):latest .
build-company-date:
	docker build -t $(COMPANY_REPOSITORY)/$(IMAGE_NAME):$(VERSION) .

run-latest:
	docker run --name $(IMAGE_NAME) -d $(NS)/$(IMAGE_NAME):latest
run-date:
	docker run --name $(IMAGE_NAME) -d $(NS)/$(IMAGE_NAME):$(VERSION)

push-latest:
	docker push $(NS)/$(IMAGE_NAME):latest
push-date:
	docker push $(NS)/$(IMAGE_NAME):$(VERSION)
push-company-latest:
	docker push $(COMPANY_REPOSITORY)/$(IMAGE_NAME):latest
push-company-date:
	docker push $(COMPANY_REPOSITORY)/$(IMAGE_NAME):$(VERSION)

check-dependencies:
	docker exec nightwatch sh -c "google-chrome --version"

default: build