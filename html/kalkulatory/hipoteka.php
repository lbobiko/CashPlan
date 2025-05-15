<?php include('../header.php'); ?>

<h2 class="text-center my-4">Kalkulator hipoteki</h2>

<form method="post" action="">
  <div class="row mb-4">
    <div class="col-md-6">
      <label class="form-label">Wartość nieruchomości (zł):</label>
      <input type="number" step="0.01" class="form-control" name="property_value" required>

      <label class="form-label mt-3">Wkład własny (zł):</label>
      <input type="number" step="0.01" class="form-control" name="own_contribution" required>

      <label class="form-label mt-3">Oprocentowanie roczne (%):</label>
      <input type="number" step="0.01" class="form-control" name="interest" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Okres kredytowania (lata):</label>
      <input type="number" class="form-control" name="years" required>

      <label class="form-label mt-3">Rodzaj rat:</label>
      <select class="form-select" name="type">
        <option value="annuity">Raty równe</option>
        <option value="declining">Raty malejące</option>
      </select>

      <label class="form-label mt-3">Ubezpieczenie (opcjonalnie, zł):</label>
      <input type="number" step="0.01" class="form-control" name="insurance">
    </div>
  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary">Oblicz kredyt hipoteczny</button>
  </div>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Pobranie danych z formularza
  $propertyValue = (float) $_POST['property_value'];
  $ownContribution = (float) $_POST['own_contribution'];
  $interest = (float) $_POST['interest'];
  $years = (int) $_POST['years'];
  $type = $_POST['type'];
  $insurance = isset($_POST['insurance']) ? (float) $_POST['insurance'] : 0;

  // Obliczenie podstawowych wartości kredytu
  $loanAmount = $propertyValue - $ownContribution;
  $months = $years * 12;
  $monthlyRate = $interest / 12 / 100;

  echo "<div class='mt-5'>";
  echo "<h4 class='text-center'>Podsumowanie kredytu</h4>";
  echo "<div class='alert alert-light'>";
  echo "<p>Kwota kredytu: <strong>" . number_format($loanAmount, 2, ',', ' ') . " zł</strong></p>";
  echo "<p>Okres: <strong>$months miesięcy</strong></p>";
  echo "<p>Oprocentowanie: <strong>" . number_format($interest, 2, ',', ' ') . " %</strong></p>";
  if ($insurance > 0) {
    echo "<p>Ubezpieczenie: <strong>" . number_format($insurance, 2, ',', ' ') . " zł</strong></p>";
  }

  if ($type === 'annuity') {
    // Obliczenia dla rat równych
    if ($monthlyRate > 0) {
      $payment = ($loanAmount * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));
    } else {
      $payment = $loanAmount / $months;
    }
    $total = $payment * $months + $insurance;

    echo "<p>Rata miesięczna (równa): <strong>" . number_format($payment, 2, ',', ' ') . " zł</strong></p>";
    echo "<p>Całkowity koszt: <strong>" . number_format($total, 2, ',', ' ') . " zł</strong></p>";
    // Płótno wykresu dla rat równych
    echo "<div class='mt-4'><canvas id='mortgageChart' height='100'></canvas></div>";
  } elseif ($type === 'declining') {
    // Obliczenia dla rat malejących
    $capitalPart = $loanAmount / $months;
    $remaining = $loanAmount;
    $total = 0;

    echo "<div class='table-responsive mt-4'>";
    echo "<table class='table table-bordered table-sm'>";
    echo "<thead><tr><th>Miesiąc</th><th>Rata</th><th>Kapitał</th><th>Odsetki</th><th>Pozostało</th></tr></thead><tbody>";

    for ($i = 1; $i <= $months; $i++) {
      $interestPart = $remaining * $monthlyRate;
      $installment = $capitalPart + $interestPart;
      $total += $installment;

      echo "<tr><td>$i</td><td>" . number_format($installment, 2, ',', ' ') . " zł</td><td>" .
        number_format($capitalPart, 2, ',', ' ') . " zł</td><td>" .
        number_format($interestPart, 2, ',', ' ') . " zł</td><td>" .
        number_format($remaining, 2, ',', ' ') . " zł</td></tr>";

      $remaining -= $capitalPart;
    }

    $total += $insurance;
    echo "</tbody></table></div>";
    echo "<p class='mt-3'>Całkowity koszt z ubezpieczeniem: <strong>" . number_format($total, 2, ',', ' ') . " zł</strong></p>";
    // Płótno wykresu dla rat malejących
    echo "<div class='mt-4'><canvas id='mortgageChart' height='100'></canvas></div>";
  }

  // Eksport harmonogramu do plików (raty malejące)
  if ($type === 'declining') {
    $csvFile = fopen(__DIR__ . "/../exports/hipoteka_declining.csv", "w");
    fputcsv($csvFile, ["Miesiąc", "Rata", "Kapitał", "Odsetki", "Pozostało"]);

    $remaining = $loanAmount;
    for ($i = 1; $i <= $months; $i++) {
      $interestPart = $remaining * $monthlyRate;
      $installment = $capitalPart + $interestPart;
      fputcsv($csvFile, [$i, round($installment, 2), round($capitalPart, 2), round($interestPart, 2), round($remaining, 2)]);
      $remaining -= $capitalPart;
    }
    fclose($csvFile);

    $txt = fopen(__DIR__ . "/../exports/hipoteka_declining.txt", "w");
    fwrite($txt, "Harmonogram spłat - raty malejące\n\n");
    fwrite($txt, "Miesiąc | Rata | Kapitał | Odsetki | Pozostało\n");
    $remaining = $loanAmount;
    for ($i = 1; $i <= $months; $i++) {
      $interestPart = $remaining * $monthlyRate;
      $installment = $capitalPart + $interestPart;
      fwrite($txt, sprintf("%6d | %7.2f | %7.2f | %7.2f | %9.2f\n", $i, $installment, $capitalPart, $interestPart, $remaining));
      $remaining -= $capitalPart;
    }
    fclose($txt);

    // Wyświetlenie przycisków do pobrania plików
    echo "<div class='mt-3 text-center'>";
    echo "<a class='btn btn-outline-success me-2' href='../exports/hipoteka_declining.csv' download>Pobierz CSV</a>";
    echo "<a class='btn btn-outline-secondary' href='../exports/hipoteka_declining.txt' download>Pobierz TXT</a>";
    echo "</div>";
  }

  // Eksport harmonogramu rat równych
  if ($type === 'annuity') {
    $csv = fopen(__DIR__ . "/../exports/hipoteka_annuity.csv", "w");
    fputcsv($csv, ["Miesiąc", "Rata"]);
    for ($i = 1; $i <= $months; $i++) {
      fputcsv($csv, [$i, round($payment, 2)]);
    }
    fclose($csv);

    $txt = fopen(__DIR__ . "/../exports/hipoteka_annuity.txt", "w");
    fwrite($txt, "Harmonogram rat równych\n\n");
    for ($i = 1; $i <= $months; $i++) {
      fwrite($txt, "Miesiąc $i: " . number_format($payment, 2, ',', ' ') . " zł\n");
    }
    fclose($txt);

    // Wyświetlenie przycisków do pobrania plików
    echo "<div class='mt-3 text-center'>";
    echo "<a class='btn btn-outline-success me-2' href='../exports/hipoteka_annuity.csv' download>Pobierz CSV</a>";
    echo "<a class='btn btn-outline-secondary' href='../exports/hipoteka_annuity.txt' download>Pobierz TXT</a>";
    echo "</div>";
  }

  // --- Generowanie PDF z podsumowaniem kredytu ---
  require_once(__DIR__ . '/../fpdf/fpdf.php');

  $pdf = new FPDF();
  $pdf->AddPage();
  $pdf->SetFont('Arial', 'B', 16);
  $pdf->Cell(0, 10, 'Podsumowanie kredytu hipotecznego', 0, 1, 'C');
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 12);

  $pdf->Cell(0, 8, 'Kwota kredytu: ' . number_format($loanAmount, 2, ',', ' ') . ' zl', 0, 1);
  $pdf->Cell(0, 8, 'Okres: ' . $months . ' miesiecy', 0, 1);
  $pdf->Cell(0, 8, 'Oprocentowanie: ' . number_format($interest, 2, ',', ' ') . ' %', 0, 1);
  if ($insurance > 0) {
    $pdf->Cell(0, 8, 'Ubezpieczenie: ' . number_format($insurance, 2, ',', ' ') . ' zl', 0, 1);
  }

  if ($type === 'annuity') {
    $pdf->Cell(0, 8, 'Rata miesieczna (rowna): ' . number_format($payment, 2, ',', ' ') . ' zl', 0, 1);
  } else {
    $pdf->Cell(0, 8, 'Typ rat: malejace', 0, 1);
  }

  $pdf->Cell(0, 8, 'Calkowity koszt: ' . number_format($total, 2, ',', ' ') . ' zl', 0, 1);

  $pdfPath = __DIR__ . '/../exports/hipoteka_podsumowanie.pdf';
  $pdf->Output('F', $pdfPath);

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
            <?= round($loanAmount, 2) ?>,
            <?= $type === 'annuity'
                  ? round($payment * $months - $loanAmount, 2)
                  : round($total - $loanAmount - $insurance, 2) ?>,
            <?= round($insurance, 2) ?>,
            <?= round($total, 2) ?>
          ],
          backgroundColor: ['#0d6efd', '#6c757d', '#ffc107', '#198754']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: {
            display: true,
            text: 'Struktura kosztów kredytu'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => value.toLocaleString('pl-PL') + ' zł'
            }
          }
        }
      }
    });
  }
</script>
<?php endif; ?>

<?php include('../footer.php'); ?>