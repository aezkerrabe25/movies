<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    if ($nombre !== '') {
        $_SESSION['usuario'] = $nombre;
        // redirigir a main.php
        header("Location: main.php");
        exit;
    } else {
        $error = "Mesedez, idatzi zure izena.";
    }
}
?>
<!doctype html>
<html lang="eu">
<head>
  <meta charset="utf-8">
  <title>Top Movies - Sarrera</title>
</head>
<body>
  <h1>TOP Movies - Sarrera</h1>
  <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
  <form method="post">
    <label>Izena: <input type="text" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"></label>
    <button type="submit">Sartu</button>
  </form>
</body>
</html>
