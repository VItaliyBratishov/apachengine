#!/bin/bash
clear
echo 'Удалить Apache/PHP?'
sudo service apache2 stop
sudo apt-get purge apache2 apache2-utils mysql-client mysql-server mysql-common
sudo apt-get purge php7.0-common
sudo apt-get purge phpmyadmin php5-mysql php5 php7 mysql-server apache2
sudo apt-get purge apache2.2-common apache2-mpm-prefork libapache2-mod-php5

sudo apt-get autoremove

sudo rm -rf /etc/mysql/ ~/domains/ /etc/php/ /etc/apache2/ /etc/php5/
sudo rm -rf /usr/share/phpmyadmin