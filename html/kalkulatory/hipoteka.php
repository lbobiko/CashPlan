<?php include('../header.php'); ?>
<head>
<link href="https://fonts.googleapis.com/css2?family=Comic+Neue:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>
<h2 class="text-center my-4 fw-bold" style="font-family: 'Oswald', serif; font-size: 4rem;">Kalkulator hipoteki</h2>

<form method="post" action="">
  <div class="row mb-4">
    <div class="col-md-6">
      <label class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Wartość nieruchomości (zł):</label>
      <input type="number" step="0.01" class="form-control" name="wartosc_nieruchomosci" required>

      <label class="form-label mt-3 fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Wkład własny (zł):</label>
      <input type="number" step="0.01" class="form-control" name="wklad_wlasny" required>

      <label class="form-label mt-3 fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Oprocentowanie roczne (%):</label>
      <input type="number" step="0.01" class="form-control" name="oprocentowanie" required>
    </div>

    <div class="col-md-6">
      <label class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Okres kredytowania (lata):</label>
      <input type="number" class="form-control" name="lata" required>

      <label class="form-label mt-3 fw-bold " style="font-family: 'Oswald', serif; font-size: 1rem;" >Rodzaj rat:</label>
      <select class="form-select" name="rodzaj_rat">
        <option value="rowne"style="font-family: 'Oswald', serif; font-size: 1rem;" >Raty równe</option>
        <option value="malejace" style="font-family: 'Oswald', serif; font-size: 1rem;">Raty malejące</option>
      </select>

      <label class="form-label mt-3 fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Ubezpieczenie (opcjonalnie, zł):</label>
      <input type="number" step="0.01" class="form-control" name="ubezpieczenie">
    </div>
  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary " style="font-family: 'Oswald', serif; font-size: 2rem;">Oblicz kredyt hipoteczny</button>
  </div>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Pobranie danych z formularza
  $wartoscNieruchomosci = (float) $_POST['wartosc_nieruchomosci'];
  $wkladWlasny = (float) $_POST['wklad_wlasny'];
  $oprocentowanie = (float) $_POST['oprocentowanie'];
  $lata = (int) $_POST['lata'];
  $rodzajRat = $_POST['rodzaj_rat'];
  $ubezpieczenie = isset($_POST['ubezpieczenie']) ? (float) $_POST['ubezpieczenie'] : 0;

  // Obliczenie podstawowych wartości kredytu
  $kwotaKredytu = $wartoscNieruchomosci - $wkladWlasny;
  $miesiace = $lata * 12;
  $oprocentowanieMiesieczne = $oprocentowanie / 12 / 100;

  echo "<div class='mt-5'>";
  echo "<h4 class='text-center fw-bold' style='font-family: \"Oswald\", serif; font-size: 3rem;'>Podsumowanie kredytu</h4>";
  echo "<div class='alert alert-light'>";
  echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Kwota kredytu: <strong>" . number_format($kwotaKredytu, 2, ',', ' ') . " zł</strong></p>";
  echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Okres: <strong>$miesiace miesięcy</strong></p>";
  echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Oprocentowanie: <strong>" . number_format($oprocentowanie, 2, ',', ' ') . " %</strong></p>";
  if ($ubezpieczenie > 0) {
    echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Ubezpieczenie: <strong>" . number_format($ubezpieczenie, 2, ',', ' ') . " zł</strong></p>";
  }

  if ($rodzajRat === 'rowne') {
    // Obliczenia dla rat równych
    if ($oprocentowanieMiesieczne > 0) {
      $rata = ($kwotaKredytu * $oprocentowanieMiesieczne) / (1 - pow(1 + $oprocentowanieMiesieczne, -$miesiace));
    } else {
      $rata = $kwotaKredytu / $miesiace;
    }
    $calkowityKoszt = $rata * $miesiace + $ubezpieczenie;

    echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Rata miesięczna (równa): <strong>" . number_format($rata, 2, ',', ' ') . " zł</strong></p>";
    echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Całkowity koszt: <strong>" . number_format($calkowityKoszt, 2, ',', ' ') . " zł</strong></p>";
    // Płótno wykresu dla rat równych
    echo "<div class='mt-4'><canvas id='mortgageChart' height='100'></canvas></div>";
  } elseif ($rodzajRat === 'malejace') {
    // Obliczenia dla rat malejących
    $czescKapitalowa = $kwotaKredytu / $miesiace;
    $pozostalo = $kwotaKredytu;
    $calkowityKoszt = 0;

    echo "<div class='table-responsive mt-4'>";
    echo "<table class='table table-bordered table-sm' style='font-family: \"Oswald\", serif; font-size: 1rem;'>";
    echo "<thead><tr>
	<th style='text-align: center;'>Miesiąc</th>
	<th style='text-align: center;'>Rata</th>
	<th style='text-align: center;'>Kapitał</th>
	<th style='text-align: center;'>Odsetki</th>
	<th style='text-align: center;'>Pozostało</th>
	</tr></thead><tbody>";

    for ($i = 1; $i <= $miesiace; $i++) {
      $czescOdsetkowa = $pozostalo * $oprocentowanieMiesieczne;
      $rataMiesieczna = $czescKapitalowa + $czescOdsetkowa;
      $calkowityKoszt += $rataMiesieczna;

      echo "<tr><td>$i</td><td>" . number_format($rataMiesieczna, 2, ',', ' ') . " zł</td><td>" .
        number_format($czescKapitalowa, 2, ',', ' ') . " zł</td><td>" .
        number_format($czescOdsetkowa, 2, ',', ' ') . " zł</td><td>" .
        number_format($pozostalo, 2, ',', ' ') . " zł</td></tr>";

      $pozostalo -= $czescKapitalowa;
    }

    $calkowityKoszt += $ubezpieczenie;
    echo "</tbody></table></div>";
    echo "<p class='mt-3' style='font-family: \"Oswald\", serif; font-size: 2rem;'>Całkowity koszt z ubezpieczeniem: <strong>" . number_format($calkowityKoszt, 2, ',', ' ') . " zł</strong></p>";
    // Płótno wykresu dla rat malejących
    echo "<div class='mt-4'><canvas id='mortgageChart' height='100'></canvas></div>";
  }

  // Eksport harmonogramu do plików (raty malejące)
  if ($rodzajRat === 'malejace') {
    $plikCSV = fopen(__DIR__ . "/../exports/hipoteka_malejaco.csv", "w");
    fputcsv($plikCSV, ["Miesiąc", "Rata", "Kapitał", "Odsetki", "Pozostało"]);

    $pozostalo = $kwotaKredytu;
    for ($i = 1; $i <= $miesiace; $i++) {
      $czescOdsetkowa = $pozostalo * $oprocentowanieMiesieczne;
      $rataMiesieczna = $czescKapitalowa + $czescOdsetkowa;
      fputcsv($plikCSV, [$i, round($rataMiesieczna, 2), round($czescKapitalowa, 2), round($czescOdsetkowa, 2), round($pozostalo, 2)]);
      $pozostalo -= $czescKapitalowa;
    }
    fclose($plikCSV);

    $plikTXT = fopen(__DIR__ . "/../exports/hipoteka_malejaco.txt", "w");
    fwrite($plikTXT, "Harmonogram spłat - raty malejące\n\n");
    fwrite($plikTXT, "Miesiąc | Rata | Kapitał | Odsetki | Pozostało\n");
    $pozostalo = $kwotaKredytu;
    for ($i = 1; $i <= $miesiace; $i++) {
      $czescOdsetkowa = $pozostalo * $oprocentowanieMiesieczne;
      $rataMiesieczna = $czescKapitalowa + $czescOdsetkowa;
      fwrite($plikTXT, sprintf("%6d | %7.2f | %7.2f | %7.2f | %9.2f\n", $i, $rataMiesieczna, $czescKapitalowa, $czescOdsetkowa, $pozostalo));
      $pozostalo -= $czescKapitalowa;
    }
    fclose($plikTXT);

    // Wyświetlenie przycisków do pobrania plików
    echo "<div class='mt-3 text-center'>";
    echo "<a class='btn btn-outline-success me-2' href='../exports/hipoteka_malejaco.csv' download>Pobierz CSV</a>";
    echo "<a class='btn btn-outline-secondary' href='../exports/hipoteka_malejaco.txt' download>Pobierz TXT</a>";
    echo "</div>";
  }

  // Eksport harmonogramu rat równych
  if ($rodzajRat === 'rowne') {
    $plikCSV = fopen(__DIR__ . "/../exports/hipoteka_rowne.csv", "w");
    fputcsv($plikCSV, ["Miesiąc", "Rata"]);
    for ($i = 1; $i <= $miesiace; $i++) {
      fputcsv($plikCSV, [$i, round($rata, 2)]);
    }
    fclose($plikCSV);

    $plikTXT = fopen(__DIR__ . "/../exports/hipoteka_rowne.txt", "w");
    fwrite($plikTXT, "Harmonogram rat równych\n\n");
    for ($i = 1; $i <= $miesiace; $i++) {
      fwrite($plikTXT, "Miesiąc $i: " . number_format($rata, 2, ',', ' ') . " zł\n");
    }
    fclose($plikTXT);

    // Wyświetlenie przycisków do pobrania plików
    echo "<div class='mt-3 text-center'>";
    echo "<a class='btn btn-outline-success me-2' href='../exports/hipoteka_rowne.csv' download>Pobierz CSV</a>";
    echo "<a class='btn btn-outline-secondary' href='../exports/hipoteka_rowne.txt' download>Pobierz TXT</a>";
    echo "</div>";
  }

  // --- Generowanie PDF z podsumowaniem kredytu ---
  require_once(__DIR__ . '/../fpdf/fpdf.php');

  $pdfDokument = new FPDF();
  $pdfDokument->AddPage();
  $pdfDokument->SetFont('Arial', 'B', 16);
  $pdfDokument->Cell(0, 10, 'Podsumowanie kredytu hipotecznego', 0, 1, 'C');
  $pdfDokument->Ln(5);
  $pdfDokument->SetFont('Arial', '', 12);

  $pdfDokument->Cell(0, 8, 'Kwota kredytu: ' . number_format($kwotaKredytu, 2, ',', ' ') . ' zl', 0, 1);
  $pdfDokument->Cell(0, 8, 'Okres: ' . $miesiace . ' miesiecy', 0, 1);
  $pdfDokument->Cell(0, 8, 'Oprocentowanie: ' . number_format($oprocentowanie, 2, ',', ' ') . ' %', 0, 1);
  if ($ubezpieczenie > 0) {
    $pdfDokument->Cell(0, 8, 'Ubezpieczenie: ' . number_format($ubezpieczenie, 2, ',', ' ') . ' zl', 0, 1);
  }

  if ($rodzajRat === 'rowne') {
    $pdfDokument->Cell(0, 8, 'Rata miesieczna (rowna): ' . number_format($rata, 2, ',', ' ') . ' zl', 0, 1);
  } else {
    $pdfDokument->Cell(0, 8, 'Typ rat: malejace', 0, 1);
  }

  $pdfDokument->Cell(0, 8, 'Calkowity koszt: ' . number_format($calkowityKoszt, 2, ',', ' ') . ' zl', 0, 1);

  $sciezkaPDF = __DIR__ . '/../exports/hipoteka_podsumowanie.pdf';
  $pdfDokument->Output('F', $sciezkaPDF);

  // Dodaj link do pobrania PDF
  echo "<div class='mt-3 text-center'>";
  echo "<a class='btn btn-outline-primary' href='../exports/hipoteka_podsumowanie.pdf' download>Pobierz PDF</a>";
  echo "</div>";

  echo "</div></div>";
}
?>

