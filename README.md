# 📦 StockMaster API

<p align="center" style="font-size: 80px;">📦🏭</p>

> **StockMaster** - API RESTful para gestión avanzada de inventarios multialmacén

## 📋 Acerca de StockMaster

**StockMaster** es una API RESTful para la gestión avanzada de inventarios multialmacén, diseñada para garantizar la integridad de los datos mediante lógica robusta en base de datos (MariaDB) y una arquitectura de software sólida (Laravel/PHP). El sistema permite el control de existencias, auditoría de movimientos, gestión de proveedores y alertas automáticas de reposición.

## 🛠️ Stack Tecnológico y Arquitectura

| Componente | Tecnología |
|------------|------------|
| **Base de Datos** | MariaDB (Motor InnoDB) |
| **Backend** | PHP con Tipado Estricto (Laravel) |
| **Autenticación** | OAuth2 mediante Laravel Passport |
| **Patrones de Diseño** | Repository Pattern / Service Pattern (SOLID) |
| **Documentación** | Scramble (OpenAPI/Swagger) |

## 🗃️ Core de Base de Datos (MariaDB)

El diseño se basa en la separación de tablas de catálogo y transacciones:

### Tablas Principales
- [`products`](database/migrations/2026_02_11_190453_create_products_table.php) - SKU único y `min_stock_level`
- [`warehouses`](database/migrations/2026_02_11_190452_create_warehouses_table.php) - Ubicaciones de almacén
- [`categories`](database/migrations/2026_02_11_190450_create_categories_table.php) - Categorías de productos
- [`suppliers`](database/migrations/2026_02_11_190451_create_suppliers_table.php) - Gestión de proveedores

### Relaciones
- **N:M:** [`inventories`](database/migrations/2026_02_11_190500_create_inventories_table.php) - Stock real por producto/almacén
- **Auditoría:** [`stock_movements`](database/migrations/2026_02_11_190512_create_stock_movements_table.php) - Registro obligatorio de entrada/salida vinculado a `user_id` no nulo

### Automatización
- **Patrón Observer:** [`StockMovementObserver`](app/Observers/StockMovementObserver.php) gestiona actualizaciones automáticas de inventario y alertas de reposición
- **Vistas:** `vw_inventory_valuation` (valor total) y `vw_out_of_stock`

## 🔐 Autenticación y Seguridad

- **OAuth2** mediante Laravel Passport
- **RBAC** (Control de Acceso Basado en Roles) con middleware de protección
- **Rate Limiting** habilitado en todas las rutas protegidas
- **Rutas Nombradas** - Sin URLs hardcodeadas
- **Validación de Password** - Regla StrongPassword: mínimo 8 caracteres, mayúscula y carácter especial

## 📡 Endpoints de la API

### Autenticación (Público)

| Método | Endpoint | Acción del Controlador | Descripción |
|--------|----------|------------------------|-------------|
| POST | `/api/auth/register` | [`AuthController@register`](app/Http/Controllers/Api/AuthController.php:26) | Registrar nuevo usuario |
| POST | `/api/auth/login` | [`AuthController@login`](app/Http/Controllers/Api/AuthController.php:63) | Iniciar sesión |

> **Nota:** El endpoint de login tiene rate limiting de 5 intentos por minuto.

### Perfil de Usuario (Protegido)

| Método | Endpoint | Acción del Controlador | Descripción |
|--------|----------|------------------------|-------------|
| GET | `/api/user` | Closure | Obtener perfil del usuario autenticado |

### Productos (Protegido)

