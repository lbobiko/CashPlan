<?php include('../header.php'); ?>

<h2 class="text-center my-4">Porównanie dwóch kredytów</h2>

<form method="post" action="">
  <div class="row mb-4">
    <div class="col-md-6">
      <h5>Kredyt 1</h5>
      <label for="amount1" class="form-label">Kwota (zł):</label>
      <input type="number" step="0.01" class="form-control" name="amount1" id="amount1" required>

      <label for="interest1" class="form-label mt-2">Oprocentowanie roczne (%):</label>
      <input type="number" step="0.01" class="form-control" name="interest1" id="interest1" required>

      <label for="months1" class="form-label mt-2">Liczba miesięcy:</label>
      <input type="number" class="form-control" name="months1" id="months1" required>
    </div>
    <div class="col-md-6">
      <h5>Kredyt 2</h5>
      <label for="amount2" class="form-label">Kwota (zł):</label>
      <input type="number" step="0.01" class="form-control" name="amount2" id="amount2" required>

      <label for="interest2" class="form-label mt-2">Oprocentowanie roczne (%):</label>
      <input type="number" step="0.01" class="form-control" name="interest2" id="interest2" required>

      <label for="months2" class="form-label mt-2">Liczba miesięcy:</label>
      <input type="number" class="form-control" name="months2" id="months2" required>
    </div>
  </div>
  <!-- Export select input -->
  <div class="row mb-3">
    <div class="col-md-6 offset-md-3">
      <label for="export" class="form-label">Zapisz porównanie do pliku:</label>
      <select class="form-select" name="export" id="export">
        <option value="">Nie zapisuj</option>
        <option value="csv">CSV</option>
        <option value="txt">TXT</option>
      </select>
    </div>
  </div>
  <div class="text-center">
    <button type="submit" class="btn btn-primary">Porównaj</button>
  </div>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $amount1 = (float) $_POST['amount1'];
  $interest1 = (float) $_POST['interest1'];
  $months1 = (int) $_POST['months1'];

  $amount2 = (float) $_POST['amount2'];
  $interest2 = (float) $_POST['interest2'];
  $months2 = (int) $_POST['months2'];

  function calculateAnnuity($amount, $interest, $months) {
    $monthlyRate = $interest / 12 / 100;
    if ($monthlyRate > 0) {
      return ($amount * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));
    } else {
      return $amount / $months;
    }
  }

  $rata1 = calculateAnnuity($amount1, $interest1, $months1);
  $suma1 = $rata1 * $months1;

  $rata2 = calculateAnnuity($amount2, $interest2, $months2);
  $suma2 = $rata2 * $months2;

  echo "<div class='row mt-4'>";
  echo "<div class='col-md-6'><div class='alert alert-secondary'><h5>Kredyt 1</h5>";
  echo "<p>Rata miesięczna: <strong>" . number_format($rata1, 2, ',', ' ') . " zł</strong></p>";
  echo "<p>Łączna kwota do spłaty: <strong>" . number_format($suma1, 2, ',', ' ') . " zł</strong></p></div></div>";

  echo "<div class='col-md-6'><div class='alert alert-secondary'><h5>Kredyt 2</h5>";
  echo "<p>Rata miesięczna: <strong>" . number_format($rata2, 2, ',', ' ') . " zł</strong></p>";
  echo "<p>Łączna kwota do spłaty: <strong>" . number_format($suma2, 2, ',', ' ') . " zł</strong></p></div></div>";
  echo "</div>";

  echo "<div class='alert alert-info text-center mt-3'>";
  if ($suma1 < $suma2) {
    echo "Kredyt 1 jest korzystniejszy o <strong>" . number_format($suma2 - $suma1, 2, ',', ' ') . " zł</strong>.";
  } elseif ($suma2 < $suma1) {
    echo "Kredyt 2 jest korzystniejszy o <strong>" . number_format($suma1 - $suma2, 2, ',', ' ') . " zł</strong>.";
  } else {
    echo "Obie oferty są identyczne pod względem całkowitej spłaty.";
  }
  echo "</div>";
  echo "<div class='mt-4'>";
  echo "<canvas id='comparisonChart' height='100'></canvas>";
  echo "</div>";

  // Export logic
  if (!empty($_POST['export'])) {
    $export = $_POST['export'];
    $lines = [
      "Kredyt 1:",
      "Kwota: " . number_format($amount1, 2, ',', ' ') . " zł",
      "Oprocentowanie: " . number_format($interest1, 2, ',', ' ') . " %",
      "Okres: $months1 miesięcy",
      "Rata miesięczna: " . number_format($rata1, 2, ',', ' ') . " zł",
      "Suma spłat: " . number_format($suma1, 2, ',', ' ') . " zł",
      "",
      "Kredyt 2:",
      "Kwota: " . number_format($amount2, 2, ',', ' ') . " zł",
      "Oprocentowanie: " . number_format($interest2, 2, ',', ' ') . " %",
      "Okres: $months2 miesięcy",
      "Rata miesięczna: " . number_format($rata2, 2, ',', ' ') . " zł",
      "Suma spłat: " . number_format($suma2, 2, ',', ' ') . " zł",
    ];

    $summary = "";
    if ($suma1 < $suma2) {
      $summary = "Kredyt 1 jest korzystniejszy o " . number_format($suma2 - $suma1, 2, ',', ' ') . " zł.";
    } elseif ($suma2 < $suma1) {
      $summary = "Kredyt 2 jest korzystniejszy o " . number_format($suma1 - $suma2, 2, ',', ' ') . " zł.";
    } else {
      $summary = "Obie oferty są identyczne pod względem całkowitej spłaty.";
    }

    $lines[] = "";
    $lines[] = "Wniosek: " . $summary;

    if ($export === 'txt') {
      file_put_contents('../exports/porownanie_kredytow.txt', implode(PHP_EOL, $lines));
      echo "<div class='alert alert-info mt-3'>Plik <strong>porownanie_kredytow.txt</strong> został zapisany.</div>";
      echo "<div class='mt-2 text-center'>";
      echo "<a class='btn btn-outline-success' href='../exports/porownanie_kredytow.txt' download>Pobierz plik TXT</a>";
      echo "</div>";
    } elseif ($export === 'csv') {
      $csv = fopen('../exports/porownanie_kredytow.csv', 'w');
      fputcsv($csv, ['Oferta', 'Kwota', 'Oprocentowanie', 'Okres', 'Rata', 'Suma']);
      fputcsv($csv, ['Kredyt 1', $amount1, $interest1, $months1, round($rata1, 2), round($suma1, 2)]);
      fputcsv($csv, ['Kredyt 2', $amount2, $interest2, $months2, round($rata2, 2), round($suma2, 2)]);
      fputcsv($csv, []);
      fputcsv($csv, ['Wniosek', $summary]);
      fclose($csv);
      echo "<div class='alert alert-info mt-3'>Plik <strong>porownanie_kredytow.csv</strong> został zapisany.</div>";
      echo "<div class='mt-2 text-center'>";
      echo "<a class='btn btn-outline-success' href='../exports/porownanie_kredytow.csv' download>Pobierz plik CSV</a>";
      echo "</div>";
    }
  }
}
?>

<?php if ($_SERVER["REQUEST_METHOD"] === "POST") : ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('comparisonChart').getContext('2d');
  const comparisonChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Kredyt 1', 'Kredyt 2'],
      datasets: [{
        label: 'Kwota całkowita do spłaty (zł)',
        data: [<?= round($suma1, 2) ?>, <?= round($suma2, 2) ?>],
        backgroundColor: ['#0d6efd', '#6c757d']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: 'Porównanie całkowitych kosztów kredytu'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { callback: value => value.toLocaleString('pl-PL') + ' zł' }
        }
      }
    }
  });
</script>
<?php endif; ?>
<?php include('../footer.php'); ?>