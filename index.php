<?php
session_start();

// Si ya hay usuario, ir directo a main.php
if (isset($_SESSION['usuario'])) {
    header("Location: main.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? "");
    if ($usuario !== "") {
        $_SESSION['usuario'] = $usuario;
        header("Location: main.php");
        exit;
    } else {
        $mensaje = "⚠️ Por favor, introduce un nombre de usuario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TOP Movies - Iniciar Sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0; padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        h1 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #2980b9;
        }
        .mensaje {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🎬 TOP Movies</h1>
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?= $mensaje ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="usuario" placeholder="Introduce tu nombre de usuario">
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
