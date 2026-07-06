# 24/7 Registration Portal

## Setup Instructions

1. **Clone the repository**
2. **Install dependencies:**
   ```
   composer install
   ```
3. **Copy the example environment file and configure:**
   ```
   cp .env.example .env
   # Edit .env with your DB and mail settings
   ```
4. **Set up your web server to serve the `public/` directory as the document root.**
5. **Run the app:**
   - Visit `http://localhost` (or your configured APP_URL)

## Directory Structure
- `app/` - Application code (Controllers, Models, Views)
- `config/` - Configuration files
- `public/` - Web server document root (entry point)
- `vendor/` - Composer dependencies

## Next Steps
- Begin migrating business logic into Controllers and Models
- Refactor views into `app/Views/`
- Follow the checklist in `build-checklist.md` 