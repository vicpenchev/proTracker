#!/usr/bin/env bash

mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS finhub;
    GRANT ALL PRIVILEGES ON \`finhub%\`.* TO '$MYSQL_USER'@'%';
    CREATE DATABASE IF NOT EXISTS testing;
    GRANT ALL PRIVILEGES ON \`testing%\`.* TO '$MYSQL_USER'@'%';
EOSQL
