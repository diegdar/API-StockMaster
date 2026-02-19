# 📦 StockMaster API

<p align="center" style="font-size: 80px;">📦🏭</p>

> **StockMaster** - API RESTful para gestión avanzada de inventarios multialmacén

## 📋 Acerca de StockMaster

**StockMaster** es una API RESTful para la gestión avanzada de inventarios multialmacén, diseñada para garantizar la integridad de los datos mediante lógica robusta en base de datos (MariaDB) y una arquitectura de software sólida (Laravel/PHP). El sistema permite el control de existencias, auditoría de movimientos, gestión de proveedores y alertas automáticas de reposición.

## 🛠️ Stack Tecnológico y Arquitectura

| Componente | Tecnología |
|------------|------------|
| **Base de Datos** | MariaDB (Motor InnoDB) |
| **Backend** | PHP 8.2+ con Tipado Estricto (Laravel 11) |
| **Autenticación** | OAuth2 mediante Laravel Passport |
| **Role-Based Access Control** | Spatie Laravel Permission |
| **Patrones de Diseño** | Repository, Service, DTO, Observer, Strategy, Factory |
| **Documentación** | Scramble (OpenAPI 3.0) |
| **Testing** | PHPUnit (Unit y Feature Tests) |

---

## 🏗️ Arquitectura de Software

### Flujo de Comunicación entre Capas

```
Controller → DTO → Service → Repository → MariaDB
                ↓
            Observer (eventos del modelo)
```

### Reglas de Comunicación (Strict Flow)

| Capa | Responsabilidad | Regla de Oro |
|------|-----------------|--------------|
| **Controller** | Recibe Request, transforma a DTO | Nunca llama al Repository o Model directamente |
| **Service** | Ejecuta lógica de negocio | Solo acepta DTOs o tipos primitivos |
| **Repository** | Acceso a datos (Eloquent/Query Builder) | Único lugar con lógica de BD |
| **Observer** | Reacciona a eventos del modelo | Automatización transparente |

### Patrones de Diseño Implementados

#### 1. Repository Pattern
Abstracción del acceso a datos para centralizar queries complejas y desacoplar la lógica de negocio de Eloquent.

```php
// Ejemplo: ProductRepository
interface ProductRepositoryInterface
{
    public function getAll(int $perPage): LengthAwarePaginator;
    public function findById(int $id): ?Product;
    public function getLowStockProducts(): Collection;
}
```

#### 2. Service Pattern
Encapsula la lógica de negocio y orquesta las operaciones entre Controller y Repository.

```php
// Ejemplo: WarehouseService
class WarehouseService
{
    public function transferBetweenWarehouses(TransferStockDTO $dto): array
    {
        // Validación de stock, capacidad, y ejecución transaccional
    }
}
```

#### 3. DTO (Data Transfer Object)
Estandarización de datos de entrada/salida entre capas, evitando exponer modelos directamente.

```php
// Ejemplo: TransferStockDTO
readonly class TransferStockDTO
{
    public function __construct(
        public int $productId,
        public int $sourceWarehouseId,
        public int $destinationWarehouseId,
        public int $quantity,
        public ?string $description = null
    ) {}
}
```

#### 4. Observer Pattern
Automatización de tareas en respuesta a eventos del modelo (creating, created, updating, deleting).

- [`CategoryObserver`](app/Observers/CategoryObserver.php) - Auto-generación de slugs
- [`WarehouseObserver`](app/Observers/WarehouseObserver.php) - Auto-generación de slugs
- [`StockMovementObserver`](app/Observers/StockMovementObserver.php) - Actualización automática de inventario

#### 5. Strategy Pattern (Valoración de Inventario)
Permite diferentes algoritmos de valoración de stock:

| Estrategia | Descripción | Clase |
|------------|-------------|-------|
| **FIFO** | First In, First Out | [`FifoValuation`](app/Domain/Inventory/Strategies/FifoValuation.php) |
| **LIFO** | Last In, First Out | [`LifoValuation`](app/Domain/Inventory/Strategies/LifoValuation.php) |
| **Average** | Costo Promedio Ponderado | [`AvgValuation`](app/Domain/Inventory/Strategies/AvgValuation.php) |

#### 6. Factory Pattern
Creación de estrategias de valoración de forma desacoplada.

