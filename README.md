# HSIControl

Sistema central de gestión administrativa, control de accesos y estructura organizacional. Desarrollado por la División de Salud Digital del HIGA "Gral. San Martín" de La Plata.

## 🚀 Características Principales

* **Padrón de Personal:** Gestión completa de agentes, vinculación de usuarios y control de legajos de HSI.
* **Estructura Profesional:** Administración centralizada de especialidades médicas, profesiones, ocupaciones y tipos de documentos.
* **Tablero Estructural Funcional:** Visualización y gestión de Unidades Jerárquicas (Direcciones, Departamentos, Servicios, Consultorios, etc.) mediante un mapa panorámico interactivo con cálculo automático de dependencias funcionales y administrativas.
* **Control de Accesos (RBAC):** Sistema de permisos granulares para gobernar el acceso a los distintos módulos.
* **Auditoría Integral:** Registro automático de actividad, trazabilidad de sesiones y control de cambios en los modelos críticos del sistema.

## 🛠 Stack Tecnológico

* **Backend:** Laravel 13
* **Frontend:** Livewire 3 (Arquitectura Volt), Alpine.js, Tailwind CSS
* **Control de Accesos:** Spatie Laravel Permission
* **Auditoría:** Spatie Laravel Activitylog

## 📋 Requisitos Previos

* PHP >= 8.4
* Composer
* Node.js y NPM
* Motor de Base de Datos relacional (MySQL 8.0+, MariaDB, PostgreSQL o SQLite)

## ⚙️ Instalación y Despliegue

Sigue estos pasos para levantar un entorno local o preparar el pase a producción en un servidor nuevo:

1.  **Clonar el repositorio:**
    ```bash
    git clone <url-del-repositorio>
    cd HSIControl
    ```

2.  **Instalar dependencias:**
    ```bash
    composer install
    npm install
    ```

3.  **Configurar el entorno:**
    Crea una copia del archivo de variables de entorno.
    ```bash
    cp .env.example .env
    ```
    Abre el archivo `.env` recién creado y configura la conexión a tu base de datos local o de producción (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

4.  **Generar la clave de la aplicación:**
    ```bash
    php artisan key:generate
    ```

5.  **Estructurar la Base de Datos (Migraciones y Seeders):**
    Este comando es crucial. Creará todas las tablas y ejecutará los catálogos base indispensables para el funcionamiento del sistema (Permisos, Tipos de Unidades Jerárquicas y Cuenta Maestra).
    ```bash
    php artisan migrate --seed
    ```

6.  **Compilar los assets del frontend:**
    Para un entorno de desarrollo:
    ```bash
    npm run dev
    ```
    Para un entorno de producción:
    ```bash
    npm run build
    ```

## 🔐 Primer Ingreso al Sistema

Para facilitar la instalación en cualquier institución, el proceso de sembrado (`--seed`) genera automáticamente una cuenta de "Super Administrador" genérica para el primer login.

* **Documento / Usuario:** `12345678`
* **Contraseña:** `password`

> **⚠️ IMPORTANTE:** Por razones estrictas de seguridad, la primera acción tras un despliegue exitoso debe ser ingresar con esta cuenta, dar de alta al administrador real del sistema (con su DNI auténtico), asignarle los privilegios totales y, finalmente, **eliminar** la cuenta genérica `12345678` de la base de datos.
