# Framework Refactoring TODO

## High Impact — Structural

### 1. Extract Dispatcher from Controller
`Controller` is both the base class users inherit from **and** the dispatch engine (via static methods). The static dispatch logic should live in a dedicated `Dispatcher` class.

**Methods to move:**
- `execute()`, `executeAnnotated()`, `executeLegacy()`
- `resolveParameters()`, `castValue()`
- `toResponse()`, `notFoundResponse()`, `createController()`

**Files affected:**
- `lib/Ngames/Framework/Controller.php` — remove static dispatch methods, keep instance helpers (`ok()`, `redirect()`, `json()`, `forward()`, etc.)
- New `lib/Ngames/Framework/Dispatcher.php`
- `lib/Ngames/Framework/Application.php:192` — call `Dispatcher::execute()` instead of `Controller::execute()`
- `Controller::forward()` (line 160) — delegate to `Dispatcher::execute()`
- All test files that call `Controller::execute()` (ControllerTest, ParameterInjectionTest, IntegrationTest, MiddlewareTest)

### 2. Inject database config instead of reaching into Application singleton
`Connection` calls `Application::getInstance()->getConfiguration()` to get DB credentials. This inverts the dependency — the database layer shouldn't know the app layer exists.

**Fix:** Accept config (host, name, username, password) via a static `configure()` method or constructor injection. Have `Application` push config into `Connection` during initialization.

**Files affected:**
- `lib/Ngames/Framework/Database/Connection.php`
- `lib/Ngames/Framework/Application.php`

### 3. Decouple `Application::run()` from implicit route registration
`run()` calls `registerAnnotatedControllers()` as a side effect on every request. Route registration should be an explicit setup step, not hidden inside the request cycle.

**Files affected:**
- `lib/Ngames/Framework/Application.php`

---

## Medium Impact — Consistency

### 4. Unify error handling strategy
The framework mixes three error strategies:
- `Connection` returns `false` on failure
- `Controller` / `Dispatcher` throw exceptions
- `Router` uses `trigger_error()`

Pick one approach (exceptions recommended) and apply it consistently.

**Files affected:**
- `lib/Ngames/Framework/Database/Connection.php` — throw on failure instead of returning `false`
- `lib/Ngames/Framework/Router/Router.php` — throw instead of `trigger_error()`

### 5. Fix `Request::extractUri()` regex
The current pattern `[a-z0-9_\-\/]+` silently strips dots, percent-encoding, and other valid URL characters. `/api/v2.1/users` becomes `/api/v2`.

**Fix:** Use a more permissive pattern or `parse_url()` + normalization.

**Files affected:**
- `lib/Ngames/Framework/Request.php`

---

## Low Impact — Cleanup

### 6. Collapse Route/Matcher field duplication
`Route` and `Matcher` both carry the same nullable annotated/legacy field split with the same `isAnnotated()` check. Consider whether `Matcher` can produce a `Route` without duplicating all the fields.

**Files affected:**
- `lib/Ngames/Framework/Router/Route.php`
- `lib/Ngames/Framework/Router/Matcher.php`

### 7. Make `View` root directory injectable
`View` hardcodes the `ROOT_DIR` constant. Pass the root directory through the constructor or a setter so it can be tested in isolation.

**Files affected:**
- `lib/Ngames/Framework/View.php`

### 8. Make Logger injectable (optional)
`Logger` is pure static state — can't inject, can't have two instances, can't reset cleanly in tests. Consider an instance-based logger behind an interface.

**Files affected:**
- `lib/Ngames/Framework/Logger.php`
- All call sites

### 9. Fix potential SQL injection in `Connection::insert()`
Table and column names are interpolated directly into SQL strings. If names ever come from untrusted input, this is an injection vector. Add identifier quoting.

**Files affected:**
- `lib/Ngames/Framework/Database/Connection.php`

---

## What's solid (no changes needed)
- Storage layer (clean interface, ArrayAccess bridge, recursive nesting)
- Attribute-based routing + middleware chain
- Matcher pattern matching logic
- Response factory methods
- Overall framework size — small and focused
