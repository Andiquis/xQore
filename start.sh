#!/bin/bash

# Otorgar permisos de ejecución a todos los archivos en la carpeta xqore
chmod +x $HOME/xQore/*

# Verificar si PHP y SQLite están instalados, e instalarlos si no lo están
if ! command -v php >/dev/null 2>&1; then
    echo "Instalando PHP..."
    pkg install php -y
fi

if ! command -v sqlite3 >/dev/null 2>&1; then
    echo "Instalando SQLite3..."
    pkg install sqlite -y
fi

# ==============================
# CONFIGURACIÓN bash.bashrc
# ==============================

FILE_PATH="$PREFIX/etc/bash.bashrc"

CONFIG_BASH=$(cat <<'EOF'
≡# =============================#
#      🌟 xQore SYSTEM 🌟      #
# =============================#

# Configuración del historial de comandos
shopt -s histappend
shopt -s histverify
export HISTCONTROL=ignoreboth

# Personalización del prompt con colores
PROMPT_DIRTRIM=2
PS1='\e[1;34m \e[1;32m\w\e[1;36m ➜ \e[1;37m'

# Interfaz visual al iniciar la terminal
clear
echo -e "\033[1;36m========================================\033[0m"
echo -e "\033[1;33m        🚀 BIENVENIDO A xQore 🚀        \033[0m"
echo -e "\033[1;36m========================================\033[0m"

echo -e "\033[1;34m██╗  ██╗ ██████╗  ██████╗ ██████╗ ███████╗\033[0m"
echo -e "\033[1;34m╚██╗██╔╝██╔═══██╗██╔═══██╗██╔══██╗██╔════╝\033[0m"
echo -e "\033[1;34m ╚███╔╝ ██║   ██║██║   ██║██████╔╝█████╗  \033[0m"
echo -e "\033[1;34m ██╔██╗ ██║██ ██║██║   ██║████╔═╝ ██╔══╝  \033[0m"
echo -e "\033[1;34m██╔╝ ██╗╚███ ██╝╚██████╔╝██║██║  ███████╗\033[0m"
echo -e "\033[1;34m╚═╝  ╚═╝ ╚═════╝  ╚═════╝ ╚═╝ ═╝  ╚══════╝\033[0m"

echo -e "\n\033[1;32m🌟 Proyecto: \033[0mxQore"
echo -e "\033[1;32m👤 Autor: \033[0mAndi"
echo -e "\033[1;32m🔌 Puerto usado: \033[0m8003"
echo -e "\033[1;32m📝 Descripción: \033[0mSistema de administración de listas"
echo -e "\033[1;32m🟢 Estado: \033[0mServidor corriendo"

# Verificar existencia de la base de datos y crearla si no existe
DB_PATH="$HOME/xQore/database/xqore.db"
if [ ! -f "$DB_PATH" ]; then
    echo -e "\033[1;36m📂 Creando la base de datos xqore.db...\033[0m"
    php "$HOME/xQore/config.php"
    echo -e "\033[1;32m✔ Base de datos creada.\033[0m"
else
    echo -e "\033[1;34m✔ Base de datos encontrada.\033[0m"
fi

# Iniciar el servidor PHP automáticamente
cd $HOME/xQore || exit
php -S 0.0.0.0:8003 -t $HOME/xQore &

# Abrir la URL en Google Chrome
am start -n com.android.chrome/com.google.android.apps.chrome.Main -d http://localhost:8003/index.php &
EOF
)

# Sobrescribir bash.bashrc con la nueva configuración
echo "$CONFIG_BASH" > "$FILE_PATH"
echo "✅ bash.bashrc actualizado. Reinicia Termux para aplicar los cambios."

# ==============================
# MENÚ INTERACTIVO PARA EL USUARIO
# ==============================

while true; do
    clear
    echo -e "\033[1;36m====================================\033[0m"
    echo -e "\033[1;33m     🌍 SERVIDOR PHP - TERMUX 🚀     \033[0m"
    echo -e "\033[1;36m====================================\033[0m"
    echo -e "\033[1;34m 1) \033[1;32mIniciar servidor\033[0m"
    echo -e "\033[1;34m 2) \033[1;31mDetener servidor\033[0m"
    echo -e "\033[1;34m 3) \033[1;33mAbrir en navegador\033[0m"
    echo -e "\033[1;34m 4) \033[1;31mSalir\033[0m"
    echo -e "\033[1;36m====================================\033[0m"

    read -p $'\033[1;37mSeleccione una opción: \033[0m' opcion

    case $opcion in
        1)
            echo -e "\033[1;32m🚀 Iniciando servidor en el puerto 8003...\033[0m"
            php -S 0.0.0.0:8003 -t $HOME/xQore &
            sleep 2
            ;;
        2)
            echo -e "\033[1;31m🛑 Deteniendo servidor...\033[0m"
            pkill -f "php -S 0.0.0.0:8003"
            sleep 2
            ;;
        3)
            echo -e "\033[1;33m🔄 Abriendo en el navegador...\033[0m"
            am start -n com.android.chrome/com.google.android.apps.chrome.Main -d http://localhost:8003/index.php &
            ;;
        4)
            echo -e "\033[1;31m👋 Saliendo...\033[0m"
            exit 0
            ;;
        *)
            echo -e "\033[1;31m❌ Opción inválida. Inténtalo de nuevo.\033[0m"
            sleep 2
            ;;
    esac
done