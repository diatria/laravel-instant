# AI Documentation Index

This directory contains documentation specifically designed for AI assistants (Claude, ChatGPT, GitHub Copilot, etc.) to understand and use Laravel Instant package effectively.

## 📚 Documentation Files

### 1. **CLAUDE.md** (Main Guide - Published to project root)
**Location:** `/CLAUDE.md` (after `php artisan vendor:publish --tag=li-docs`)

**Purpose:** Complete usage guide for AI assistants

**Contains:**
- Package overview and architecture
- Quick start guide
- Code templates for Service & Controller
- API request examples
- Query parameters reference
- Response format documentation
- Permission system guide
- Advanced usage patterns
- Troubleshooting guide

**When to read:** Always read this first when working on a Laravel project that uses Laravel Instant

---

### 2. **INSTALLATION.md**
**Location:** `.ai/INSTALLATION.md`

**Purpose:** Help AI assistants detect and setup Laravel Instant in projects

**Contains:**
- Installation verification steps
- Initial setup checklist
- Package detection patterns
- Common user requests and responses
- Quick reference for AI
- Context clues
- Important flags and reminders

**When to read:** When user just installed the package or asks about setup

---

### 3. **BEST_PRACTICES.md**
**Location:** `.ai/BEST_PRACTICES.md`

**Purpose:** Design patterns and coding standards

**Contains:**
- Recommended project structure
- Service layer patterns
- Validation patterns
- Response patterns
- Error handling patterns
- Common use case patterns
- Performance tips
- Naming conventions
- Testing patterns
- Advanced patterns

**When to read:** When implementing new features or refactoring code

---

## 📁 Examples Directory

**Location:** `examples/`

### Files:
1. **ProductService.php** - Complete Service example with custom methods
2. **ProductController.php** - Complete Controller example with custom endpoints
3. **api_routes.php** - Complete routing examples with documentation
4. **API_EXAMPLES.md** - Comprehensive API request examples (curl, JavaScript)

**When to use:** When user asks for examples or you need reference implementation

---

## 🤖 How AI Should Use These Docs

### Step 1: Detect Package Usage
Look for:
- `use Diatria\LaravelInstant\Traits\InstantServiceTrait`
- `use Diatria\LaravelInstant\Traits\InstantControllerTrait`
- `config/laravel-instant.php` file
- `CLAUDE.md` in project root

### Step 2: Read Documentation
1. **CLAUDE.md** - For general usage and patterns
2. **INSTALLATION.md** - For setup and detection
3. **BEST_PRACTICES.md** - For design decisions
4. **examples/** - For code references

### Step 3: Generate Code
Use the templates and patterns from documentation to:
- Generate Services with `make:li-service`
- Generate Controllers with `make:li-controller`
- Create routes following standard pattern
- Implement custom methods following best practices

### Step 4: Troubleshoot
If errors occur:
- Check troubleshooting section in CLAUDE.md
- Verify common pitfalls in BEST_PRACTICES.md
- Check if `initModel()` is called
- Verify `$fillable` in Model
- Check validation rules in Service

---

## 🎯 Quick Reference for Common Tasks

### Create New CRUD Module
```bash
php artisan make:li-service Product
php artisan make:li-controller Product
# Add routes to routes/api.php
```

### Add Custom Service Method
See: **BEST_PRACTICES.md** → "Service Layer Pattern"

### Add Custom Controller Endpoint
See: **examples/ProductController.php** → Custom endpoints section

### Handle Complex Queries
See: **CLAUDE.md** → "Advanced Service Usage"

### Debug Issues
See: **CLAUDE.md** → "Troubleshooting" section

---

## 📝 File Priority

When answering user questions:

1. **🔴 High Priority** - Read these first:
   - `CLAUDE.md` - Main reference
   - `examples/ProductController.php` - Controller patterns
   - `examples/ProductService.php` - Service patterns

2. **🟡 Medium Priority** - Read when needed:
   - `BEST_PRACTICES.md` - Design decisions
   - `examples/api_routes.php` - Routing patterns
   - `examples/API_EXAMPLES.md` - Request examples

3. **🟢 Low Priority** - Reference only:
   - `INSTALLATION.md` - Setup and detection
   - `README.md` - Package overview

---

## 🚀 Remember

- **Always suggest using Artisan commands** (`make:li-service`, `make:li-controller`)
- **Always call `initModel()`** in controller constructor
- **Use `collect()` for Service parameters**
- **Follow the three-layer pattern**: Controller → Service → Model
- **Use standardized `Response::json()`** for responses
- **Wrap operations in transactions** when needed

---

## 📖 Additional Resources

- **Package README:** `README.md` - General package information
- **Source Code:** `src/` - Actual implementation
  - `src/Traits/InstantControllerTrait.php` - Controller methods
  - `src/Traits/InstantServiceTrait.php` - Service methods
  - `src/Utils/QueryMaker.php` - Query building logic
  - `src/Utils/Response.php` - Response handling

---

**This documentation is designed to make AI assistants productive immediately, without needing extensive learning or experimentation.**
