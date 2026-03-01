<?php

namespace App\Livewire;

use Livewire\Component;

class WelcomeView extends Component
{
    public array $stack = [];
    public array $apiEntities = [];

    public function mount()
    {
        $this->stack = [
            ['Database', 'MariaDB 10.11', 'Motor InnoDB con soporte para transacciones ACID y Vistas de valoración.'],
            ['Core Framework', 'Laravel 11', 'Estructura moderna con sistema de servicios y repositorios desacoplados.'],
            ['Language', 'PHP 8.2+', 'Uso de Strict Typing y Property Promotion para un código impecable.'],
            ['Auth Standard', 'OAuth2 / Passport', 'Implementación de tokens industriales para aplicaciones de alta seguridad.'],
            ['RBAC System', 'Spatie Permission', 'Control de acceso granular basado en roles: Admin, Worker y Viewer.'],
            ['Documentation', 'Scramble OpenAPI', 'Documentación viva 100% interactiva sin mantenimiento manual.'],
            ['Quality Control', 'TDD (PHPUnit)', 'Metodología Red-Green-Refactor aplicada a cada funcionalidad crítica.'],
            ['Arch Patterns', 'S.O.L.I.D.', 'Cumplimiento estricto de principios para alta mantenibilidad.'],
        ];

        $this->apiEntities = [
            'Autenticación y Perfil' => [
                ['POST', '/api/auth/register', 'Registro de Usuario', 'Requiere name (max:255), email (unique, format) y password (min:8, upper, symbol + confirmed).', 'Público', 'auth.register'],
                ['POST', '/api/auth/login', 'Inicio de Sesión', 'Requiere email (format) y password. Limitado a 5 intentos/minuto.', 'Público', 'auth.login'],
                ['GET', '/api/user', 'Perfil Actual', 'Retorna JSON con datos básicos y roles asociados al token Bearer.', 'Autenticado', 'user.profile'],
            ],
            'Categorías' => [
                ['GET', '/api/categories', 'Listar Categorías', 'Retorna todas las categorías con slugs automáticos.', 'Admin, Worker, Viewer', 'categories.index'],
                ['POST', '/api/categories', 'Nueva Categoría', 'Requiere name (req, max:255, unique).', 'Admin', 'categories.store'],
                ['GET', '/api/categories/{id}', 'Mostrar una Categoría', 'Acceso directo por ID numérico.', 'Admin, Worker, Viewer', 'categories.show'],
                ['PUT', '/api/categories/{id}', 'Actualizar una Categoría', 'Acceso directo por ID numérico.', 'Admin', 'categories.update'],
                ['DELETE', '/api/categories/{id}', 'Borrado Seguro', 'Falla con 422 si hay productos asociados (Restringido).', 'Admin', 'categories.destroy'],
            ],
            'Gestión de Productos' => [
                ['GET', '/api/products', 'Listar Productos', 'Soporta paginación. Roles: Todos.', 'Admin, Worker, Viewer', 'products.index'],
                ['POST', '/api/products', 'Crear Producto', 'Requiere SKU único, unit_price/unit_cost (numeric, min:0) y valuation_strategy (fifo, lifo, avg).', 'Admin', 'products.store'],
                ['GET', '/api/products/{id}', 'Ver Detalle', 'Búsqueda por ID. Retorna relaciones de categoría y proveedor.', 'Admin, Worker, Viewer', 'products.show'],
                ['PUT/PATCH', '/api/products/{id}', 'Actualizar', 'Permite actualización parcial. SKU único excluyendo actual.', 'Admin', 'products.update'],
                ['DELETE', '/api/products/{id}', 'Eliminar', 'Protección de integridad: Falla si existen movimientos o stock asociado.', 'Admin', 'products.destroy'],
                ['GET', '/api/products/sku/{sku}', 'Buscar por SKU', 'Búsqueda de producto por código SKU único.', 'Admin, Worker, Viewer', 'products.show-by-sku'],
                ['GET', '/api/products/warehouse/{warehouse}', 'Productos por Almacén', 'Lista productos disponibles en un almacén específico.', 'Admin, Worker, Viewer', 'products.by-warehouseId'],
                ['GET', '/api/products/supplier/{supplier}', 'Productos por Proveedor', 'Lista productos asociados a un proveedor específico.', 'Admin, Worker, Viewer', 'products.by-supplierId'],
                ['GET', '/api/products/category/{category}', 'Productos por Categoría', 'Lista productos pertenecientes a una categoría específica.', 'Admin, Worker, Viewer', 'products.by-categoryId'],
            ],
            'Almacenes y Logística' => [
                ['GET', '/api/warehouses', 'Listar Almacenes', 'Vista resumida con nombres y ubicaciones.', 'Admin, Worker, Viewer', 'warehouses.index'],
                ['POST', '/api/warehouses', 'Crear Almacén', 'Requiere name (unique, max:255), location (max:500) y capacity (min:0).', 'Admin', 'warehouses.store'],
                ['GET', '/api/warehouses/{id}', 'Ficha de Almacén', 'Acceso directo por ID numérico.', 'Admin, Worker, Viewer', 'warehouses.show'],
                ['PUT', '/api/warehouses/{id}', 'Actualizar Almacén', 'Acceso directo por ID numérico.', 'Admin, Worker, Viewer', 'warehouses.update'],
                ['GET', '/api/warehouses/{id}/capacity', 'Obtiene las métricas de capacidad de un almacén.', 'Acceso directo por ID numérico', 'Admin, Worker, Viewer', 'warehouses.capacity'],
                ['GET', '/api/warehouses/slug/{slug}', 'Búsqueda por slug', 'Resolución automática de modelo vía slug para URLs amigables.', 'Admin, Worker, Viewer', 'warehouses.show-by-slug'],
                ['POST', '/api/warehouses/transfer', 'Mover Stock entre almacenes', 'Requiere product_id, source/dest (different) y quantity (min:1). Destino debe estar ACTIVO.', 'Admin, Worker', 'warehouses.transfer'],
                ['GET', '/api/warehouses/with-capacity', 'Métricas Capacidad', ' lista de todos los almacenes, incluidas sus métricas de capacidad.', 'Admin, Worker, Viewer', 'warehouses.with-capacity'],
                ['GET', '/api/warehouses/with-inventory-count', 'Métricas Capacidad', ' lista de todos los almacenes, incluidas sus métricas de capacidad y el recuento de inventario.', 'Admin, Worker, Viewer', 'warehouses.with-inventory-count'],
                ['DELETE', '/api/warehouses/{id}', 'Eliminar un Almacén', 'Elimina un almacén por ID numérico. Falla si hay si hay inventario asociado.', 'Admin', 'warehouses.destroy'],
            ],
            'Gestión de Proveedores' => [
                ['GET', '/api/suppliers', 'Listar Proveedores', 'Vista resumida con nombres, slugs y estado (is_active).', 'Admin, Worker, Viewer', 'suppliers.index'],
                ['POST', '/api/suppliers', 'Crear Proveedor', 'Requiere name (unique, max:255), email, phone y address.', 'Admin', 'suppliers.store'],
                ['GET', '/api/suppliers/{id}', 'Ficha de Proveedor', 'Acceso directo por ID numérico.', 'Admin, Worker, Viewer', 'suppliers.show'],
                ['PUT', '/api/suppliers/{id}', 'Actualizar Proveedor', 'Acceso directo por ID numérico.', 'Admin', 'suppliers.update'],
                ['GET', '/api/suppliers/slug/{slug}', 'Búsqueda por slug', 'Resolución automática de modelo vía slug para URLs amigables.', 'Admin, Worker, Viewer', 'suppliers.show-by-slug'],
                ['PATCH', '/api/suppliers/{id}/activate', 'Activar Proveedor', 'Marca el proveedor como activo (is_active = true).', 'Admin', 'suppliers.activate'],
                ['PATCH', '/api/suppliers/{id}/deactivate', 'Desactivar Proveedor', 'Marca el proveedor como inactivo (is_active = false).', 'Admin', 'suppliers.deactivate'],
                ['GET', '/api/suppliers/{id}/performance', 'Métricas de Rendimiento', 'Retorna total_products, active_products y total_inventory_value.', 'Admin', 'suppliers.performance'],
                ['DELETE', '/api/suppliers/{id}', 'Eliminar un Proveedor', 'Elimina un proveedor por ID numérico. Falla si hay productos asociados.', 'Admin', 'suppliers.destroy'],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.welcome-view');
    }
}
