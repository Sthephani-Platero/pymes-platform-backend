# 🚀 Plataforma Inteligente para PYMEs – Backend API

![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-10-red?logo=laravel&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-blue?logo=postgresql&logoColor=white)
![Status](https://img.shields.io/badge/Status-MVP-yellow)

Backend desarrollado para el MVP de una plataforma web orientada a pequeñas y medianas empresas (PYMEs), diseñada para ofrecer autenticación, configuración empresarial, dashboards y generación de insights mediante procesamiento básico de datos.

---

## 🧠 Descripción del Proyecto

Este backend expone una API REST desarrollada con **Laravel**, que permite:

- Registro e inicio de sesión de usuarios
- Configuración inicial de empresa
- Consulta de métricas (dashboards)
- Generación y visualización de insights
- Consumo de datos simulados o externos
- Procesamiento básico de información empresarial

El sistema está diseñado con **arquitectura desacoplada** (frontend y backend separados).

---

## 🏗️ Arquitectura General

Frontend (React + Vite)
↓
Backend (Laravel API REST)
↓
Base de datos PostgreSQL (ElephantSQL)


---

## ⚙️ Stack Tecnológico

- PHP 8+
- Laravel 12
- Laravel Sanctum (autenticación por tokens)
- PostgreSQL 15
- ElephantSQL
- Composer

---

## 🔐 Autenticación

Se utiliza **Laravel Sanctum** para autenticación basada en tokens.

**Flujo:**

1. El usuario se registra
2. El backend genera un token
3. El frontend almacena el token
4. Las rutas protegidas requieren autenticación vía Bearer Token

---

## 🗄️ Modelo de Base de Datos

### Entidades principales

**Users**
- id
- name
- email
- password
- role

**Companies**
- id
- user_id (FK)
- name
- industry
- size

**Dashboards**
- id
- company_id (FK)
- metric_name
- metric_value

**Insights**
- id
- company_id (FK)
- insight_text

### Relaciones

- Un usuario tiene una empresa
- Una empresa tiene múltiples métricas (dashboards)
- Una empresa puede generar múltiples insights

---

## 📡 Endpoints Principales

**Autenticación**
- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`

**Empresa**
- `POST /api/company`
- `GET /api/company`

**Dashboard**
- `GET /api/dashboard`

**Insights**
- `GET /api/insights`

---

## 📦 Instalación

1. Clonar repositorio
```bash
git clone https://github.com/Sthephani-Platero/pymes-platform-backend.git
cd backend
Instalar dependencias

composer install
Configurar variables de entorno

cp .env.example .env
Editar .env con conexión a PostgreSQL:

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pymes_platform
DB_USERNAME=postgres
DB_PASSWORD=2026

php artisan key:generate
Migraciones

php artisan migrate
Instalar Sanctum

php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
Ejecutar servidor

php artisan serve
🧩 Decisiones Técnicas
¿Por qué Laravel?

Estructura clara y profesional

Sistema robusto de autenticación

ORM Eloquent eficiente

Arquitectura limpia y mantenible

¿Por qué PostgreSQL?

Mejor manejo de datos relacionales complejos

Alta estabilidad

Escalable para futuras versiones

¿Por qué arquitectura desacoplada?

Permite escalabilidad independiente

Facilita mantenimiento

Compatible con futuras apps móviles

🚧 Estado del Proyecto
Versión actual: MVP

Enfoque: Autenticación + Configuración empresarial + Dashboard básico + Insights básicos

👩‍💻 Autora
Desarrollado como proyecto académico para diseño y desarrollo de MVP de plataforma para PYMEs.

🔗 Roadmap / Próximas versiones
Integración de APIs externas reales

Visualización avanzada de métricas y gráficos

Gestión de roles y permisos avanzados

Notificaciones y alertas inteligentes
