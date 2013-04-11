.PHONY: subproject subfolder

docroot = /Library/WebServer/Documents/bootstrap-test
outside_of_docroot = /Users/sergey/php-bootstrap-outside-docroot

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
	cd php-bootstrap; git archive master | tar -x -C ${docroot}/subproject/php-bootstrap/

# Installs all code using methods being tested
install: package alias subfolder port symlink

package:
	tar --exclude ".git*" -c . >/tmp/test.tar

alias:
	# building alias
	mkdir ${outside_of_docroot}/
	tar -C ${outside_of_docroot}/ -xf /tmp/test.tar

subfolder:
	mkdir ${docroot}/subfolder/
	tar -C ${docroot}/subfolder/ -xf /tmp/test.tar

port:
	ln -s . port

symlink:
	ln -s ${outside_of_docroot}/ symlink

clean:
	rm -rf ${docroot}/subproject/php-bootstrap
	rm -rf ${outside_of_docroot}
	rm -rf ${docroot}/subfolder
	rm -f ${docroot}/symlink
	rm -f ${docroot}/port

test:
	./test.sh
