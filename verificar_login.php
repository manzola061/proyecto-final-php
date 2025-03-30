<?php

    include("db.php");

    if (isset($_POST['login'])) {
        $correo = $_POST['correo'];
        $contra = $_POST['contra'];

        $query = "SELECT * FROM usuario WHERE correo = '$correo' AND contra = '$contra'";
        $resultado = mysqli_query($conn, $query);

        if (mysqli_num_rows($resultado) == 1) {
            $fila = mysqli_fetch_array($resultado);
            $_SESSION['correo'] = $correo;
            $_SESSION['id_usuario'] = $fila['id'];
            header("Location: index.php");
        } else {
            $_SESSION['message'] = "Correo o contraseña incorrectos";
            $_SESSION['message_type'] = "danger";
            header("Location: login.php");
        }
    }

?>