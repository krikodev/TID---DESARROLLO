# API móvil de consulta — TID

API independiente para Flutter, PHP 8.2+ y MySQL/MariaDB. Todos los archivos nuevos están en `mobile-api/`. No modifica controladores, modelos, sesiones web, contraseñas ni tablas del sistema general. La API escribe exclusivamente su propio archivo SQLite de sesiones y límites de solicitudes.

## Qué se corrigió

- Login compatible con el AES de `admin/libs/modules/Security.php`. Acepta también `password_hash` si una instalación ya lo usa. Nunca transforma contraseñas existentes ni elimina espacios de la contraseña.
- La API anterior necesitaba `config/Security.php`, no incluido entre los adjuntos. Ahora `bin/init-local.php` obtiene la configuración de compatibilidad de la copia local del sistema.
- El login antiguo seleccionaba columnas no necesarias y aceptaba cualquier usuario activo. Ahora usa los campos verificados en los modelos y permite tipos `INTERNO` y `DESARROLLO`, igual que el login general. Rechaza correos duplicados entre cuentas internas activas.
- Cada empresa tiene su propio dominio lógico y conexión. Se rechaza configurar dos empresas con el mismo host, puerto y nombre de base. Esto evita la mezcla evidente del archivo antiguo; el administrador también debe evitar aliases que resuelvan a la misma BD y usar usuarios MySQL distintos.
- El token aleatorio se guarda en el servidor solo como SHA-256, vence y se revoca al cerrar sesión. Cambiar el header de empresa no permite reutilizarlo.
- CORS centralizado permite `Content-Type`, `Authorization` y `X-Company-Domain`. OPTIONS se resuelve antes de conectar a la empresa.
- Consultas parametrizadas, paginación, respuestas HTTP coherentes, esquema comprobable, errores sin SQL/credenciales, límites de intentos y límites generales de solicitudes.
- Resumen con cifras reales y consultas que no duplican encomiendas al tener varios productos o detalles de venta.
- `fotos_evidencia` es opcional: si no existe, devuelve `null`. No agrega esa columna ni recorre carpetas de imágenes globales.

## 1. Descargar la rama

En PowerShell:

```powershell
git clone --branch codex/api-multiempresa-local https://github.com/krikodev/TID---DESARROLLO.git
cd TID---DESARROLLO\mobile-api
```

Si ya tienes el repositorio, guarda tu trabajo local antes de cambiar de rama:

```powershell
git fetch origin
git switch codex/api-multiempresa-local
cd mobile-api
```

El nombre anterior `App-Encominedas-` devolvió 404 durante la revisión; el usuario confirmó `TID---DESARROLLO` como sistema general.

## 2. PHP y base de datos local

Inicia MySQL desde XAMPP. Usa la BD que ya tiene tus datos; la API **no importa ni migra tablas**. Si la BD se llama diferente de `transporte`, configúrala con su nombre real.

Comprueba que el PHP de consola corresponde a XAMPP:

```powershell
C:\xampp\php\php.exe -v
C:\xampp\php\php.exe -m
```

Necesita `pdo_mysql`, `pdo_sqlite` y `openssl`. En `C:\xampp\php\php.ini` habilita esas extensiones si faltan. SQLite debe ser 3.35+ por el límite de solicitudes atómico. Reinicia Apache si usas Apache.

Recomendado: crea un usuario MySQL con SELECT únicamente. Ejecuta en phpMyAdmin como administrador, sustituyendo el nombre de BD y contraseña por valores locales:

```sql
CREATE USER 'tid_api'@'127.0.0.1' IDENTIFIED BY 'TU_PASSWORD_LOCAL';
GRANT SELECT ON transporte.* TO 'tid_api'@'127.0.0.1';
```

En instalaciones que resuelven la conexión como localhost, crea el usuario equivalente en `localhost` y concede SELECT en la misma BD. Este usuario no es el correo del operador.

El adaptador abre la conexión `utf8mb4` con prepares nativos y `SET SESSION TRANSACTION READ ONLY`. No necesita permisos CREATE/INSERT/UPDATE/DELETE sobre la BD de negocio.

## 3. Generar configuración privada

```powershell
$env:TID_DB_NAME = 'transporte'
$env:TID_DB_USER = 'tid_api'
$env:TID_DB_PASSWORD = 'TU_PASSWORD_LOCAL'
C:\xampp\php\php.exe bin\init-local.php
C:\xampp\php\php.exe bin\check.php
```