```php
// ValuationStrategyFactory
class ValuationStrategyFactory
{
    public function make(string $strategy): InventoryValuationStrategy
    {
        return match ($strategy) {
            'fifo' => new FifoValuation(),
            'lifo' => new LifoValuation(),
            'average' => new AvgValuation(),
        };
    }
}
```

---

## 🗃️ Core de Base de Datos (MariaDB)

El diseño se basa en la separación de tablas de catálogo y transacciones:

### Tablas Principales

| Tabla | Descripción | Características |
|-------|-------------|-----------------|
| [`products`](database/migrations/2026_02_11_190453_create_products_table.php) | Catálogo de productos | SKU único, `min_stock_level`, `valuation_strategy` |
| [`warehouses`](database/migrations/2026_02_11_190452_create_warehouses_table.php) | Almacenes | Slug único, `capacity`, `is_active` |
| [`categories`](database/migrations/2026_02_11_190450_create_categories_table.php) | Categorías | Slug único auto-generado |
| [`suppliers`](database/migrations/2026_02_11_190451_create_suppliers_table.php) | Proveedores | Datos de contacto |

### Relaciones N:M

| Tabla | Descripción |
|-------|-------------|
| [`inventories`](database/migrations/2026_02_11_190500_create_inventories_table.php) | Stock real por producto/almacén |
| [`stock_movements`](database/migrations/2026_02_11_190512_create_stock_movements_table.php) | Auditoría de entradas/salidas (vinculado a `user_id`) |

### Automatización

- **Observer Pattern:** [`StockMovementObserver`](app/Observers/StockMovementObserver.php) gestiona actualizaciones automáticas de inventario
- **Vistas de Base de Datos:**
  - `vw_inventory_valuation` - Valor total del inventario
  - `vw_out_of_stock` - Productos sin stock

---

## 🔐 Autenticación y Seguridad

### OAuth2 con Laravel Passport

El sistema utiliza OAuth2 para autenticación, proporcionando tokens de acceso seguros.

### RBAC (Control de Acceso Basado en Roles)

La gestión de roles y permisos se implementa mediante **Spatie Laravel Permission**.

| Rol | Permisos |
|-----|----------|
| **Admin** | Acceso total: CRUD productos, categorías, almacenes, transferencias |
| **Worker** | Transferencias entre almacenes, consulta de inventario |
| **Viewer** | Solo lectura de datos |

### Rate Limiting

| Endpoint | Límite |
|----------|--------|
| Login | 5 intentos/minuto |
| API General | 60 requests/minuto |

### Validación de Password

Regla [`StrongPassword`](app/Rules/StrongPassword.php):
- Mínimo **8 caracteres**
- Al menos una **letra mayúscula**
- Al menos un **carácter especial** (`!@#$%^&*(),.?":{}|<>`)

---

## 📡 Endpoints de la API

### Autenticación (Público)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth/register` | Registrar nuevo usuario |
| POST | `/api/auth/login` | Iniciar sesión (rate limited) |

### Perfil de Usuario (Protegido)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/user` | Obtener perfil del usuario autenticado |

### Productos (Protegido)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/products` | Listar todos los productos (paginado) |
| POST | `/api/products` | Crear nuevo producto |
| GET | `/api/products/{id}` | Mostrar producto individual |
| PUT/PATCH | `/api/products/{id}` | Actualizar producto |
| DELETE | `/api/products/{id}` | Eliminar producto |

### Categorías (Protegido)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/categories` | Listar todas las categorías |
| POST | `/api/categories` | Crear nueva categoría |
| GET | `/api/categories/{id}` | Mostrar categoría individual |
| PUT/PATCH | `/api/categories/{id}` | Actualizar categoría |
| DELETE | `/api/categories/{id}` | Eliminar categoría |

> **Nota:** No se puede eliminar una categoría que tenga productos asociados (retorna 422).

### Almacenes (Protegido)

| Método | Endpoint | Descripción | Roles |
|--------|----------|-------------|-------|
| GET | `/api/warehouses` | Listar almacenes (paginado) | Todos |
| POST | `/api/warehouses` | Crear almacén | Admin |
| GET | `/api/warehouses/{id}` | Mostrar almacén por ID | Todos |
| GET | `/api/warehouses/slug/{slug}` | Mostrar almacén por slug | Todos |
| PUT/PATCH | `/api/warehouses/{id}` | Actualizar almacén | Admin |
| DELETE | `/api/warehouses/{id}` | Eliminar almacén | Admin |
| GET | `/api/warehouses/{id}/capacity` | Capacidad del almacén | Todos |
| GET | `/api/warehouses/with-capacity` | Almacenes con métricas de capacidad | Todos |
| GET | `/api/warehouses/with-inventory-count` | Almacenes con conteo de inventario | Todos |
| POST | `/api/warehouses/transfer` | Transferir stock entre almacenes | Admin, Worker |

