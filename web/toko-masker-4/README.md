# Toko Masker 4

## Deploy

```bash
docker-compose build
docker-compose up -d

# Populate database (do it only once during first run)
docker-compose run web python manage.py migrate
docker-compose run web python manage.py populate_db
```

## Scenario

No encryption key given during the competition so the expected solution is chosen-plaintext attack to forge valid encrypted bytes for 100 quantity of N99 mask with 0 price.
