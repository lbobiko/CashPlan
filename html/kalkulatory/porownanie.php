<?php include('../header.php'); ?>
<head>
<link href="https://fonts.googleapis.com/css2?family=Comic+Neue:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>
<h2 class="text-center my-4" style="font-family: 'Oswald', serif; font-size: 4rem;">Porównywarka kredytów</h2>

<form method="post" action="">
  <div class="mb-3 text-center">
    <label for="liczba_ofert" class="form-label" style="font-family: 'Oswald', serif; font-size: 1rem;">Ile ofert chcesz porównać?</label>
    <select class="form-select w-auto d-inline-block" name="liczba_ofert" id="liczba_ofert" onchange="this.form.submit()">
      <?php for ($o = 2; $o <= 4; $o++): ?>
        <option value="<?= $o ?>" <?= (isset($_POST['liczba_ofert']) && $_POST['liczba_ofert'] == $o) ? 'selected' : '' ?>><?= $o ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="row mb-4">
    <?php
    $liczba_ofert = isset($_POST['liczba_ofert']) ? (int)$_POST['liczba_ofert'] : 2;
    for ($i = 1; $i <= $liczba_ofert; $i++): ?>
      <div class="col-md-6" id="oferta<?= $i ?>">
        <h5 style="font-family: 'Oswald', serif; font-size: 2rem;">Kredyt <?= $i ?></h5>
        <label class="form-label" style="font-family: 'Oswald', serif; font-size: 1.5rem;" for="kwota<?= $i ?>">Kwota (zł):</label>
        <input type="number" step="0.01" class="form-control" name="kwota<?= $i ?>" id="kwota<?= $i ?>" required>

        <label class="form-label mt-2" style="font-family: 'Oswald', serif; font-size: 1.5rem;" for="oprocentowanie<?= $i ?>">Oprocentowanie roczne (%):</label>
        <input type="number" step="0.01" class="form-control" name="oprocentowanie<?= $i ?>" id="oprocentowanie<?= $i ?>" required>

        <label class="form-label mt-2" style="font-family: 'Oswald', serif; font-size: 1.5rem;" for="liczbaMiesiecy<?= $i ?>">Liczba miesięcy:</label>
        <input type="number" class="form-control" name="liczbaMiesiecy<?= $i ?>" id="liczbaMiesiecy<?= $i ?>" required>

        <label class="form-label mt-2" style="font-family: 'Oswald', serif; font-size: 1.5rem;" for="rodzajRat<?= $i ?>">Rodzaj rat:</label>
        <select class="form-select" name="rodzajRat<?= $i ?>" id="rodzajRat<?= $i ?>">
          <option value="rowne">Równe</option>
          <option value="malejace">Malejące</option>
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
    const select = document.getElementById("liczba_ofert");
    const updateForm = () => {
      const count = parseInt(select.value);
      for (let i = 1; i <= 4; i++) {
        const container = document.getElementById(`oferta${i}`);
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
  $liczba_ofert = isset($_POST['liczba_ofert']) ? (int)$_POST['liczba_ofert'] : 2;

  function oblicz_rate_stala($kwota, $oprocentowanie, $liczba_miesiecy) {
    $miesieczna_stopa = $oprocentowanie / 12 / 100;
    if ($miesieczna_stopa > 0) {
      return ($kwota * $miesieczna_stopa) / (1 - pow(1 + $miesieczna_stopa, -$liczba_miesiecy));
    } else {
      return $kwota / $liczba_miesiecy;
    }
  }

  // Generowanie harmonogramu dla rat równych
  function generuj_harmonogram_staly($kwota, $oprocentowanie, $liczba_miesiecy, $rata) {
    $lista_rat = [];
    $pozostalo_do_splaty = $kwota;
    for ($i = 1; $i <= $liczba_miesiecy; $i++) {
      $czesc_odsetkowa = ($pozostalo_do_splaty * $oprocentowanie / 100) / 12;
      $czesc_kapitalowa = $rata - $czesc_odsetkowa;
      $lista_rat[] = [
        'miesiac' => $i,
        'rata' => round($rata, 2),
        'kapital' => round($czesc_kapitalowa, 2),
        'odsetki' => round($czesc_odsetkowa, 2),
        'pozostalo' => round($pozostalo_do_splaty, 2)
      ];
      $pozostalo_do_splaty -= $czesc_kapitalowa;
    }
    return $lista_rat;
  }

  function oblicz_sume_malejaca($kwota, $oprocentowanie, $liczba_miesiecy) {
    $czesc_kapitalowa = $kwota / $liczba_miesiecy;
    $suma_splat = 0;
    $pozostalo_do_splaty = $kwota;
    for ($i = 1; $i <= $liczba_miesiecy; $i++) {
      $czesc_odsetkowa = ($pozostalo_do_splaty * $oprocentowanie / 100) / 12;
      $rata = $czesc_kapitalowa + $czesc_odsetkowa;
      $suma_splat += $rata;
      $pozostalo_do_splaty -= $czesc_kapitalowa;
    }
    return $suma_splat;
  }

  function generuj_harmonogram_malejacy($kwota, $oprocentowanie, $liczba_miesiecy) {
    $lista_rat = [];
    $czesc_kapitalowa = $kwota / $liczba_miesiecy;
    $pozostalo_do_splaty = $kwota;

    for ($i = 1; $i <= $liczba_miesiecy; $i++) {
      $czesc_odsetkowa = ($pozostalo_do_splaty * $oprocentowanie / 100) / 12;
      $rata = $czesc_kapitalowa + $czesc_odsetkowa;
      $lista_rat[] = [
        'miesiac' => $i,
        'rata' => round($rata, 2),
        'kapital' => round($czesc_kapitalowa, 2),
        'odsetki' => round($czesc_odsetkowa, 2),
        'pozostalo' => round($pozostalo_do_splaty, 2)
      ];
      $pozostalo_do_splaty -= $czesc_kapitalowa;
    }

    return $lista_rat;
  }

  $raty_miesieczne = [];
  $sumy_splat = [];
  $etykiety_kredytow = [];
  $linijki_podsumowania = [];

  echo "<div class='row mt-4'>";
  for ($i = 1; $i <= $liczba_ofert; $i++) {
    $klucz_kwota = "kwota$i";
    $klucz_oprocentowanie = "oprocentowanie$i";
    $klucz_liczba_miesiecy = "liczbaMiesiecy$i";
    $klucz_rodzaj_rat = "rodzajRat$i";

    if (
      !isset($_POST[$klucz_kwota], $_POST[$klucz_oprocentowanie], $_POST[$klucz_liczba_miesiecy]) ||
      $_POST[$klucz_kwota] === '' || $_POST[$klucz_liczba_miesiecy] === ''
    ) {
      echo "<div class='alert alert-danger'>Błąd: Nieprawidłowe dane dla Kredytu $i. Upewnij się, że wszystkie pola są wypełnione.</div>";
      continue;
    }

    $kwota = (float) $_POST[$klucz_kwota];
    $oprocentowanie = (float) $_POST[$klucz_oprocentowanie];
    $liczba_miesiecy = (int) $_POST[$klucz_liczba_miesiecy];
    $rodzaj_rat = $_POST[$klucz_rodzaj_rat] ?? 'rowne';

    if ($kwota <= 0 || $liczba_miesiecy <= 0) {
      echo "<div class='alert alert-danger'>Błąd: Nieprawidłowe dane dla Kredytu $i. Upewnij się, że kwota i liczba miesięcy są większe od zera.</div>";
      continue;
    }

    if ($rodzaj_rat === 'malejace') {
      $rata = null;
      $lista_rat = generuj_harmonogram_malejacy($kwota, $oprocentowanie, $liczba_miesiecy);
      $suma_splat = array_sum(array_column($lista_rat, 'rata'));
    } else {
      $rata = oblicz_rate_stala($kwota, $oprocentowanie, $liczba_miesiecy);
      $suma_splat = $rata * $liczba_miesiecy;
      // Generowanie harmonogramu rat równych
      $lista_rat = generuj_harmonogram_staly($kwota, $oprocentowanie, $liczba_miesiecy, $rata);
    }

    $raty_miesieczne[$i] = $rata;
    $sumy_splat[$i] = $suma_splat;
    $etykiety_kredytow[$i] = "Kredyt $i";

    echo "<div class='col-md-6'><div class='alert alert-secondary' style='font-family: \"Oswald\", serif; font-size: 1rem;'>";
    echo "<h5>Kredyt $i (" . ($rodzaj_rat === 'malejace' ? "malejące" : "równe") . ")</h5>";
    if ($rata !== null) {
      echo "<p>Rata miesięczna: <strong>" . number_format($rata, 2, ',', ' ') . " zł</strong></p>";
    }
    echo "<p>Łączna kwota do spłaty: <strong>" . number_format($suma_splat, 2, ',', ' ') . " zł</strong></p></div>";

    if (!empty($lista_rat)) {
      echo "<div class='table-responsive'><table class='table table-sm table-bordered mt-3'>";
      echo "<thead>
	  <tr  style='font-family: \"Oswald\", serif; font-size: 1rem; text-align: center;'><th>Miesiąc</th>
	  <th style='font-family: \"Oswald\", serif; font-size: 1rem; text-align: center;'>Rata</th>
	  <th style='font-family: \"Oswald\", serif; font-size: 1rem; text-align: center;'>Kapitał</th>
	  <th style='font-family: \"Oswald\", serif; font-size: 1rem; text-align: center;'>Odsetki</th>
	  <th style='font-family: \"Oswald\", serif; font-size: 1rem; text-align: center;'>Pozostało</th>
	  </tr></thead><tbody>";
      foreach ($lista_rat as $row) {
        echo "<tr>
		<td style='font-family: \"Oswald\", serif; font-size: 1rem;' >{$row['miesiac']}</td>
		<td style='font-family: \"Oswald\", serif; font-size: 1rem;'>{$row['rata']} zł</td>
		<td style='font-family: \"Oswald\", serif; font-size: 1rem;'>{$row['kapital']} zł</td>
		<td style='font-family: \"Oswald\", serif; font-size: 1rem;'>{$row['odsetki']} zł</td>
		<td style='font-family: \"Oswald\", serif; font-size: 1rem;'>{$row['pozostalo']} zł</td></tr>";
      }
      echo "</tbody></table></div>";
    }

    echo "</div>";

    $linijki_podsumowania[] = "Kredyt $i:";
    $linijki_podsumowania[] = "Kwota: " . number_format($kwota, 2, ',', ' ') . " zł";
    $linijki_podsumowania[] = "Oprocentowanie: " . number_format($oprocentowanie, 2, ',', ' ') . " %";
    $linijki_podsumowania[] = "Okres: $liczba_miesiecy miesięcy";
    $linijki_podsumowania[] = "Rodzaj rat: " . ($rodzaj_rat === 'malejace' ? 'malejące' : 'równe');
    if ($rata !== null) {
      $linijki_podsumowania[] = "Rata miesięczna: " . number_format($rata, 2, ',', ' ') . " zł";
    }
    $linijki_podsumowania[] = "Suma spłat: " . number_format($suma_splat, 2, ',', ' ') . " zł";
    $linijki_podsumowania[] = "";
  }
  echo "</div>";

  if (empty($sumy_splat)) {
    echo "<div class='alert alert-warning text-center'>Brak poprawnych danych do porównania.</div>";
    return;
  }

  $minKey = array_keys($sumy_splat, min($sumy_splat))[0];
  $minValue = $sumy_splat[$minKey];
  $summaryText = "Najkorzystniejszy jest <strong>Kredyt $minKey</strong> o łącznym koszcie " . number_format($minValue, 2, ',', ' ') . " zł.";

  echo "<div class='alert alert-info text-center mt-3' style='font-family: \"Oswald\", serif; font-size: 2rem;'>$summaryText</div>";
  echo "<div class='mt-4'>";
  echo "<canvas id='comparisonChart' height='100'></canvas>";
  echo "</div>";

  // Eksport porównania do pliku
  if (!empty($_POST['export'])) {
    $export = $_POST['export'];

    $lines = $linijki_podsumowania;

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
      for ($i = 1; $i <= $liczba_ofert; $i++) {
        $kwota = (float) $_POST["kwota$i"];
        $oprocentowanie = (float) $_POST["oprocentowanie$i"];
        $liczba_miesiecy = (int) $_POST["liczbaMiesiecy$i"];
        $rodzaj_rat = $_POST["rodzajRat$i"] ?? 'rowne';
        fputcsv($csv, [
          "Kredyt $i",
          $kwota,
          $oprocentowanie,
          $liczba_miesiecy,
          $rodzaj_rat,
          $raty_miesieczne[$i] ?? '',
          round($sumy_splat[$i], 2)
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
  const ctx = document.getElementById('comparisonChart').getContext('2d');
const comparisonChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_values($etykiety_kredytow)) ?>,
    datasets: [{
      label: 'Kwota całkowita do spłaty (zł)',
      data: <?= json_encode(array_values(array_map(fn($v) => round($v, 2), $sumy_splat))) ?>,
      backgroundColor: ['#0d6efd', '#6c757d', '#198754', '#dc3545'],
      borderRadius: 8
    }]
  },
  options: {
    responsive: true,
    animation: {
      duration: 1000,
      easing: 'easeOutBounce'
    },
    plugins: {
      legend: { display: false },
      title: {
        display: true,
        text: 'Porównanie całkowitych kosztów kredytu'
      },
      tooltip: {
        callbacks: {
          label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('pl-PL') + ' zł'
        }
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
  },
  plugins: [{
    id: 'custom_canvas_background_color',
    beforeDraw: (chart) => {
      const ctx = chart.canvas.getContext('2d');
      ctx.save();
      ctx.globalCompositeOperation = 'destination-over';
      ctx.fillStyle = 'white';
      ctx.fillRect(0, 0, chart.width, chart.height);
      ctx.restore();
    }
  }]
});
  
</script>
<?php endif; ?>
<?php include('../footer.php'); ?>