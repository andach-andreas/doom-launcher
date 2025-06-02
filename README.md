# Andach Doom

This project is in an early pre-alpha stage and is not ready for general use. 

## Install

```
composer install
npm install
php artisan migrate
php artisan port:sync
```

## Run Dev Copy

```
php artisan native:serve
```

## Build

Stop the local NPM Dev server if it is running, update the version in nativephp.php, then run:

```bash
npm run build
php artisan native:build
```

## Storage

The storage root directory is `./storage` if using the webclient or `C:\Users\andre\AppData\Roaming\laravel-dev\storage` if using the application. 

Various storage paths are (relative to the storage root directory):

* Zipped WAD files are stored in `./zips` in a folder structure similar to the normal idgames structure, so `./zips/a-c/andach.zip` for example. 
* Unzipped WAD files are stored in `./wads/{wadname}` in the same folder structure. So there would be files like:
  * `./wads/andach/andach.wad`
  * `./wads/andach/andach.txt`
