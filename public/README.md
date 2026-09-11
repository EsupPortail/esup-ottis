# Public Directory - Front Controller

This directory serves as the **single entry point** for all HTTP requests to the OMIST application.

## Structure

```
public/
├── index.php       # Front Controller - Main entry point
├── .htaccess       # Apache rewrite rules
└── README.md       # This file
```

## Front Controller (index.php)

The Front Controller (`index.php`) is responsible for:

1. **Initialization**
   - Setting up error reporting based on environment
   - Loading Composer autoloader
   - Loading environment variables via phpdotenv
   - Starting the session

2. **Routing**
   - Creating a Router instance
   - Registering all application routes
   - Matching the current request to a route
   - Dispatching to the appropriate controller and action

3. **Error Handling**
   - Catching and logging errors
   - Displaying error pages in development
   - Showing generic error messages in production

## Routing Configuration

All routes are defined in `/config/routes.php`. The Front Controller loads this configuration and registers the routes with the Router.

### Route Definition Example

```php
// In config/routes.php
'/conference/{roomId}' => [
    'controller' => \App\Controller\ConferenceController::class,
    'action' => 'show',
    'methods' => ['GET'],
    'params' => ['roomId']
],
```

### Supported HTTP Methods

- GET - Retrieve resources
- POST - Create resources
- PUT - Update resources
- DELETE - Delete resources

## Apache Configuration (.htaccess)

The `.htaccess` file in this directory contains rewrite rules to:

1. Enable the rewrite engine
2. Serve existing files and directories directly
3. Route all other requests to `index.php`

### Example Configuration

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /public/
    
    # Serve existing files directly
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d [OR]
    RewriteCond %{REQUEST_FILENAME} -l
    RewriteRule ^ - [L]
    
    # Route everything else to Front Controller
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

## How It Works

### Request Flow

```
HTTP Request
    ↓
Apache .htaccess
    ↓
public/index.php (Front Controller)
    ↓
Router::match() - Find matching route
    ↓
Controller::action() - Execute controller method
    ↓
Response (HTML, JSON, redirect, etc.)
```

## Development Notes

### Adding New Routes

1. Add route definition to `config/routes.php`
2. Create the controller class in `src/Controller/`
3. Create the view files in `views/`
4. Test the route

### Testing Routes

You can test routes by accessing them directly in your browser:

```
http://localhost:8000/public/
http://localhost:8000/public/conference
http://localhost:8000/public/conference/abc123
```

### Environment Variables

The Front Controller respects the following environment variables:

- `APP_ENV` - Set to `production` to disable error display
- `APP_DEBUG` - Set to `true` to enable debug mode

## Security Considerations

1. **Input Validation** - All user input should be validated using the Validator service
2. **Output Escaping** - All output should be escaped using the `e()` function or `$this->escape()`
3. **CSRF Protection** - All forms should include CSRF tokens using `csrfField()`
4. **HTTPS** - Always use HTTPS in production

## Performance

The Front Controller uses output buffering to ensure that headers can be set at any point before output is sent to the client.

---

**Created**: August 26, 2026  
**Part of**: Task 12 - MVC Architecture Implementation
