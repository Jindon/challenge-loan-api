#!/bin/bash

echo "Project installation started..."

cp .env.example .env

echo "Generating App key"
php artisan key:generate

composer install

# database name
echo -n "Enter a database name > "
read database
sed -i "s/DB_DATABASE=laravel/DB_DATABASE=$database/" .env

# db username
echo -n "Enter a  username > "
read username
sed -i "s/DB_USERNAME=root/DB_USERNAME=$username/" .env

# db password
echo -n "Enter  password > "
read password
sed -i "s/DB_PASSWORD=/DB_PASSWORD=$password/" .env

echo "Migration Started"
php artisan migrate

php artisan serve
