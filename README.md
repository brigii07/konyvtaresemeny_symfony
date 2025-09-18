# Könyvtár megnyitó esemény - REST API 
Modern Symfony alkalmazás könyvtár megnyitási események kezelésére fix kapacitással és várólistával.

# Funkciók

- *Jelentkezés kezelés* - 50 fős fix kapacitás
- *Automatikus várólistázás* - betelt esemény esetén
- *Előléptetés* - lemondáskor automatikus
- *Egyedi jelentkezés* - felhasználónként csak egyszer
- *Valós idejű státusz* - REST API végpontokon keresztül
- *Modern frontend* - responsive design, animációkkal

# Technológiák
- *Backend*: Symfony 6.x, Doctrine ORM
- *Frontend*: HTML5, CSS3, JavaScript
- *Adatbázis*: SQLite (MySQL kompatibilis)
- *API*: REST JSON

## 📦 Telepítés

```bash
#Az alábbiakat telepítettem én:
composer require symfony/orm-pack
composer require symfony/validator
composer require symfony/twig-bundle
composer require symfony/serializer
composer require --dev symfony/maker-bundle
composer require --dev doctrine/doctrine-fixtures-bundle

# Függőségek telepítése:
composer install

# Adatbázis létrehozása:
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Fejlesztői szerver indítása:
symfony server:start
```

# Frontend

A modern, responsive frontend tartalmazza:
- *Valós idejű statisztikák*
- *Interaktív jelentkezési form*
- *Állapot ellenőrzés*
- *Automatikus frissítés** (30 mp)
- *Animációk és hover effektek*

Elérhető: `http://localhost:8000`

# Hibakezelés

- Duplicate jelentkezés védelem
- API hibák JSON válaszokkal
- Frontend toast üzenetek
- Input validáció

# Biztonság
- Input sanitization
- SQL injection védelem (Doctrine ORM)
- XSS védelem
- CSRF token support ready
