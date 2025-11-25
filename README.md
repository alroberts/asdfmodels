# ASDF Models

An online platform designed to connect models and photographers from around the world.

## About ASDF Models

ASDF Models is a professional networking and collaboration space where:

- **Models** can create profiles, showcase their portfolios, upload photo galleries, and connect with photographers looking for talent
- **Photographers** can search for models, showcase their work, and find models for various projects (fashion, beauty, commercial, editorial, etc.)

### Key Features

- Model and photographer directory/search functionality
- User registration and profile management system
- Portfolio galleries and photo albums
- User verification system to increase profile credibility
- Social features including activity feeds, messaging, and following
- Educational articles and guides covering topics such as:
  - Taking modeling polaroids
  - Understanding TFCD (Time For CD) photo sessions
  - Model release forms (TFCD and paid)
  - Communication tips for models and photographers
  - Portfolio management
  - Photographic copyright
  - Taking measurements for profiles
- Model release form resources (PDF downloads)
- Support and feedback systems

The platform provides a safe and professional environment for both aspiring and experienced models and photographers to connect, collaborate, and grow their careers.

## Branding

- **Primary logo:** `asdfmodels.com/public_html/assets/graphics/logo/ASDFModels.svg`
- **Color scheme:** High-contrast black and white (white backgrounds, black UI elements)

## Project Structure

```
domains/
├── README.md (this file)
├── asdfmodels.com/          # Main project folder
│   └── public_html/         # Web root
│       ├── assets/          # Static assets
│       │   ├── graphics/    # Logo and graphics
│       │   ├── images/      # Article and photo-shoot images
│       │   └── resources/   # PDF model release forms
│       └── resources/       # Placeholder directories
└── ...
```

### Assets

- 14 article images in `assets/images/articles/`
- 15 photo-shoot images in `assets/images/`
- 2 PDF model release forms (paid and TFCD) in `assets/resources/`

## Technology Stack

### Server Environment

**Web Server:**
- Apache 2.4.65
- PHP Handler: php-cgi84 (PHP 8.4)
- DirectAdmin control panel
- .htaccess support enabled

**PHP:**
- Version: PHP 8.4.14
- Memory Limit: 512M
- Upload Max Filesize: 64M
- Post Max Size: 64M
- Max Input Vars: 1000
- Default Charset: UTF-8
- Timezone: Etc/UTC

**PHP Extensions Available:**
- Database: mysqli, mysqlnd, PDO, pdo_mysql, pdo_sqlite
- Image Processing: gd, imagick, exif
- Data Formats: json, xml, xmlreader, xmlwriter, yaml, zip
- Security: openssl, sodium, hash, random
- Text Processing: mbstring, iconv, gettext, intl
- Network: curl, ftp, sockets, ssh2
- Caching: redis, igbinary, Zend OPcache
- Other: bcmath, calendar, ctype, date, dom, fileinfo, filter, gmp, libxml, pcre, Phar, session, SimpleXML, soap, sqlite3, zlib

**Database:**
- MariaDB 11.8.4

**Development Tools:**
- Composer 2.8.12
- Git 2.43.0

**Technology Stack:**
- **Framework:** Laravel 11
- **Backend:** PHP 8.4.14, MariaDB 11.8.4, Apache 2.4.65
- **Database:** Eloquent ORM with migrations and seeders
- **Templates:** Blade templating engine
- **Editor:** Tiptap (block-based WYSIWYG)
- **Authentication:** Laravel Breeze + Sanctum (Argon2id password hashing)
- **Frontend:** Plain JavaScript and CSS (no build tools)
- **Dependency Management:** Composer for PHP packages only

## Development

### Requirements

- PHP 8.4+
- MariaDB 11.8+
- Composer (for PHP dependencies only)
- No Node.js required (using plain JavaScript/CSS)

### Setup

1. Clone the repository
2. Install dependencies with Composer
3. Configure environment variables (see `.env.example`)
4. Set up database connection
5. Run migrations

## License

[To be determined]

