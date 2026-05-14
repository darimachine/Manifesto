# IMPORT / EXPORT FORMAT

> JSON формат за export и import на цял Project с цялата му инфраструктура.
> Версия: 1.0 · Дата: 2026-05-14

---

## 1. Цел

- **Export:** потребител експортира конфигурацията на цял Project (плюс инфраструктурния му контекст) като един JSON файл, който може да съхрани или прехвърли на друга машина.
- **Import:** потребител качва JSON файл и системата създава всички записи (инфраструктура + project + services + webapps).

**Принципи:**
1. Един файл = един Project (плюс контекст). Без multi-project bundles в MVP.
2. Self-contained: import-ът на нова машина не изисква никакви предварителни данни.
3. Транзакционен: ако import-ът се провали, нищо не се записва.
4. Версиониран: `format_version` позволява бъдещи промени без счупване на стари файлове.

---

## 2. Pъководна схема (JSON)

```json
{
  "format_version": "1.0",
  "exported_at": "2026-05-14T18:00:00Z",
  "exported_by": "admin",

  "infrastructure_context": {
    "hardware_hosts": [
      {
        "id_ref": "hw-1",
        "name": "fmi-server-01",
        "mac_address": "AA:BB:CC:DD:EE:FF",
        "physical_location": "fmi",
        "mgmt_type": "kvm",
        "admin_email": "admin@fmi.uni-sofia.bg",
        "ip": "192.168.1.10",
        "web_url": "https://ilo.fmi.uni-sofia.bg",
        "notes": ""
      }
    ],
    "hypervisors": [
      {
        "id_ref": "hv-1",
        "hardware_host_ref": "hw-1",
        "name": "hyperv-main",
        "vendor": "HyperV",
        "os": "WindowsDatacenter2019R2",
        "admin_email": "admin@fmi.uni-sofia.bg",
        "ip": "192.168.1.20",
        "notes": ""
      }
    ],
    "virtual_machines": [
      {
        "id_ref": "vm-1",
        "hypervisor_ref": "hv-1",
        "name": "vm-prod-01",
        "ip": "192.168.1.30",
        "port": 22,
        "status": "running",
        "notes": ""
      }
    ],
    "docker_hosts": [
      {
        "id_ref": "dh-1",
        "vm_ref": "vm-1",
        "hardware_host_ref": null,
        "name": "docker-main",
        "docker_version": "24.0.5",
        "notes": ""
      }
    ]
  },

  "project": {
    "docker_host_ref": "dh-1",
    "name": "MyShop",
    "slug": "myshop",
    "description": "Demo e-commerce stack",

    "networks": [
      { "name": "frontend", "driver": "bridge" },
      { "name": "backend",  "driver": "bridge" }
    ],

    "services": [
      {
        "name": "web",
        "image": "nginx:alpine",
        "build_context": null,
        "command": null,
        "depends_on": "api",
        "restart_policy": "unless-stopped",
        "notes": "",

        "ports": [
          { "host_port": 8080, "container_port": 80, "protocol": "tcp" }
        ],
        "env_vars": [
          { "key": "NGINX_HOST", "value": "myshop.local", "is_secret": false, "description": "" }
        ],
        "volumes": [
          { "host_path": "./public", "container_path": "/usr/share/nginx/html", "mode": "ro" }
        ],
        "networks": ["frontend"],

        "webapps": [
          {
            "name": "main-site",
            "url": "https://myshop.local",
            "dns_name": "myshop.local",
            "vhost_ip": "127.0.0.1",
            "vhost_path": "/var/www/myshop",
            "authors": "fn9999, email: a@b.c",
            "status": "unknown",
            "notes": "",

            "roles": [
              {
                "role_name": "admin",
                "url_path": "/admin",
                "description": "Full admin panel",
                "users": [
                  { "username": "root", "password_hint": "see env DB_PASS", "permissions": "all" }
                ]
              },
              {
                "role_name": "editor",
                "url_path": "/editor",
                "description": "Content editor",
                "users": [
                  { "username": "editor1", "password_hint": "", "permissions": "edit,publish" }
                ]
              }
            ]
          }
        ]
      },
      {
        "name": "api",
        "image": "node:20-alpine",
        "build_context": "./api",
        "command": "node server.js",
        "depends_on": "db",
        "restart_policy": "unless-stopped",
        "notes": "",

        "ports": [],
        "env_vars": [
          { "key": "DB_HOST", "value": "db", "is_secret": false, "description": "" },
          { "key": "DB_PASS", "value": "supersecret", "is_secret": true, "description": "" }
        ],
        "volumes": [],
        "networks": ["frontend", "backend"],
        "webapps": []
      },
      {
        "name": "db",
        "image": "mysql:8.0",
        "build_context": null,
        "command": null,
        "depends_on": null,
        "restart_policy": "unless-stopped",
        "notes": "",

        "ports": [],
        "env_vars": [
          { "key": "MYSQL_ROOT_PASSWORD", "value": "supersecret", "is_secret": true, "description": "" },
          { "key": "MYSQL_DATABASE",      "value": "myshop",      "is_secret": false, "description": "" }
        ],
        "volumes": [
          { "host_path": "db-data", "container_path": "/var/lib/mysql", "mode": "rw" }
        ],
        "networks": ["backend"],
        "webapps": []
      }
    ]
  }
}
```

