# Sistema de Prácticas Pre-Profesionales (PPP-FIIS)

Este es el sistema de gestión para las prácticas pre-profesionales de la FIIS - UNAS.
Desarrollado utilizando **Laravel 12** y **Laravel Breeze**.

## Requisitos Previos

Asegúrate de tener instalado en tu entorno local:
- PHP >= 8.2
- Composer
- Node.js y NPM
- MySQL

## Instrucciones de Instalación

Sigue estos pasos para levantar el proyecto en un entorno local nuevo:

1. **Clonar el repositorio:**
   ```bash
   git clone [https://github.com/manuel2813/ppp-fiis-system.git](https://github.com/manuel2813/ppp-fiis-system.git)
  y luego entrar a dicha carpeta donde clonaste el repositorio para ejecutar lo siguiente:
  
2. Instalar dependencias de Backend (PHP): Entra a la carpeta del proyecto y ejecuta:
   " composer install "
   
4. Instalar dependencias de Frontend (JS/CSS):
   " npm install "

5. Copia el archivo de ejemplo para crear tu archivo de configuración:
  " cp .env.example .env "
Abre el archivo .env y configura tus credenciales de base de datos (DB_DATABASE, DB_USERNAME, etc.).

6. Luego creas tu base de datos en mysql workbench 8.0 CE , le pones un nombre por ejemplo : "ppp-system" y ese mismo nombre 
debe de estar en tu archivo .env

7. Generar la clave de aplicación:
   " php artisan key:generate "

8. Ejecutar las migraciones: Crea las tablas en tu base de datos:
    " php artisan migrate "
   
9. Ejecución del Proyecto :
 abrir dos terminales desde la carpeta raiz donde clonaste el repositorio:
Terminal 1 (Servidor Backend):
" php artisan serve "
Terminal 2 (Compilador de estilos/scripts):
" npm run dev "

10. finalmente El sistema estará accesible en: http://127.0.0.1:8000 o en el http://localhost:8000 según como configuraste en tu archivo .env
