<?php
session_start();

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Archivo JSON para guardar películas
$archivo = __DIR__ . "/peliculas.json";

// Cargar películas guardadas
if (file_exists($archivo)) {
    $_SESSION['peliculas'] = json_decode(file_get_contents($archivo), true);
} else {
    $_SESSION['peliculas'] = [];
}

$mensaje = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? "");
    $isan = trim($_POST['isan'] ?? "");
    $anio = trim($_POST['anio'] ?? "");
    $puntuacion = $_POST['puntuacion'] ?? "";
    $buscar = trim($_POST['buscar'] ?? "");

    // Buscar
    if ($buscar !== "") {
        $resultados = [];
        foreach ($_SESSION['peliculas'] as $i => $p) {
            if (strpos(strtolower($p['nombre']), strtolower($buscar)) !== false) {
                $resultados[] = $p;
            }
        }
    }
    // Insertar nuevo
    elseif ($nombre !== "" && $isan !== "" && strlen($isan) == 8 && !isset($_SESSION['peliculas'][$isan])) {
        $_SESSION['peliculas'][$isan] = [
            "nombre" => $nombre,
            "anio" => $anio,
            "puntuacion" => $puntuacion
        ];
        $mensaje = "🎬 Película añadida correctamente.";
    }
    // Actualizar existente
    elseif ($isan !== "" && isset($_SESSION['peliculas'][$isan]) && $nombre !== "") {
        $_SESSION['peliculas'][$isan] = [
            "nombre" => $nombre,
            "anio" => $anio,
            "puntuacion" => $puntuacion
        ];
        $mensaje = "✅ Película actualizada.";
    }
    // Eliminar si solo ponen ISAN
    elseif ($isan !== "" && $nombre === "") {
        unset($_SESSION['peliculas'][$isan]);
        $mensaje = "🗑️ Película eliminada.";
    }
    else {
        $mensaje = "⚠️ Datos incorrectos o incompletos.";
    }

    // Guardar cambios en JSON
    file_put_contents($archivo, json_encode($_SESSION['peliculas'], JSON_PRETTY_PRINT));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TOP Movies</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0; padding: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .logout {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }
        .logout a {
            display: inline-block;
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .logout a:hover {
            background: #c0392b;
        }
        .container {
            max-width: 900px;
            margin: auto;
        }
        .card {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #34495e;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        input, select, button {
            padding: 8px;
            margin: 6px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 100%;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #2980b9;
        }
        .mensaje {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .mensaje.ok { background: #d4edda; color: #155724; }
        .mensaje.warn { background: #fff3cd; color: #856404; }
        .mensaje.del { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h1>🎥 PELÍCULAS DE <?= htmlspecialchars($_SESSION['usuario'] ?? "INVITADO") ?></h1>
    <div class="logout">
        <a href="?logout=1">🚪 Cerrar sesión</a>
    </div>

    <?php if($mensaje): ?>
        <div class="mensaje <?= strpos($mensaje,"añadida")||strpos($mensaje,"actualizada")?"ok":(strpos($mensaje,"eliminada")?"del":"warn") ?>">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <!-- Lista de películas -->
    <div class="card">
        <h2>📋 Mis películas</h2>
        <?php if(!empty($_SESSION['peliculas'])): ?>
        <table>
            <tr>
                <th>ISAN</th><th>Título</th><th>Año</th><th>Puntuación</th>
            </tr>
            <?php foreach($_SESSION['peliculas'] as $i => $p): ?>
            <tr>
                <td><?= $i ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['anio']) ?></td>
                <td><?= str_repeat("⭐", (int)$p['puntuacion']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
            <p>No hay películas guardadas.</p>
        <?php endif; ?>
    </div>

    <!-- Buscar películas -->
    <div class="card">
        <h2>🔎 Buscar película</h2>
        <form method="post">
            <input type="text" name="buscar" placeholder="Introduce título a buscar...">
            <button type="submit">Buscar</button>
        </form>
        <?php if(isset($resultados)): ?>
            <h3>Resultados:</h3>
            <?php if(empty($resultados)): ?>
                <p>No se encontraron coincidencias.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ISAN</th><th>Título</th><th>Año</th><th>Puntuación</th>
                    </tr>
                    <?php foreach($resultados as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(array_search($r,$_SESSION['peliculas'])) ?></td>
                        <td><?= htmlspecialchars($r['nombre']) ?></td>
                        <td><?= htmlspecialchars($r['anio']) ?></td>
                        <td><?= str_repeat("⭐",(int)$r['puntuacion']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Formulario añadir/editar -->
    <div class="card">
        <h2>➕ Añadir / Editar película</h2>
        <form method="post">
            <input type="text" name="nombre" placeholder="Título de la película">
            <input type="text" name="isan" placeholder="ISAN (8 dígitos)">
            <input type="number" name="anio" placeholder="Año">
            <select name="puntuacion">
                <option value="">Puntuación</option>
                <?php for($i=0;$i<=5;$i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">Guardar</button>
        </form>
    </div>
</div>
</body>
</html>
