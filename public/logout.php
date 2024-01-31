<?php
    $_SESSION['MM_Username']="";
    $_SESSION['MM_UserGroup']="";
    $_SESSION['mp_UserId']="";
    $_SESSION['mp_Mail']="";
    $_SESSION['mp_Nivel']="";

    unset($_SESSION['MM_Username']);
    unset($_SESSION['MM_UserGroup']);
    unset($_SESSION['mp_UserId']);
    unset($_SESSION['mp_Mail']);
    unset($_SESSION['mp_Nivel']);
    session_start();
    session_destroy();

    header("Location: discover");
    exit;
?>