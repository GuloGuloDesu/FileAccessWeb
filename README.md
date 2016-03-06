This is a website for organizing files on my network.
I will use this to classify file types, ratings, tags, and display them.

This will be used for things such as movies, recipes, photo's and such.

This is built on FreeBSD 10.2

Install MariaDB
	pkg install mariadb55-server
	cp /usr/local/share/mysql/my-medium.cnf /var/db/mysql/my.cnf
	sysrc mysql_enable=YES
	service mysql-server start
	mysql_secure_installation
