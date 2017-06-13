Wise Error Page
===============

Fast error page handling, graciously provided by [Visol](https://www.visol.ch/).
This is a TYPO3 CMS extension to help handling error pages in a fast way. The
main idea is to deliver static 404 content directly from the web server,
skipping the CMS part when possible - for speed reasons - while still having the
possibility to integrate and edit such error page within the CMS. It sounds a
bit contradictory but it was made possible while supporting multi-domains and
multi-language. The extension works hands in hands with `EXT:nc_staticfilecache`
whose purposes is to statically cache pages.

Installation
------------

1.  Install the extension as normal in the Extension Manager. Test that the new
    page type is correctly working by visiting `domain.tld?type=1497284951` in
    the browser. This special type will be used to resolve the speaking URL to
    the 404 page.

2.  Make sure extension `EXT:nc_staticfilecache` is correctly installed and is
    able to cache the 404 page. Call the 404 page directly and check the page is
    cached into `typo3temp/tx_ncstaticfilecache`.

3.  Configure Nginx to serve 404 page for asset. See configuration below.

4.  Configure TYPO3 to serve 404 via the 404 script linked above

5.  Optional: a symlink could be created as follows for clarity sake:

```
 cd web
 ln -s typo3conf/ext/wise_error_page/Classes/Page404Handler.php 404.php
```

Configuration
-------------

### Nginx

Configure Nginx so that 404 content is directly served for all resources except
PHP files. They should be delivered by the CMS which is the only one who can 
know about missing pages.

```
location ~ \.(png|jpg|jpeg|gif|css|js|svg|webp|pdf|doc|docx)$ {
    error_page 404  /typo3conf/ext/wise_error_page/Classes/Page404Handler.php;
}
```

If you have made a symlink, you should point to the symlink.

```
location ~ \.(png|jpg|jpeg|gif|css|js|svg|webp|pdf|doc|docx)$ {
    error_page 404  /404.php;
}
```

Todo: improve this configuration by matching all files except the PHP files. A
PR is welcome here since the configuration above hasn't work for me

```
# TODO: improve me, not working...
# We want all files except PHP files to be matched
location ~ .+(?<!\.php)$ {
  error_page 404  /typo3conf/ext/wise_error_page/Classes/Page404Handler.php;
}
```

### TYPO3 CMS Configuration

Configure TYPO3 so it serves its contents from the `Page404Handler` script 
that is the same entry points as the other resources.

```
$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'] => '/typo3conf/ext/wise_error_page/Classes/Page404Handler.php',
```

If you have symlink the script to a `404.php` file, you could configure as follows:

```
$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'] => '/404.php',
```

Known issues
------------

The `Page404Handler` script should be improved in the context of multi-domains
by reading its configuration from RealURL. PR are welcome. [@see]
Page404Handler.php

Todos
-----

* The internal data source of EXT:wise_error_page must be flushed when cache is
cleared
