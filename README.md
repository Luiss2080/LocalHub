# 🖥️ LocalHub

> Un panel de inicio "Cyberpunk" para Laragon que reemplaza la página de
> bienvenida por defecto: lista todos tus proyectos locales ordenados por
> actividad reciente y te da acceso rápido a phpMyAdmin y otras
> herramientas de desarrollo, todo en una sola página PHP sin
> dependencias de backend.

## Características

Verificadas directamente en `index.php`:

- **Listado automático de proyectos**: escanea los directorios dentro de
  la carpeta donde vive el panel (típicamente `www/` de Laragon) e ignora
  `.git`, `.idea`, `vscode`, `node_modules` y `vendor`.
- **Orden por actividad**: los proyectos se muestran del más
  recientemente modificado (`filemtime`) al más antiguo.
- **Búsqueda instantánea**: campo de búsqueda del lado del cliente que
  filtra la grilla en tiempo real (atajo de teclado `/` para enfocarlo).
- **Fijar proyectos (pin)**: cada tarjeta tiene un icono para fijar un
  proyecto; los fijados siempre aparecen primero. Se guarda en
  `localStorage` del navegador (por dispositivo, no se sincroniza).
- **Alternar orden**: botón para alternar entre orden por actividad y
  orden alfabético (los proyectos fijados siempre van primero en ambos
  modos).
- **Dos vistas de grilla**: normal y compacta.
- **Sidebar colapsable**, con contador de proyectos y versión de PHP en
  ejecución (`phpversion()`).
- **Accesos rápidos**: enlace directo a `/phpmyadmin`; "Terminal" y
  "Virtual Host" son accesos de marcador de posición (`href="#"`) listos
  para conectarse a una herramienta real más adelante.
- **Enlaces de desarrollo**: GitHub, Stack Overflow y ChatGPT, abiertos
  en una pestaña nueva.
- **Modo pantalla completa** y botón de recarga manual.
- Tema visual "Cyberpunk Red" con cuadrícula animada de fondo, totalmente
  en CSS/JS, sin librerías de frontend.

## Cómo usar

1. Copia `index.php` (y la carpeta `src/`, ver más abajo) a la raíz de tu
   carpeta de proyectos de Laragon (por defecto `C:\laragon\www`).
2. Abre `http://localhost` en el navegador: verás la grilla con todas las
   carpetas de proyecto que tengas junto a `index.php`.
3. Haz clic en cualquier tarjeta para abrir ese proyecto en
   `localhost/<nombre-del-proyecto>`.

## Instalación y uso local

Requiere PHP 8.0+ (probado con PHP 8.5) y, opcionalmente, Composer si vas
a ejecutar las pruebas automatizadas.

```bash
git clone https://github.com/Luiss2080/Panel_Laragon.git
cd Panel_Laragon

# Servir con el servidor embebido de PHP para probarlo sin Laragon:
php -S localhost:8000
```

Para usarlo como panel real de Laragon, coloca los archivos en la raíz de
tu carpeta `www` (donde Laragon ya sirve tus demás proyectos como
subcarpetas hermanas).

## Tecnologías

- PHP puro (sin framework), un único punto de entrada (`index.php`).
- HTML5 + CSS3 (variables CSS, grid, backdrop-filter) y JavaScript vanilla
  para la interactividad del lado del cliente (búsqueda, pines, vistas).
- Font Awesome y las fuentes Outfit / JetBrains Mono vía CDN.
- Composer + PHPUnit para las pruebas unitarias de la lógica de
  ordenamiento (`src/ProjectSorter.php`).
- GitHub Actions para lint de PHP (`php -l`) y ejecución de PHPUnit en
  cada push/PR.

## Tests

```bash
composer install
composer exec phpunit
# o directamente:
vendor/bin/phpunit
```

Las pruebas cubren `ProjectSorter`, la lógica pura de "ordenar por
actividad" extraída de `index.php` (orden descendente por timestamp,
lista vacía, un solo proyecto, empates de timestamp, timestamps
negativos/cero, y que el arreglo de entrada no se mute).

## Licencia

Este repositorio no incluye un archivo `LICENSE`. Sin uno, por defecto
aplican los derechos de autor exclusivos del autor (todos los derechos
reservados): técnicamente no está autorizado su uso, copia o
distribución por terceros hasta que se agregue una licencia explícita.
Si la intención es que el proyecto sea de código abierto, se recomienda
añadir un archivo `LICENSE` (por ejemplo MIT) cuanto antes.
