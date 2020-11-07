# Toko Masker 4

## Build & Run

```bash
docker-compose build
docker-compose up -d

# Populate database (do it only once during first run)
docker-compose run web python manage.py migrate
docker-compose run web python manage.py populate_db
```