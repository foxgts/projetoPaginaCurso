#!/bin/bash

# --- Configurações ---
DB_USER="admin_cursos"
DB_PASSWORD="@Cq124578" # <<< Sua senha do banco de dados aqui!
DB_NAME="site_cursos_db"
HTML_DIR="/var/www/html"
BACKUP_BASE_DIR="/home/$(whoami)/backups" # Diretório base para todos os backups

# --- Diretórios de destino ---
DB_BACKUP_DIR="$BACKUP_BASE_DIR/mysql"
HTML_BACKUP_DIR="$BACKUP_BASE_DIR/html"

# --- Criar diretórios de backup se não existirem ---
mkdir -p "$DB_BACKUP_DIR"
mkdir -p "$HTML_BACKUP_DIR"

# --- Gerar timestamp para o nome do arquivo ---
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# --- Caminhos completos dos arquivos de backup ---
DB_BACKUP_FILE="$DB_BACKUP_DIR/${DB_NAME}_backup_${TIMESTAMP}.sql.gz"
HTML_BACKUP_FILE="$HTML_BACKUP_DIR/html_backup_${TIMESTAMP}.tar.gz"

# --- Iniciar Backup do Banco de Dados ---
echo "Iniciando backup do banco de dados '$DB_NAME'..."
# Usando a senha diretamente no comando (sem espaço entre -p e a senha)
mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" | gzip > "$DB_BACKUP_FILE"

# Verificar se o backup do BD foi bem-sucedido
if [ $? -eq 0 ]; then
    echo "Backup do banco de dados concluído com sucesso: $DB_BACKUP_FILE"
else
    echo "ERRO: Falha no backup do banco de dados '$DB_NAME'."
fi

# --- Iniciar Backup da Pasta HTML ---
echo "Iniciando backup da pasta HTML '$HTML_DIR'..."
# Usar sudo para garantir permissões para ler /var/www/html
sudo tar -czf "$HTML_BACKUP_FILE" "$HTML_DIR"

# Verificar se o backup da pasta HTML foi bem-sucedido
if [ $? -eq 0 ]; then
    echo "Backup da pasta HTML concluído com sucesso: $HTML_BACKUP_FILE"
else
    echo "ERRO: Falha no backup da pasta HTML '$HTML_DIR'."
fi

echo "Processo de backup concluído."
echo "Lembre-se de transferir seus backups para um local seguro fora do servidor!"
