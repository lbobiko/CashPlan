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
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
	<link href="style.css" rel="stylesheet">
	<link rel="icon" type="image/png" href="img/favicon.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
	<style>
	body{
		
		font-family: 'Roboto', sans-serif;
	}
		:root {
			--primary: #007BFF;
			--secondary: #28A745;
			--light: #F8F9FA;
		}
	</style>
</head>
<body class="custom-bg">
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/index.php">CashPlan - Twoje narzędzie inwestycyjno bankowe</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'index.php') echo 'active'; ?>" href="/index.php">Start</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage == 'kredyt.php') echo 'active'; ?>" href="/kalkulatory/kredyt.php">Kredyt</a>
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
</body>

  
      
   