<?php include('../header.php'); ?>

<h2 class="text-center my-4">Porównywarka kredytów</h2>

<form method="post" action="">
  <div class="mb-3 text-center">
    <label for="offersCount" class="form-label">Ile ofert chcesz porównać?</label>
    <select class="form-select w-auto d-inline-block" name="offersCount" id="offersCount" onchange="this.form.submit()">
      <?php for ($o = 2; $o <= 4; $o++): ?>
        <option value="<?= $o ?>" <?= (isset($_POST['offersCount']) && $_POST['offersCount'] == $o) ? 'selected' : '' ?>><?= $o ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="row mb-4">
    <?php
    $offersCount = isset($_POST['offersCount']) ? (int)$_POST['offersCount'] : 2;
    for ($i = 1; $i <= $offersCount; $i++): ?>
      <div class="col-md-6" id="offer<?= $i ?>">
        <h5>Kredyt <?= $i ?></h5>
        <label class="form-label" for="amount<?= $i ?>">Kwota (zł):</label>
        <input type="number" step="0.01" class="form-control" name="amount<?= $i ?>" id="amount<?= $i ?>" required>

        <label class="form-label mt-2" for="interest<?= $i ?>">Oprocentowanie roczne (%):</label>
        <input type="number" step="0.01" class="form-control" name="interest<?= $i ?>" id="interest<?= $i ?>" required>

        <label class="form-label mt-2" for="months<?= $i ?>">Liczba miesięcy:</label>
        <input type="number" class="form-control" name="months<?= $i ?>" id="months<?= $i ?>" required>

        <label class="form-label mt-2" for="type<?= $i ?>">Rodzaj rat:</label>
        <select class="form-select" name="type<?= $i ?>" id="type<?= $i ?>">
          <option value="annuity">Równe</option>
          <option value="declining">Malejące</option>
        </select>
      </div>
    <?php endfor; ?>
  </div>
  <!-- Wybór formatu eksportu -->
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
<!-- End of form -->
</form>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const select = document.getElementById("offersCount");
    const updateForm = () => {
      const count = parseInt(select.value);
      for (let i = 1; i <= 4; i++) {
        const container = document.getElementById(`offer${i}`);
        if (!container) continue;
        const inputs = container.querySelectorAll("input");
        if (i <= count) {
          container.style.display = "block";
          inputs.forEach(el => el.required = true);
        } else {
          container.style.display = "none";
          inputs.forEach(el => el.required = false);
        }
      }
    };
    select.addEventListener("change", updateForm);
    updateForm(); // inicjalizacja przy ładowaniu strony
  });
