# Installation guide

1. **clone** this repo to target directory
2. **run** `composer install`
3. edit .env and **set database** driver (any suitable for Doctrine ORM, e.g. MySQL etc)
4. **run** `php bin/console database:create somedbname` to create databaase, if need
5. **run** `php bin/console do:mi:mi` to create DB structure and default user account
6. make sure **web-server** will execute /public/index.php
### Installation complete.

Usage:
1. go to `/` and login with some account, by default are insecure test@test.com\555
2. in `/admin` navigate to Parser and put any generated with GCal 11 text file, also select existed or write new _City name_
3. go to `/api` and feel pleasure with Swagger to explore and test how API work
