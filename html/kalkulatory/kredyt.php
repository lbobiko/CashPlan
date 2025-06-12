<?php include('../header.php'); ?>
<head>
<link href="https://fonts.googleapis.com/css2?family=Comic+Neue:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>
<h2 class="text-center my-4 fw-bold " class="form-label " style="font-family: 'Oswald', serif; font-size: 4rem;">Kalkulator rat kredytu</h2>

<form method="post" action="">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="kwota_kredytu" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Kwota kredytu (zł):</label>
            <input type="number" step="0.01" class="form-control" name="kwota_kredytu" id="kwota_kredytu" required>
        </div>
        <div class="col-md-6">
            <label for="oprocentowanie_roczne" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Oprocentowanie roczne (%):</label>
            <input type="number" step="0.01" class="form-control" name="oprocentowanie_roczne" id="oprocentowanie_roczne" required>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="okres_kredytowania" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Liczba miesięcy:</label>
            <input type="number" class="form-control" name="okres_kredytowania" id="okres_kredytowania" required>
        </div>
        <div class="col-md-6">
            <label for="typ_rat" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Rodzaj rat:</label>
            <select class="form-select" name="typ_rat" id="typ_rat">
                <option value="rowne">Równe</option>
                <option value="malejace">Malejące</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="eksport" class="form-label fw-bold" style="font-family: 'Oswald', serif; font-size: 1rem;">Zapisz harmonogram do pliku:</label>
            <select class="form-select" name="eksport" id="eksport">
                <option value="">Nie zapisuj</option>
                <option value="csv">CSV</option>
                <option value="txt">TXT</option>
            </select>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary" style="font-family: 'Oswald', serif; font-size: 2rem;">Oblicz raty</button>
    </div>
</form>


