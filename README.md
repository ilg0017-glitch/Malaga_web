# Málaga CF - Web Pro ⚽

Este es un proyecto web dedicado al Málaga CF, que incluye información sobre la plantilla, clasificación, tienda oficial y noticias del club.

## Características
- ⚽ **Plantilla Interactiva**: Información detallada de los jugadores.
- 📊 **Clasificación**: Tabla actualizada de la liga.
- 🛍️ **Tienda**: Acceso a productos oficiales.
- 📰 **Noticias**: Últimas novedades sobre el equipo.
- 🎫 **Abonos**: Sistema de suscripciones (Joven, General, VIP).
- 🗄️ **Base de Datos**: Backend con MariaDB para gestión de datos.

## Estructura del Proyecto

```
Malaga_p_2/
├── MalagaCF_Pro/
│   └── MalagaCF_Pro/
│       ├── index.html          # Página principal
│       └── img/                # Imágenes y recursos visuales
├── database/
│   ├── malaga_cf_db.sql        # Dump de la base de datos
│   ├── db_connection.php       # Archivo de conexión PDO
│   └── README.md               # Documentación de la BD
└── README.md                   # Este archivo
```

## Base de Datos

El proyecto utiliza **MariaDB 10.4** con las siguientes tablas:

| Tabla | Descripción |
|-------|-------------|
| `users` | Usuarios registrados |
| `players` | Plantilla de jugadores |
| `matches` | Partidos y resultados |
| `news` | Noticias del club |
| `products` | Tienda oficial |
| `orders` / `order_items` | Pedidos |
| `subscription_types` / `user_subscriptions` | Abonos |

📖 Ver [documentación completa de la BD](database/README.md)

## Requisitos

- Navegador web moderno
- **XAMPP** / **MAMP** / **WAMP** (para el backend PHP + MariaDB)
- PHP 8.2+
- MariaDB 10.4+ / MySQL 5.7+

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/ilg0017-glitch/Malaga_web.git
   ```

2. Importa la base de datos:
   ```bash
   mysql -u root -p < database/malaga_cf_db.sql
   ```
   O usa phpMyAdmin: Importar → `database/malaga_cf_db.sql`

3. Abre `MalagaCF_Pro/MalagaCF_Pro/index.html` en tu navegador.

---
Proyecto desarrollado para la gestión y visualización de contenidos del Málaga CF.
