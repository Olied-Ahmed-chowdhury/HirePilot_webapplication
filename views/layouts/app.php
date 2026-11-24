<?php
/**
 * HirePilot AI — Main App Layout Wrapper
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Usage in view files:
 *   $pageTitle   = 'Dashboard';
 *   $navbarTitle = 'Dashboard';
 *   ob_start();
 *   // ... your page HTML content ...
 *   $content = ob_get_clean();
 *   require VIEWS_PATH . '/layouts/app.php';
 */

declare(strict_types=1);

use HirePilot\Core\Auth;
use HirePilot\Core\Flash;

Auth::require(); // Redirect to login if not authenticated

$pageTitle   ??= 'HirePilot AI';
$navbarTitle ??= $pageTitle;
?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>

<div class="app-wrapper">

  <!-- Sidebar -->
  <?php require VIEWS_PATH . '/layouts/sidebar.php'; ?>

  <!-- Main -->
  <main class="app-main">

    <!-- Navbar -->
    <?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

    <!-- Flash messages -->
    <?php if (Flash::has()): ?>
      <div class="px-4 pt-3">
        <?= Flash::render() ?>
      </div>
    <?php endif; ?>

    <!-- Page content -->
    <div class="app-content">
      <?= $content ?? '' ?>
    </div>

  </main>
</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>

