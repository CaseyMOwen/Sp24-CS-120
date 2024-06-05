
<?php 
    session_start();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST as $key=>$value)
        {
            $_SESSION[$key] = $value;
            echo "Session variable $key set to $value";
        }
    }
?>