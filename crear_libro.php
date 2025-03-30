<?php
session_start();
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['message'] = "Debe iniciar sesión para realizar esta acción";
    $_SESSION['message_type'] = "danger";
    header("Location: login.php");
    exit();
}

if (isset($_POST['crear_libro'])) {
    $titulo = trim($_POST['titulo']);
    $id_autor = intval($_POST['id_autor']);
    $descripcion = trim($_POST['descripcion']);
    $id_usuario = $_SESSION['id_usuario'];
    $categorias = isset($_POST['categorias']) ? explode(',', $_POST['categorias']) : [];

    if (empty($titulo) || empty($id_autor)) {
        $_SESSION['message'] = 'Título y autor son obligatorios';
        $_SESSION['message_type'] = 'danger';
        header("Location: index.php");
        exit();
    }

    mysqli_begin_transaction($conn);

    try {
        $query_libro = "INSERT INTO libro (titulo, id_autor, descripcion, id_usuario) VALUES (?, ?, ?, ?)";
        $stmt_libro = $conn->prepare($query_libro);
        $stmt_libro->bind_param("sisi", $titulo, $id_autor, $descripcion, $id_usuario);
        
        if (!$stmt_libro->execute()) {
            throw new Exception("Error al crear el libro: " . $stmt_libro->error);
        }
        
        $id_libro = $stmt_libro->insert_id;
        $stmt_libro->close();

        if (!empty($categorias)) {
            $query_categoria = "INSERT INTO libro_categoria (id_libro, id_categoria) VALUES (?, ?)";
            $stmt_categoria = $conn->prepare($query_categoria);
            
            foreach ($categorias as $id_categoria) {
                $id_categoria = intval($id_categoria);
                if ($id_categoria > 0) {
                    $stmt_categoria->bind_param("ii", $id_libro, $id_categoria);
                    if (!$stmt_categoria->execute()) {
                        throw new Exception("Error al asignar categorías: " . $stmt_categoria->error);
                    }
                }
            }
            $stmt_categoria->close();
        }

        mysqli_commit($conn);

        $_SESSION['message'] = 'Libro agregado correctamente';
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }

    header("Location: index.php");
    exit();
} else {
    $_SESSION['message'] = 'Acción no permitida';
    $_SESSION['message_type'] = 'danger';
    header("Location: index.php");
    exit();
}
?>