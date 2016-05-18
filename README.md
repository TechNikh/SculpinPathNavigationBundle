SculpinPathNavigationBundle
===========================

Generate date navigation block (with pages) in Sculpin :
* It generates pages listing posts for each year and couple year/month
* And a  block to navigate between this pages

You can see a working demo on the right column of my [personal french blog](http://blog.bouzekri.net).

Installation
------------

Using composer, add the dependancy to your composer.json :

``` json
require: {
    "jbouzekri/sculpin-date-navigation-bundle": "1.*"
}
```

And run the composer update command

Enable the bundle. If you have already have an app/SculpinKernel.php, add this bundle to it otherwise create the file with the following content :

``` php
<?php

class SculpinKernel extends \Sculpin\Bundle\SculpinBundle\HttpKernel\AbstractKernel
{
    protected function getAdditionalSculpinBundles()
    {
        return array(
            'Tn\Bundle\PathNavigationBundle\TnPathNavigationBundle'
        );
    }
}
```

Then you need to add the date page html and the date navigation block html to your project :
* Copy the Resources/html/include/path_navigation.html file in the _includes folder of your source
* Copy the Resources/html/page/date.html file in the blog folder of your source (or any other html folder you use). For information, a path_paginated.html template is available for paginated date page.

Usage
-----

In a template, you can now call the following twig function :

``` twig
{{ path_navigation(page) }}
```

It will generate the date navigation html.

You can specify a custom template :

``` twig
{{ tag_cloud(page, 'my_template.html') }}
```

Configuration
-------------

``` yml
tn_path_navigation:
    permalink_year: /:year/index.html
    permalink_month: /:year/:month/index.html
```

* tn_path_navigation.permalink_year : the url mask for the date year page
* tn_path_navigation.permalink_month : the url mask for the date month page

License
-------

[MIT](LICENSE)

