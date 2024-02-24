<?php
    // session_start();

    // $_SESSION['MM_Username']    = "";
    // $_SESSION['MM_UserGroup']   = "";
    // $_SESSION['mp_UserId']      = "";
    // $_SESSION['mp_Mail']        = "";
    // $_SESSION['mp_Nivel']       = "";

    // unset($_SESSION['MM_Username']);
    // unset($_SESSION['MM_UserGroup']);
    // unset($_SESSION['mp_UserId']);
    // unset($_SESSION['mp_Mail']);
    // unset($_SESSION['mp_Nivel']);

    // session_destroy();

    // header("Location: discover");
    // exit;



    // Inicia la sesión
    session_start();

    // Limpia el contenido de las variables de sesión
    $_SESSION = [];

    // Elimina la cookie de sesión si es que existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finalmente, destruye la sesión.
    session_destroy();

    header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado

    // Redirecciona al usuario después de cerrar la sesión
    header("Location: discover?home=1");
    exit;
?>