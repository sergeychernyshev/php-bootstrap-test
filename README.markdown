This is a test harness for a [PHP Bootstrap](https://github.com/sergeychernyshev/php-bootstrap/) project.

Don't forget to init the php-bootstrap submodule:

```
$ git submodule init
$ git submodule update
```

Testing steps:

1. Use `httpd.sample.conf` file to set up Apache hosts, or `nginx.sample.conf` for nginx.
2. Edit `docroot` and `outside\_of\_docroot` variables in `Makefile.conf`
3. Run `make` to set up all file copies of the project (you can always run `make clean` to remove them).
4. Copy `config.sample.php` to `config.php` and edit it to match values in `Makefile.conf`
5. Either open your harness URL in the browser or run `make test` (or just `./test.sh`) to run test from command line

