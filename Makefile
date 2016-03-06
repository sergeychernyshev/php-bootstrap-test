.PHONY: subproject subfolder

include Makefile.config

all:	init install

# Initializes primary install
init:	clean php-bootstrap subproject

submodules:
	git submodule init
	git submodule update

php-bootstrap:
	git submodule init
	git submodule update

subproject:
	mkdir ${docroot}/subproject/php-bootstrap/
	git archive master | tar -x -C ${docroot}/subproject/php-bootstrap/

# Installs all code using methods being tested
install: package alias subfolder port symlink ssl ssl-port rmpackage

package:
	tar --exclude ".git*" -c . >/tmp/test.tar

alias:
	# building alias
	mkdir ${outside_of_docroot}/
	tar -C ${outside_of_docroot}/ -xf /tmp/test.tar

subfolder:
	mkdir ${docroot}/subfolder/
	tar -C ${docroot}/subfolder/ -xf /tmp/test.tar

rmpackage:
	rm /tmp/test.tar

port:
	ln -s . port

ssl:
	ln -s . ssl

ssl-port:
	ln -s . ssl-port

symlink:
	ln -s ${outside_of_docroot}/ symlink

clean:
	rm -rf ${docroot}/subproject/php-bootstrap
	rm -rf ${outside_of_docroot}
	rm -rf ${docroot}/subfolder
	rm -f ${docroot}/symlink
	rm -f ${docroot}/port
	rm -f ${docroot}/ssl
	rm -f ${docroot}/ssl-port

test:
ifeq "$(wildcard config.php)" ""
	@echo =
	@echo =	You must create config.php and match values from Makefile.config
	@echo =	Start by copying config.sample.php
	@echo =
	@exit 1
endif

	./test.sh
