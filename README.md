## Stack/requirements
- PHP ^8.2
- Composer

## How to run a command

`cp .env.example .env`</br>
`composer install`</br>

Command signature is as follows:</br>
`php artisan calculate:commission-fees`

There is an optional argument - `path`: </br>
`php artisan calculate:commission-fees path/to/a/csv/file.csv`</br>
CSV file should be put within `storage` project directory, and path should indicate that part after storage/ dir.</br>
For example if file will be in `$HOME/project-name/storage/app/public/file.csv` then given path should be as follows:</br>
`app/public/file.csv`.

## How to run tests
When being in a project root dir (cd $HOME/project-name):</br>
`vendor/bin/phpunit tests`

## Summary

There is a lot of places that should be improved 
(e.g. DTO/VOs for data transfer, config fetching improvements, singleton to save on external API
calls because of the limits, Dockerfile)