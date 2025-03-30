<?php

    include("db.php");

    if (!isset($_SESSION['id_usuario'])) {
        $_SESSION['message'] = "Debes iniciar sesion para realizar esta accion";
        $_SESSION['message_type'] = "danger";
        header("Location: login.php");
        exit();
    }

    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $id_usuario = $_SESSION['id_usuario'];
        $query = "DELETE FROM libro WHERE id = $id AND id_usuario = '$id_usuario'";
        $resultado = mysqli_query($conn, $query);
        if(!$resultado) {
            die("La query fallo");
        }

        $_SESSION['message'] = 'Libro borrado correctamente';
        $_SESSION['message_type'] = 'danger';

        header("Location: index.php");
        exit();
    }

?>