# NIGHTWATCH FOR GITLAB-CI
- A basic image that can be used in gitlab-ci to test on your code on localhost while in the runner.
- This document covers configuration of this image for your project.
---

## Pulling the Image and Customizing it for Your Project
- pull the image and build it.
- this example is for a project named commerce!

```bash
$ docker pull superelectron/nightwatch:latest
$ docker build -t superelectron/nightwatch:latest .
```

```bash
/commerce
    /.docker
        /docker-ci-commerce
            ci.settings.php
            Dockerfile
            nginx.conf
            README.md
    /nightwatch
        /node_modules
        /reports
        /tests
        nightwatch.conf.js
        package.json
        package-lock.json
        README.md
    /wwwroot
        /some-project-files
    docker-compose.yml
    gitlab-ci.yml
    Makefile
```

---

### Modifying nginx.conf for your project
- the Server setup must be customized for your project
- note that the setup is gitlab-ci specific, thus ```builds``` is used with ```/path/to/web```
- here the ```path/to/web``` is ```wwwroot``` and the project is stored under ```builds/commerce``` in the pipeline.


```bash
server {
  listen 8000 default_server;
  listen [::]:8000 default_server;
  root /builds/commerce/wwwroot;
  index index.php index.html index.htm index.nginx-debian.html;
  server_name _;
```

- go to ```/.docker/docker-ci-projectName``` and modify ```nginx.conf``` for your project.

---

### Verifying Dependencies for Your Nightwatch Tests
- before modifying your package.json in your nightwatch tests, check the version of google-chrome in your container

```bash
$  docker images
```                                      
| REPOSITORY | TAG | IMAGE ID | CREATED | SIZE |
|------------|:---:|:--------:|:-------:|:----:|
|git.company-name.com:xxxx/commerce/nightwatch| latest | 17ce9d54348b | 33 minutes ago | 1.33GB |

- run the image so it is a container, and check the container ID so we can ssh into it.
```bash
$ docker run -d 17ce9d54348b
$ docker ps
```

| CONTAINER ID | IMAGE | COMMAND | CREATED | STATUS | PORTS | NAMES |
|------------|:-----:|:-------:|:-------:|:------:|:-----:|:-----:|
| 204367467e05 | 17ce9d54348b |  "/start.sh" | 3 seconds ago | Up 3 seconds  | 80/tcp | vigilant_leavitt |


- now we can use the container ID to ssh into it!
```bash
$ docker exec -it 204367467e05 /bin/bash

root@204367467e05:/# google-chrome --version
Google Chrome 78.0.3904.108
```


- make sure google-chrome matches the **integer version number** of chromedriver.
- modify ```project-name/nightwatch/package.json``` if needed: 

```bash
"devDependencies": {
"chromedriver": "^78.0.1",
"fs": "0.0.1-security",
"env2": "^2.2.2",
"geckodriver": "^1.19.1",
"glob": "^7.1.6",
"nightwatch": "^1.3.1",
"path": "^0.12.7",
"selenium-server": "^3.141.59",
"request": "^2.88.0"
}
```
        
---

### Modifying your ci.settings.php
- you can copy/paste contents of your settings.php into ci.settings.php
- add the following to the end of the ci.settings.php (this is a drupal 7 configuration)

```bash
$settings['trusted_host_patterns'] = [
  '^commerce.local.webpage.com$',
  '^local\.webpage\.com$',
  '^127\.0\.0\.1$',
  '^nginx$',
  '^localhost$'
];

$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'root',
  'password' => 'mysql_strong_password',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$config['system.logging']['error_level'] = 'verbose';
```

### Rebuild Container and Push Changes to your Project Registry
- Go to your container registry in gitlab: project >> packages >> container registry and view a similar Docker image path.

Example:
- git.company-name.com:xxxx/commerce/mysql
- replace 'mysql' with 'nightwatch' at the end like so, and build it:

```bash
$ docker build -t git.company-name.com:xxxx/commerce/nightwatch .
```

- did you notice the '.' at the end?  Include it OR IT WON'T WORK!

<br />

<br />

- Push the image to your project container registry
```bash
$ docker push git.company-name.com:xxxx/commerce/nightwatch
```

## Modifying My gitlab-ci.yml for a pipeline

Noteworthy Points
1. the image is assume to be in your gitlab registry!
2. declare a basic setup to include "test" in your pipeline.
3. service: mysql:5.7 requires VARIABLES in gitlab runner & in settings.php (note the cp command on ci.settings.php below)
4. drush cc all is drupal 7.  If this is not working then you have a [something] failure. Refer to [something] below.
5. only should target the branch you are pushing, or on which branches you wish to run this test
6. ```tail -f /dev/null``` is used to for debugging in the pipeline. TAKE THIS OUT OR IT WON'T PASS THIS POINT!


```bash
nightwatch-test:
  image: git.company-name.com:xxxx/commerce/docker-ci
  cache: {}
  tags:
    - runner3
  stage: test
  services:
    - mysql:5.7
  allow_failure: true
  artifacts:
    when: on_failure
    paths:
      - e2e/reports
  only:
    - /^MPF*$/
    - /^nightwatch-ci$/
  variables:
    DRUPAL_BASE_URL: http://localhost
    NIGHTWATCH_ADMIN_USER: "admin"
    NIGHTWATCH_ADMIN_PASS: "password"
    DB_HOST: 'mysql'
    DB_NAME: 'drupal'
    DB_USER: 'root'
    DB_PASSWORD: 'mysql_strong_password'
    WEB_ROOT: path/to/web-root
    DOWNLOAD_DB_SCRIPT: path/to/script.sh
  before_script:
    - service nginx start
    - service php7.3-fpm start
    - ls -al
    - cp .docker/docker-ci-commerce/ci.settings.php $WEB_ROOT/sites/default/settings.php
    - cd $WEB_ROOT
    - drush cc all
    - ./$DOWNLOAD_DB_SCRIPT # setup database for website
    - tail -f /dev/null
    - cd e2e && npm install
  script:
    - npm run all
```
---

# TROUBLESHOOTING

---

## Troubleshooting the gitlab-ci pipeline
- keep ```tail -f /dev/null``` and when it hits that click ```debug```


- check to see if nightwatch is setup: this will not pass if it isn't setup OR you may have a runner which cannot reach the external world!
- if this fails, change your gitlab-ci setup to have something either than ```tags: runner3```.  
- You can test the runner by trying ```curl www.google.ca``` after you open the debug terminal.

```bash
$ ./node_modules/.bin/nightwatch node_modules/nightwatch/examples/tests/ecosia.js
```

---

# CONTRIBUTING TO THE README.md
- here are some things that can help. Feel free to add points here if you think improvements can be made and push your suggestions to [github](https://github.com/SuperElectron/nightwatch).


1. configuration using docker-compose.yml in your project
2. running nightwatch tests locally for your project in the docker container
3. basic project configuration of nightwatch in THIS docker container so that you only need to add your custom tests
4. basic Q&A put into the TROUBLESHOOTING 
5. Setup instructions for gitlab & dockerhub so that changes make to dockerhub and automatically uploaded to your project container registry.

---
