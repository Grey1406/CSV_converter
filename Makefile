test:
	chmod 111 Test/notReadableCSV.csv
	./vendor/bin/phpunit --bootstrap vendor/autoload.php --testdox Test/csv_converter_Test.php
	chmod 777 Test/notReadableCSV.csv
install:
	php composer.phar install