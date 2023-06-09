# Test task to create API

Foobar is a Python library for dealing with word pluralization.

## Installation

Clone the repository

```bash
git clone https://github.com/ZT00OK53/test-task.git
```

## Setting up the database connection manually in .env file

Please create the **.env file** and add your database connection

## Setup database and application using docker
Navigate to the cloned directory and run the command
```bash
docker compose --env-file ./.docker/.env.dev up
```

## Install Dependencies and migrate the database

Navigate to project directory and run the command

```bash
composer install
php artisan migrate
php artisan passport:install
```

## Improt the API collection in Postman
- Environment variable file: **localhost.postman_environment.json**
- API collection file: **Test Task.postman_collection.json**
