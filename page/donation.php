<?php
require_once '../inc/configuration.php';

$currencySymbols = [
    'AUD' => 'A$',   // Australian Dollar
    'BRL' => 'R$',   // Brazilian Real
    'CAD' => 'CA$',  // Canadian Dollar
    'CNY' => '¥',    // Chinese Yuan
    'CZK' => 'Kč',   // Czech Koruna
    'DKK' => 'kr',   // Danish Krone
    'EUR' => '€',    // Euro
    'HKD' => 'HK$',  // Hong Kong Dollar
    'HUF' => 'Ft',   // Hungarian Forint
    'INR' => '₹',    // Indian Rupee
    'ILS' => '₪',    // Israeli Shekel
    'JPY' => '¥',    // Japanese Yen
    'MYR' => 'RM',   // Malaysian Ringgit
    'MXN' => 'MX$',  // Mexican Peso
    'TWD' => 'NT$',  // Taiwan Dollar
    'NZD' => 'NZ$',  // New Zealand Dollar
    'NOK' => 'kr',   // Norwegian Krone
    'PHP' => '₱',    // Philippine Peso
    'PLN' => 'zł',   // Polish Zloty
    'GBP' => '£',    // British Pound Sterling
    'RUB' => '₽',    // Russian Ruble
    'SGD' => 'S$',   // Singapore Dollar
    'SEK' => 'kr',   // Swedish Krona
    'CHF' => 'CHF',  // Swiss Franc
    'THB' => '฿',    // Thai Baht
    'USD' => '$',    // US Dollar
];

$currencySymbol = $currencySymbols[PAYPAL_CURRENCY] ?? PAYPAL_CURRENCY;

// Define gem images based on points
function getPointsImage($points) {
    if ($points >= 8000) {
        return 'buy_ap6.png';
    } elseif ($points >= 4000) {
        return 'buy_ap5.png';
    } elseif ($points >= 2800) {
        return 'buy_ap4.png';
    } elseif ($points >= 1600) {
        return 'buy_ap3.png';
    } elseif ($points >= 800) {
        return 'buy_ap2.png';
    } else {
        return 'buy_ap1.png';
    }
}
?>
<div class="container">
    <div id="donationMessage"></div>
    <div class="row justify-content-center">
        <?php
        foreach (DONATION_AMOUNTS as $donation) {
            $totalPoints = $donation['points'];
            $bonusPoints = $donation['bonus'];
            $pointsDisplay = $totalPoints . ' Points';
            if ($bonusPoints > 0) {
                $pointsDisplay = $totalPoints . ' + ' . $bonusPoints . ' Points';
            }
            // Get the appropriate gem image based on points
            $pointsImage = getPointsImage($totalPoints);
            ?>
            <div class="col-md-3">
                <div class="donation-card" style="background-image: url('/img/<?php echo $pointsImage; ?>');">
                    <div class="donation-overlay text-center">
                        <h5 class="donation-title"><?php echo $currencySymbol . number_format($donation['amount'], 2); ?></h5>
                        <p class="donation-text"><?php echo $pointsDisplay; ?></p>
                        <button class="btn btn-primary donation-button" data-amount="<?php echo $donation['amount']; ?>" data-points="<?php echo $donation['points']; ?>" data-bonus="<?php echo $donation['bonus']; ?>">Buy</button>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <p class="text-muted text-center mt-2">* Your donations help us to keep the server running and provide new updates.</p>
</div>