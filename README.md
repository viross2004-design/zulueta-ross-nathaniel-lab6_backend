# Laboratory Exercise 6 — Stockroom

React + LavaLust inventory manager. The Laravel typo in the request is handled as LavaLust because that is the backend framework supplied in this workspace and named in the activity sheet.

## Included

- LavaLust JSON API for registration, login, logout, and authenticated product CRUD.
- React catalog with inventory summary, search, add/edit dialog, and delete confirmation.
- LavaLust migrations for `users`, `refresh_tokens`, and `products`.
- Migration controller and `php lava migration ...` CLI helper from the migration guide. The helper folder follows this version of LavaLust: `app/commands`.

## Run locally

1. Copy `.env.example` to `.env`. Set `DB_DRIVER=mysql`, `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, and `DB_NAME` for your MySQL database. Set `DB_CHARSET=utf8mb4`.
2. Generate private API signing keys with `php lava jwt:generate`. Keep `.env` out of Git.
3. Run migrations with `php lava migration run`. Use `php lava migration status` to see the migration state. `rollback-all` and `refresh` remove application tables and should only be used with a development database.
4. Start LavaLust with `php lava serve` (port 3000).
5. In another terminal, run `cd frontend`, `npm install`, then `npm run dev`. Open the Vite URL and create an account. Vite proxies `/api` to `http://127.0.0.1:3000`.

The migration routes (`/migrate`, `/rollback`, `/rollback-all`, `/refresh`, `/status`, and `/create-migration/{name}`) are available for this lab in the development environment. The controller blocks browser migration requests when `APP_ENV=production`; use the CLI command when deploying.

## Aiven MySQL and Render

- Create an Aiven MySQL service and database. Put its host, port, database name, username, and password in the LavaLust service environment variables. Set `DB_SSL_CA` to the path of Aiven's CA certificate (mount it as a Render secret file) so PDO verifies the TLS certificate.
- Deploy this repository as a Render Docker web service using the included `Dockerfile`. Set `APP_ENV=production`, `DB_DRIVER=mysql`, all `DB_*` values, `JWT_SECRET`, `REFRESH_TOKEN_KEY`, and `FRONTEND_URL` to your deployed React origin. Generate separate random values of at least 32 characters for the JWT and refresh keys; never commit them.
- Run `php lava migration run` against the configured production database once as a release/deploy step. The React app only calls the LavaLust API; it never receives MySQL credentials.
- Deploy `frontend` as a static site. Build command: `npm install && npm run build`; publish directory: `frontend/dist`. Set `VITE_API_URL` to the Render API origin (no trailing slash), then rebuild.
- Register an account from the login screen, then use the product screen to add, edit, search, and delete inventory.

## API routes

| Method | Route | Access |
| --- | --- | --- |
| POST | `/api/register` | Public |
| POST | `/api/login` | Public |
| POST | `/api/logout` | Bearer token |
| GET | `/api/products` | Bearer token |
| POST | `/api/products` | Bearer token |
| PUT, PATCH | `/api/products/{id}` | Bearer token |
| DELETE | `/api/products/{id}` | Bearer token |

The API uses LavaLust's `Api` library for JSON responses, rate limiting, access-token checks, and refresh-token revocation. Registration is open for the class demo; add an invite or admin approval flow before using it for a public production service.

---

# LavaLust Framework