</script>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $offersCount = isset($_POST['offersCount']) ? (int)$_POST['offersCount'] : 2;

  function calculateAnnuity($amount, $interest, $months) {
    $monthlyRate = $interest / 12 / 100;
    if ($monthlyRate > 0) {
      return ($amount * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));
    } else {
      return $amount / $months;
    }
  }

  // Generowanie harmonogramu dla rat równych
  function generateAnnuitySchedule($amount, $interest, $months, $rate) {
    $schedule = [];
    $remaining = $amount;
    for ($i = 1; $i <= $months; $i++) {
      $interestPart = ($remaining * $interest / 100) / 12;
      $capitalPart = $rate - $interestPart;
      $schedule[] = [
        'month' => $i,
        'payment' => round($rate, 2),
        'capital' => round($capitalPart, 2),
        'interest' => round($interestPart, 2),
        'remaining' => round($remaining, 2)
      ];
      $remaining -= $capitalPart;
    }
    return $schedule;
  }

  function calculateDecliningTotal($amount, $interest, $months) {
    $capitalPart = $amount / $months;
    $total = 0;
    $remaining = $amount;
    for ($i = 1; $i <= $months; $i++) {
      $interestPart = ($remaining * $interest / 100) / 12;
      $payment = $capitalPart + $interestPart;
      $total += $payment;
      $remaining -= $capitalPart;
    }
    return $total;
  }

  function generateDecliningSchedule($amount, $interest, $months) {
    $schedule = [];
    $capitalPart = $amount / $months;
    $remaining = $amount;

    for ($i = 1; $i <= $months; $i++) {
      $interestPart = ($remaining * $interest / 100) / 12;
      $payment = $capitalPart + $interestPart;
      $schedule[] = [
        'month' => $i,
        'payment' => round($payment, 2),
        'capital' => round($capitalPart, 2),
        'interest' => round($interestPart, 2),
        'remaining' => round($remaining, 2)
      ];
      $remaining -= $capitalPart;
    }

    return $schedule;
  }

  $rates = [];
  $totals = [];
  $labels = [];
  $summaryLines = [];

  echo "<div class='row mt-4'>";
  for ($i = 1; $i <= $offersCount; $i++) {
    $amountKey = "amount$i";
    $interestKey = "interest$i";
    $monthsKey = "months$i";
    $typeKey = "type$i";

    if (
      !isset($_POST[$amountKey], $_POST[$interestKey], $_POST[$monthsKey]) ||
      $_POST[$amountKey] === '' || $_POST[$monthsKey] === ''
    ) {
      echo "<div class='alert alert-danger'>Błąd: Nieprawidłowe dane dla Kredytu $i. Upewnij się, że wszystkie pola są wypełnione.</div>";
      continue;
    }

    $amount = (float) $_POST[$amountKey];
    $interest = (float) $_POST[$interestKey];
    $months = (int) $_POST[$monthsKey];
    $type = $_POST[$typeKey] ?? 'annuity';

    if ($amount <= 0 || $months <= 0) {
      echo "<div class='alert alert-danger'>Błąd: Nieprawidłowe dane dla Kredytu $i. Upewnij się, że kwota i liczba miesięcy są większe od zera.</div>";
      continue;
    }

    if ($type === 'declining') {
      $rate = null;
      $schedule = generateDecliningSchedule($amount, $interest, $months);
      $total = array_sum(array_column($schedule, 'payment'));
    } else {
      $rate = calculateAnnuity($amount, $interest, $months);
      $total = $rate * $months;
      // Generowanie harmonogramu rat równych
      $schedule = generateAnnuitySchedule($amount, $interest, $months, $rate);
    }

    $rates[$i] = $rate;
    $totals[$i] = $total;
    $labels[$i] = "Kredyt $i";

    echo "<div class='col-md-6'><div class='alert alert-secondary'>";
    echo "<h5>Kredyt $i (" . ($type === 'declining' ? "malejące" : "równe") . ")</h5>";
    if ($rate !== null) {
      echo "<p>Rata miesięczna: <strong>" . number_format($rate, 2, ',', ' ') . " zł</strong></p>";
    }
    echo "<p>Łączna kwota do spłaty: <strong>" . number_format($total, 2, ',', ' ') . " zł</strong></p></div>";

    if (!empty($schedule)) {
      echo "<div class='table-responsive'><table class='table table-sm table-bordered mt-3'>";
      echo "<thead><tr><th>Miesiąc</th><th>Rata</th><th>Kapitał</th><th>Odsetki</th><th>Pozostało</th></tr></thead><tbody>";
      foreach ($schedule as $row) {
        echo "<tr><td>{$row['month']}</td><td>{$row['payment']} zł</td><td>{$row['capital']} zł</td><td>{$row['interest']} zł</td><td>{$row['remaining']} zł</td></tr>";
      }
      echo "</tbody></table></div>";
    }

    echo "</div>";

    $summaryLines[] = "Kredyt $i:";
    $summaryLines[] = "Kwota: " . number_format($amount, 2, ',', ' ') . " zł";
    $summaryLines[] = "Oprocentowanie: " . number_format($interest, 2, ',', ' ') . " %";
    $summaryLines[] = "Okres: $months miesięcy";
    $summaryLines[] = "Rodzaj rat: " . ($type === 'declining' ? 'malejące' : 'równe');
    if ($rate !== null) {
      $summaryLines[] = "Rata miesięczna: " . number_format($rate, 2, ',', ' ') . " zł";
    }
    $summaryLines[] = "Suma spłat: " . number_format($total, 2, ',', ' ') . " zł";
    $summaryLines[] = "";
  }
  echo "</div>";

  if (empty($totals)) {
    echo "<div class='alert alert-warning text-center'>Brak poprawnych danych do porównania.</div>";
    return;
  }

  $minKey = array_keys($totals, min($totals))[0];
  $minValue = $totals[$minKey];
  $summaryText = "Najkorzystniejszy jest <strong>Kredyt $minKey</strong> o łącznym koszcie " . number_format($minValue, 2, ',', ' ') . " zł.";

  echo "<div class='alert alert-info text-center mt-3'>$summaryText</div>";
  echo "<div class='mt-4'>";
  echo "<canvas id='comparisonChart' height='100'></canvas>";
  echo "</div>";

  // Eksport porównania do pliku
  if (!empty($_POST['export'])) {
    $export = $_POST['export'];

    $lines = $summaryLines;

    $lines[] = "";
    $lines[] = "Wniosek: " . strip_tags($summaryText);

    if ($export === 'txt') {
      file_put_contents('../exports/porownanie_kredytow.txt', implode(PHP_EOL, $lines));
      echo "<div class='alert alert-info mt-3'>Plik <strong>porownanie_kredytow.txt</strong> został zapisany.</div>";
      echo "<div class='mt-2 text-center'>";
      echo "<a class='btn btn-outline-success' href='../exports/porownanie_kredytow.txt' download>Pobierz plik TXT</a>";
      echo "</div>";
    } elseif ($export === 'csv') {
      $csv = fopen('../exports/porownanie_kredytow.csv', 'w');
      fputcsv($csv, ['Oferta', 'Kwota', 'Oprocentowanie', 'Okres', 'Typ rat', 'Rata', 'Suma']);
      for ($i = 1; $i <= $offersCount; $i++) {
        $amount = (float) $_POST["amount$i"];
        $interest = (float) $_POST["interest$i"];
        $months = (int) $_POST["months$i"];
        $type = $_POST["type$i"] ?? 'annuity';
        fputcsv($csv, [
          "Kredyt $i",
          $amount,
          $interest,
          $months,
          $type,
          $rates[$i] ?? '',
          round($totals[$i], 2)
        ]);
      }
      fputcsv($csv, []);
      fputcsv($csv, ['Wniosek', strip_tags($summaryText)]);
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
<!-- Renderowanie wykresu Chart.js po przesłaniu formularza -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('comparisonChart')?.getContext('2d');
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_values($labels)) ?>,
        datasets: [{
          label: 'Kwota całkowita do spłaty (zł)',
          data: <?= json_encode(array_values(array_map(fn($v) => round($v, 2), $totals))) ?>,
          backgroundColor: ['#0d6efd', '#6c757d', '#198754', '#dc3545']
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
  }
</script>
<?php endif; ?>
<?php include('../footer.php'); ?>