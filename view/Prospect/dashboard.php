<?php
require_once '../layout/header.php';

$selectedHouse = $_GET['selected'] ?? '';

$houseCards = [
    [
        'slug' => 'aimee',
        'title' => 'Aimee Rowhouse',
        'type' => 'One-Storey Rowhouse',
        'image' => '/luminest/assets/Lumina(Aimee).jpg',
        'description' => 'Compact, practical, and easy to maintain.',
        'details' => '../house_types/aimee.php',
        'badge' => 'Starter home',
    ],
    [
        'slug' => 'angelique_duplex',
        'title' => 'Angelique Duplex',
        'type' => 'Two-Storey Duplex',
        'image' => '/luminest/assets/Lumina(Angelique).jpg',
        'description' => 'A balanced layout with a quiet upstairs zone.',
        'details' => '../house_types/angelique_duplex.php',
        'badge' => 'Family ready',
    ],
    [
        'slug' => 'armina_single',
        'title' => 'Armina Single',
        'type' => 'Two-Storey Single',
        'image' => '/luminest/assets/Lumina(Armina_single).jpg',
        'description' => 'More room for growing households and flexible living.',
        'details' => '../house_types/armina_single.php',
        'badge' => 'Room to grow',
    ],
    [
        'slug' => 'armina_duplex',
        'title' => 'Armina Duplex',
        'type' => 'Two-Storey Duplex',
        'image' => '/luminest/assets/Lumina(Armina).jpg',
        'description' => 'A generous duplex plan with a modern, functional flow.',
        'details' => '../house_types/armina_duplex.php',
        'badge' => 'Spacious choice',
    ],
];

$selectedLabels = [
    'aimee' => 'Aimee Rowhouse',
    'angelique_duplex' => 'Angelique Duplex',
    'armina_single' => 'Armina Single',
    'armina_duplex' => 'Armina Duplex',
];
?>

<style>
    :root {
        --lm-red: #c1121f;
        --lm-blue: #1d4ed8;
        --lm-soft: #eff6ff;
        --lm-ink: #111827;
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(29, 78, 216, 0.12), transparent 32%),
            radial-gradient(circle at top right, rgba(193, 18, 31, 0.1), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        color: var(--lm-ink);
    }

    .hero-banner {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #dbeafe;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }

    .hero-pill {
        background: var(--lm-soft);
        color: var(--lm-blue);
        border: 1px solid #bfdbfe;
    }

    .hero-title span {
        color: var(--lm-red);
    }

    .house-card {
        border: 1px solid #e5e7eb;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    }

    .house-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
        border-color: #dbeafe;
    }

    .house-image {
        height: 190px;
        object-fit: cover;
    }

    .house-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
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
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="../auth/logout.php">←Back to Welcome Page</a>
            <a href="history.php" class="btn btn-outline-secondary btn-sm">Reservation History</a>
        </div>
        <section class="hero-banner rounded-4 p-4 p-lg-5 mb-4">
            <div class="row align-items-end g-3">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold hero-title mb-2">Reserve your next <span>Luminest home</span> <?= $_SESSION['username'] ?? '' ?></h1>
                    <p class="lead text-secondary mb-0">Browse the house types below, open a full details page for each listing, and continue to reserve the one that fits your needs.</p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <span class="badge rounded-pill text-bg-light border px-3 py-2">4 house options</span>
                        <span class="badge rounded-pill text-bg-light border px-3 py-2">Details page per house</span>
                    </div>
                </div>
            </div>

            <?php if ($selectedHouse !== ''): ?>
                <?php $selectedLabel = $selectedLabels[$selectedHouse] ?? ucwords(str_replace('_', ' ', $selectedHouse)); ?>
                <div class="alert border-0 mt-4 mb-0" role="alert" style="background: linear-gradient(135deg, rgba(29, 78, 216, 0.08), rgba(193, 18, 31, 0.08)); color: #334155;">
                    You selected <strong><?php echo htmlspecialchars($selectedLabel, ENT_QUOTES, 'UTF-8'); ?></strong>. Open its details page to review the layout, then continue to reserve it.
                </div>
            <?php endif; ?>
        </section>

        <section class="row g-4">
            <?php foreach ($houseCards as $card): ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card house-card h-100 overflow-hidden">
                        <a href="<?php echo htmlspecialchars($card['details'], ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none position-relative d-block">
                            <img src="<?php echo htmlspecialchars($card['image'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top house-image" alt="<?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="badge rounded-pill text-bg-dark house-badge"><?php echo htmlspecialchars($card['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>

                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 card-title mb-1"><?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <div class="text-secondary mb-2"><?php echo htmlspecialchars($card['type'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <p class="card-text text-secondary flex-grow-1"><?php echo htmlspecialchars($card['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="d-flex gap-2 mt-2">
                                <a class="btn btn-sm btn-lm-secondary flex-fill" href="<?php echo htmlspecialchars($card['details'], ENT_QUOTES, 'UTF-8'); ?>">View details</a>
                                <a class="btn btn-sm btn-lm-primary flex-fill" href="reservation.php?house=<?php echo urlencode($card['slug']); ?>">Reserve</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</main>

<?php require_once '../layout/footer.php'; ?>