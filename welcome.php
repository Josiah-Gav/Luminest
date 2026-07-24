<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Luminest | Real State Management System</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--lm-red: #c1121f;
			--lm-blue: #1d4ed8;
			--lm-white: #ffffff;
			--lm-ink: #111827;
			--lm-sky: #eff6ff;
		}

		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		body {
			font-family: 'Source Sans 3', sans-serif;
			color: var(--lm-ink);
			background:
				radial-gradient(circle at 10% 5%, rgba(29, 78, 216, 0.15) 0 10%, transparent 45%),
				radial-gradient(circle at 90% 15%, rgba(193, 18, 31, 0.12) 0 12%, transparent 42%),
				linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
			line-height: 1.5;
		}

		.container {
			width: min(1100px, 92%);
			margin: 0 auto;
		}

		.navbar {
			position: sticky;
			top: 0;
			z-index: 50;
			background: rgba(255, 255, 255, 0.96);
			border-bottom: 2px solid #e5e7eb;
			backdrop-filter: blur(6px);
		}

		.nav-wrap {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 1rem;
			min-height: 78px;
		}

		.brand {
			text-decoration: none;
			color: var(--lm-red);
			font-family: 'Playfair Display', serif;
			font-size: 1.9rem;
			letter-spacing: 0.8px;
		}

		.brand span {
			color: var(--lm-blue);
		}

		.nav-links {
			display: flex;
			align-items: center;
			gap: 1rem;
			list-style: none;
			flex-wrap: wrap;
		}

		.nav-links a {
			text-decoration: none;
			color: #1f2937;
			font-weight: 600;
			padding: 0.45rem 0.7rem;
			border-radius: 8px;
			transition: color 200ms ease, background-color 200ms ease;
		}

		.nav-links a:hover {
			color: var(--lm-red);
			background-color: #f9fafb;
		}

		.member-link {
			background-color: var(--lm-blue);
			color: var(--lm-white) !important;
			padding: 0.55rem 1rem !important;
			box-shadow: 0 7px 16px rgba(29, 78, 216, 0.25);
		}

		.member-link:hover {
			background-color: #1e40af !important;
			color: var(--lm-white) !important;
		}

		.hero {
			padding: 5rem 0 3rem;
		}

		.hero-grid {
			display: grid;
			grid-template-columns: 1.1fr 1fr;
			gap: 2rem;
			align-items: center;
		}

		.hero-copy h1 {
			font-family: 'Playfair Display', serif;
			font-size: clamp(2.1rem, 5vw, 3.3rem);
			line-height: 1.1;
			margin-bottom: 1rem;
		}

		.hero-copy h1 .red { color: var(--lm-red); }
		.hero-copy h1 .blue { color: var(--lm-blue); }

		.hero-copy p {
			font-size: 1.1rem;
			max-width: 62ch;
			margin-bottom: 1.4rem;
		}

		.hero-actions {
			display: flex;
			gap: 0.9rem;
			flex-wrap: wrap;
		}

		.btn {
			text-decoration: none;
			padding: 0.72rem 1.1rem;
			border-radius: 10px;
			font-weight: 700;
			display: inline-block;
		}

		.btn-primary {
			background-color: var(--lm-red);
			color: var(--lm-white);
		}

		.btn-secondary {
			background-color: var(--lm-white);
			border: 2px solid var(--lm-blue);
			color: var(--lm-blue);
		}

		.hero-panel {
			background: linear-gradient(160deg, #ffffff 0%, #f3f8ff 100%);
			border: 1px solid #dbeafe;
			border-left: 8px solid var(--lm-blue);
			border-radius: 18px;
			padding: 1.2rem;
			box-shadow: 0 16px 34px rgba(2, 6, 23, 0.08);
		}

		.hero-panel h3 {
			color: var(--lm-blue);
			margin-bottom: 0.5rem;
		}

		.pill-list {
			display: flex;
			gap: 0.6rem;
			flex-wrap: wrap;
			margin-top: 0.85rem;
		}

		.pill {
			border: 1px solid #bfdbfe;
			background-color: #f8fbff;
			color: #1e3a8a;
			padding: 0.35rem 0.65rem;
			border-radius: 999px;
			font-size: 0.92rem;
			font-weight: 600;
		}

		.section-title {
			margin-top: 2.2rem;
			margin-bottom: 1rem;
			font-size: 1.8rem;
			font-family: 'Playfair Display', serif;
			color: #0f172a;
		}

		.property-grid {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 1rem;
			margin-bottom: 2.5rem;
		}

		.property-card {
			background: var(--lm-white);
			border-radius: 14px;
			overflow: hidden;
			border: 1px solid #e5e7eb;
			box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
			transform: translateY(20px);
			opacity: 0;
			animation: riseIn 700ms ease forwards;
		}

		.property-card:nth-child(2) { animation-delay: 100ms; }
		.property-card:nth-child(3) { animation-delay: 200ms; }
		.property-card:nth-child(4) { animation-delay: 300ms; }
		.property-card:nth-child(5) { animation-delay: 400ms; }
		.property-card:nth-child(6) { animation-delay: 500ms; }

		.property-image {
			height: 155px;
			padding: 0.8rem;
			color: var(--lm-white);
			display: flex;
			align-items: end;
			font-weight: 700;
			letter-spacing: 0.4px;
			background-size: cover;
			background-position: center;
		}

		.image-house-1 { background-image: linear-gradient(130deg, rgba(193, 18, 31, 0.82), rgba(29, 78, 216, 0.72)), url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=900&q=80'); }
		.image-house-2 { background-image: linear-gradient(130deg, rgba(29, 78, 216, 0.8), rgba(193, 18, 31, 0.74)), url('https://images.unsplash.com/photo-1605276374104-dee2a0ed3cd6?auto=format&fit=crop&w=900&q=80'); }
		.image-house-3 { background-image: linear-gradient(130deg, rgba(193, 18, 31, 0.8), rgba(29, 78, 216, 0.68)), url('https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?auto=format&fit=crop&w=900&q=80'); }
		.image-lot-1 { background-image: linear-gradient(130deg, rgba(29, 78, 216, 0.8), rgba(193, 18, 31, 0.64)), url('https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=900&q=80'); }
		.image-lot-2 { background-image: linear-gradient(130deg, rgba(193, 18, 31, 0.8), rgba(29, 78, 216, 0.65)), url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=900&q=80'); }
		.image-lot-3 { background-image: linear-gradient(130deg, rgba(29, 78, 216, 0.85), rgba(193, 18, 31, 0.62)), url('https://images.unsplash.com/photo-1460574283810-2aab119d8511?auto=format&fit=crop&w=900&q=80'); }

		.property-content {
			padding: 0.95rem;
		}

		.property-content h4 {
			color: #1f2937;
			margin-bottom: 0.3rem;
		}

		.property-meta {
			font-size: 0.95rem;
			color: #475569;
			margin-bottom: 0.45rem;
		}

		.price {
			color: var(--lm-red);
			font-weight: 800;
		}

		.footer-note {
			text-align: center;
			padding: 1.5rem 0 2.2rem;
			color: #334155;
			border-top: 1px solid #e5e7eb;
			margin-top: 1rem;
		}

		@keyframes riseIn {
			from {
				opacity: 0;
				transform: translateY(20px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		@media (max-width: 900px) {
			.hero-grid {
				grid-template-columns: 1fr;
			}

			.property-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}

		@media (max-width: 640px) {
			.nav-wrap {
				flex-direction: column;
				align-items: flex-start;
				padding: 0.7rem 0;
			}

			.property-grid {
				grid-template-columns: 1fr;
			}

			.hero {
				padding-top: 3.2rem;
			}
		}
	</style>
</head>
<body>
	<nav class="navbar">
		<div class="container nav-wrap">
			<a href="welcome.php" class="brand">Lumi<span>nest</span></a>
			<ul class="nav-links">
				<li><a href="#home">Home</a></li>
				<li><a href="#about">About Us</a></li>
				<li><a href="#properties">Properties</a></li>
				<li><a href="#contact">Contacts Us</a></li>
				<li><a href="view/auth/register.php" class="member-link">Reserve Now</a></li>
			</ul>
		</div>
	</nav>

	<header class="hero" id="home">
		<div class="container hero-grid">
			<div class="hero-copy">
				<h1>Discover <span class="red">Real State Houses</span> and <span class="blue">Prime Lots</span> with Luminest</h1>
				<p>
					Luminest is your Real State Management System for premium homes, secure neighborhoods,
					and ready-to-build lots. Browse verified listings and find your ideal investment today.
				</p>
				<div class="hero-actions">
					<a href="#properties" class="btn btn-primary">Browse Properties</a>
					<a href="view/auth/login.php" class="btn btn-secondary">Member Login</a>
				</div>
			</div>
			<aside class="hero-panel" id="about">
				<h3>Why Luminest?</h3>
				<p>
					We connect buyers, tenants, and property managers through transparent listings and
					organized property data.
				</p>
				<div class="pill-list">
					<span class="pill">Verified Listings</span>
					<span class="pill">House and Lot Options</span>
					<span class="pill">Nationwide Coverage</span>
					<span class="pill">Member Dashboard</span>
				</div>
			</aside>
		</div>
	</header>

	<main class="container" id="properties">
		<h2 class="section-title">Featured Houses and Lots</h2>
		<section class="property-grid">
			<article class="property-card">
				<div class="property-image image-house-1">City View House</div>
				<div class="property-content">
					<h4>4-Bedroom Modern House</h4>
					<p class="property-meta">Quezon City | 280 sqm lot</p>
					<p class="price">USD 265,000</p>
				</div>
			</article>

			<article class="property-card">
				<div class="property-image image-house-2">Family Residence</div>
				<div class="property-content">
					<h4>3-Bedroom Corner Home</h4>
					<p class="property-meta">Baguio | 210 sqm lot</p>
					<p class="price">USD 189,000</p>
				</div>
			</article>

			<article class="property-card">
				<div class="property-image image-house-3">Luxury Hillside Home</div>
				<div class="property-content">
					<h4>5-Bedroom Premium Villa</h4>
					<p class="property-meta">Tagaytay | 450 sqm lot</p>
					<p class="price">USD 410,000</p>
				</div>
			</article>

			<article class="property-card">
				<div class="property-image image-lot-1">Investment Lot</div>
				<div class="property-content">
					<h4>Commercial Lot Near Highway</h4>
					<p class="property-meta">Cebu | 520 sqm</p>
					<p class="price">USD 98,500</p>
				</div>
			</article>

			<article class="property-card">
				<div class="property-image image-lot-2">Garden Community Lot</div>
				<div class="property-content">
					<h4>Residential Lot in Subdivision</h4>
					<p class="property-meta">Pampanga | 300 sqm</p>
					<p class="price">USD 63,700</p>
				</div>
			</article>

			<article class="property-card">
				<div class="property-image image-lot-3">Beachfront Development Lot</div>
				<div class="property-content">
					<h4>Vacation Lot Opportunity</h4>
					<p class="property-meta">Batangas | 780 sqm</p>
					<p class="price">USD 175,200</p>
				</div>
			</article>
		</section>
	</main>

	<footer class="container footer-note" id="contact">
		<p><strong>Luminest Real State Management System</strong> | Contact us: +63 912 345 6789 | hello@luminest.com</p>
	</footer>
</body>
</html>
