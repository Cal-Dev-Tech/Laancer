### Local development - Laancer.com

- **App URLs**
  - https://localhost:9443/
  - https://localhost:9443/admin/integrations-manage
  - Dev server (plain HTTP): http://127.0.0.1:9090/

- **Start**
  - make -C /Volumes/SSK_SSD/Work/Laancer/public_html start

- **Stop**
  - make -C /Volumes/SSK_SSD/Work/Laancer/public_html stop

- **Status**
  - make -C /Volumes/SSK_SSD/Work/Laancer/public_html status

- **What it does**
  - Starts Laravel at 127.0.0.1:9090.
  - Starts Caddy TLS reverse proxy on 9443 and serves static files from `core/public`.

- **Certificates**
  - Dev certs are generated in `certs/` using `mkcert`.
  - If the browser warns about the certificate, run: `mkcert -install`.

- **Environment**
  - `APP_URL` is set to `https://localhost:9443` in `core/.env`.

- **Logs / PIDs**
  - PIDs stored under `.pids/`.
  - Caddy stdout redirected to `/opt/homebrew/var/log/caddy-stdout.log`.
