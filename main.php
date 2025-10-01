<?php
session_start();
mb_internal_encoding("UTF-8");

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['peliculas'])) {
    $_SESSION['peliculas'] = [];
}

$warning = "";
$resultados = [];
$old = ['nombre'=>'','isan'=>'','anio'=>'','puntuacion'=>''];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $isan   = trim($_POST['isan'] ?? '');
    $anio   = trim($_POST['anio'] ?? '');
    $punt   = $_POST['puntuacion'] ?? '';

    $old = ['nombre'=>$nombre,'isan'=>$isan,'anio'=>$anio,'puntuacion'=>$punt];

    // 1. Nombre e ISAN vacíos
    if ($nombre === '' && $isan === '') {
        $warning = "Izena eta ISAN hutsik daude.";
    }
    // 2. Buscar por nombre
    elseif ($isan === '' && $nombre !== '') {
        foreach ($_SESSION['peliculas'] as $p) {
            if (normalizar($p['nombre']) === normalizar($nombre)) {
                $resultados[] = $p;
            }
        }
    }
    // 3. ISAN ya existe
    elseif (buscarPelicula($isan) !== null) {
        // Si nombre vacío → borrar
        if ($nombre === '') {
            foreach ($_SESSION['peliculas'] as $i=>$p) {
                if ($p['isan']===$isan) unset($_SESSION['peliculas'][$i]);
            }
        }
        // Si todos campos rellenos → actualizar
        elseif ($anio!=='' && $punt!=='') {
            foreach ($_SESSION['peliculas'] as &$p) {
                if ($p['isan']===$isan) {
                    $p['nombre']=$nombre;
                    $p['anio']=$anio;
                    $p['puntuacion']=$punt;
                }
            }
        } else {
            $warning = "Eremu guztiak bete behar dira eguneratzeko.";
        }
    }
    // 4. ISAN nuevo y válido (8 dígitos) → añadir
    elseif (preg_match('/^\d{8}$/',$isan)) {
        if ($nombre!=='' && $anio!=='' && $punt!=='') {
            $_SESSION['peliculas'][]=[
                'nombre'=>$nombre,'isan'=>$isan,'anio'=>$anio,'puntuacion'=>$punt
            ];
        } else {
            $warning="Eremu guztiak bete behar dira gehitzeko.";
        }
    }
    else {
        $warning="ISAN ez da baliozkoa.";
    }
}

function normalizar($s) {
    $s = mb_strtolower($s, 'UTF-8');
    $s = iconv('UTF-8','ASCII//TRANSLIT',$s);
    return preg_replace('/[^a-z0-9]/','',$s);
}

function buscarPelicula($isan) {
    foreach ($_SESSION['peliculas'] as $p) {
        if ($p['isan']===$isan) return $p;
    }
    return null;
}
?>
<!doctype html>
<html lang="eu">
<head>
  <meta charset="utf-8">
  <title>Top Movies</title>
</head>
<body>
  <h1>ERABILTZAILEAREN FILMAK: <?= htmlspecialchars($_SESSION['usuario']) ?></h1>

  <!-- Lista -->
  <h2>Zerrenda</h2>
  <?php if (!empty($_SESSION['peliculas'])): ?>
    <ul>
    <?php foreach ($_SESSION['peliculas'] as $p): ?>
      <li><?= htmlspecialchars($p['nombre']) ?> (<?= $p['anio'] ?>) - 
          ISAN: <?= $p['isan'] ?> - 
          Puntuazioa: <?= $p['puntuacion'] ?></li>
    <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>Ez dago filmik gehituta oraindik.</p>
  <?php endif; ?>

  <!-- Resultados búsqueda -->
  <?php if (!empty($resultados)): ?>
    <h3>Bilaketaren emaitzak:</h3>
    <ul>
    <?php foreach ($resultados as $r): ?>
      <li>"<?= htmlspecialchars($r['nombre']) ?>" <?= $r['anio'] ?> urtekoa.</li>
    <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <!-- Warning -->
  <?php if ($warning!==""): ?>
    <p style="color:red"><?= $warning ?></p>
  <?php endif; ?>

  <!-- Formulario -->
  <h2>Formularioa</h2>
  <form method="post">
    Izena: <input type="text" name="nombre" value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"><br>
    ISAN: <input type="text" name="isan" value="<?= htmlspecialchars($old['isan'] ?? '') ?>"><br>
    Urtea: <input type="text" name="anio" value="<?= htmlspecialchars($old['anio'] ?? '') ?>"><br>
    Puntuazioa:
    <select name="puntuacion">
      <option value="">--</option>
      <?php for($i=0;$i<=5;$i++): ?>
        <option value="<?= $i ?>" <?= (($old['puntuacion'] ?? '')==$i?'selected':'') ?>><?= $i ?></option>
      <?php endfor; ?>
    </select>
    <br>
    <button type="submit">Bidali</button>
  </form>
</body>
</html>
