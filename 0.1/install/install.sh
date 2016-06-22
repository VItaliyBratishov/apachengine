#!/bin/bash
echo 'Установка PHP7 + Apache2.4 + MySQL5.4 + PHPMyAdmin + Composer'

# установка php7
sudo apt-get install python-software-properties
sudo add-apt-repository ppa:ondrej/php-7.0
sudo apt-get update
sudo apt-get purge php5-fpm
sudo apt-get install php7.0-cli php7.0-common libapache2-mod-php7.0 php7.0 php7.0-mysql php7.0-fpm php7.0-curl php7.0-gd php7.0-mysql php7.0-bz2 
sudo apt-get install php7.0-mbstring php7.0-zip php7.0-xml

# установка apache2.4
sudo add-apt-repository ppa:ondrej/apache2
sudo apt-get update
sudo apt-get install apache2

#установка mysql5.6
sudo add-apt-repository -y ppa:ondrej/mysql-5.6
sudo apt-get update
sudo apt-get install mysql-server-5.6

# установка зависимостей
sudo apt-get install libapache2-mod-php7.0 php7.0-mysql php7.0-curl php7.0-json
sudo cp dir.conf /etc/apache2/mods-enabled/dir.conf
sudo a2enmod php7.0

# установка composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# установка phpmyadmin
cd /usr/share
sudo wget https://files.phpmyadmin.net/phpMyAdmin/4.5.4.1/phpMyAdmin-4.5.4.1-all-languages.zip
sudo unzip phpMyAdmin-4.5.4.1-all-languages.zip
sudo mv phpMyAdmin-4.5.4.1-all-languages phpmyadmin
sudo chmod -R 0755 phpmyadmin
sudo  ln -s /usr/share/phpmyadmin /var/www/phpmyadmin

#настройка доступа к phpmyadmin
sudo mkdir /etc/apache2/sites-available/
sudo touch /etc/apache2/sites-available/000-default.conf
echo "Внимание, сейчас откроется редактор, скопируйте и добавьте новое содержание:"
echo "Alias /phpmyadmin \"/usr/share/phpmyadmin/\""
echo "<Directory \"/usr/share/phpmyadmin/\">"
echo "     Order allow,deny"
echo "     Allow from all"
echo "     Require all granted"
echo "</Directory>"
read -p "Дальше? [Д/н] " answer
sudo gedit /etc/apache2/sites-available/000-default.conf

sudo cp index.html /var/www/html/

sudo a2enmod rewrite
sudo a2dismod mpm_event
sudo a2enmod mpm_prefork
sudo service apache2 restart