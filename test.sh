#!/bin/bash

(test -t 1 && env php tester.php -c; test -t 1 || env php tester.php)
