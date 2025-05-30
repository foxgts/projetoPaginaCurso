#!/bin/bash

# --- Configurações de Restauração ---
DB_USER="admin_cursos"
DB_PASSWORD="@Cq124578" # <<< SUA SENHA DO BANCO DE DADOS AQUI!
DB_NAME="site_cursos_db"
HTML_TARGET_DIR="/var/www/html" # Onde os arquivos HTML devem ser restaurados

# --- Diretório onde estão os backups ---
BACKUP_BASE_DIR="/home/$(whoami)/backups"
DB_BACKUP_DIR="$BACKUP_BASE_DIR/mysql"
HTML_BACKUP_DIR="$BACKUP_BASE_DIR/html"
# --- Funções de Restauração ---

# Função para restaurar o banco de dados
restore_database() {
    clear # Limpa a tela para a restauração do BD
    echo ""
    echo "--- RESTAURANDO BANCO DE DADOS ---"

    # Encontra o backup de banco de dados mais recente
    local DB_BACKUP_FILE=$(ls -1 "$DB_BACKUP_DIR"/${DB_NAME}_backup_*.sql.gz 2>/dev/null | tail -n 1)

    if [ -z "$DB_BACKUP_FILE" ]; then
        echo "ERRO: Nenhum backup de banco de dados encontrado em '$DB_BACKUP_DIR'." >&2
        echo "Restauração do banco de dados cancelada."
        return 1
    fi

    echo "Você selecionou o backup mais recente: $DB_BACKUP_FILE"

    read -p "Tem certeza que deseja RESTAURAR o banco de dados '$DB_NAME'? Isso APAGARÁ os dados atuais! (s/N): " confirm_db
    if [[ "$confirm_db" =~ ^[Ss]$ ]]; then
        echo "Restaurando banco de dados '$DB_NAME'..."
        # Usando a senha diretamente com -p (sem espaço entre -p e a senha)
        echo "DROP DATABASE IF EXISTS $DB_NAME;" | mysql -u "$DB_USER" -p"$DB_PASSWORD"
        if [ $? -ne 0 ]; then echo "ERRO ao dropar o banco de dados. Verifique a senha do MySQL e permissões." >&2; return 1; fi
        echo "CREATE DATABASE $DB_NAME;" | mysql -u "$DB_USER" -p"$DB_PASSWORD"
        if [ $? -ne 0 ]; then echo "ERRO ao criar o banco de dados. Verifique a senha do MySQL e permissões." >&2; return 1; fi
        
        # Restaurar o backup
        gunzip < "$DB_BACKUP_FILE" | mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME"

        if [ $? -eq 0 ]; then
            echo "Backup do banco de dados '$DB_NAME' restaurado com sucesso!"
        else
            echo "ERRO: Falha ao restaurar o banco de dados '$DB_NAME'." >&2
            return 1
        fi
    else
        echo "Restauração do banco de dados cancelada pelo usuário."
        return 1
    fi
    return 0
}

