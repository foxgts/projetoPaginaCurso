#!/bin/bash

# --- Configurações Apache ---
APACHE_DIR_CONF_FILE="/etc/apache2/mods-enabled/dir.conf" # Arquivo global de DirectoryIndex
APACHE_DEFAULT_VHOST_FILE="/etc/apache2/sites-enabled/000-default.conf" # Arquivo do Virtual Host padrão

echo "--- Configurando a Ordem de Prioridade de Arquivos no Apache ---"
echo "Este script garante que 'index.php' seja prioritário sobre 'index.html'."
echo "---------------------------------------------------------------"

# Define a nova string DirectoryIndex com espaços corretos
NEW_DIRECTORY_INDEX="DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm"

# --- Configurar o arquivo dir.conf (global) ---
echo "Processando arquivo global: $APACHE_DIR_CONF_FILE"
# Garante que o arquivo tem a estrutura básica <IfModule dir_module>
if [ ! -f "$APACHE_DIR_CONF_FILE" ] || ! grep -q "<IfModule dir_module>" "$APACHE_DIR_CONF_FILE"; then
    echo "  -> '$APACHE_DIR_CONF_FILE' está vazio ou sem a estrutura <IfModule>. Recriando..."
    sudo bash -c "cat << 'EOF_DIR_CONF' > $APACHE_DIR_CONF_FILE
<IfModule dir_module>
    $NEW_DIRECTORY_INDEX
</IfModule>
EOF_DIR_CONF"
else
    # Se a estrutura existe, mas a linha DirectoryIndex não está lá ou está incorreta, corrige
    sudo cp "$APACHE_DIR_CONF_FILE" "${APACHE_DIR_CONF_FILE}.bak" # Backup
    sudo sed -i '/^\s*DirectoryIndex/d' "$APACHE_DIR_CONF_FILE" # Remove linha antiga (se houver)
    sudo sed -i "/<IfModule dir_module>/a \ \ \ \ $NEW_DIRECTORY_INDEX" "$APACHE_DIR_CONF_FILE" # Adiciona nova linha
    echo "  -> '$APACHE_DIR_CONF_FILE' configurado."
fi

# --- Configurar o arquivo 000-default.conf (Virtual Host padrão) ---
echo "Processando arquivo de Virtual Host padrão: $APACHE_DEFAULT_VHOST_FILE"
if [ ! -f "$APACHE_DEFAULT_VHOST_FILE" ]; then
    echo "AVISO: Arquivo de Virtual Host padrão '$APACHE_DEFAULT_VHOST_FILE' não encontrado." >&2
    echo "Pode ser que você tenha um Virtual Host diferente configurado, ou a instalação do Apache não está padrão."
else
    sudo cp "$APACHE_DEFAULT_VHOST_FILE" "${APACHE_DEFAULT_VHOST_FILE}.bak" # Backup
    
    # Verifica se já existe uma linha DirectoryIndex dentro do Virtual Host
    if grep -q "DirectoryIndex" "$APACHE_DEFAULT_VHOST_FILE"; then
        # Se existe, remove a linha existente
        sudo sed -i '/^\s*DirectoryIndex/d' "$APACHE_DEFAULT_VHOST_FILE"
    fi
    
    # Adiciona a nova linha DirectoryIndex dentro do bloco <VirtualHost *:80>
    # Procura pela linha "DocumentRoot" e insere DirectoryIndex logo abaixo
    # Importante: a indentação (8 espaços) é para se alinhar com as linhas existentes no 000-default.conf
    sudo sed -i "/DocumentRoot/a \ \ \ \ \ \ \ \ $NEW_DIRECTORY_INDEX" "$APACHE_DEFAULT_VHOST_FILE"
    
    echo "  -> '$APACHE_DEFAULT_VHOST_FILE' configurado."
fi

# Recarrega a configuração do Apache para que as mudanças entrem em vigor
echo "Recarregando serviço Apache para aplicar as mudanças..."
sudo systemctl reload apache2
if [ $? -eq 0 ]; then
    echo "Ordem de prioridade do Apache ajustada e serviço recarregado com sucesso!"
    echo ""
    echo "Conteúdo de '$APACHE_DIR_CONF_FILE' após modificação (verifique espaços):"
    cat "$APACHE_DIR_CONF_FILE"
    echo ""
    echo "Conteúdo de '$APACHE_DEFAULT_VHOST_FILE' após modificação (verifique se DirectoryIndex está lá):"
    cat "$APACHE_DEFAULT_VHOST_FILE"
else
    echo "ERRO: Falha ao recarregar o serviço Apache. Verifique as configurações manualmente." >&2
fi

echo ""
echo "--- Configuração do Apache Concluída ---"
echo "Por favor, teste seu site agora para verificar a prioridade do 'index.php'."