<?php
// Renderowanie wykresu Chart.js po przesłaniu formularza
if ($_SERVER["REQUEST_METHOD"] === "POST") : ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
  const ctx = document.getElementById('mortgageChart')?.getContext('2d');
  if (ctx) {
    new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Kwota kredytu', 'Odsetki', 'Ubezpieczenie', 'Całkowity koszt'],
    datasets: [{
      label: 'Koszty (zł)',
      data: [
        <?= round($kwotaKredytu, 2) ?>,
        <?= $rodzajRat === 'rowne'
              ? round($rata * $miesiace - $kwotaKredytu, 2)
              : round($calkowityKoszt - $kwotaKredytu - $ubezpieczenie, 2) ?>,
        <?= round($ubezpieczenie, 2) ?>,
        <?= round($calkowityKoszt, 2) ?>
      ],
      backgroundColor: [
        'rgba(13, 110, 253, 0.7)',
        'rgba(108, 117, 125, 0.7)',
        'rgba(255, 193, 7, 0.7)',
        'rgba(25, 135, 84, 0.7)'
      ],
      borderColor: ['#0d6efd', '#6c757d', '#ffc107', '#198754'],
      borderWidth: 1
    }]
  },
  options: {
    indexAxis: 'y', // zmiana na wykres poziomy
    responsive: true,
    layout: {
      padding: { top: 20, bottom: 20, left: 10, right: 10 }
    },
    plugins: {
      legend: { display: false },
      title: {
        display: true,
        text: 'Struktura kosztów kredytu',
        font: { size: 18,
		family: 'Oswald',
		weight: 'bold' }
      },
      tooltip: {
        callbacks: {
          label: ctx => ctx.raw.toLocaleString('pl-PL') + ' zł'
        }
      },
      datalabels: {
        anchor: 'end',
        align: 'end',
        formatter: value => value.toLocaleString('pl-PL') + ' zł',
        font: { weight: 'bold' }
      }
    },
    scales: {
      x: {
        beginAtZero: true,
        ticks: {
          callback: value => value.toLocaleString('pl-PL') + ' zł'
        }
      }
    }
  },
  plugins: [ChartDataLabels]
});
  }
</script>
<?php endif; ?>

<?php include('../footer.php'); ?>