### Cabeceras de Petición

```
Authorization: Bearer {access_token}
Accept: application/json
```

---

## 🚀 Nuevas Features

### Transferencia de Stock entre Almacenes

El sistema permite transferir productos entre almacenes con validaciones completas:

```json
POST /api/warehouses/transfer
{
    "product_id": 1,
    "source_warehouse_id": 1,
    "destination_warehouse_id": 2,
    "quantity": 50,
    "description": "Reposición de stock"
}
```

**Validaciones:**
- Stock suficiente en almacén origen
- Capacidad disponible en almacén destino
- Almacenes activos
- Transacción atómica (rollback automático en error)

### Valoración de Inventario

Cada producto puede tener una estrategia de valoración:

```php
// Calcular valor del inventario
$valuationService->calculate($product); // Usa la estrategia configurada
```

### Excepciones Personalizadas

| Excepción | Código | Uso |
|-----------|--------|-----|
| [`DeletionException`](app/Exceptions/DeletionException.php) | 422 | Entidad con dependencias |
| [`InsufficientStockException`](app/Exceptions/InsufficientStockException.php) | 422 | Stock insuficiente para transferencia |
| [`InsufficientCapacityException`](app/Exceptions/InsufficientCapacityException.php) | 422 | Capacidad de almacén excedida |

---

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
cd API-StockMaster
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

7. **Ejecutar seeders**
```bash
php artisan db:seed
```

---

## 📦 Seeders de Base de Datos

### Estructura Modular

| Seeder | Entidad | Registros |
|--------|---------|-----------|
| [`RoleAndPermissionSeeder`](database/seeders/RoleAndPermissionSeeder.php) | Roles y Permisos | 3 roles, 4 permisos |
| [`UserSeeder`](database/seeders/UserSeeder.php) | Usuarios | 1 admin + 4 usuarios |
| [`CategorySeeder`](database/seeders/CategorySeeder.php) | Categorías | 5 categorías |
| [`SupplierSeeder`](database/seeders/SupplierSeeder.php) | Proveedores | 3 proveedores |
| [`WarehouseSeeder`](database/seeders/WarehouseSeeder.php) | Almacenes | 3 almacenes |
| [`ProductSeeder`](database/seeders/ProductSeeder.php) | Productos | 20 productos |
| [`StockMovementSeeder`](database/seeders/StockMovementSeeder.php) | Movimientos | ~100-200 movimientos |

### Orden de Ejecución

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

### Usuario de Prueba

| Campo | Valor |
|-------|-------|
| Email | admin@stockmaster.com |
| Password | Password$1234 |

---

## 🧪 Pruebas

```bash
# Ejecutar todas las pruebas
php artisan test

# Ejecutar con cobertura
php artisan test --coverage

# Ejecutar prueba específica
php artisan test --filter ProductTest
```

---

## 📁 Estructura del Proyecto

```
API-StockMaster/
├── app/
│   ├── DTO/                          # Data Transfer Objects
│   │   ├── Category/                 # CreateCategoryDTO, UpdateCategoryDTO
│   │   ├── Product/                  # CreateProductDTO, UpdateProductDTO
│   │   └── Warehouse/                # CreateWarehouseDTO, UpdateWarehouseDTO, TransferStockDTO
│   ├── Domain/
│   │   └── Inventory/                # Capa de Dominio
│   │       ├── Contracts/            # Interfaces (InventoryValuationStrategy)
│   │       ├── Factories/            # ValuationStrategyFactory
│   │       ├── Services/             # InventoryValuationService, StockService
│   │       └── Strategies/           # FifoValuation, LifoValuation, AvgValuation
│   ├── Exceptions/                   # Excepciones personalizadas
│   │   ├── DeletionException.php
│   │   ├── InsufficientCapacityException.php
│   │   └── InsufficientStockException.php
│   ├── Http/
│   │   ├── Controllers/Api/          # Controladores de API
│   │   ├── Requests/                 # FormRequest validation
│   │   └── Resources/                # API Resources
│   ├── Models/                       # Modelos Eloquent
│   ├── Observers/                    # Observers (Category, Warehouse, StockMovement)
│   ├── Repositories/                 # Repository Pattern
│   │   ├── Contracts/                # Interfaces de repositorio
│   │   ├── CategoryRepository.php
│   │   ├── ProductRepository.php
│   │   ├── StockMovementRepository.php
│   │   └── WarehouseRepository.php
│   ├── Rules/                        # Reglas de validación personalizadas
│   │   ├── ActiveWarehouse.php
│   │   └── StrongPassword.php
│   └── Services/                     # Service Layer
│       ├── CategoryService.php
│       ├── ProductService.php
│       ├── WarehouseService.php
│       └── Traits/
│           └── WarehouseTransferTrait.php
├── config/
│   └── scramble.php                  # Configuración de documentación API
├── database/
│   ├── factories/                    # Model Factories
│   ├── migrations/                   # Migraciones de BD
│   └── seeders/                      # Seeders modulares
├── routes/
│   └── api.php                       # Rutas API con nombres
└── tests/                            # Feature y Unit Tests
```

