<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Finansowy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-sm navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="/index.php">CashPlan</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'index.php') echo 'active'; ?>" href="/index.php">Start</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'kredyt.php') echo 'active'; ?>" href="/kalkulatory/kredyt.php">Raty</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'hipoteka.php') echo 'active'; ?>" href="/kalkulatory/hipoteka.php">Hipoteka</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'inwestycje.php') echo 'active'; ?>" href="/kalkulatory/inwestycje.php">Inwestycje</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'porownanie.php') echo 'active'; ?>" href="/kalkulatory/porownanie.php">Porównanie</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'autorzy.php') echo 'active'; ?>" href="/autorzy.php">Autorzy</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<main class="container mt-4">

    <header class="bg-primary text-white text-center py-4">
        <h1>Kalkulator Finansowy</h1>
    </header>
    <div class="bg-light text-center py-2 border-top border-bottom">
      <p class="mb-0 text-muted">Planowanie finansów w prosty sposób</p>
    </div>