---

## 3. `id_ref` система

JSON-ът използва символни препратки (`hw-1`, `hv-1`, `vm-1`, `dh-1`) вместо database ID-та, защото:
- DB ID-тата на source машината не съществуват на target машината.
- Препратките са четими от човек.

**Правила:**
- Всеки entity в `infrastructure_context` има `id_ref` (низ, уникален в рамките на файла).
- Препратки между entities (parent FK) използват `_ref` суфикс (`hardware_host_ref`, `hypervisor_ref`, `vm_ref`, `docker_host_ref`).
- На import — системата първо създава всички entities, мапва `id_ref` → новогенериран DB ID, после resolve-ва препратките.

---

## 4. Валидация при import

### 4.1 Schema валидация (преди всичко)
- `format_version` присъства и е поддържана.
- Задължителни полета на всеки entity са попълнени.
- Enum стойностите са валидни (`status`, `protocol`, `mode`, `driver`, `restart_policy`).
- IP полета изглеждат като валиден IPv4/IPv6 (по избор, не блокер).

### 4.2 Reference валидация
- Всеки `_ref` сочи към съществуващ `id_ref` в същия файл.
- DockerHost има точно едно от `vm_ref` или `hardware_host_ref`.
- Network names referenced от service.networks съществуват в `project.networks`.

### 4.3 Business валидация
- Project `slug` не съществува вече в базата → ако съществува, питаме за overwrite или нов slug.
- Service names са уникални в рамките на проекта.
- WebApp names са уникални в рамките на service.

### 4.4 Транзакционност
- Целият import се прави в една DB транзакция.
- При първа грешка → ROLLBACK + детайлен error report на потребителя.
- При успех → COMMIT + flash message „Imported N entities, project created".

---

## 5. Поведение при conflict

- Ако project `slug` вече съществува:
  - Опция А: показваме форма с input за нов slug.
  - Опция Б (MVP): просто отказваме с error message.
- Ако HardwareHost с същото име вече съществува:
  - Опция А: предлагаме reuse (връзваме новата йерархия към него).
  - Опция Б (MVP): създаваме нов с suffix `-imported`.

**Решение за MVP:** използваме **опция Б** навсякъде (по-проста имплементация). Подобрения — `FUTURE_WORK.md`.

---

## 6. Сигурност при export/import

- **Secret values (`is_secret=true`)** се експортират като plain text в JSON. Файлът съдържа чувствителни данни.
- Export-ът показва warning преди download: „⚠️ Този файл съдържа secret values в plain text. Третирай го като чувствителен."
- В бъдеще: опция за encrypted export (с парола). За MVP — само warning.
- Import: ако файлът съдържа `is_secret=true` стойности, те се записват в DB както са.

---

## 7. Емпирични правила за работа с този формат

1. **Експортирай преди да направиш голяма промяна.** Това е твоят savepoint.
2. **Версионирай експортите.** Преглеждай разликата с git diff за да видиш какво се е променило.
3. **Не редактирай експорта ръчно** — лесно се счупват references.
4. **Тествай import на тестова база** преди да го пуснеш на production seed.
