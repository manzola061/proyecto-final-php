<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libreria the goat - Inicio</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a href="index.php" class="navbar-brand">LIBRERIA THE GOAT 🐐</a>

        <form class="d-flex" action="buscar_libros.php" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" name="q" placeholder="Buscar por título..." 
                       value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button class="btn btn-outline-light" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </form>
    </div>

    <?php if (isset($_SESSION['correo'])) { ?>
    <a href="logout.php" class="btn btn-dark">
        <i class="fa-solid fa-right-to-bracket"></i>
    </a>
    <?php } ?>
</nav>