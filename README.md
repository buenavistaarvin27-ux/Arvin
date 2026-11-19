@@ -1,33 +1 @@
# Arvin Buenavista — Personal Portfolio

A PHP/MySQL portfolio site with public pages, a project catalogue, a contact inbox, and a protected admin area.

## Included features

- Responsive navigation and accessible keyboard focus states
- Dark/light mode with local storage
- Project category filters and image preview modal
- Dynamic projects loaded from MySQL
- Contact form validation, server-side validation, and a spam honeypot
- Admin project and contact-message management with CSRF protection
- SEO metadata, canonical URLs, Open Graph tags, favicon, `robots.txt`, and sitemap
- Security headers and protected configuration/include directories

## TinkerHost deployment

The intended host is TinkerHost. The GitHub Actions workflow in `.github/workflows/deploy-tinkerhost.yml` deploys to `/htdocs/` over FTPS.

1. Wait for the TinkerHost account to become active.
2. Create a MySQL database in TinkerHost and note its host, database name, username, and password.
3. Import `database/schema.sql` into the new database using phpMyAdmin.
4. Generate a bcrypt password hash for the first admin account:

   ```sh
   php -r "echo password_hash('REPLACE_WITH_A_LONG_UNIQUE_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
   ```

   Use the generated hash in the commented admin-user insert at the bottom of `database/schema.sql`. Use a real email address and a unique password.
5. Add these GitHub Actions repository secrets:

   - `TINKERHOST_FTP_SERVER` — normally `ftpupload.net`
   - `TINKERHOST_FTP_USERNAME`
   - `TINKERHOST_FTP_PASSWORD`
   - `TINKERHOST_DB_HOST`
   - `TINKERHOST_DB_NAME`
   - `TINKERHOST_DB_USERNAME`
   - `TINKERHOST_DB_PASSWORD`

6. Push to `main` or run the workflow manually from GitHub Actions.

The workflow creates `config/database.php` from secrets at deploy time. Credentials must never be committed to Git or posted in chat.

## Personal information still needed

The code is technically prepared, but the portfolio should still be personalized with truthful information: a downloadable resume PDF, real project names and outcomes, individual project URLs, certifications or training, and a professional email address. The database starter records contain clearly marked replacement text so no personal achievements are fabricated.