# Função para restaurar a pasta HTML
restore_html() {
    clear # Limpa a tela para a restauração do HTML
    echo ""
    echo "--- RESTAURANDO PASTA HTML ---"

    # Encontra o backup de HTML mais recente
    local HTML_BACKUP_FILE=$(ls -1 "$HTML_BACKUP_DIR"/html_backup_*.tar.gz 2>/dev/null | tail -n 1)

    if [ -z "$HTML_BACKUP_FILE" ]; then
        echo "ERRO: Nenhum backup de HTML encontrado em '$HTML_BACKUP_DIR'." >&2
        echo "Restauração da pasta HTML cancelada."
        return 1
    fi

    echo "Você selecionou o backup mais recente: $HTML_BACKUP_FILE"

    # Verifica se o diretório de destino é acessível e pede sudo se necessário
    if [ ! -w "$HTML_TARGET_DIR" ] && [ ! -w "$(dirname "$HTML_TARGET_DIR")" ]; then
        echo "AVISO: O usuário atual não tem permissão de escrita em '$HTML_TARGET_DIR'." >&2
        echo "Será solicitada a sua senha de 'sudo' para prosseguir." >&2
        # Testa o sudo para garantir que a senha seja pedida agora, se necessário
        sudo -v # Pede a senha do sudo e a mantém em cache por alguns minutos
        if [ $? -ne 0 ]; then
            echo "ERRO: Permissão de sudo negada ou cancelada. Não é possível restaurar a pasta HTML." >&2
            return 1
        fi
    fi

    read -p "Tem certeza que deseja RESTAURAR a pasta '$HTML_TARGET_DIR'? Isso APAGARÁ os arquivos atuais! (s/N): " confirm_html
    if [[ "$confirm_html" =~ ^[Ss]$ ]]; then
        echo "Restaurando pasta HTML para '$HTML_TARGET_DIR'..."
        # Remover o conteúdo existente (com cuidado!)
        if [ -d "$HTML_TARGET_DIR" ]; then # Apenas tenta remover se o diretório existe
            if [ "$(ls -A $HTML_TARGET_DIR)" ]; then # Apenas se não estiver vazio
                echo "Removendo conteúdo atual de $HTML_TARGET_DIR/*..."
                sudo rm -rf "$HTML_TARGET_DIR"/* "$HTML_TARGET_DIR"/.[!.]* 2>/dev/null
                if [ $? -ne 0 ]; then echo "ERRO ao remover o conteúdo existente. Verifique permissões." >&2; return 1; fi
            fi
        fi
        
        # Recriar o diretório se ele foi removido ou não existia, com sudo
        sudo mkdir -p "$HTML_TARGET_DIR"
        if [ $? -ne 0 ]; then echo "ERRO ao criar ou garantir o diretório de destino. Verifique permissões." >&2; return 1; fi
        
        # Extrair o backup
        sudo tar -xzf "$HTML_BACKUP_FILE" -C /
        if [ $? -eq 0 ]; then
            echo "Backup da pasta HTML restaurado com sucesso para '$HTML_TARGET_DIR'!"
            # Ajustar permissões para o Apache/Nginx
            echo "Ajustando permissões da pasta HTML..."
            sudo chown -R www-data:www-data "$HTML_TARGET_DIR"
            sudo chmod -R 755 "$HTML_TARGET_DIR"
            if [ $? -ne 0 ]; then echo "AVISO: Não foi possível ajustar permissões e propriedade da pasta HTML. Faça isso manualmente." >&2; fi
        else
            echo "ERRO: Falha ao restaurar a pasta HTML '$HTML_TARGET_DIR'. Verifique o arquivo de backup e permissões." >&2
            return 1
        fi
    else
        echo "Restauração da pasta HTML cancelada pelo usuário."
        return 1
    fi
    return 0
}

# --- Menu Principal ---
clear # Limpa a tela no início
echo "--- Script de Restauração de Site de Cursos ---"
echo "Por favor, selecione uma opção:"
echo "  1) Restaurar SOMENTE Banco de Dados (último backup)"
echo "  2) Restaurar SOMENTE Pasta HTML (último backup)"
echo "  3) Restaurar AMBOS (últimos backups)"
echo "  4) Sair"
echo "------------------------------------------------"

read -p "Digite sua opção [1-4]: " main_choice

case "$main_choice" in
    1)
        restore_database
        ;;
    2)
        restore_html
        ;;
    3)
        # Executa o segundo comando apenas se o primeiro for bem-sucedido
        restore_database && restore_html
        ;;
    4)
        echo "Saindo do script de restauração. Nenhuma ação tomada."
        ;;
    *)
        echo "Opção inválida. Saindo."
        ;;
esac

echo ""
echo "Processo de restauração concluído."
echo "Lembre-se de verificar seu site após a restauração."
