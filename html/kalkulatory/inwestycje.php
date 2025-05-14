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
    } elseif ($type === 'compound') {
        $finalAmount = $amount * pow(1 + $rate / 100, $years);
        $profit = $finalAmount - $amount;
        echo "<div class='alert alert-info mt-4'>";
        echo "<h5 class='mb-3'>Procent składany:</h5>";
        echo "<p>Zysk z inwestycji: <strong>" . number_format($profit, 2, ',', ' ') . " zł</strong></p>";
        echo "<p>Kwota końcowa: <strong>" . number_format($finalAmount, 2, ',', ' ') . " zł</strong></p>";
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
            $file = fopen("../wynik_inwestycji.csv", "w");
            fputcsv($file, ['Kwota początkowa', 'Oprocentowanie (%)', 'Lata', 'Rodzaj', 'Zysk', 'Kwota końcowa']);
            fputcsv($file, [$amount, $rate, $years, $type, $profit, $finalAmount]);
            fclose($file);
            echo "<div class='alert alert-info'>Wynik został zapisany do pliku <strong>wynik_inwestycji.csv</strong>.</div>";
        } elseif ($export === 'txt') {
            $file = fopen("../wynik_inwestycji.txt", "w");
            foreach ($lines as $line) {
                fwrite($file, $line . PHP_EOL);
            }
            fclose($file);
            echo "<div class='alert alert-info'>Wynik został zapisany do pliku <strong>wynik_inwestycji.txt</strong>.</div>";
        }
    }
}
?>
<?php
include('../footer.php');
?>