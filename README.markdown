This is a test harness for a [PHP Bootstrap](https://github.com/sergeychernyshev/php-bootstrap/) project.

Testing steps:

1. Use `httpd.sample.conf` file to set up Apache hosts.
2. Edit `docroot` and `outside_of_docroot` variables in `Makefile`
3. Run `make` to set up all file copies of the project (you can always run `make clean` to remove them).
4. Edit `config.php` to match values in `Makefile`
5. Either open your harness URL in the browser or run `make test` (or just `./test.sh`) to run test from command line

