<?php include("db.php") ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería The Goat - Login</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .login-container {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
        }
        .row-full-height {
            margin-right: 0;
            margin-left: 0;
        }
        .login-form-col {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-image-col {
            padding: 0;
            margin: 0;
        }
        .login-image {
            width: 100%;
            height: 100vh;
            object-fit: cover;
            object-position: center;
            display: block;
        }
        .logo-text {
            font-weight: 700;
            color: #343a40;
        }
        .btn-login {
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(to right, #5a6fd1, #6a4295);
            transform: translateY(-2px);
        }
        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid login-container p-0">
        <div class="row row-full-height g-0">
            <div class="col-lg-6 login-form-col">
                <div class="w-100" style="max-width: 500px;">
                    <div class="mb-5 text-center">
                        <i class="fas fa-book-open fa-2x me-3" style="color: #764ba2;"></i>
                        <span class="logo-text h1">LIBRERIA THE GOAT</span>
                    </div>
                    <div class="card shadow-sm p-4">
                        <div class="card-body">
                            <h3 class="mb-4 text-center">Iniciar sesión</h3>
                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
                                    <?= $_SESSION['message'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php 
                                unset($_SESSION['message']);
                                unset($_SESSION['message_type']);
                                ?>
                            <?php endif; ?>
                            <form action="verificar_login.php" method="POST">
                                <div class="form-floating mb-3">
                                    <input type="email" name="correo" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                                    <label for="floatingInput">Correo electrónico</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="password" name="contra" class="form-control" id="floatingPassword" placeholder="Contraseña" required>
                                    <label for="floatingPassword">Contraseña</label>
                                </div>
                                <div class="d-grid">
                                    <button class="btn btn-primary btn-login btn-lg" type="submit" name="login">
                                        Ingresar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 login-image-col">
                <img src="media/Mountain_Goat,_Enchantments_Basin.jpg" alt="Biblioteca" class="login-image">
            </div>
        </div>
    </div>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>