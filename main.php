<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// JSON fitxategiaren izena
$archivo = "peliculas.json";

// JSON-etik datuak kargatu
if (file_exists($archivo)) {
    $_SESSION['peliculas'] = json_decode(file_get_contents($archivo), true);
} else {
    $_SESSION['peliculas'] = [];
}

// Normalizazioa (sinplea)
function normalizar($s) {
    $s = strtolower($s);
    $s = str_replace(
        ['á','é','í','ó','ú','ü','ñ'],
        ['a','e','i','o','u','u','n'],
        $s
    );
    return preg_replace('/[^a-z0-9]/','',$s);
}

// Formularioaren balio zaharrak
$old = ['nombre'=>'','isan'=>'','anio'=>'','puntuacion'=>''];

// JSON gordetzeko funtzioa
function guardar_json($archivo) {
    file_put_contents($archivo, json_encode($_SESSION['peliculas'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// PROZESATU PELIKULA FORMULARIOA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "pelicula") {
    $nombre = trim($_POST['nombre']);
    $isan = trim($_POST['isan']);
    $anio = trim($_POST['anio']);
    $puntuacion = $_POST['puntuacion'];

    $old = ['nombre'=>$nombre,'isan'=>$isan,'anio'=>$anio,'puntuacion'=>$puntuacion];

    if (empty($isan) && empty($nombre)) {
        echo "<p style='color:red'>Debes introducir al menos nombre o ISAN.</p>";
    }
    // Añadir
    elseif (!isset($_SESSION['peliculas'][$isan]) && preg_match('/^[0-9]{8}$/', $isan)) {
        if ($nombre && $anio && $puntuacion !== '') {
            $_SESSION['peliculas'][$isan] = [
                'nombre'=>$nombre,
                'anio'=>$anio,
                'puntuacion'=>$puntuacion
            ];
            guardar_json($archivo);
        } else {
            echo "<p style='color:red'>Faltan datos para añadir película.</p>";
        }
    }
    // Actualizar existente
    elseif (!empty($isan) && isset($_SESSION['peliculas'][$isan])) {
        if (!empty($nombre) && $puntuacion !== '') {
            $_SESSION['peliculas'][$isan]['nombre'] = $nombre;
            $_SESSION['peliculas'][$isan]['puntuacion'] = $puntuacion;
            guardar_json($archivo);
        } else {
            unset($_SESSION['peliculas'][$isan]);
            guardar_json($archivo);
        }
    }
    else {
        echo "<p style='color:red'>ISAN inválido o repetido.</p>";
    }
}

// PROZESATU BILAKETA FORMULARIOA
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
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f9; }
        h1 { background: #0077cc; color: white; padding: 10px; border-radius: 8px; }
        h2 { color: #0077cc; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; background: white; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #0077cc; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .low { color: red; font-weight: bold; }
        .medium { color: orange; font-weight: bold; }
        .high { color: green; font-weight: bold; }
        form { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        input, select, button { margin: 5px 0; padding: 6px; width: 100%; }
        button { background: #0077cc; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005fa3; }
    </style>
</head>
<body>
    <h1>ERABILTZAILEAREN FILMAK - <?= htmlspecialchars($_SESSION['usuario'] ?? "Invitado") ?></h1>

    <!-- LISTA DE PELICULAS -->
    <h2>Lista de películas</h2>
    <table>
        <tr>
            <th>ISAN</th>
            <th>Nombre</th>
            <th>Año</th>
            <th>Puntuación</th>
        </tr>
        <?php foreach($_SESSION['peliculas'] as $isan => $p): ?>
            <tr>
                <td><?= $isan ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= $p['anio'] ?></td>
                <td class="<?= ($p['puntuacion']<=2?'low':($p['puntuacion']<4?'medium':'high')) ?>">
                    <?= $p['puntuacion'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- FORMULARIO DE PELICULAS -->
    <h2>Añadir / Editar / Eliminar película</h2>
    <form method="post">
        <input type="hidden" name="accion" value="pelicula">

        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($old['nombre']) ?>">

        <label>ISAN (8 dígitos):</label>
        <input type="text" name="isan" value="<?= htmlspecialchars($old['isan']) ?>">

        <label>Año:</label>
        <input type="text" name="anio" value="<?= htmlspecialchars($old['anio']) ?>">

        <label>Puntuación:</label>
        <select name="puntuacion">
            <option value="">--</option>
            <?php for ($i=0; $i<=5; $i++): ?>
                <option value="<?= $i ?>" <?= ($old['puntuacion']==$i ? 'selected':'') ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit">Enviar</button>
    </form>

    <!-- FORMULARIO DE BUSQUEDA -->
    <h2>Buscar películas por nombre</h2>
    <form method="post">
        <input type="hidden" name="accion" value="buscar">
        <input type="text" name="buscar" placeholder="Introduce un nombre">
        <button type="submit">Buscar</button>
    </form>

</body>
</html>
