#!/usr/bin/env bash
supervisord -c /etc/supervisord.conf
eval $(ssh-agent -s)
echo "$DEPLOY_KEY" | ssh-add -
mkdir -p ~/.ssh
'[[ -f /.dockerenv ]] && echo -e "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config'

echo 'if $programname == "drupal" then $APPLICATION_ROOT/logs/drupal.log' >> /etc/rsyslog.d/50-default.conf
service rsyslog start && service nginx start && service php7.3-fpm start
export PATH=$APPLICATION_ROOT/vendor/bin:$PATH
cp .docker/docker-ci-commerce/ci.settings.php $WEB_ROOT/sites/default/settings.php
mkdir --parents /var/www/private/keys && mv $EPP_JWT_CERT /var/www/private/keys/public.pem
date
make dl-db
date
gunzip "$APPLICATION_ROOT"/.docker/dbdump/commerce_live.latest.sql.gz
date
mysql --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --host="$MYSQL_CONTAINER_HOSTNAME" "$MYSQL_DATABASE" < "$APPLICATION_ROOT"/.docker/dbdump/commerce_live.latest.sql
date
cd $WEB_ROOT
drush cc all
cd $APPLICATION_ROOT
#    - tail -f /dev/null
cd $APPLICATION_ROOT
google-chrome --version
cd e2e && npm install