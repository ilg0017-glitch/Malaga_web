# Base de Datos - Málaga CF

## Estructura de la Base de Datos (`malaga_cf_db`)

La base de datos utiliza **MariaDB 10.4.28** con charset `utf8mb4_unicode_ci`.

### Tablas

| Tabla | Descripción |
|-------|-------------|
| `users` | Usuarios registrados (datos personales, DNI, email, login) |
| `addresses` | Direcciones de envío de usuarios |
| `players` | Jugadores de la plantilla (nombre, dorsal, posición, imagen) |
| `team_categories` | Categorías de equipo (Primer Equipo, Leyendas, Academia) |
| `matches` | Partidos programados y resultados |
| `news` | Noticias del club |
| `products` | Productos de la tienda oficial |
| `orders` | Pedidos de usuarios |
| `order_items` | Detalle de productos por pedido |
| `subscription_types` | Tipos de abono (Joven, General, VIP) |
| `user_subscriptions` | Abonos adquiridos por usuarios |

### Diagrama de relaciones

```
users ──┬── addresses
        ├── orders ── order_items ── products
        └── user_subscriptions ── subscription_types

team_categories ── players
```

## Cómo importar la base de datos

### Opción 1: phpMyAdmin
1. Abre phpMyAdmin (`http://localhost/phpmyadmin`)
2. Crea una nueva base de datos llamada `malaga_cf_db`
3. Selecciona la base de datos → **Importar** → Selecciona `malaga_cf_db.sql` → **Continuar**

### Opción 2: Línea de comandos
```bash
mysql -u root -p < malaga_cf_db.sql
```

## Conexión desde PHP

Usa el archivo `db_connection.php` incluido:

```php
require_once 'database/db_connection.php';

// Ejemplo: obtener todos los jugadores
$stmt = $pdo->query("SELECT p.*, tc.nombre AS categoria FROM players p JOIN team_categories tc ON p.category_id = tc.id");
$jugadores = $stmt->fetchAll();
```

## ⚠️ Notas de seguridad

- **No subir credenciales de producción** a Git. Usa variables de entorno.
- El archivo `db_connection.php` usa credenciales por defecto de XAMPP (desarrollo local).
- En producción, configura un usuario con permisos limitados.