> A lightweight, fast PHP framework built for developers who want clean MVC architecture without unnecessary complexity or performance overhead.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF)](https://www.php.net/)
[![GitHub Stars](https://img.shields.io/github/stars/ronmarasigan/lavalust?style=flat)](https://github.com/ronmarasigan/lavalust/stargazers)

---

## Overview

**LavaLust** is an open-source PHP framework that follows the **MVC (Model–View–Controller)** architectural pattern. It is designed for developers who need a structured, maintainable, and scalable foundation — without the bloat of heavier modern frameworks.

Whether you are building a simple web application, a REST API, or a teaching project, LavaLust provides the right tools with minimal friction.

---

## Features

| Feature | Description |
|---|---|
| **MVC Architecture** | Clean separation of Models, Views, and Controllers for organized, maintainable code |
| **Built-in Routing** | Flexible URL routing that maps requests to controllers with minimal configuration |
| **Libraries & Helpers** | Reusable components for sessions, forms, validation, and database access |
| **Modular Design** | Scalable structure that supports clean organization as your application grows |
| **REST API Support** | First-class support for building RESTful APIs using LavaLust conventions |
| **ORM-like Models** | Simplified, readable database interaction without a heavy abstraction layer |

---

## Requirements

- PHP 7.4 or higher
- A web server with URL rewriting support (Apache `.htaccess` or Nginx config)
- Composer (optional, for dependency management)

---

## Installation

**Clone the repository:**

```bash
git clone https://github.com/ronmarasigan/lavalust.git
cd lavalust
```

**Or download a release directly:**

```bash
wget https://github.com/ronmarasigan/lavalust/archive/refs/heads/main.zip
unzip main.zip
```

Configure your web server to point to the project root and ensure `mod_rewrite` (Apache) or equivalent is enabled.

---

## Quick Start

### 1. Define a Route

**File:** `app/config/routes.php`

```php
$router->get('/', 'Welcome::index');
$router->get('/about', 'Welcome::about');
$router->post('/users/store', 'Users::store');
```

### 2. Create a Controller

**File:** `app/controllers/Welcome.php`

```php
<?php

class Welcome extends Controller
{
    public function index()
    {
        $data['title'] = 'Home';
        $this->call->view('welcome', $data);
    }

    public function about()
    {
        $this->call->view('about');
    }
}
```

### 3. Create a View

**File:** `app/views/welcome.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
</head>
<body>
    <h1>Welcome to LavaLust Framework</h1>
    <p>Lightweight. Fast. MVC.</p>
</body>
</html>
```

### 4. Create a Model

**File:** `app/models/User_model.php`

```php
<?php

class User_model extends Model
{
    protected $table = 'users';

    public function getAll()
    {
        return $this->db->table($this->table)->get()->getResult();
    }

    public function findById(int $id)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->get()
    }
}
```

---

## Project Structure

```
lavalust/
├── app/
│   ├── config/          # Application configuration (database, routes, etc.)
│   ├── controllers/     # Controller classes
│   ├── models/          # Model classes
│   ├── views/           # View templates
│   └── libraries/       # Custom libraries and helpers
├── scheme/              # Core framework files (do not modify)
├── public/              # Publicly accessible entry point
│   └── index.php
└── runtime/            # Cache, logs, and uploads (must be writable)
```

---

## Configuration

### Database

**File:** `app/config/database.php`

```php
$database['main'] = array(
    'driver'	=> getenv('DB_DRIVER') ?: '',
    'hostname'	=> getenv('DB_HOST') ?: '',
    'port'		=> getenv('DB_PORT') ?: '',
    'username'	=> getenv('DB_USER') ?: '',
    'password'	=> getenv('DB_PASSWORD') ?: '',
    'database'	=> getenv('DB_NAME') ?: '',
    'charset'	=> getenv('DB_CHARSET') ?: '',
    'dbprefix'	=> getenv('DB_PREFIX') ?: '',
    // Optional for SQLite
    'path'      => ''
);
```

### Base URL

**File:** `app/config/config.php`

```php
$config['base_url'] = 'http://localhost:3000/';
```

---

## Building a REST API

LavaLust supports REST API development out of the box. Controllers can return JSON responses for API endpoints.

```php
<?php

class Api extends Controller
{
    $this->call->library('api');

    public function users()
    {
        $this->api->require_method('GET');
        $auth = $this->api->require_jwt(); 

        $this->call->model('User_model');
        $users = $this->User_model->getAll();

        $this->api->respond(['data' => $users]);
    }
}
```

Route definition:

```php
$router->get('/api/users', 'Api::users');
```

---

## Philosophy

LavaLust is built on a single principle: **minimal core, maximum control.**

Modern frameworks often add layers of abstraction that benefit large enterprise teams but get in the way of developers who want to understand exactly what their code is doing. LavaLust provides structure and utilities without hiding the underlying logic — making it an excellent choice for:

- **Rapid prototyping** — Get an application running in minutes
- **Learning MVC** — Understand how each architectural layer works
- **Lightweight production apps** — Deploy without dragging in unused dependencies
- **Teaching PHP development** — Clear conventions, readable source code

---

## Documentation

Full documentation is available at **[https://lavalust.netlify.app](https://lavalust.netlify.app)**

Topics covered include:

- Installation and server configuration
- Routing: static, dynamic, and grouped routes
- Controllers and request handling
- Models and query builder
- Views, layouts, and partials
- Built-in libraries (sessions, form validation, file upload)
- Helper functions
- REST API development
- Security best practices

---

## Contributing

Contributions are welcome. To contribute:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -m "Add your feature description"`
4. Push to your branch: `git push origin feature/your-feature-name`
5. Open a pull request against `main`

Please ensure your code follows the existing style conventions and includes relevant documentation or comments where appropriate.

---

## Roadmap

- [ ] CLI tool for generating controllers, models, and migrations
- [ ] Middleware support
- [ ] Improved query builder with relationship support
- [ ] Enhanced error handling and debugging tools

---

## License

LavaLust Framework is open-source software licensed under the **[MIT License](https://opensource.org/licenses/MIT)**.

---

## Links

- **GitHub Repository:** [https://github.com/ronmarasigan/lavalust](https://github.com/ronmarasigan/lavalust)
- **Documentation:** [https://lavalust.netlify.app](https://lavalust.netlify.app)
- **Report an Issue:** [https://github.com/ronmarasigan/lavalust/issues](https://github.com/ronmarasigan/lavalust/issues)
