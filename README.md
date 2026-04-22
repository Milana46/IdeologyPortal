
Актуальные версии в проекте

- Docker: `27.3.1`
- Docker Compose: `v2.30.3-desktop.1`
- Node.js: `v22.22.0`
- npm: `10.9.4`
- @wordpress/env (wp-env): `10.38.0`
- WordPress core (локальная среда): `6.7.1` (из `.wp-env.json`)
- PHP минимум для плагина `portal-core`: `8.1+`

Что установить на сервер/рабочую машину

- Docker Engine `27.x+`
- Docker Compose `v2.x`
- Node.js `18+` (в проекте сейчас `22.22.0`)
- npm `10+`

Команды поднятия проекта 

npm run start

npx wp-env stop

Команда для поднятия темы WordPress 

npx wp-env run cli wp theme activate portal-theme

