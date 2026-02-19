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
                ['POST', '/api/auth/register', 'Registro de Usuario', 'Requiere name (max:255), email (unique, format) y password (min:8, upper, symbol + confirmed).', 'Público'],
                ['POST', '/api/auth/login', 'Inicio de Sesión', 'Requiere email (format) y password. Limitado a 5 intentos/minuto.', 'Público'],
                ['GET', '/api/user', 'Perfil Actual', 'Retorna JSON con datos básicos y roles asociados al token Bearer.', 'Autenticado'],
            ],
            'Gestión de Productos' => [
                ['GET', '/api/products', 'Listar Productos', 'Soporta paginación. Roles: Todos.', 'Viewer+'],
                ['POST', '/api/products', 'Crear Producto', 'Requiere SKU único, unit_price/unit_cost (numeric, min:0) y valuation_strategy (fifo, lifo, avg).', 'Admin'],
                ['GET', '/api/products/{id}', 'Ver Detalle', 'Búsqueda por ID. Retorna relaciones de categoría y proveedor.', 'Viewer+'],
                ['PUT/PATCH', '/api/products/{id}', 'Actualizar', 'Permite actualización parcial. SKU único excluyendo actual.', 'Admin'],
                ['DELETE', '/api/products/{id}', 'Eliminar', 'Protección de integridad: Falla si existen movimientos o stock asociado.', 'Admin'],
            ],
            'Almacenes y Logística' => [
                ['GET', '/api/warehouses', 'Listar Almacenes', 'Vista resumida con nombres y ubicaciones.', 'Viewer+'],
                ['POST', '/api/warehouses', 'Crear Almacén', 'Requiere name (unique, max:255), location (max:500) y capacity (min:0).', 'Admin'],
                ['GET', '/api/warehouses/{id}', 'Ficha de Almacén', 'Acceso directo por ID numérico.', 'Viewer+'],
                ['GET', '/api/warehouses/slug/{slug}', 'Búsqueda slug', 'Resolución automática de modelo vía slug para URLs amigables.', 'Viewer+'],
                ['POST', '/api/warehouses/transfer', 'Mover Stock', 'Requiere product_id, source/dest (different) y quantity (min:1). Destino debe estar ACTIVO.', 'Admin, Worker'],
                ['GET', '/api/warehouses/with-capacity', 'Métricas Capacidad', 'Algoritmo de cálculo de ocupación porcentual en tiempo real.', 'Viewer+'],
            ],
            'Categorías' => [
                ['GET', '/api/categories', 'Listar Categorías', 'Retorna todas las categorías con slugs automáticos.', 'Viewer+'],
                ['POST', '/api/categories', 'Nueva Categoría', 'Requiere name (req, max:255, unique).', 'Admin'],
                ['DELETE', '/api/categories/{id}', 'Borrado Seguro', 'Falla con 422 si hay productos asociados (Restringido).', 'Admin'],
            ]
        ];
    }

    public function render()
    {
        return view('livewire.welcome-view');
    }
}
