database migrations

php bin/console doctrine:migrations:generate

optimization:

weksocket server
php bin/console websocket-server

message queue
php bin/console messenger:consume async

unit test
./vendor/bin/phpunit tests