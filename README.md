# Fraud Detector

Herramienta de consola para detectar lecturas sospechosas a partir de archivos de entrada (CSV o XML).

## Instalación

Levanta el contenedor e instala las dependencias:

```bash
docker compose up -d
docker compose exec app composer install
```

## Tests

```bash
docker compose exec app ./vendor/bin/phpunit tests
```

## Comando `app:detect-fraud`

```bash
docker compose exec app php bin/console app:detect-fraud <ruta-al-archivo> [--format=table|csv]
```

- **`<ruta-al-archivo>`** (obligatorio): ruta al fichero de lecturas (CSV o XML).
- **`--format` / `-f`** (opcional): formato de salida. Valores: `table` (por defecto) o `csv`.

**Ejemplos:**

```bash
docker compose exec app php bin/console app:detect-fraud ./data/lecturas.csv
docker compose exec app php bin/console app:detect-fraud ./data/lecturas.xml --format=csv
```
