# Sistema de Gestión de Biblioteca

Aplicación web desarrollada en PHP con Programación Orientada a Objetos para gestionar libros, usuarios y préstamos de una biblioteca.

## Funcionalidades implementadas

- CRUD de libros
- CRUD de usuarios
- Registro de préstamos
- Registro de devoluciones
- Actualización automática del stock disponible
- Interfaz web simple y funcional

## Estructura del proyecto

- [index.php](index.php): interfaz principal y control de acciones
- [classes/Database.php](classes/Database.php): conexión PDO a MySQL
- [classes/Biblioteca.php](classes/Biblioteca.php): lógica de negocio
- [classes/Libro.php](classes/Libro.php): modelo de libro
- [classes/Usuario.php](classes/Usuario.php): modelo de usuario
- [classes/Prestamo.php](classes/Prestamo.php): modelo de préstamo
- [biblioteca.sql](biblioteca.sql): script de base de datos

## Requisitos

- XAMPP, Laragon o cualquier servidor local con Apache + MySQL
- PHP 8+
- MySQL

## Instalación y configuración

1. Clona o descarga este repositorio.
2. Coloca la carpeta del proyecto en la ruta pública de tu servidor local, por ejemplo:
   - XAMPP: `C:\xampp\htdocs\library-management-system`
3. Crea la base de datos ejecutando el archivo [biblioteca.sql](biblioteca.sql) en phpMyAdmin o MySQL Workbench.
4. Ajusta las credenciales de conexión en [classes/Database.php](classes/Database.php) si tu usuario o contraseña son distintos.
5. Inicia Apache y MySQL.
6. Abre en el navegador:
   - `http://localhost/library-management-system/index.php`

## Configuración de base de datos

El archivo SQL crea las tablas:

- `libros`
- `usuarios`
- `prestamos`

## Uso

- Desde la interfaz puedes agregar libros y usuarios.
- En la sección de préstamos puedes registrar un préstamo y luego devolverlo.
- El sistema actualiza automáticamente la cantidad disponible del libro.

## Capturas de pantalla

Puedes incluir aquí capturas del sistema funcionando una vez subido el repositorio.

## Documentación adicional

- CRUD completo para libros y usuarios.
- Gestión de préstamos con validación de stock.
- Arquitectura orientada a objetos y separación entre lógica y vista.
