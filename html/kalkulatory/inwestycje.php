<?php
include('../header.php');
?>

<h2 class="text-center my-4">Kalkulator inwestycji</h2>

<form method="post" action="">
  <div class="row mb-3">
    <div class="col-md-6">
      <label for="amount" class="form-label">Kwota początkowa (zł):</label>
      <input type="number" step="0.01" class="form-control" name="amount" id="amount" required>
    </div>
    <div class="col-md-6">
      <label for="rate" class="form-label">Oprocentowanie roczne (%):</label>
      <input type="number" step="0.01" class="form-control" name="rate" id="rate" required>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-6">
      <label for="years" class="form-label">Okres inwestycji (lata):</label>
      <input type="number" class="form-control" name="years" id="years" required>
    </div>
    <div class="col-md-6">
      <label for="type" class="form-label">Rodzaj odsetek:</label>
      <select class="form-select" name="type" id="type">
        <option value="simple">Procent prosty</option>
        <option value="compound">Procent składany</option>
      </select>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-6">
      <label for="export" class="form-label">Zapisz wynik do pliku:</label>
      <select class="form-select" name="export" id="export">
        <option value="">Nie zapisuj</option>
        <option value="csv">CSV</option>
        <option value="txt">TXT</option>
      </select>
    </div>
  </div>
  <div class="text-center">
    <button type="submit" class="btn btn-primary">Oblicz zysk</button>
  </div>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = (float) $_POST['amount'];
    $rate = (float) $_POST['rate'];
    $years = (int) $_POST['years'];
    $type = $_POST['type'];

    if ($type === 'simple') {
        $profit = $amount * ($rate / 100) * $years;
        $finalAmount = $amount + $profit;
        echo "<div class='alert alert-success mt-4'>";
        echo "<h5 class='mb-3'>Procent prosty:</h5>";
        echo "<p>Zysk z inwestycji: <strong>" . number_format($profit, 2, ',', ' ') . " zł</strong></p>";
        echo "<p>Kwota końcowa: <strong>" . number_format($finalAmount, 2, ',', ' ') . " zł</strong></p>";
        echo "</div>";
        echo "<div class='mt-4'>";
        echo "<canvas id='investmentChart' height='100'></canvas>";
        echo "</div>";
    } elseif ($type === 'compound') {
        $finalAmount = $amount * pow(1 + $rate / 100, $years);
        $profit = $finalAmount - $amount;
        echo "<div class='alert alert-info mt-4'>";
        echo "<h5 class='mb-3'>Procent składany:</h5>";
        echo "<p>Zysk z inwestycji: <strong>" . number_format($profit, 2, ',', ' ') . " zł</strong></p>";
        echo "<p>Kwota końcowa: <strong>" . number_format($finalAmount, 2, ',', ' ') . " zł</strong></p>";
        echo "</div>";
        echo "<div class='mt-4'>";
        echo "<canvas id='investmentChart' height='100'></canvas>";
        echo "</div>";
    }

    if (!empty($_POST['export'])) {
        $export = $_POST['export'];
        $lines = [
            "Kwota początkowa: " . number_format($amount, 2, ',', ' ') . " zł",
            "Oprocentowanie: " . number_format($rate, 2, ',', ' ') . " %",
            "Czas trwania: $years lata",
            "Rodzaj odsetek: " . ($type === 'simple' ? 'Procent prosty' : 'Procent składany'),
            "Zysk: " . number_format($profit, 2, ',', ' ') . " zł",
            "Kwota końcowa: " . number_format($finalAmount, 2, ',', ' ') . " zł"
        ];

        if ($export === 'csv') {
            $file = fopen(__DIR__ . "/../exports/wynik_inwestycji.csv", "w");
            fputcsv($file, ['Kwota początkowa', 'Oprocentowanie (%)', 'Lata', 'Rodzaj', 'Zysk', 'Kwota końcowa']);
            fputcsv($file, [$amount, $rate, $years, $type, $profit, $finalAmount]);
            fclose($file);
            echo "<div class='alert alert-info'>Wynik został zapisany do pliku <strong>wynik_inwestycji.csv</strong>.</div>";
            echo "<div class='mt-2 text-center'>";
            echo "<a class='btn btn-outline-success' href='../exports/wynik_inwestycji.csv' download>Pobierz plik CSV</a>";
            echo "</div>";
        } elseif ($export === 'txt') {
            $file = fopen(__DIR__ . "/../exports/wynik_inwestycji.txt", "w");
            foreach ($lines as $line) {
                fwrite($file, $line . PHP_EOL);
            }
            fclose($file);
            echo "<div class='alert alert-info'>Wynik został zapisany do pliku <strong>wynik_inwestycji.txt</strong>.</div>";
            echo "<div class='mt-2 text-center'>";
            echo "<a class='btn btn-outline-success' href='../exports/wynik_inwestycji.txt' download>Pobierz plik TXT</a>";
            echo "</div>";
        }
    }
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('investmentChart')?.getContext('2d');
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Kwota początkowa', 'Zysk', 'Kwota końcowa'],
        datasets: [{
          label: 'Wartości inwestycji (zł)',
          data: [<?= round($amount, 2) ?>, <?= round($profit, 2) ?>, <?= round($finalAmount, 2) ?>],
          backgroundColor: ['#6c757d', '#0d6efd', '#198754']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: {
            display: true,
            text: 'Wynik inwestycji'
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
<?php
include('../footer.php');
?>