---

## 🔧 Servicios Clave

### WarehouseService

Gestiona la lógica de negocio de almacenes:

| Método | Descripción |
|--------|-------------|
| `getAll()` | Listado paginado |
| `create()` | Crear almacén |
| `update()` | Actualizar almacén |
| `delete()` | Eliminar (valida inventario) |
| `transferBetweenWarehouses()` | Transferencia con validaciones |
| `getWarehouseCapacity()` | Métricas de capacidad |
| `getWarehousesWithCapacity()` | Todos con métricas |

### ProductService

Gestiona productos con validación de dependencias:

| Método | Descripción |
|--------|-------------|
| `getAll()` | Listado paginado |
| `findById()` | Búsqueda por ID |
| `create()` | Crear producto |
| `update()` | Actualizar producto |
| `delete()` | Eliminar (valida inventario, movimientos, alertas) |

### CategoryService

Gestiona categorías con auto-generación de slugs:

| Método | Descripción |
|--------|-------------|
| `getAllCategories()` | Listado paginado |
| `findCategoryById()` | Búsqueda por ID |
| `findCategoryBySlug()` | Búsqueda por slug |
| `createCategory()` | Crear categoría |
| `updateCategory()` | Actualizar categoría |
| `deleteCategory()` | Eliminar (valida productos asociados) |

### InventoryValuationService

Calcula el valor del inventario usando estrategias:

```php
$service = new InventoryValuationService(new FifoValuation());
$value = $service->calculate($product);
```

---

## 📤 Postman Collection

Se incluye una colección de Postman lista para importar con todos los endpoints documentados.

### Importar Colección

1. Abre Postman
2. Haz clic en **Import**
3. Selecciona el archivo [`postman-collection.json`](postman-collection.json)

### Variables de Entorno

| Variable | Valor | Descripción |
|----------|-------|-------------|
| `baseUrl` (Local) | `http://localhost:8000/api` | URL base de la API en desarrollo |
| `baseUrl` (Producción) | `https://stockmaster.diegochacondev.es/api` | URL base de la API en producción |
| `accessToken` | (auto-configurada) | Token OAuth2 |
| `productId` | (auto-configurada) | ID del producto para pruebas |

---

## 📖 Documentación API (Scramble)

La documentación de la API se genera automáticamente usando **Scramble** (OpenAPI 3.0).

### Acceso a la Documentación

| Entorno | URL |
|---------|-----|
| Local | `http://localhost:8000/docs/api` |
| Producción | `https://stockmaster.diegochacondev.es/docs/api` |

### Características

- **Generación automática** desde código PHP
- **Try It** habilitado para probar endpoints
- **Tema oscuro** con layout responsive
- **Exportación** a OpenAPI JSON en [`api.json`](api.json)

### Configuración

La configuración está en [`config/scramble.php`](config/scramble.php):

```php
'ui' => [
    'title' => 'StockMaster API',
    'theme' => 'light',
    'hide_try_it' => false,
    'layout' => 'responsive',
],
```

---

## 🤝 Contribuciones

1. Haz fork del repositorio
2. Crea una rama de característica (`git checkout -b feature/nueva-funcionalidad`)
3. Guarda tus cambios (`git commit -m 'Agrega nueva funcionalidad'`)
4. Envía la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

---

## 📄 Licencia

StockMaster API es software de código abierto bajo licencia MIT.
