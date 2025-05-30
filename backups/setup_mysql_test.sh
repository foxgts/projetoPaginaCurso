#!/bin/bash

# --- Configurações ---
MYSQL_ROOT_PASSWORD="" # Senha root inicial (se houver, deixe em branco para instalações novas sem senha)
DB_USER="admin_cursos"
DB_PASSWORD="@Cq124578"
DB_NAME="site_cursos_db"

echo "--- Configuração Rápida do MySQL para Ambiente de Testes ---"
echo "AVISO: Este script é para AMBIENTES DE TESTE SOMENTE e desativa medidas de segurança do MySQL."
echo "NÃO USE EM PRODUÇÃO!"
echo "-------------------------------------------------------------"

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


# 3. Remover a instalação de segurança padrão (mysql_secure_installation)
# Esta parte é crucial para evitar prompts interativos de segurança
echo "Removendo configurações de segurança padrão do MySQL (para teste)..."
sudo mysql -u root <<EOF
  ALTER USER 'root'@'localhost' IDENTIFIED BY '';
  FLUSH PRIVILEGES;
EOF

if [ $? -ne 0 ]; then
    echo "AVISO: Não foi possível redefinir a senha do root MySQL. Pode ser que já esteja vazia ou haja outro problema de permissão."
    echo "Tentando prosseguir..."
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

echo "--- Configuração do MySQL Concluída para Testes ---"
echo "Você pode agora usar o usuário '$DB_USER' e a senha '$DB_PASSWORD' para seus testes."
echo "Lembre-se de deletar esta VM ou reconfigurar o MySQL para um ambiente seguro se for usar em produção."
