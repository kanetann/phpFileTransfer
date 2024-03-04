# phpFileTransfer

A simple file upload and download service in a single PHP file.


## How to Use

- Deploy to your server.
- Access to https://<yourserver.com>/phpFileTransfer/index.php
- Upload file.
- Get download url, and send this to your friend.
- Access to download url, download it and automatically delete.


## Requirements

PHP 7.4+


## Setup php.ini(.user.ini)

```
; PHP: Where a configuration setting may be set - Manual https://www.php.net/manual/en/configuration.changes.modes.php
; PHP_INI_PERDIR Entry can be set in php.ini, .htaccess, httpd.conf or .user.ini
; 1GB + 4096 = (1024x1024x1024) + 4096 = 1073745920
upload_max_filesize = 1073745920
post_max_size = 1073745920
memory_limit = 1073745920
```


## Change Upload Directory, Enable Basic Authentication

phpFileTransfer/index.php

```
<?php
// Change to match your server if necessary.
error_reporting(E_ALL);
const UPLOADS_DIRECTORY = "../../uploads/"; // RECOMMEND: Please specify outside DocumentRoot.
const UPLOADS_DIRECTORY_PERMISSION = 0700;
const BASIC_AUTH_ENABLED = false;
const BASIC_AUTH_USER = "admin";
const BASIC_AUTH_PASS = "<YOUR PASSWORD>";
```


## Local Testing

```
php -S localhost:8000 -c .user.ini
open http://localhost:8000/
```

## License

MIT
