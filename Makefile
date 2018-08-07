test:
	./vendor/bin/phpunit --bootstrap vendor/autoload.php --testdox Test/csv_converter_Test.php
install:
	composer install
	chmod 111 Test/notReadableCSV.csv
