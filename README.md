# BimmerTech Firmware Download Manager

A Symfony application for managing and distributing firmware updates for BimmerTech CarPlay / Android Auto MMI devices.

## Features

- **Customer-Facing Download Page**: Customers enter their software and hardware versions to find the correct firmware download
- **Admin Panel**: Non-technical staff can add, edit, and manage firmware versions without developer help
- **Exact Business Logic Match**: All version matching, hardware detection, and error messages are identical to the original system

## System Requirements

### Option A: Docker (Recommended)
- Docker and Docker Compose

### Option B: Local PHP Setup
- PHP 8.1+ with extensions: `pdo_sqlite`, `intl`, `mbstring`
- Composer 2.x
- SQLite 3

## Quick Start

### Option A: Using Docker

```bash
docker compose up --build -d
```

The app will be available at:
- **Customer page**: http://localhost:8080/carplay/software-download
- **Admin panel**: http://localhost:8080/admin

### Option B: Without Docker

```bash
# 1. Install dependencies
composer install

# 2. Create the database and load firmware versions
php bin/console doctrine:schema:update --force
php bin/console app:load-software-versions

# 3. Start the development server
php -S localhost:8080 -t public/
```

The app will be available at:
- **Customer page**: http://localhost:8080/carplay/software-download
- **Admin panel**: http://localhost:8080/admin

## How It Works

### Customer Download Page

1. Customer enters their **Software Version** (e.g., `v3.3.5.mmipri.b` or `3.3.5.mmipri.b`)
2. Customer enters their **HW Version** (e.g., `CPAA_2024.01.15`)
3. The system detects the hardware type and finds the matching firmware
4. If an update is available, download links for the correct hardware variant (ST and/or GD) are shown

### Hardware Version Patterns

| Pattern | Example | Hardware Type |
|---------|---------|--------------|
| `CPAA_YYYY.MM.DD` | `CPAA_2024.01.15` | Standard ST |
| `CPAA_G_YYYY.MM.DD` | `CPAA_G_2024.01.15` | Standard GD |
| `B_C_YYYY.MM.DD` | `B_C_2024.01.15` | LCI CIC |
| `B_N_G_YYYY.MM.DD` | `B_N_G_2024.01.15` | LCI NBT |
| `B_E_G_YYYY.MM.DD` | `B_E_G_2024.01.15` | LCI EVO |

## Managing Software Versions (Admin Panel)

Navigate to **http://localhost:8080/admin** to access the admin panel.

### Adding a New Version

1. Click **Software Versions** in the sidebar
2. Click **Create Software Version**
3. Fill in:
   - **Product Name**: Select from dropdown (e.g., "MMI Prime NBT", "LCI MMI PRO EVO")
   - **System Version**: The full version string including `v` prefix (e.g., `v3.3.8.mmipri.b`)
   - **Version Alt**: The version without the `v` prefix — this is what customers enter (e.g., `3.3.8.mmipri.b`)
   - **Download Links**: Paste Google Drive folder URLs for General, ST, and GD variants
   - **Latest Version?**: Toggle ON only for the newest version in each product group
4. Click **Create**

### Important Notes

- **Only one version per product group should be marked as "Latest"**
- When releasing a new firmware, add the new version (marked as Latest) and un-mark the previous latest version
- **LCI fields** (isLci, lciHwType) are automatically calculated from the Product Name — you don't need to set them
- **Version Alt** must exactly match what customers see on their device (without the `v` prefix)

### Filtering & Searching

- Use the **Filters** panel to filter by Product Name, Latest status, or LCI
- Use the **Search** box to find versions by name or version string

## Product Groups

| Standard | LCI |
|----------|-----|
| MMI Prime CIC | LCI MMI Prime CIC |
| MMI Prime NBT | LCI MMI Prime NBT |
| MMI Prime EVO | LCI MMI Prime EVO |
| MMI PRO CIC | LCI MMI PRO CIC |
| MMI PRO NBT | LCI MMI PRO NBT |
| MMI PRO EVO | LCI MMI PRO EVO |

## API

The firmware lookup API is available at:

```
POST /api/carplay/software/version
Content-Type: application/json

{
    "version": "3.3.5.mmipri.b",
    "hwVersion": "CPAA_2024.01.15",
    "mcuVersion": ""
}
```

## Project Structure

```
firmware-download-app/
├── src/
│   ├── Controller/
│   │   ├── Api/SoftwareVersionApiController.php   # API endpoint
│   │   ├── Admin/DashboardController.php          # Admin dashboard
│   │   ├── Admin/SoftwareVersionCrudController.php # Admin CRUD
│   │   └── SoftwareDownloadController.php         # Customer page
│   ├── Entity/SoftwareVersion.php                 # Database entity
│   ├── Repository/SoftwareVersionRepository.php   # DB queries
│   └── Command/LoadSoftwareVersionsCommand.php    # Data import
├── templates/
│   ├── base.html.twig
│   ├── software_download/index.html.twig          # Customer page
│   └── admin/dashboard.html.twig                  # Admin dashboard
├── public/css/software-download.css               # Customer page styles
├── data/softwareversions.json                     # Initial fixture data
├── Dockerfile
├── docker-compose.yml
└── README.md
```
