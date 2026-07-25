<?php
if (!isset($house) || !is_array($house)) {
    http_response_code(500);
    exit('House details are unavailable.');
}

require_once __DIR__ . '/../layout/header.php';

$houseTitle = $house['title'] ?? 'House Details';
$houseType = $house['type'] ?? 'Property';
$houseImage = $house['image'] ?? '';
$houseDescription = $house['description'] ?? '';
$houseBedrooms = $house['bedrooms'] ?? '';
$houseBathrooms = $house['bathrooms'] ?? '';
$houseCarports = $house['carports'] ?? '';
$houseReserveUrl = $house['reserve_url'] ?? '../Prospect/dashboard.php';
?>

<style>
    :root {
        --lm-red: #c1121f;
        --lm-blue: #1d4ed8;
        --lm-soft: #eff6ff;
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(29, 78, 216, 0.12), transparent 34%),
            radial-gradient(circle at top right, rgba(193, 18, 31, 0.12), transparent 30%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .detail-hero {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid #dbeafe;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }

    .detail-kicker {
        background: var(--lm-soft);
        color: var(--lm-blue);
        border: 1px solid #bfdbfe;
    }

    .detail-title span {
        color: var(--lm-red);
    }

    .detail-image {
        min-height: 100%;
        object-fit: cover;
    }

    .detail-stat {
        background: #f8fbff;
        border: 1px solid #e0ecff;
    }

    .btn-lm-primary {
        background: linear-gradient(135deg, var(--lm-red), #8f0f18);
        border-color: var(--lm-red);
        color: #fff;
    }

    .btn-lm-secondary {
        background: var(--lm-soft);
        border-color: #bfdbfe;
        color: var(--lm-blue);
    }

    .btn-lm-primary:hover,
    .btn-lm-secondary:hover {
        color: #fff;
    }
</style>

<main class="py-4 py-lg-5">
    <div class="container">
        <a href="../Prospect/dashboard.php" class="btn btn-link text-decoration-none ps-0 mb-3 fw-semibold">&larr; Back to Prospect Dashboard</a>

        <section class="card detail-hero overflow-hidden rounded-4">
            <div class="row g-0">
                <div class="col-lg-6">
                    <img src="<?php echo htmlspecialchars($houseImage, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid w-100 detail-image" alt="<?php echo htmlspecialchars($houseTitle, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-lg-6">
                    <div class="card-body p-4 p-lg-5 h-100 d-flex flex-column justify-content-center">
                        <span class="badge rounded-pill detail-kicker align-self-start mb-3 px-3 py-2"><?php echo htmlspecialchars($houseType, ENT_QUOTES, 'UTF-8'); ?></span>
                        <h1 class="display-6 fw-bold detail-title mb-3"><?php echo htmlspecialchars($houseTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="text-secondary fs-5 mb-4"><?php echo htmlspecialchars($houseDescription, ENT_QUOTES, 'UTF-8'); ?></p>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-4 detail-stat h-100">
                                    <div class="fw-bold text-danger fs-4"><?php echo htmlspecialchars($houseBedrooms, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-secondary">Bedrooms</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-4 detail-stat h-100">
                                    <div class="fw-bold text-danger fs-4"><?php echo htmlspecialchars($houseBathrooms, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-secondary">Bathrooms</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-4 detail-stat h-100">
                                    <div class="fw-bold text-danger fs-4"><?php echo htmlspecialchars($houseCarports, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-secondary">Carports</div>
                                </div>
                            </div>
                        </div>

                        <p class="text-secondary mb-4">Review the layout, then use the reservation button below to continue with this home.</p>

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <a class="btn btn-lm-primary btn-lg flex-fill" href="<?php echo htmlspecialchars($houseReserveUrl, ENT_QUOTES, 'UTF-8'); ?>">Reserve This Home</a>
                            <a class="btn btn-lm-secondary btn-lg flex-fill" href="../Prospect/dashboard.php">Choose Another House</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>