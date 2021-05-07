![teikirize logo](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/teikirize.png "Teikirize")


# Teikirize bookmarks

Your self host solution for bookmarks saving.

> You can see a live version [here](https://bookmarks.josemoreira.pt) (not with admin access).


<!-- vscode-markdown-toc -->
* 1. [Features](#Features)
* 2. [Requirements](#Requirements)
* 3. [Screenshots](#Screenshots)
* 4. [Instalation](#Instalation)
	* 4.1. [Easy setup](#Easysetup)
		* 4.1.1. [Quick run](#Quickrun)
		* 4.1.2. [With some configuration](#Withsomeconfiguration)
	* 4.2. [Reset password](#Resetpassword)
	* 4.3. [Login](#Login)
* 5. [Libraries used](#Librariesused)
* 6. [Security notes](#Securitynotes)
* 7. [Upgrades](#Upgrades)
* 8. [New features or implementations](#Newfeaturesorimplementations)
	* 8.1. [Roadmap / PR suggestions](#RoadmapPRsuggestions)
	* 8.2. [Routes](#Routes)
	* 8.3. [Custom theme or more javascript](#Customthemeormorejavascript)
	* 8.4. [CSS customization](#CSScustomization)
* 9. [Troubleshooting](#Troubleshooting)

<!-- vscode-markdown-toc-config
	numbering=true
	autoSave=true
	/vscode-markdown-toc-config -->
<!-- /vscode-markdown-toc -->


##  1. <a name='Features'></a>Features

* Easy to install and run
* SQLite database
* Less than 1Mb
* Insert, edit, delete bookmarks
* Automatically media info fetch from url
* Bookmarks can have tags associated
* Search bookmarks with text or tags
* Sort bookmarks by name or insertion date
* Private mode (only admin can see the stored bookmarks)
* Export bookmarks to a .json file
* Import bookmarks from a .json file (as long it is in the correct format)
* Redis ready
* Easily upgradable with new options/configurations

##  2. <a name='Requirements'></a>Requirements

```
php  >= 7.2
php curl extension
php SQLite3 extension
Browser that supports es6 (any browser > ~2014)
Redis (optional)
```

##  3. <a name='Screenshots'></a>Screenshots


Some context menus (click to view)

[![Sort menu](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/sort_menu-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/sort_menu.jpg)[![view menu](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/view_menu-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/view_menu.jpg)[![Tags edition](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/tags_edition-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/tags_edition.jpg)[![Insert bookmark](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/insert_bookmark-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/insert_bookmark.jpg)

Administration (click to view)

[![administration](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/administration_zone-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/administration_zone.jpg)


Views (click to view)

[![Main view](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/mainview-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/mainview.jpg)[![List view](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/list_view-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/list_view.jpg)[![Private Mode](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/private_mode-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/private_mode.jpg)[![Logged out no bookmarks](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/logged_out_nobookmarks-th-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/logged_out_nobookmarks-th.jpg)

Search examples (click to view)

[![Tags search](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/tags_search-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/tags_search.jpg)
[![Text search](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/text_search-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/text_search.jpg)


##  4. <a name='Instalation'></a>Instalation

###  4.1. <a name='Easysetup'></a>Easy setup

####  4.1.1. <a name='Quickrun'></a>Quick run

* Just upload all the files to your host.
* If not on the domain root (e.g. example.com or subdomain.example.com) you will need to edit the .htaccess line `RewriteBase /bookmarks/` to your current path.
* Open the browser in your domain.tld and insert the admin password.

[![firstrun](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/firstrun-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/firstrun.jpg)

####  4.1.2. <a name='Withsomeconfiguration'></a>With some configuration

* Edit the /core/configs.php file

```php
$items_per_page = 25; //set mumber of items to load per request
$redis = true; //redis will only be used if present, but can force not to use.
$redis_cache_time = 60 * 60 * 24 * 7 * 2; //2 weeks default, redis timeout
$private = false; // info is only show to logged in user (admin)
$base_path = get_base_url(true) . '/'; //should not change this
```

* If not on the domain root (e.g. example.com or subdomain.example.com) you will need to edit the .htaccess line ```RewriteBase /bookmarks/``` to your current path.
* You can delete the `assets/screenshots` to save up some space
* Uploads all the files to your host (/docs folder can be ignored)
* Open the browser in your domain.tld and insert the admin password.

[![firstrun](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/firstrun-th.jpg)](https://github.com/giventofly/teikirize-bookmarks/raw/main/assets/screenshots/firstrun.jpg)


###  4.2. <a name='Resetpassword'></a>Reset password

To reset your password create a file in the root folder named `firstrun` and open your browser on the domain.tld and you will be prompted to set a password.

###  4.3. <a name='Login'></a>Login

If you log out, to login in again go to the route `domain.tld/manage` and login again.


##  5. <a name='Librariesused'></a>Libraries used

* [PHP-Auth](https://github.com/delight-im/PHP-Auth) to manage login and create uuids
* [Axios](https://github.com/axios/axios) Promise based HTTP client, ajax post


##  6. <a name='Securitynotes'></a>Security notes

* databases should not be in a "public_html" folder, although the `.htacess` denies access and no directory listing it is a good practice to change the location (update the location accordingly: `core/auth.php` and `core/db.php`)
* Since I made this to be a single user application, if you are going to change to multi-user bear in mind to double check all the user input.

##  7. <a name='Upgrades'></a>Upgrades

I made this mainly to my personal needs, so unless my needs change or someone asks for some small implementation (or I find a bug) this should be the final version. 

Pull Requests are welcome, nevertheless.

##  8. <a name='Newfeaturesorimplementations'></a>New features or implementations

If you want to make upgrades or changes to fit your needs or even to make a PR


###  8.1. <a name='RoadmapPRsuggestions'></a>Roadmap / PR suggestions

This doesn't mean I will develop them - one day might - but take this as suggestions or things I might do someday.

* Multi-search categories and text
* Themes
* User settings stored in database
* Store images+favicons locally
* Local Time-machine bookmark copy
* Shareable link for searches
* Tags with colors associated
* Multi user
* Better REDIS management


###  8.2. <a name='Routes'></a>Routes

It is pretty easy to add a new route or edit current ones. In the `index.php` file there is an array `$routes` contaning the current routes. Besides the route for 404 and method not allowed you can add new ones following the format:

```php
$routes = [
  ...
  'routes' => [
    [
      'method' => ['get', 'post'], //get, post or both
      'function' => 'fn', // function name to call
      'expression' => '/bookmark/([\w\d\-]+)' //regex to capture the path
    ],
  ];
```

###  8.3. <a name='Customthemeormorejavascript'></a>Custom theme or more javascript

You can easily add extra `.css` and `.js` files to be loaded when calling the header (`get_header`) by passing the filenames as arguments, the folders for them would be `assets/css/` and `assets/js` respectively:

```php
  $js = ['animations.js','app.js'];
  $css = ['new_theme.css'];
  $title = 'My custom title';
  get_header($title,$css,$js);
```

###  8.4. <a name='CSScustomization'></a>CSS customization

All the .css files are compiled from the .scss and are stored in the `assets/css` folder.

##  9. <a name='Troubleshooting'></a>Troubleshooting

* Keeps looping in the first run screen

Probably it doesn't have permissions to delete the `firstrun` file, change the folder permissions to 755 and should be fixed. 