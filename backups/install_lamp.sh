#!/bin/bash

# --- Script de Instalação LAMP (Linux, Apache, MySQL, PHP) para Ubuntu Server ---
# Por: Gemini AI

echo "Iniciando a instalação do LAMP no seu Ubuntu Server..."
echo "----------------------------------------------------"

# 1. Atualizar o Sistema
echo "1. Atualizando a lista de pacotes e pacotes existentes..."
sudo apt update -y
sudo apt upgrade -y
echo "Sistema atualizado."
echo "--------------------"

# 2. Instalar Apache2
echo "2. Instalando o servidor web Apache2..."
sudo apt install apache2 -y
echo "Apache2 instalado."
echo "Configurando firewall UFW para permitir tráfego HTTP e HTTPS..."
sudo ufw allow 'Apache Full'
sudo ufw enable # Habilita o UFW, se ainda não estiver habilitado.
sudo ufw status
echo "--------------------"

# 3. Instalar MySQL Server
echo "3. Instalando o MySQL Server..."
sudo apt install mysql-server -y
echo "MySQL Server instalado. Recomenda-se executar 'mysql_secure_installation' após o script."
echo "--------------------"

# 4. Instalar PHP e Módulos Essenciais
echo "4. Instalando PHP e módulos essenciais (libapache2-mod-php, php-mysql, php-cli, php-mbstring, php-xml, php-gd, php-curl)..."
sudo apt install php libapache2-mod-php php-mysql php-cli php-mbstring php-xml php-gd php-curl -y
echo "PHP e módulos essenciais instalados."
echo "--------------------"

# 5. Habilitar Módulos PHP no Apache (se necessário, já feito automaticamente pelo pacote)
# Este passo geralmente não é necessário pois o 'libapache2-mod-php' já faz isso.
# No entanto, se precisar habilitar módulos manualmente no futuro, use:
# sudo a2enmod php7.4 (ou a versão do seu PHP)

# 6. Reiniciar Apache para aplicar as configurações
echo "6. Reiniciando o Apache2 para aplicar as novas configurações..."
sudo systemctl restart apache2
sudo systemctl status apache2 | grep Active
echo "Apache2 reiniciado. Verifique o status acima."
echo "--------------------"

echo "Instalação LAMP concluída!"
echo "Para testar, abra seu navegador e digite o endereço IP do seu Ubuntu Server."
echo "Você deverá ver a página 'Apache2 Ubuntu Default Page'."
echo "--------------------"
echo "Próximos passos importantes:"
echo "1. Configure a segurança do MySQL executando: sudo mysql_secure_installation"
echo "2. Para colocar seu site, você usará o diretório: /var/www/html"
echo "3. Crie um arquivo info.php em /var/www/html para testar o PHP. Ex: <?php phpinfo(); ?>"
echo "----------------------------------------------------"
