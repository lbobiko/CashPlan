<?php include('../header.php'); ?>

<h2 class="mb-4 text-center">Kalkulator rat kredytu</h2>

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
        echo "<div class='alert alert-info mt-4'>";
        echo "<p><strong>Obliczanie rat malejących</strong> – funkcja będzie dostępna wkrótce.</p>";
        echo "</div>";
    }
}
?>

<?php include('../footer.php'); ?>