| Método | Endpoint | Acción del Controlador | Descripción |
|--------|----------|------------------------|-------------|
| GET | `/api/products` | [`ProductController@index`](app/Http/Controllers/Api/ProductController.php:23) | Listar todos los productos |
| POST | `/api/products` | [`ProductController@store`](app/Http/Controllers/Api/ProductController.php:31) | Crear nuevo producto |
| GET | `/api/products/{id}` | [`ProductController@show`](app/Http/Controllers/Api/ProductController.php:44) | Mostrar producto individual |
| PUT/PATCH | `/api/products/{id}` | [`ProductController@update`](app/Http/Controllers/Api/ProductController.php:52) | Actualizar producto |
| DELETE | `/api/products/{id}` | [`ProductController@destroy`](app/Http/Controllers/Api/ProductController.php:65) | Eliminar producto |

### Categorías (Protegido)

| Método | Endpoint | Acción del Controlador | Descripción |
|--------|----------|------------------------|-------------|
| GET | `/api/categories` | [`CategoryController@index`](app/Http/Controllers/Api/CategoryController.php:29) | Listar todas las categorías |
| POST | `/api/categories` | [`CategoryController@store`](app/Http/Controllers/Api/CategoryController.php:39) | Crear nueva categoría |
| GET | `/api/categories/{id}` | [`CategoryController@show`](app/Http/Controllers/Api/CategoryController.php:54) | Mostrar categoría individual |
| PUT/PATCH | `/api/categories/{id}` | [`CategoryController@update`](app/Http/Controllers/Api/CategoryController.php:64) | Actualizar categoría |
| DELETE | `/api/categories/{id}` | [`CategoryController@destroy`](app/Http/Controllers/Api/CategoryController.php:77) | Eliminar categoría |

> **Nota:** No se puede eliminar una categoría que tenga productos asociados. Retorna 422 con mensaje de error indicando la cantidad de productos.

### Cabeceras de Petición

Todos los endpoints protegidos requieren:
```
Authorization: Bearer {access_token}
Accept: application/json
```

## 📦 Instalación

### Requisitos Previos
- PHP 8.2+
- Composer
- MariaDB 10.6+
- Laravel Passport

### Pasos de Configuración

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd API StockMaster
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos** en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stockmaster
DB_USERNAME=root
DB_PASSWORD=
```

5. **Ejecutar migraciones**
```bash
php artisan migrate
```

6. **Instalar Passport**
```bash
php artisan passport:install --force
```

### Requisitos de Password para Registro

El endpoint de registro requiere un password que cumpla con:
- Mínimo **8 caracteres**
- Al menos una **letra mayúscula**
- Al menos un **carácter especial** (`!@#$%^&*(),.?":{}|<>`)

**Ejemplo de password válido:** `Password123!`

| Campo | Valor |
|-------|-------|
| Password | `Password123!` |

> **Nota:** El [`UserFactory`](database/factories/UserFactory.php) y los tests usan passwords que cumplen estos requisitos.

## 📦 Seeders de Base de Datos

### Estructura Modular

El proyecto sigue un patrón de seeders modulares donde cada entidad tiene su propio Seeder:

| Seeder | Entidad | Registros |
|--------|---------|-----------|
| [`RoleAndPermissionSeeder`](database/seeders/RoleAndPermissionSeeder.php) | Roles y Permisos | 3 roles, 4 permisos |
| [`UserSeeder`](database/seeders/UserSeeder.php) | Usuarios | 1 admin + 4 usuarios |
| [`CategorySeeder`](database/seeders/CategorySeeder.php) | Categorías | 5 categorías |
| [`SupplierSeeder`](database/seeders/SupplierSeeder.php) | Proveedores | 3 proveedores |
| [`WarehouseSeeder`](database/seeders/WarehouseSeeder.php) | Almacenes | 3 almacenes |
| [`ProductSeeder`](database/seeders/ProductSeeder.php) | Productos | 20 productos |
| [`StockMovementSeeder`](database/seeders/StockMovementSeeder.php) | Movimientos de Stock | ~100-200 movimientos |

### Orden de Ejecución

El [`DatabaseSeeder`](database/seeders/DatabaseSeeder.php) orquesta la ejecución en el orden correcto:

