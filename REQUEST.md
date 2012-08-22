# Request

Request object represents incoming requests.
This is simplified Request.

Request properties:

* ``xhr`` - if true, request was made by AJAX (had XMLHTTPRequest header - all modern libraries pass that header),
* ``method`` - request method, if request is made via console - request method is set to ``CLI``,
* ``schema`` - request protocol,
* ``domain`` - requested host/domain,
* ``dir`` - directory where request is handled,
* ``referer`` - request referer,
* ``baseName`` - server basename,
* ``clientIP`` - clients IP address (or their proxy),
* ``identifier`` - controllers identifier received from router,
* ``url`` - requested URL without basename
* ``self`` - full requested URL regenerated by router (can be used as cannonical)
* ``invalidRedirect`` - by default is ``false``, when request was redirected from another directory, contains path differences,
* ``cacheable`` - route definitions contain parameter defining if route can be cached by Gateway Caches/Reverse proxy
* ``lang`` - two letter language identifier from controller identifier (set by Router when friendly link),
* ``controller`` - controller name from controller identifier (set by Router when friendly link),
* ``action`` - controllers action name from controller identifier (set by Router when friendly link),
* ``headers`` - array containing request headers in key-value pairs
* ``query`` - array containing request query parameters (those received from request and from route definition)
* ``post`` - array containing post parameters

When request is made from console - call should look like ``index.php /foo/bar.html -var=value``
Where first arguments is friendly link, all other arguments (prefixed with minus ``-``) will be threated as query parameters and will be available in ``Request`` object in ``Request::query`` property.