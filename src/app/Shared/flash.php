<!-- show error / success messages -->
<!--  
User submits Create RFQ form
        ↓
Database insert succeeds
        ↓
Redirect to RFQ Detail page
        ↓
Show message: RFQ created successfully -->

<?php
if (!empty($_SESSION['flash'])) {
    echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['flash']) . '</div>';
    unset($_SESSION['flash']);
}
