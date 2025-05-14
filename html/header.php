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
          <a class="nav-link <?php if ($currentPage == 'index.php') echo 'active'; ?>" href="/index.php">Strona główna</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'kredyt.php') echo 'active'; ?>" href="/kalkulatory/kredyt.php">Kalkulator rat</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'inwestycje.php') echo 'active'; ?>" href="/kalkulatory/inwestycje.php">Kalkulator inwestycji</a>
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