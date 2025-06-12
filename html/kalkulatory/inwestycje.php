<?php
include('../header.php');
?>
<head>
<link href="https://fonts.googleapis.com/css2?family=Comic+Neue:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>
<h2 class="text-center my-4 fw-bold" style="font-family: 'Oswald', serif; font-size: 4rem;">Kalkulator inwestycji</h2>

<form method="post" action="">
  <div class="row mb-3">
    <div class="col-md-6">
      <label for="kwotaPoczatkowa" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Kwota początkowa (zł):</label>
      <input type="number" step="0.01" class="form-control" name="kwotaPoczatkowa" id="kwotaPoczatkowa" required>
    </div>
    <div class="col-md-6">
      <label for="oprocentowanie" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Oprocentowanie roczne (%):</label>
      <input type="number" step="0.01" class="form-control" name="oprocentowanie" id="oprocentowanie" required>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-6">
      <label for="okresLata" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Okres inwestycji (lata):</label>
      <input type="number" class="form-control" name="okresLata" id="okresLata" required>
    </div>
    <div class="col-md-6">
      <label for="rodzajOdsetek" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Rodzaj odsetek:</label>
      <select class="form-select" name="rodzajOdsetek" id="rodzajOdsetek">
        <option value="simple">Procent prosty</option>
        <option value="compound">Procent składany</option>
      </select>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-6">
      <label for="eksport" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Zapisz wynik do pliku:</label>
      <select class="form-select" name="eksport" id="eksport">
        <option value="">Nie zapisuj</option>
        <option value="csv">CSV</option>
        <option value="txt">TXT</option>
      </select>
    </div>
  </div>
  <div class="text-center">
    <button type="submit" class="btn btn-primary" style="font-family: 'Oswald', serif; font-size: 2rem;">Oblicz zysk</button>
  </div>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kwota_poczatkowa = (float) $_POST['kwotaPoczatkowa'];
    $oprocentowanie = (float) $_POST['oprocentowanie'];
    $okres_lata = (int) $_POST['okresLata'];
    $rodzaj_odsetek = $_POST['rodzajOdsetek'];

    if ($rodzaj_odsetek === 'simple') {
        $zysk = $kwota_poczatkowa * ($oprocentowanie / 100) * $okres_lata;
        $kwota_koncowa = $kwota_poczatkowa + $zysk;
        echo "<div class='alert alert-success mt-4'>";
        echo "<h5 class='mb-3' style='font-family: \"Oswald\", serif; font-size: 2rem;'>Procent prosty:</h5>";
        echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Zysk z inwestycji: <strong>" . number_format($zysk, 2, ',', ' ') . " zł</strong></p>";
        echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Kwota końcowa: <strong>" . number_format($kwota_koncowa, 2, ',', ' ') . " zł</strong></p>";
        echo "</div>";
        echo "<div class='mt-4'>";
        echo "<canvas id='investmentChart' height='100'></canvas>";
        echo "</div>";
    } elseif ($rodzaj_odsetek === 'compound') {
        $kwota_koncowa = $kwota_poczatkowa * pow(1 + $oprocentowanie / 100, $okres_lata);
        $zysk = $kwota_koncowa - $kwota_poczatkowa;
        echo "<div class='alert alert-info mt-4'>";
        echo "<h5 class='mb-3' style='font-family: \"Oswald\", serif; font-size: 1rem;'>Procent składany:</h5>";
        echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Zysk z inwestycji: <strong>" . number_format($zysk, 2, ',', ' ') . " zł</strong></p>";
        echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Kwota końcowa: <strong>" . number_format($kwota_koncowa, 2, ',', ' ') . " zł</strong></p>";
        echo "</div>";
        echo "<div class='mt-4'>";
        echo "<canvas id='investmentChart' height='100'></canvas>";
        echo "</div>";
    }

    if (!empty($_POST['eksport'])) {
		echo "<div class='bg-white p-4 rounded shadow-sm mt-4'>";
        $eksport = $_POST['eksport'];
        $lines = [
            "Kwota początkowa: " . number_format($kwota_poczatkowa, 2, ',', ' ') . " zł",
            "Oprocentowanie: " . number_format($oprocentowanie, 2, ',', ' ') . " %",
            "Czas trwania: $okres_lata lata",
            "Rodzaj odsetek: " . ($rodzaj_odsetek === 'simple' ? 'Procent prosty' : 'Procent składany'),
            "Zysk: " . number_format($zysk, 2, ',', ' ') . " zł",
            "Kwota końcowa: " . number_format($kwota_koncowa, 2, ',', ' ') . " zł"
        ];

        if ($eksport === 'csv') {
            $file = fopen(__DIR__ . "/../exports/wynik_inwestycji.csv", "w");
            fputcsv($file, ['Kwota początkowa', 'Oprocentowanie (%)', 'Lata', 'Rodzaj', 'Zysk', 'Kwota końcowa']);
            fputcsv($file, [$kwota_poczatkowa, $oprocentowanie, $okres_lata, $rodzaj_odsetek, $zysk, $kwota_koncowa]);
            fclose($file);
            echo "<div class='alert alert-info'>Wynik został zapisany do pliku <strong>wynik_inwestycji.csv</strong>.</div>";
            echo "<div class='mt-2 text-center'>";
            echo "<a class='btn btn-outline-success' href='../exports/wynik_inwestycji.csv' download>Pobierz plik CSV</a>";
            echo "</div>";
        } elseif ($eksport === 'txt') {
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
		echo "</div>";
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('investmentChart')?.getContext('2d');

  // Wtyczka do tła
  const whiteBackground = {
    id: 'whiteBackground',
    beforeDraw: (chart) => {
      const ctx = chart.canvas.getContext('2d');
      ctx.save();
      ctx.globalCompositeOperation = 'destination-over';
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, chart.width, chart.height);
      ctx.restore();
    }
  };

  if (ctx) {
    // Gradienty dla słupków
    const gradient1 = ctx.createLinearGradient(0, 0, 0, 300);
    gradient1.addColorStop(0, '#6c757d');
    gradient1.addColorStop(1, '#adb5bd');

    const gradient2 = ctx.createLinearGradient(0, 0, 0, 300);
    gradient2.addColorStop(0, '#0d6efd');
    gradient2.addColorStop(1, '#74c0fc');

    const gradient3 = ctx.createLinearGradient(0, 0, 0, 300);
    gradient3.addColorStop(0, '#198754');
    gradient3.addColorStop(1, '#69db7c');

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Kwota początkowa', 'Zysk', 'Kwota końcowa'],
        datasets: [{
          label: 'Wartości inwestycji (zł)',
          data: [<?= round($kwota_poczatkowa, 2) ?>, <?= round($zysk, 2) ?>, <?= round($kwota_koncowa, 2) ?>],
          backgroundColor: [gradient1, gradient2, gradient3],
          borderRadius: 10,
          barThickness: 50
        }]
      },
      options: {
        responsive: true,
        animation: {
          duration: 1000,
          easing: 'easeOutBounce'
        },
        plugins: {
          legend: {
            display: false
          },
          title: {
            display: true,
            text: 'Wynik inwestycji',
            color: '#333',
            font: {
              size: 20,
              weight: 'bold'
            },
            padding: {
              top: 20,
              bottom: 20
            }
          },
          tooltip: {
            backgroundColor: '#f8f9fa',
            titleColor: '#000',
            bodyColor: '#000',
            borderColor: '#ccc',
            borderWidth: 1
          }
        },
        scales: {
          x: {
            ticks: {
              color: '#495057',
              font: {
                weight: 'bold'
              }
            },
            grid: {
              display: false
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => value.toLocaleString('pl-PL') + ' zł',
              color: '#495057'
            },
            grid: {
              color: '#dee2e6'
            }
          }
        }
      },
      plugins: [whiteBackground]
    });
  }
</script>
<?php
include('../footer.php');
?>