<?php
require_once '../layout/header.php';
?>

<h2>Reserve Now! <?=$_SESSION['username']?></h2>

<h5>Select your chosen house:</h5>

<div class="container-fluid">
  <!-- Content here -->

    <div class="row">
        <div class="col">
            <!-- AIMEE -->
            <div class="card" style="width: 18rem;">
            <img src="/luminest/assets/Lumina(Aimee).jpg" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Aimee Rowhouse</h5>
                <p class="card-text">
                    🏠 One-Storey, Rowhouse <br>
                    🛏️ 1 Bedrooms<br>🛁 1 Bathrooms
                </p>
                <a href="#" class="btn btn-primary">Inquire</a>
            </div>
            </div>
        </div>
    <div class="col">
    <!-- Angelique -->
            <div class="card" style="width: 18rem;">
            <img src="/luminest/assets/Lumina(Angelique).jpg" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Angelique Duplex</h5>
                <p class="card-text">
                    🏠 Two-Storey, Duplex <br>
                    🛏️ 2 Bedrooms<br>🛁 1 Bathrooms<br>🚗 1 Carport
                </p>
                <a href="#" class="btn btn-primary">Inquire</a>
            </div>
            </div>
    </div>
        <div class="col">
            <!-- Armina Single -->
            <div class="card" style="width: 18rem;">
            <img src="/luminest/assets/Lumina(Armina_single).jpg" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Armina Single</h5>
                <p class="card-text">
                    🏠 Two-Storey, Single <br>
                    🛏️ 3 Bedrooms<br>🛁 1 Bathrooms<br>🚗 1 Carport
                </p>
                <a href="#" class="btn btn-primary">Inquire</a>
            </div>
            </div>
        </div>
        <div class="col">
            <!-- Armina -->
            <div class="card" style="width: 18rem;">
            <img src="/luminest/assets/Lumina(Armina).jpg" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Armina Duplex</h5>
                <p class="card-text">
                    🏠 Two-Storey, Duplex <br>
                    🛏️ 3 Bedrooms<br>🛁 1 Bathrooms<br>🚗 1 Carport
                </p>
                <a href="#" class="btn btn-primary">Inquire</a>
            </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once '../layout/footer.php';
?>