`init-local.php` crea `config/local.php` solo si no existe y copia las claves AES desde el archivo local del sistema, sin imprimirlas. Si la base usa otra clave AES de otra instalación, configura `legacy_key` y `legacy_iv` con los valores de **esa instalación**. Un valor incorrecto produce credenciales inválidas.

Revisa `config/local.php`: host, puerto, nombre de BD, usuario, contraseña y empresa. Para una prueba provisional puedes usar tu usuario MySQL local existente, pero para la instalación final utiliza SELECT únicamente. Nunca subas `local.php` ni `storage/` al repositorio.

`bin/check.php` verifica las columnas utilizadas por la API sin modificar datos. Si falla, revisa los nombres de BD y campos reales; no ejecutes migraciones para intentar resolverlo. Columnas requeridas: las SELECT enumeradas en `src/Repository.php::health()`.

## 4. Iniciar API local sin tocar Apache ni el sistema

Desde `mobile-api/`:

```powershell
C:\xampp\php\php.exe -S 0.0.0.0:8080 -t public bin/router.php
```

Mantén esta consola abierta. Este servidor PHP es exclusivamente para desarrollo. MySQL continúa ejecutándose en XAMPP. No hace falta ejecutar Composer para usar la API; no tiene dependencias descargables.

Prueba desde la PC:

```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8080/v1/health' -Headers @{'X-Company-Domain'='tid.net.pe'}
```

Debe responder `success: true`, `api_version: v1`, `read_only: true` y la empresa. Una URL genérica de la web del sistema no equivale a la URL de esta API.

## 5. Configurar Flutter

Usa la rama `codex/login-api-multiempresa` de `Aplicativo-web-tid-flutter`.

| Dónde ejecutas Flutter | URL de API | Empresa |
|---|---|---|
| Emulador Android oficial | `http://10.0.2.2:8080/` | `tid.net.pe` |
| Teléfono físico en el mismo Wi-Fi | `http://IP_DE_TU_PC:8080/` | `tid.net.pe` |
| Flutter web en la misma PC | `http://localhost:8080/` | `tid.net.pe` |
| Windows desktop en la misma PC | `http://127.0.0.1:8080/` | `tid.net.pe` |

En teléfono físico no uses localhost ni 10.0.2.2. Usa `ipconfig` en Windows para obtener la IPv4 de tu PC. Permite el puerto 8080 en Windows Firewall para la red privada. Ambos dispositivos deben compartir red y no estar aislados por el router.

La app valida la conexión antes de guardar y conserva la configuración anterior si falla. La URL es la dirección donde corre la API; el dominio es el identificador de empresa y no tiene que resolver DNS durante estas pruebas locales.

Android permite HTTP solo en el modo debug. La versión release exige HTTPS. Para Flutter web fija el puerto para coincidir con CORS:

```powershell
flutter run -d chrome --web-port 5173
```

## 6. Login local

Usa el **correo y contraseña de un operador interno activo del sistema general**. No se crean cuentas nuevas, no se reemplazan contraseñas y no se usa la contraseña de MySQL como login de Flutter.

Ejemplo desde PowerShell, sin almacenar el password en un archivo:

```powershell
$headers = @{'X-Company-Domain'='tid.net.pe'}
$body = @{email='TU_CORREO'; password='TU_PASSWORD'} | ConvertTo-Json
$session = Invoke-RestMethod -Method Post -Uri 'http://127.0.0.1:8080/v1/auth/login' -Headers $headers -ContentType 'application/json' -Body $body
$headers['Authorization'] = 'Bearer ' + $session.access_token
Invoke-RestMethod -Uri 'http://127.0.0.1:8080/v1/auth/me' -Headers $headers
```

El token dura 8 horas por defecto. Todos los intentos de login cuentan para un máximo de 10 por IP/empresa y por correo/empresa en 15 minutos. Las consultas tienen un límite adicional de 300 solicitudes por IP/empresa/minuto. Flutter conserva la sesión en memoria: al cerrar por completo la app debes iniciar sesión de nuevo; la configuración del servidor sí permanece. Esto evita guardar un token sensible en SharedPreferences.

## 7. Endpoints v1

Todos necesitan `X-Company-Domain`. Todos salvo health y login requieren `Authorization: Bearer TOKEN`.

