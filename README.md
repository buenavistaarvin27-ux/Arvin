@@ -1,33 +1 @@
# Arvin Buenavista — Personal Portfolio

## Pages
Home, About, Skills, Projects, Resume/Experience, Contact.

## JavaScript features
1. Mobile navigation
2. Contact form validation
3. Dark/light mode with LocalStorage
4. Project filtering
5. Project image modal
6. Scroll-to-top button
7. Typing effect
8. Skill-bar animation
9. Accordion
10. Active navigation

## Folder structure
- index.html
- about.html
- skills.html
- projects.php
- resume.html
- contact.html
- assets/style.css
- assets/script.js
- assets/profile.svg
- assets/project1.svg ... project4.svg

## Deploy to InfinityFree

This site uses PHP and MySQL, so it needs PHP hosting; GitHub Pages cannot run its PHP files.

1. Create the InfinityFree website and MySQL database. Keep the database host, name, username, and password from the control panel.
2. In the GitHub repository, open **Settings → Secrets and variables → Actions** and add these repository secrets:
   - `INFINITYFREE_FTP_SERVER`
   - `INFINITYFREE_FTP_USERNAME`
   - `INFINITYFREE_FTP_PASSWORD`
   - `INFINITYFREE_DB_HOST`
   - `INFINITYFREE_DB_NAME`
   - `INFINITYFREE_DB_USERNAME`
   - `INFINITYFREE_DB_PASSWORD`
3. Push to `main`. The `Deploy to InfinityFree` workflow uploads the site to `/htdocs/`; you can also run it manually from the repository's **Actions** tab.

Use the FTP server and database details shown in your InfinityFree control panel. Do not commit credentials or post them in chat. The workflow creates `config/database.php` from Actions secrets during deployment; that file remains ignored by Git.

Note: Replace the placeholder email/social links with your real links before submission.