```mermaid
flowchart TD
    A[RoleAndPermissionSeeder] --> B[UserSeeder]
    B --> C[CategorySeeder]
    C --> D[SupplierSeeder]
    D --> E[WarehouseSeeder]
    E --> F[ProductSeeder]
    F --> G[StockMovementSeeder]
    
    note for G "Dispara StockMovementObserver<br/>para poblar Inventory"
```

### Características de los Seeders

- **Trait `DisablesForeignKeyChecking`**: Cada seeder usa el trait para permitir `truncate()` sin errores de foreign keys
- **Datos Coherentes**: Los seeders obtienen registros existentes para crear relaciones válidas
- **Factories Inteligentes**: Uso de `recycle()` y closures para mantener integridad referencial
- **Observer Activo**: `StockMovementSeeder` dispara `StockMovementObserver` automáticamente

### Ejecutar Seeders

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar un seeder específico
php artisan db:seed --class=CategorySeeder

# Refrescar y sembrar
php artisan migrate:fresh --seed
```

### Usuario de Prueba

| Campo | Valor |
|-------|-------|
| Email | admin@stockmaster.com |
| Password | password |

## 🧪 Pruebas

```bash
# Ejecutar todas las pruebas
php artisan test

# Ejecutar con cobertura
php artisan test --coverage
```

## 📁 Estructura del Proyecto

```
API StockMaster/
├── app/
│   ├── Domain/
│   │   └── Inventory/          # Capa de lógica de negocio
│   │       ├── Contracts/      # Definiciones de interfaces
│   │       ├── Factories/      # Implementaciones de patrón Factory
│   │       ├── Services/       # Servicios de negocio
│   │       └── Strategies/     # Estrategias de valoración
│   ├── Http/
│   │   ├── Controllers/Api/    # Controladores de API
│   │   ├── Requests/           # Validación FormRequest
│   │   └── Resources/          # Transformadores de recursos API
│   ├── Models/                 # Modelos Eloquent
│   └── Observers/              # Observadores de modelos (StockMovementObserver)
├── database/
│   ├── factories/             # Factorías de modelos
│   ├── migrations/            # Migraciones de base de datos
│   └── seeders/               # Seeders de base de datos
├── routes/
│   └── api.php                # Definición de rutas API
└── tests/                     # Pruebas Feature y Unit
```

## 🔧 Servicios Clave

### StockService
Gestiona movimientos de stock (ENTRADA/SALIDA) con actualizaciones automáticas de inventario.

### InventoryValuationService
Calcula el valor del inventario usando diferentes estrategias:
- **FIFO** (First In, First Out - Primera Entrada, Primera Salida)
- **LIFO** (Last In, First Out - Última Entrada, Primera Salida)
- **Costo Promedio**

## 📤 Postman Collection

Se incluye una colección de Postman lista para importar con todos los endpoints documentados.

### Importar Colección

1. Abre Postman
2. Haz clic en **Import**
3. Selecciona el archivo [`postman-collection.json`](postman-collection.json)

### Variables de Entorno

La colección incluye las siguientes variables:

| Variable | Valor | Descripción |
|----------|-------|-------------|
| `baseUrl` | `http://localhost:8000/api` | URL base de la API |
| `accessToken` | (se auto-configura) | Token de acceso OAuth2 |
| `productId` | (se auto-configura) | ID del producto para pruebas |

### Flujo de Prueba Recomendado

1. **Iniciar sesión** (o registrar): `POST /auth/login` → Obtiene `{accessToken}`
2. **Listar productos**: `GET /products` → Obtiene el primer ID de producto
3. **Probar endpoints**: Usa el ID obtenido para probar Show, Update y Delete

## 📖 Documentación

La documentación de la API se genera automáticamente usando Scramble. Accede en:
```
/api/docs
```

## 🤝 Contribuciones

1. Haz fork del repositorio
2. Crea una rama de característica
3. Guarda tus cambios
4. Envía la rama
5. Abre un Pull Request

## 📄 Licencia

StockMaster API es software de código abierto bajo licencia MIT.
