<?php
require_once __DIR__ . '/config/database.php';

// Get all active projects from the database
try {
    $stmt = $pdo->query("
        SELECT *
        FROM projects
        WHERE status = 'active'
        ORDER BY sort_order ASC, created_at DESC
    ");

    $projects = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Public projects loading failed: " . $e->getMessage());
    $projects = [];
}

// Escape output before displaying it
function escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Selected web development, design, and networking projects by Arvin Buenavista." />
    <link rel="canonical" href="https://arvinbuenavista.thsite.top/projects.php" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Projects · Arvin Buenavista" />
    <meta property="og:description" content="Selected web development, design, and networking projects by Arvin Buenavista." />
    <meta property="og:url" content="https://arvinbuenavista.thsite.top/projects.php" />
    <meta property="og:image" content="https://arvinbuenavista.thsite.top/assets/profile.jpg" />
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml" />
    <title>Projects · Arvin Buenavista</title>

    <script>
      (() => {
        const savedTheme = localStorage.getItem("portfolio-theme");
        const prefersLight = window.matchMedia("(prefers-color-scheme: light)").matches;

        if (savedTheme === "light" || (!savedTheme && prefersLight)) {
          document.documentElement.classList.add("light");
        }
      })();
    </script>

    <link rel="stylesheet" href="assets/style.css" />
  </head>

  <body>
    <header class="site-header">
      <div class="container nav">
        <a class="logo" href="index.html">Arvin<span>.</span></a>

        <button class="menu-btn" aria-label="Open menu" aria-expanded="false" aria-controls="site-navigation">☰</button>

        <nav id="site-navigation">
          <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="skills.html">Skills</a></li>
            <li><a href="projects.php">Projects</a></li>
            <li><a href="resume.html">Resume</a></li>
            <li><a href="contact.html">Contact</a></li>
          </ul>
        </nav>

        <button class="theme-btn desktop-theme" aria-label="Toggle theme">
          ☾
        </button>
      </div>
    </header>

    <main class="container page">
      <header class="page-head reveal">
        <div class="eyebrow">Projects</div>
        <h1>Selected Work</h1>
        <p>
          Sample academic and personal projects showing development, design, and
          problem-solving skills.
        </p>
      </header>

      <div class="filters reveal">
        <button class="filter active" data-filter="all">All</button>
        <button class="filter" data-filter="web">Web</button>
        <button class="filter" data-filter="design">Designs</button>
        <button class="filter" data-filter="networking">Networking</button>
      </div>

      <!-- Dynamic Projects from Database -->
      <section class="grid two">

        <?php if (!empty($projects)): ?>

          <?php foreach ($projects as $project): ?>

            <article
              class="card project-card reveal"
              data-category="<?php echo escape(strtolower($project['category'] ?? '')); ?>"
            >

              <?php if (!empty($project['image'])): ?>
                <img
                  class="project-img zoomable"
                  src="<?php echo escape($project['image']); ?>"
                  alt="<?php echo escape($project['title']); ?>"
                />
              <?php endif; ?>

              <h2>
                <?php echo escape($project['title']); ?>
              </h2>

              <p class="muted">
                <?php echo escape($project['description']); ?>
              </p>

              <?php if (!empty($project['technologies'])): ?>
                <div class="tags">

                  <?php foreach (explode(',', $project['technologies']) as $technology): ?>

                    <span class="tag">
                      <?php echo escape(trim($technology)); ?>
                    </span>

                  <?php endforeach; ?>

                </div>
              <?php endif; ?>

              <?php if (!empty($project['project_url'])): ?>
                <a
                  class="btn"
                  href="<?php echo escape($project['project_url']); ?>"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  View project
                </a>
              <?php endif; ?>

            </article>

          <?php endforeach; ?>

        <?php else: ?>

          <p class="muted">No projects available at the moment.</p>

        <?php endif; ?>

      </section>
    </main>

    <!-- Image Preview Modal -->
    <div id="modal" class="modal">
      <button id="modalClose" aria-label="Close">×</button>
      <img id="modalImg" alt="Project preview" />
    </div>

    <footer class="footer">
      <div class="container">
        © 2026 Arvin Buenavista · Personal Portfolio
      </div>
    </footer>

    <button id="topBtn" class="top-btn" aria-label="Scroll to top">
      ↑
    </button>

    <script src="assets/script.js"></script>
  </body>
</html>
