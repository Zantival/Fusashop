# 🛒 FusaShop — Plataforma E-Commerce

## ⚡ Instalación rápida (XAMPP)

### 1. Requisitos
- PHP 8.2+
- Composer
- MySQL (XAMPP)
- Node.js (opcional, no requerido)

### 2. Clonar / Extraer
```bash
# Extraer el ZIP en htdocs o cualquier carpeta
cd fusashop
```

### 3. Dependencias
```bash
composer install
```

### 4. Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env`:
```
DB_HOST=127.0.0.1
DB_DATABASE=fusashop
DB_USERNAME=root
DB_PASSWORD=          # vacío en XAMPP por defecto
```

### 5. Base de datos
Crear la BD `fusashop` en phpMyAdmin, luego:
```bash
php artisan migrate --seed
```

### 6. Storage
```bash
php artisan storage:link
```

### 7. Ejecutar
```bash
php artisan serve
```
Abrir: http://localhost:8000

---

## 👥 Cuentas de prueba

| Rol         | Email                   | Contraseña    |
|-------------|-------------------------|---------------|
| Analista    | admin@fusashop.com      | password123   |
| Comerciante | tienda@fusashop.com     | password123   |
| Comerciante | tech@fusashop.com       | password123   |
| Consumidor  | juan@fusashop.com       | password123   |
| Consumidor  | maria@fusashop.com      | password123   |

---

## 🗺️ Rutas principales

| URL                        | Descripción                  |
|----------------------------|------------------------------|
| `/login`                   | Inicio de sesión             |
| `/register`                | Registro de usuario          |
| `/shop`                    | Tienda (consumidor)          |
| `/shop/catalog`            | Catálogo con filtros         |
| `/shop/cart`               | Carrito de compras           |
| `/shop/checkout`           | Pago simulado                |
| `/merchant/dashboard`      | Panel comerciante            |
| `/merchant/products`       | CRUD productos               |
| `/analyst/dashboard`       | Panel analítica + charts     |
| `/api/products`            | API REST pública             |
| `/api/login`               | Auth API (Sanctum)           |

---

## 🐍 Python Analytics

```bash
cd python
pip install mysql-connector-python pandas
python analytics.py --report all --output json
python analytics.py --report ventas --output csv
```

---

## 🔐 Seguridad implementada (OWASP)
- ✅ CSRF tokens en todos los formularios
- ✅ Validación y sanitización de inputs (strip_tags)
- ✅ Hash bcrypt de contraseñas
- ✅ Protección SQL Injection (Eloquent ORM + bindings)
- ✅ Protección XSS (e() / {{}})
- ✅ Control de acceso por roles (RoleMiddleware)
- ✅ API autenticada con Sanctum tokens
- ✅ Autorización de recursos por propietario

---

## 🏗️ Arquitectura
```
app/
  Http/
    Controllers/
      Auth/AuthController.php
      Consumer/ConsumerController.php
      Merchant/MerchantController.php
      Analyst/AnalystController.php
      ApiController.php
    Middleware/RoleMiddleware.php
  Models/ (User, Product, Cart, CartItem, Order, OrderItem)
  Providers/AppServiceProvider.php
database/migrations/   (4 archivos)
database/seeders/DatabaseSeeder.php
resources/views/
  auth/ (login, register)
  layouts/app.blade.php
  consumer/ (home, catalog, product-detail, cart, checkout, orders)
  merchant/ (dashboard, products, product-form, orders)
  analyst/ (dashboard, users, orders)
python/analytics.py
routes/ (web.php, api.php)
```
# Fusashop
