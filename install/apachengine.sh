#!/bin/bash

# создание директории domains
echo 'We create a directory "domains" on your disk with hosts'
echo 'Мы создаем на вашем диске директорию "domains" с хостами'
sudo mkdir ~/domains

# настраиваем GUIapachengine
sudo mkdir ~/domains/apachengine
sudo cp index.php ~/domains/apachengine
sudo cp info.php /var/www/html/
read -p "Введите свой пароль администратора: " passwd
echo "Для управления сервером, ваш пароль сохранен по адресу: "
echo "~/domains/apachengine/.secret"
sudo echo $passwd > ~/domains/apachengine/.secret

# настраиваем хост
sudo cp apachengine.conf /etc/apache2/sites-available/
echo "Успешно добавлен хост -- apachengine"

# настраиваем хост
sudo chmod 777 /etc/hosts
sudo echo "127.0.0.1   apachengine" >> /etc/hosts
sudo chmod 755 /etc/hosts
read -p "Добавлен хост [127.0.0.1   apachengine]. Дальше? [Д/н]" answer

echo "Внимание, сейчас откроется редактор, добавьте новой строкой:"
echo "www-data ALL=(ALL) NOPASSWD: ALL"
read -p "Дальше? [Д/н]" answer
sudo gedit /etc/sudoers

sudo a2ensite apachengine.conf
sudo service apache2 restart

name="$(whoami)"
sudo chown $name:$name ~/domains/apachengine/index.php
sudo usermod -a -G sudo www-data 

sudo chmod 775 ~/domains/

# настраиваем хост
sudo service apache2 restart