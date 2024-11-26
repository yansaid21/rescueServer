# Rescate UAM Server

**Rescate UAM Server** es una aplicación diseñada para gestionar y coordinar operaciones de rescate en la Universidad Autónoma de Manizales. Proporciona una interfaz para la administración de recursos y la comunicación entre los equipos de rescate.

## Requisitos Previos

Antes de comenzar con la instalación, asegúrate de tener instalados los siguientes componentes:

-   **PHP 8.1 o superior** (con las extensiones necesarias)
-   **Composer**
-   **MySQL o Postgresql** (u otro sistema de bases de datos compatible)

### Instalación de requisitos previos en Windows

#### 1. Instalar PHP

Si no tienes PHP instalado, descárgalo e instálalo desde su [sitio oficial](https://www.php.net/downloads.php).

1.  **Seleccionar versión y descargar:**

    Escoge la versión de PHP que deseas instalar (Current Stable or Old Stable) y selecciona "Windows downloads", esto te llevará a la página de descargas de PHP. Finalmente, selecciona y descarga el archivo ZIP según los bits de tu sistema operativo (32 o 64 bits).

2.  **Extraer y configurar PHP:**

    Extrae el contenido en `C:\Archivos de programa` o una carpeta de tu elección. Luego agrega la ruta de PHP a las variables de entorno:

    -   Abre el menú de inicio y busca "Editar las variables de entorno del sistema".
    -   Haz clic en "Variables de entorno".
    -   En la sección "Variables del sistema", selecciona la variable "Path" y haz clic en "Editar".
    -   Haz clic en "Nuevo" y agrega la ruta de PHP (por ejemplo, `C:\Archivos de programa\php8.1`).
    -   Haz clic en "Aceptar" para guardar los cambios.

3.  **Activar las extensiones necesarias:**

    Abre el archivo `php.ini` que se encuentra en la carpeta de PHP (por ejemplo, `C:\Archivos de programa\php8.1`) y elimina el punto y coma (`;`) de las siguientes líneas:

    ```ini
    extension=curl
    extension=fileinfo
    extension=gd
    extension=mbstring
    extension=mysqli
    extension=openssl
    extension=pdo_mysql
    extension=pdo_pgsql
    extension=pgsql
    extension=sqlite3
    extension=zip
    ```

4.  **Verificar la instalación:**
    Ejecuta el siguiente comando en la terminal para confirmar la instalación de PHP:

    ```bash
    php --version
    ```

#### 2. Instalar Composer

Si no tienes Composer instalado, descárgalo e instálalo desde su [sitio oficial](https://getcomposer.org/).

-   Descarga el instalador y sigue las instrucciones.
-   Verifica la instalación con el comando:

```bash
composer --version
```

---

### Instalación de requisitos previos en Linux

1.  **Actualizar paquetes del sistema operativo**

    ```bash
    sudo apt update
    sudo apt upgrade
    sudo apt autoremove
    ```

2.  **Instalar PHP y paquetes necesarios**

    ```bash
    sudo apt install unzip php-cli php-curl php-gd php-json php-mbstring php-mysql php-pgsql php-sqlite3 php-xml php-zip
    ```

3.  **Instalar Composer**

    ```bash
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    ```

    Mover el ejecutable de Composer a una ubicación general (opcional).

    ```bash
    sudo mv composer.phar /usr/bin/composer
    ```

## Instalación del servidor

1.  **Clonar el repositorio**

    ```bash
    git clone https://github.com/ttrejosg/rescate_UAM_server.git
    cd rescate_UAM_server
    ```

2.  **Instalar dependencias**

    Asegúrate de tener [Node.js](https://nodejs.org/) instalado. Luego, ejecuta:

    ```bash
    composer install
    ```

3.  **Configurar variables de entorno**

    Realiza una copia del archivo `.env.example` y renómbralo a `.env`. Luego, configura las variables de entorno según tus necesidades.

    ```bash
    cp .env.example .env
    ```

    Para MySQL:

    ```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_name
    DB_USERNAME=db_user
    DB_PASSWORD=db_password
    ```

    Para PostgreSQL:

    ```bash
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=db_name
    DB_USERNAME=db_user
    DB_PASSWORD=db_password
    ```

    En el DB_PORT normalmente es 5432 o 5433 para Postgresql, según la configuración al momento de la instalación.

4.  **Generar la clave de la aplicación**

    ```bash
    php artisan key:generate
    ```

5.  **Migrar la base de datos**

    Para migrar la base de datos y poblarla con datos de prueba:

    ```bash
    php artisan migrate --seed
    ```

    Para eliminar y recrear todas las tablas:

    ```bash
    php artisan migrate:fresh --seed
    ```

6.  **Link simbólico para almacenamiento de archivos**

    Para que los archivos subidos por los usuarios estén disponibles en la aplicación, es necesario crear un link simbólico en la carpeta `public` que apunte a la carpeta `storage/app/public`. Esto se puede hacer ejecutando el siguiente comando:

    ```bash
    php artisan storage:link
    ```

7.  **Iniciar el servidor**

    **Iniciar el Servidor en Localhost (por defecto)**

    El servidor de Laravel, por defecto, se ejecuta en el puerto 8000 de localhost. Puedes iniciarlo ejecutando el siguiente comando:

    ```bash
    php artisan serve
    ```

    Esto hará que tu aplicación esté disponible en http://localhost:8000.

    **Iniciar el Servidor en un Puerto Específico**

    ```bash
    php artisan serve --port=8080
    ```

    6.3 **Iniciar el Servidor en una Dirección IP Pública**  
    Si deseas que tu aplicación esté disponible en una dirección IP pública, puedes usar la opción `--host`. Por ejemplo, para usar la dirección IP

    ```bash
    php artisan serve --host=0.0.0.0
    ```

    Tu aplicación estará disponible en http://tu_direccion_ip:8000.

## Documentación de la API

La documentación de la API está disponible en el archivo `api_docs.json`. Está en formato **OpenAPI 3.0** y puede ser importada en herramientas como [Swagger](https://editor.swagger.io/) a fin de visualizar y probar los endpoints de la API.
