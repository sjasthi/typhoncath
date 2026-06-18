<?php
if (!empty($_SESSION['flash'])) {
    echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['flash']) . '</div>';
    unset($_SESSION['flash']);
}
