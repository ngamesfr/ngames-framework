# Changelog

## 0.6.0

### New Features

#### Attribute-Based Routing

Routes can now be defined using PHP 8 attributes directly on controller classes and methods, replacing the convention-based `module/controller/action` pattern.

**HTTP method attributes:** `#[Get]`, `#[Post]`, `#[Put]`, `#[Patch]`, `#[Delete]`

```php
use Ngames\Framework\Router\Attribute\Route;
use Ngames\Framework\Router\Attribute\Get;
use Ngames\Framework\Router\Attribute\Post;

#[Route('/api/users')]
class UserController extends Controller
{
    #[Get('/')]
    public function list() { /* ... */ }

    #[Get('/:id')]
    public function show(int $id) { /* ... */ }

    #[Post('/')]
    public function create() { /* ... */ }
}
```

**Class-level route prefix:** Use `#[Route('/prefix')]` on the controller class to set a base path for all methods.

**Automatic parameter injection:** Route parameters (e.g. `:id`) are automatically injected into action method arguments by name, with type coercion for `int`, `float`, and `bool`.

**Automatic controller discovery:** `Application::run()` now auto-scans `src/Controller/` for annotated controllers. Custom directories can be registered via `$app->registerAnnotatedControllers([...])`.

**APCu route caching:** Scanned route definitions are cached in APCu when available, avoiding filesystem scanning on every request.

#### Middleware Support

A new `MiddlewareInterface` enables request/response middleware on annotated routes:

```php
use Ngames\Framework\Router\Attribute\Middleware;

#[Route('/admin')]
#[Middleware(AuthMiddleware::class)]
class AdminController extends Controller
{
    #[Get('/dashboard')]
    #[Middleware(LoggingMiddleware::class)]
    public function dashboard() { /* ... */ }
}
```

- Middleware can be applied at class level (all methods) and method level (specific actions).
- Multiple middleware classes are supported via `#[Middleware(A::class, B::class)]` or repeated attributes.
- Middleware classes must implement `Ngames\Framework\Router\MiddlewareInterface`.
- Middleware chain executes in declaration order; class-level runs before method-level.

### Changes

- **Redirect default changed from 301 to 302:** `Response::createRedirectResponse()` now defaults to HTTP 302 (Found) instead of 301 (Moved Permanently). A `$statusCode` parameter has been added to allow specifying any redirect status code.
- **Convention-based routing deprecated:** `Matcher::forConventionRoute()`, `Route::createLegacy()`, `Controller::forward()`, and related legacy routing methods are marked `@deprecated`. They continue to work but will be removed in a future version.
- **Route constructor changed:** `Route` now uses named constructors `Route::create()` and `Route::createLegacy()` instead of a public constructor.
- **Router::getRoute() accepts HTTP method:** The method signature now accepts an optional `$method` parameter for HTTP method matching.
- **PHP requirement lowered to 8.4:** Changed from `>= 8.5` to `>= 8.4`.

### Bug Fixes

- **XSS in View:** `renderStylesheets()` and `renderScripts()` now escape paths with `htmlspecialchars()` to prevent stored XSS.
- **Multibyte truncation:** `Inflector::ellipsis()` now uses `mb_substr()` instead of `substr()`, preventing mid-character truncation on multibyte strings.
- **Implicit null return:** `AbstractStorage::__get()` now explicitly returns `null` when a key is not found.
- **Inconsistent return type:** `View::placeholder()` now always returns a `string` (returns `''` when used as a setter).
- **IniFile section support:** `IniFile::writeFile()` now handles nested arrays as INI sections instead of writing "Array".
- **Suppressed deprecation:** Removed `@` suppression on `trigger_error()` in `Matcher::forConventionRoute()` so deprecation notices are properly visible.
- **FileSystem docblock:** `FileSystem::fwriteStream()` `@return` corrected to `int|false`.

### Code Quality

- Extracted `Response::createErrorResponse()` private helper to eliminate duplication across 4 factory methods.
- Extracted `View::addToList()` private helper to eliminate duplication across 4 prepend/append methods.
- Reduced cognitive complexity in `Exception::trace()` and `IniFile::processParsedFile()`.
- Replaced regex-based class name extraction with PHP tokenizer in `RouteCollector`.
- Duplicate route registration now logs a warning.

## Migration Guide (0.5.2 to 0.6.0)

### No Breaking Changes Required

This release is backward compatible. Existing convention-based routing continues to work without modification. However, the following deprecations should be addressed before the next major release:

### Recommended Migration Steps

**1. Redirect status code (action may be needed)**

If your application relies on `Response::createRedirectResponse()` or `Controller::redirect()` returning HTTP 301, explicitly pass the status code:

```php
// Before (was 301 by default)
return $this->redirect('/new-url');

// After — if you need 301, be explicit:
return Response::createRedirectResponse('/new-url', Response::HTTP_STATUS_MOVED_PERMANENTLY);

// The new default (302) is usually what you want for non-permanent redirects
```

**2. Adopt attribute-based routing (optional, recommended)**

```php
// Before: convention-based (file at src/Controller/Default/Index.php)
// Route: /default/index/show

// After: attribute-based
#[Route('/default/index')]
class IndexController extends Controller
{
    #[Get('/show')]
    public function show() { /* ... */ }
}
```

**3. Route constructor migration (only if creating Route objects directly)**

```php
// Before
$route = new Route($module, $controller, $action);

// After
$route = Route::createLegacy($module, $controller, $action);
```

**4. Add ext-apcu for production performance**

The framework now scans controller directories for route attributes on every request. Install the APCu extension to cache scanned routes:

```bash
pecl install apcu
```

Or add to `composer.json`:
```json
{
    "suggest": {
        "ext-apcu": "For caching annotated route definitions"
    }
}
```