| Método | Ruta | Datos |
|---|---|---|
| GET | `/v1/health` | comprueba configuración, empresa, conexión y esquema |
| POST | `/v1/auth/login` | JSON `email`, `password` (también acepta form `email`, `pass`) |
| GET | `/v1/auth/me` | sesión actual |
| POST | `/v1/auth/logout` | objeto JSON `{}` |
| GET | `/v1/resumen` | `fecha` opcional `YYYY-MM-DD`; por defecto hoy en Lima |
| GET | `/v1/encomiendas/dni` | `dni` de 8 dígitos, `page`, `limit` |
| GET | `/v1/encomiendas/historial` | `fecha`, `filtro` opcional, `page`, `limit` |
| GET | `/v1/encomiendas/tracking` | `codigo_tracking` |
| GET | `/v1/encomiendas/comprobante` | `serie`, `correlativo`, `fecha`, `page`, `limit` |
| GET | `/v1/encomiendas/imagenes` | `codigo_tracking` |

Las consultas retornan `success`, `data`, `pagination` y `request_id`. Un resultado vacío es exitoso con `data: []`. `tracking` es el nombre corregido; `traking` se conserva como alias para las vistas existentes. `limit` predeterminado es 50, máximo 100. Flutter muestra las primeras 50 consultas por búsqueda; la API permite solicitar las siguientes páginas.

`scope: terminal` restringe resultados a la terminal del usuario. Pendientes solo de destino; historial/tracking/comprobantes admiten origen o destino. `scope: company` permite consultar toda esa empresa y debe habilitarse deliberadamente por el administrador para los usuarios internos autorizados; no determina permisos por columna de la tabla `permiso`.

La API no registra entregas ni sube fotos porque el alcance pedido es solo consulta. La app explica que la entrega se registra en el sistema general. Las imágenes son URLs previamente existentes en `fotos_evidencia`, no un nuevo servicio de almacenamiento. No se valida la privacidad ni disponibilidad de proveedores externos de esas URLs.

## 8. Otra empresa

Usa `config/local.example.php` como referencia y añade la empresa a `local.php`. JR Cargo necesita una **segunda BD** con sus propios datos, credenciales SELECT y claves AES correspondientes. No configures ambas empresas con `transporte`. No dupliques producción para pruebas; usa datos locales de prueba.

Cambiar de empresa en Perfil cierra la sesión. El servidor valida que el token pertenece al dominio elegido. Nunca recibe nombres de bases ni credenciales desde Flutter.

## 9. Apache/XAMPP y futuro dominio

Alternativa local con Apache: copia el repositorio dentro de `htdocs` y usa una URL como `http://IP/TID---DESARROLLO/mobile-api/public/`. Las reglas `.htaccess` requieren `mod_rewrite` y AllowOverride; la carpeta superior bloquea configuración, estado, código y pruebas. Si otro rewrite del sitio interfiere, usa el servidor PHP del paso 4 o un VirtualHost separado apuntando directamente a `mobile-api/public`.

Para nube, configura un VirtualHost independiente cuyo DocumentRoot sea **mobile-api/public**, utiliza HTTPS y cambia `environment` a `production`. El proceso PHP debe recibir HTTPS desde el servidor web; con proxy termina TLS de manera confiable y configura el servidor, no confíes en headers arbitrarios. Mantén `config/` y `storage/` fuera del acceso público y limita los orígenes web reales. Añade una cuenta MySQL SELECT por empresa. Las claves y credenciales se configuran exclusivamente en el servidor. Para varias réplicas, reemplaza SQLite por un almacén central de sesiones y límites; este diseño actual sirve una única instancia.

No se ha desplegado ningún dominio ni modificado `main`.

## 10. Pruebas y límite de validación

```powershell
C:\xampp\php\php.exe tests\run.php
C:\xampp\php\php.exe bin\check.php
```

Para comprobar también el parsing y transporte HTTP con fixtures, si tienes Python instalado:

```powershell
python tests\http_smoke.py --php C:\xampp\php\php.exe
```

`tests/run.php` verifica autenticación, tenant, terminal, consultas, paginación, estado activo, logout, expiración, CORS, HTTPS y solo lectura usando fixtures SQLite. No necesita tu BD ni genera usuarios reales. La consulta a MySQL real se valida con `bin/check.php` en tu PC y con el login de tu usuario; las pruebas SQLite no reemplazan esa comprobación.

Validación realizada: 45 verificaciones PHP y 11 verificaciones HTTP con fixtures, PHP 8.5.11. Cliente Flutter: 15 pruebas con Flutter 3.38.5 / Dart 3.10.4. No se contó con una conexión a la BD MySQL local del usuario.
