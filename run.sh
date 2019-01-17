#!/bin/bash
#script que verifica se tem script php rodando, se nao tem inicia o app.

ps -C php &>/dev/null; [ $? -ne 0 ] && php /var/www/html/demo/main.php >> logs.txt &

exit 0