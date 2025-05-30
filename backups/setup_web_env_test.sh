#!/bin/bash

# --- Configurações MySQL ---
MYSQL_ROOT_PASSWORD="" # Senha root inicial (se houver, deixe em branco para instalações novas sem senha)
DB_USER="admin_cursos"
DB_PASSWORD="@Cq124578" # Sua senha do banco de dados para testes
DB_NAME="site_cursos_db"

# --- Configurações Apache ---
APACHE_CONF_FILE="/etc/apache2/mods-enabled/dir.conf" # Arquivo de configuração para DirectoryIndex

echo "--- Configuração Rápida do Ambiente Web (MySQL + Apache) para Testes ---"
echo "AVISO: Este script é para AMBIENTES DE TESTE SOMENTE e desativa medidas de segurança do MySQL."
echo "NÃO USE EM PRODUÇÃO!"
echo "-----------------------------------------------------------------------"

# 1. Instalar MySQL Server se não estiver instalado
echo "Verificando instalação do MySQL Server..."
if ! dpkg -s mysql-server >/dev/null 2>&1; then
    echo "MySQL Server não encontrado. Instalando..."
    sudo apt update
    sudo apt install -y mysql-server
    if [ $? -ne 0 ]; then
        echo "ERRO: Falha ao instalar o MySQL Server. Saindo."
        exit 1
    fi
    echo "MySQL Server instalado com sucesso."
else
    echo "MySQL Server já está instalado."
fi

# 2. Iniciar o serviço MySQL (se não estiver rodando)
echo "Garantindo que o serviço MySQL esteja rodando..."
sudo systemctl start mysql
sudo systemctl enable mysql
if [ $? -ne 0 ]; then
    echo "AVISO: Não foi possível iniciar/habilitar o serviço MySQL. Tente reiniciar a VM."
fi

# 3. Remover a instalação de segurança padrão do MySQL (para teste)
echo "Removendo configurações de segurança padrão do MySQL (para teste)..."
sudo mysql -u root <<EOF
  ALTER USER 'root'@'localhost' IDENTIFIED BY '';
  FLUSH PRIVILEGES;
EOF

if [ $? -ne 0 ]; then
    echo "AVISO: Não foi possível redefinir a senha do root MySQL. Pode ser que já esteja vazia ou haja outro problema de permissão."
    echo "Tentando prosseguir com a configuração do usuário..."
fi

# 4. Criar banco de dados e usuário com permissões
echo "Criando banco de dados '$DB_NAME' e usuário '$DB_USER'..."

sudo mysql -u root <<EOF
  CREATE DATABASE IF NOT EXISTS $DB_NAME;
  CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
  GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
  FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo "Banco de dados '$DB_NAME' e usuário '$DB_USER' configurados com sucesso!"
    echo "Senha para '$DB_USER': $DB_PASSWORD"
else
    echo "ERRO: Falha ao configurar o banco de dados e usuário MySQL."
    exit 1
fi

# --- Nova Seção: Configuração Apache DirectoryIndex ---
echo ""
echo "--- Configurando a ordem de prioridade de arquivos no Apache ---"
echo "Priorizando index.php sobre index.html para o Apache..."

# Verifica se o arquivo de configuração existe
if [ ! -f "$APACHE_CONF_FILE" ]; then
    echo "ERRO: Arquivo de configuração do Apache '$APACHE_CONF_FILE' não encontrado." >&2
    echo "Não foi possível ajustar a ordem do DirectoryIndex."
    # Não sair, pois a parte do MySQL pode ter sido bem-sucedida
else
    # Faz um backup do arquivo original antes de modificar
    sudo cp "$APACHE_CONF_FILE" "${APACHE_CONF_FILE}.bak"

    # Usa sed para substituir a linha DirectoryIndex
    # A regex busca a linha que começa com 'DirectoryIndex' e contém 'index.html' e 'index.php'
    # e move 'index.php' para antes de 'index.html'
    sudo sed -i 's/^\s*DirectoryIndex\s*\(.*\)\(index.html\)\s*\(.*\)\(index.php\)\(.*\)$/DirectoryIndex \1\4\3\2\5/' "$APACHE_CONF_FILE"
    # Se a linha original não contiver index.php antes de index.html, a sed acima pode não funcionar.
    # Uma alternativa mais simples (mas que sobrescreve a ordem) seria:
    # sudo sed -i 's/^\s*DirectoryIndex.*/DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm/' "$APACHE_CONF_FILE"
    # A linha abaixo é mais robusta para colocar index.php na frente, se ele já existir em qualquer lugar da lista
    sudo sed -i 's/\(^DirectoryIndex\s\+\)\(.*index.php\)\(.*\)\(index.html.*\)/\1\2\3\4/' "$APACHE_CONF_FILE"
    sudo sed -i 's/\(^DirectoryIndex\s\+\)\(index.html\)\(.*\)/\1index.php \2\3/' "$APACHE_CONF_FILE" # Garante que index.php vem antes de index.html

    # Recarrega a configuração do Apache para que as mudanças entrem em vigor
    echo "Recarregando serviço Apache para aplicar as mudanças..."
    sudo systemctl reload apache2
    if [ $? -eq 0 ]; then
        echo "Ordem de prioridade do Apache ajustada e serviço recarregado com sucesso!"
    else
        echo "ERRO: Falha ao recarregar o serviço Apache. Verifique as configurações manualmente." >&2
    fi
fi

echo ""
echo "--- Configuração do Ambiente Web Concluída para Testes ---"
echo "Você pode agora usar o usuário '$DB_USER' e a senha '$DB_PASSWORD' para seus testes MySQL."
echo "O Apache agora priorizará 'index.php' em '/var/www/html'."
echo "Lembre-se de deletar esta VM ou reconfigurar o MySQL/Apache para um ambiente seguro se for usar em produção."