<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kwota_kredytu = (float) $_POST['kwota_kredytu'];
    $oprocentowanie_roczne = (float) $_POST['oprocentowanie_roczne'];
    $okres_kredytowania = (int) $_POST['okres_kredytowania'];
    $typ_rat = $_POST['typ_rat'];

    if ($typ_rat === 'rowne') {
        $miesieczna_stopa = $oprocentowanie_roczne / 12 / 100;
        if ($miesieczna_stopa > 0) {
            $rata_rowna = ($kwota_kredytu * $miesieczna_stopa) / (1 - pow(1 + $miesieczna_stopa, -$okres_kredytowania));
        } else {
            $rata_rowna = $kwota_kredytu / $okres_kredytowania;
        }

        echo "<div class='alert alert-success mt-4'>";
        echo "<h5 class='mb-3 fw-bold' style='font-family: \"Oswald\", serif; font-size: 2rem;'>Wynik:</h5>";
        echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Rata miesięczna: <strong>" . number_format($rata_rowna, 2, ',', ' ') . " zł</strong></p>";
        echo "<p style='font-family: \"Oswald\", serif; font-size: 1rem;'>Łączna kwota do spłaty: <strong>" . number_format($rata_rowna * $okres_kredytowania, 2, ',', ' ') . " zł</strong></p>";
        echo "</div>";

        // Eksport harmonogramu dla rat równych
        if (isset($_POST['eksport']) && $_POST['eksport'] === 'csv') {
            $plik_harmonogram_csv = fopen(__DIR__ . "/../exports/harmonogram_rat_rownych.csv", "w");
            fputcsv($plik_harmonogram_csv, ["Miesiąc", "Rata miesięczna"]);

            for ($i = 1; $i <= $okres_kredytowania; $i++) {
                fputcsv($plik_harmonogram_csv, [$i, number_format($rata_rowna, 2, '.', '')]);
            }

            fputcsv($plik_harmonogram_csv, []);
            fputcsv($plik_harmonogram_csv, ["Łączna kwota do spłaty", number_format($rata_rowna * $okres_kredytowania, 2, '.', '')]);

            fclose($plik_harmonogram_csv);

            echo "<div class='alert alert-info'>Plik <strong>harmonogram_rat_rownych.csv</strong> został zapisany w katalogu aplikacji.</div>";
            echo "<div class='mt-2 text-center'>";
            echo "<a class='btn btn-outline-success' href='../exports/harmonogram_rat_rownych.csv' download>Pobierz plik CSV</a>";
            echo "</div>";
        } elseif (isset($_POST['eksport']) && $_POST['eksport'] === 'txt') {
            $plik_harmonogram_txt = fopen(__DIR__ . "/../exports/harmonogram_rat_rownych.txt", "w");
            fwrite($plik_harmonogram_txt, "Harmonogram rat równych:\n\n");
            fwrite($plik_harmonogram_txt, "Miesiąc | Rata miesięczna\n");

            for ($i = 1; $i <= $okres_kredytowania; $i++) {
                fwrite($plik_harmonogram_txt, sprintf("%6d | %15.2f\n", $i, $rata_rowna));
            }

            fwrite($plik_harmonogram_txt, "\nŁączna kwota do spłaty: " . number_format($rata_rowna * $okres_kredytowania, 2, '.', '') . " zł\n");
            fclose($plik_harmonogram_txt);

            echo "<div class='alert alert-info'>Plik <strong>harmonogram_rat_rownych.txt</strong> został zapisany w katalogu aplikacji.</div>";
            echo "<div class='mt-2 text-center'>";
            echo "<a class='btn btn-outline-success' href='../exports/harmonogram_rat_rownych.txt' download>Pobierz plik TXT</a>";
            echo "</div>";
        }
    } elseif ($typ_rat === 'malejace') {
        echo "<div class='mt-4'>";
        echo "<h5 class='mb-3'>Harmonogram rat malejących:</h5>";
        echo "<table class='table table-bordered' style='font-family: \"Oswald\", serif; font-size: 1rem;'>";
        echo "<thead><tr><th style='text-align: center;'>Miesiąc</th>
		<th style='text-align: center;'>Rata całkowita</th>
		<th style='text-align: center;'>Kapitał</th>
		<th style='text-align: center;'>Odsetki</th>
		<th style='text-align: center;'>Saldo</th>
		</tr></thead>
		<tbody>";

        $rata_kapitalowa = $kwota_kredytu / $okres_kredytowania;
        $saldo_pozostale = $kwota_kredytu;

        for ($i = 1; $i <= $okres_kredytowania; $i++) {
            $rata_odsetkowa = ($saldo_pozostale * $oprocentowanie_roczne / 100) / 12;
            $rata_laczna = $rata_kapitalowa + $rata_odsetkowa;
            echo "<tr>";
            echo "<td class='fw-bold' style='text-align: center;'>$i</td>";
            echo "<td style='text-align: center;'>" . number_format($rata_laczna, 2, ',', ' ') . " zł</td>";
            echo "<td style='text-align: center;'>" . number_format($rata_kapitalowa, 2, ',', ' ') . " zł</td>";
            echo "<td style='text-align: center;'>" . number_format($rata_odsetkowa, 2, ',', ' ') . " zł</td>";
            echo "<td style='text-align: center;'>" . number_format($saldo_pozostale, 2, ',', ' ') . " zł</td>";
            echo "</tr>";
            $saldo_pozostale -= $rata_kapitalowa;
        }

        $laczone_odsetki = 0;

        // oblicz sumę odsetek
        $saldo_pozostale = $kwota_kredytu;
        for ($i = 1; $i <= $okres_kredytowania; $i++) {
            $rata_odsetkowa = ($saldo_pozostale * $oprocentowanie_roczne / 100) / 12;
            $laczone_odsetki += $rata_odsetkowa;
            $saldo_pozostale -= $rata_kapitalowa;
        }

        $laczna_kwota_splaty = $kwota_kredytu + $laczone_odsetki;

        echo "</tbody></table>";
        echo "<div class='alert alert-secondary'>";
        echo "<p><strong>Suma odsetek:</strong> " . number_format($laczone_odsetki, 2, ',', ' ') . " zł</p>";
        echo "<p><strong>Łączna kwota do spłaty:</strong> " . number_format($laczna_kwota_splaty, 2, ',', ' ') . " zł</p>";
        echo "</div>";
        echo "</div>";

        // Eksport harmonogramu dla rat malejących
        if (isset($_POST['eksport']) && $_POST['eksport'] === 'csv') {
            $plik_harmonogram_csv = fopen(__DIR__ . "/../exports/harmonogram_rat_malejacych.csv", "w");
            fputcsv($plik_harmonogram_csv, ["Miesiąc", "Rata całkowita", "Kapitał", "Odsetki", "Saldo"]);

            $saldo_pozostale = $kwota_kredytu;
            for ($i = 1; $i <= $okres_kredytowania; $i++) {
                $rata_odsetkowa = ($saldo_pozostale * $oprocentowanie_roczne / 100) / 12;
                $rata_laczna = $rata_kapitalowa + $rata_odsetkowa;
                fputcsv($plik_harmonogram_csv, [
                    $i,
                    number_format($rata_laczna, 2, '.', ''),
                    number_format($rata_kapitalowa, 2, '.', ''),
                    number_format($rata_odsetkowa, 2, '.', ''),
                    number_format($saldo_pozostale, 2, '.', '')
                ]);
                $saldo_pozostale -= $rata_kapitalowa;
            }

            fputcsv($plik_harmonogram_csv, []);
            fputcsv($plik_harmonogram_csv, ["Suma odsetek", number_format($laczone_odsetki, 2, '.', '')]);
            fputcsv($plik_harmonogram_csv, ["Łączna kwota do spłaty", number_format($laczna_kwota_splaty, 2, '.', '')]);

            fclose($plik_harmonogram_csv);

            echo "<div class='alert alert-info'>Plik <strong>harmonogram_rat_malejacych.csv</strong> został zapisany w katalogu głównym aplikacji.</div>";
            // Link do pobrania pliku
            echo "<div class='mt-2 text-center'>";
            echo "<a class='btn btn-outline-success' href='../exports/harmonogram_rat_malejacych.csv' download>Pobierz plik CSV</a>";
            echo "</div>";
        } elseif (isset($_POST['eksport']) && $_POST['eksport'] === 'txt') {
            $plik_harmonogram_txt = fopen(__DIR__ . "/../exports/harmonogram_rat_malejacych.txt", "w");
            fwrite($plik_harmonogram_txt, "Harmonogram rat malejących:\n\n");
            fwrite($plik_harmonogram_txt, "Miesiąc | Rata całkowita | Kapitał | Odsetki | Saldo\n");

            $saldo_pozostale = $kwota_kredytu;
            for ($i = 1; $i <= $okres_kredytowania; $i++) {
                $rata_odsetkowa = ($saldo_pozostale * $oprocentowanie_roczne / 100) / 12;
                $rata_laczna = $rata_kapitalowa + $rata_odsetkowa;
                fwrite($plik_harmonogram_txt, sprintf(
                    "%6d | %15.2f | %7.2f | %7.2f | %7.2f\n",
                    $i, $rata_laczna, $rata_kapitalowa, $rata_odsetkowa, $saldo_pozostale
                ));
                $saldo_pozostale -= $rata_kapitalowa;
            }

            fwrite($plik_harmonogram_txt, "\nSuma odsetek: " . number_format($laczone_odsetki, 2, '.', '') . " zł\n");
            fwrite($plik_harmonogram_txt, "Łączna kwota do spłaty: " . number_format($laczna_kwota_splaty, 2, '.', '') . " zł\n");

            fclose($plik_harmonogram_txt);

            echo "<div class='alert alert-info'>Plik <strong>harmonogram_rat_malejacych.txt</strong> został zapisany w katalogu głównym aplikacji.</div>";
            echo "<div class='mt-2 text-center'>";
            echo "<a class='btn btn-outline-success' href='../exports/harmonogram_rat_malejacych.txt' download>Pobierz plik TXT</a>";
            echo "</div>";
        }
    }
}
?>

<?php include('../footer.php'); ?>