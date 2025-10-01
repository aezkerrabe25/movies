<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Normalizar (quitar acentos, pasar a minúsculas, etc.)
function normalizar($s) {
    // Pasar a minúsculas normales (sin multibyte)
    $s = strtolower($s);

    // Quitar acentos básicos manualmente
    $s = str_replace(
        ['á','é','í','ó','ú','ü','ñ'],
        ['a','e','i','o','u','u','n'],
        $s
    );

    // Quitar todo lo que no sea letras o números
    return preg_replace('/[^a-z0-9]/','',$s);
}

// Inicializar sesión de películas
if (!isset($_SESSION['peliculas'])) {
    $_SESSION['peliculas'] = [];
}

// Guardar datos previos del formulario (para que no se pierdan al recargar)
$old = ['nombre'=>'','isan'=>'','anio'=>'','puntuacion'=>''];

// PROCESAR FORMULARIO DE PELICULAS
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "pelicula") {
    $nombre = trim($_POST['nombre']);
    $isan = trim($_POST['isan']);
    $anio = trim($_POST['anio']);
    $puntuacion = $_POST['puntuacion'];

    $old = ['nombre'=>$nombre,'isan'=>$isan,'anio'=>$anio,'puntuacion'=>$puntuacion];

    if (empty($isan) && empty($nombre)) {
        echo "<p style='color:red'>Debes introducir al menos nombre o ISAN.</p>";
    }
    // Añadir nueva película
    elseif (!isset($_SESSION['peliculas'][$isan]) && preg_match('/^[0-9]{8}$/', $isan)) {
        if ($nombre && $anio && $puntuacion !== '') {
            $_SESSION['peliculas'][$isan] = [
                'nombre'=>$nombre,
                'anio'=>$anio,
                'puntuacion'=>$puntuacion
            ];
        } else {
            echo "<p style='color:red'>Faltan datos para añadir película.</p>";
        }
    }
    // Actualizar película existente
    elseif (!empty($isan) && isset($_SESSION['peliculas'][$isan])) {
        if (!empty($nombre) && $puntuacion !== '') {
            $_SESSION['peliculas'][$isan]['nombre'] = $nombre;
            $_SESSION['peliculas'][$isan]['puntuacion'] = $puntuacion;
        } else {
            // Eliminar si nombre vacío
            unset($_SESSION['peliculas'][$isan]);
        }
    }
    else {
        echo "<p style='color:red'>ISAN inválido o repetido.</p>";
    }
}

// PROCESAR FORMULARIO DE BUSQUEDA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "buscar") {
    $buscar = trim($_POST['buscar']);
    if (!empty($buscar)) {
        $buscado = normalizar($buscar);
        echo "<h3>Resultados de búsqueda para \"$buscar\":</h3>";
        $encontrado = false;
        foreach ($_SESSION['peliculas'] as $p) {
            if (strpos(normalizar($p['nombre']), $buscado) !== false) {
                echo "{$p['nombre']} ({$p['anio']})<br>";
                $encontrado = true;
            }
        }
        if (!$encontrado) {
            echo "<p>No se encontraron películas con ese nombre.</p>";
        }
    } else {
        echo "<p style='color:red'>Introduce un nombre para buscar.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ERABILTZAILEAREN FILMAK</title>
</head>
<body>
    <h1>ERABILTZAILEAREN FILMAK - <?= htmlspecialchars($_SESSION['usuario'] ?? "Invitado") ?></h1>


    <!-- LISTA DE PELICULAS -->
    <h2>Lista de películas</h2>
    <ul>
        <?php foreach($_SESSION['peliculas'] as $isan => $p): ?>
            <li>
                <strong><?= htmlspecialchars($p['nombre']) ?></strong> (<?= $p['anio'] ?>) 
                - ISAN: <?= $isan ?> - Puntuación: <?= $p['puntuacion'] ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr>

    <!-- FORMULARIO DE PELICULAS -->
    <h2>Añadir / Editar / Eliminar película</h2>
    <form method="post">
        <input type="hidden" name="accion" value="pelicula">

        Nombre: <input type="text" name="nombre" value="<?= htmlspecialchars($old['nombre']) ?>"><br>
        ISAN (8 dígitos): <input type="text" name="isan" value="<?= htmlspecialchars($old['isan']) ?>"><br>
        Año: <input type="text" name="anio" value="<?= htmlspecialchars($old['anio']) ?>"><br>
        Puntuación:
        <select name="puntuacion">
            <option value="">--</option>
            <?php for ($i=0; $i<=5; $i++): ?>
                <option value="<?= $i ?>" <?= ($old['puntuacion']==$i ? 'selected':'') ?>><?= $i ?></option>
            <?php endfor; ?>
        </select><br><br>

        <button type="submit">Enviar</button>
    </form>

    <hr>

    <!-- FORMULARIO DE BUSQUEDA -->
    <h2>Buscar películas por nombre</h2>
    <form method="post">
        <input type="hidden" name="accion" value="buscar">
        <input type="text" name="buscar" placeholder="Introduce un nombre">
        <button type="submit">Buscar</button>
    </form>

</body>
</html>
