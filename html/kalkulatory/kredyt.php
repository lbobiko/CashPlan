<?php include('../header.php'); ?>

<h2 class="text-center my-4">Kalkulator rat kredytu</h2>

<form method="post" action="">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="amount" class="form-label">Kwota kredytu (zł):</label>
            <input type="number" step="0.01" class="form-control" name="amount" id="amount" required>
        </div>
        <div class="col-md-6">
            <label for="interest" class="form-label">Oprocentowanie roczne (%):</label>
            <input type="number" step="0.01" class="form-control" name="interest" id="interest" required>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="months" class="form-label">Liczba miesięcy:</label>
            <input type="number" class="form-control" name="months" id="months" required>
        </div>
        <div class="col-md-6">
            <label for="type" class="form-label">Rodzaj rat:</label>
            <select class="form-select" name="type" id="type">
                <option value="annuity">Równe</option>
                <option value="decreasing">Malejące</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="export" class="form-label">Zapisz harmonogram do pliku:</label>
            <select class="form-select" name="export" id="export">
                <option value="">Nie zapisuj</option>
                <option value="csv">CSV</option>
                <option value="txt">TXT</option>
            </select>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary">Oblicz raty</button>
    </div>
</form>


<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = (float) $_POST['amount'];
    $interest = (float) $_POST['interest'];
    $months = (int) $_POST['months'];
    $type = $_POST['type'];

    if ($type === 'annuity') {
        $monthlyRate = $interest / 12 / 100;
        if ($monthlyRate > 0) {
            $annuity = ($amount * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));
        } else {
            $annuity = $amount / $months;
        }

        echo "<div class='alert alert-success mt-4'>";
        echo "<h5 class='mb-3'>Wynik:</h5>";
        echo "<p>Rata miesięczna: <strong>" . number_format($annuity, 2, ',', ' ') . " zł</strong></p>";
        echo "<p>Łączna kwota do spłaty: <strong>" . number_format($annuity * $months, 2, ',', ' ') . " zł</strong></p>";
        echo "</div>";
    } elseif ($type === 'decreasing') {
        echo "<div class='mt-4'>";
        echo "<h5 class='mb-3'>Harmonogram rat malejących:</h5>";
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>Miesiąc</th><th>Rata całkowita</th><th>Kapitał</th><th>Odsetki</th><th>Saldo</th></tr></thead><tbody>";

        $capitalPart = $amount / $months;
        $remaining = $amount;

        for ($i = 1; $i <= $months; $i++) {
            $interestPart = ($remaining * $interest / 100) / 12;
            $payment = $capitalPart + $interestPart;
            echo "<tr>";
            echo "<td>$i</td>";
            echo "<td>" . number_format($payment, 2, ',', ' ') . " zł</td>";
            echo "<td>" . number_format($capitalPart, 2, ',', ' ') . " zł</td>";
            echo "<td>" . number_format($interestPart, 2, ',', ' ') . " zł</td>";
            echo "<td>" . number_format($remaining, 2, ',', ' ') . " zł</td>";
            echo "</tr>";
            $remaining -= $capitalPart;
        }

        $total = $amount;
        $totalInterest = 0;

        // oblicz sumę odsetek
        $remaining = $amount;
        for ($i = 1; $i <= $months; $i++) {
            $interestPart = ($remaining * $interest / 100) / 12;
            $totalInterest += $interestPart;
            $remaining -= $capitalPart;
        }

        $totalPayment = $amount + $totalInterest;

        echo "</tbody></table>";
        echo "<div class='alert alert-secondary'>";
        echo "<p><strong>Suma odsetek:</strong> " . number_format($totalInterest, 2, ',', ' ') . " zł</p>";
        echo "<p><strong>Łączna kwota do spłaty:</strong> " . number_format($totalPayment, 2, ',', ' ') . " zł</p>";
        echo "</div>";
        echo "</div>";

        if (isset($_POST['export']) && $_POST['export'] === 'csv') {
            $csvFile = fopen("../harmonogram_kredytu.csv", "w");
            fputcsv($csvFile, ["Miesiąc", "Rata całkowita", "Kapitał", "Odsetki", "Saldo"]);

            $remaining = $amount;
            for ($i = 1; $i <= $months; $i++) {
                $interestPart = ($remaining * $interest / 100) / 12;
                $payment = $capitalPart + $interestPart;
                fputcsv($csvFile, [
                    $i,
                    number_format($payment, 2, '.', ''),
                    number_format($capitalPart, 2, '.', ''),
                    number_format($interestPart, 2, '.', ''),
                    number_format($remaining, 2, '.', '')
                ]);
                $remaining -= $capitalPart;
            }

            fputcsv($csvFile, []);
            fputcsv($csvFile, ["Suma odsetek", number_format($totalInterest, 2, '.', '')]);
            fputcsv($csvFile, ["Łączna kwota do spłaty", number_format($totalPayment, 2, '.', '')]);

            fclose($csvFile);

            echo "<div class='alert alert-info'>Plik <strong>harmonogram_kredytu.csv</strong> został zapisany w katalogu głównym aplikacji.</div>";
        } elseif ($_POST['export'] === 'txt') {
            $txtFile = fopen("../harmonogram_kredytu.txt", "w");
            fwrite($txtFile, "Harmonogram rat malejących:\n\n");
            fwrite($txtFile, "Miesiąc | Rata całkowita | Kapitał | Odsetki | Saldo\n");

            $remaining = $amount;
            for ($i = 1; $i <= $months; $i++) {
                $interestPart = ($remaining * $interest / 100) / 12;
                $payment = $capitalPart + $interestPart;
                fwrite($txtFile, sprintf(
                    "%6d | %15.2f | %7.2f | %7.2f | %7.2f\n",
                    $i, $payment, $capitalPart, $interestPart, $remaining
                ));
                $remaining -= $capitalPart;
            }

            fwrite($txtFile, "\nSuma odsetek: " . number_format($totalInterest, 2, '.', '') . " zł\n");
            fwrite($txtFile, "Łączna kwota do spłaty: " . number_format($totalPayment, 2, '.', '') . " zł\n");

            fclose($txtFile);

            echo "<div class='alert alert-info'>Plik <strong>harmonogram_kredytu.txt</strong> został zapisany w katalogu głównym aplikacji.</div>";
        }
    }
}
?>

<?php include('../footer.php'); ?>