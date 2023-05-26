<?php
if (isset($_SESSION['rt_Mail']) && $_SESSION['rt_Mail'] == true) {
        // echo "<h3 style='color: red; text-align: center;'> Obs. The security module is active!</h3>";
    } else { 
        header(sprintf("Location: index.php")); exit; 
    }
?>