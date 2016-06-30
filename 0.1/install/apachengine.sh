#!/bin/bash

sudo service apache2 stop
sudo a2dissite apachengine.conf

# создание директории domains
echo 'Cоздаем директорию "domains" с хостами'
sudo mkdir /var/www/apachengine/

# настраиваем GUIapachengine
sudo cp index.php /var/www/apachengine/
sudo cp info.php /var/www/apachengine/

# настраиваем хост
sudo cp apachengine.conf /etc/apache2/sites-available/
echo "Успешно добавлен хост -- apachengine"

# настраиваем хост
sudo chmod 777 /etc/hosts
sudo echo -e "\\n127.0.0.1   apachengine\\n" >> /etc/hosts
sudo chmod 755 /etc/hosts
read -p "Добавлен хост [127.0.0.1   apachengine]. Дальше? [Д/н] " answer

echo "Разрешение запуска PHP из под sudo"
echo "Внимание, сейчас откроется редактор, добавьте новой строкой:"
echo "www-data ALL=(ALL) NOPASSWD: ALL"
read -p "Дальше? [Д/н] " answer
sudo gedit /etc/sudoers
sudo chmod 755 /etc/sudoers

sudo a2ensite apachengine.conf

sudo chmod -R 777 * /var/www/
sudo chown -R $USER:$USER /var/www/apachengine/

# баг c правами www-data
read -p "Имя пользователя: " user
sudo useradd -G www-data $user

echo "Внимание, сейчас откроется редактор, добавьте новой строкой:"
echo "umask 002"
read -p "Дальше? [Д/н] " answer
sudo gedit /etc/init.d/apache2

echo "Внимание, сейчас откроется редактор, добавьте новой строкой:"
echo "umask 002"
read -p "Дальше? [Д/н] " answer
sudo gedit /etc/apache2/envvars

sudo systemctl daemon-reload

# настраиваем хост
sudo service apache2 start