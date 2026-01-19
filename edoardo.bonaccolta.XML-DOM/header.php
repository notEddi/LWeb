<?php
// header.php
?>
<header>
    <div class="window">
        <div class="tab-container">
            <a href="index.php" class="tab">Home</a>
            <a href="listaFilm.php" class="tab">Lista Film</a>
            <?php if ($is_logged): ?>
                <a href="areaUtente.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'areaUtente.php' ? 'active' : ''; ?>">
                    <?php echo $is_admin ? 'Pannello Admin' : 'Area Utente'; ?>
                </a>
                <a href="logout.php" class="tab">Logout (<?php echo htmlspecialchars($username); ?>)</a>
            <?php else: ?>
                <a href="login.php" class="tab <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">Login/Registrazione</a>
            <?php endif; ?>
        </div>
    </div>
</header>