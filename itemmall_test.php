<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Refined Shop Layout</title>
  <link rel="stylesheet" href="css/bootstrap.css">
  <style>
    body {
      background-color: #ffffff;
      color: black;
    }
    .shop-container {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
    }
    .category-list {
      background-color: #ffffff;
      padding: 15px;
      border-radius: 8px;
    }
    .product-list {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
    }
    .product-item {
      background-color: #ffffff;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-direction: column;
    }
    .product-details {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      width: 100%;
    }
    .product-item img {
      width: 50px;
      height: 50px;
      margin-right: 15px;
    }
    .product-info {
      flex-grow: 1;
    }
    .product-info .price-wrapper {
      display: flex;
      justify-content: flex-end;
      width: 100%;
    }
    .price {
      font-size: 18px;
    }
    .special-price {
      font-size: 14px;
      color: red;
      text-decoration: line-through;
      margin-right: 10px;
    }
    .btn-custom {
      background-color: #ff3333;
      color: white;
      border: none;
      width: 48%;
    }
    .btn-edit {
      background-color: #00bfff;
      color: white;
      border: none;
      width: 48%;
    }
    .btn-custom:hover {
      background-color: #cc0000;
    }
    .btn-edit:hover {
      background-color: #007acc;
    }
    .nav-arrows {
      display: flex;
      justify-content: center;
      margin-top: 10px;
    }
    .nav-arrows i {
      font-size: 24px;
      margin: 0 10px;
      cursor: pointer;
    }
    .card {
      background-color: #ffffff;
      border: none;
    }
    .card-header {
      background-color: #f1f1f1;
      color: black;
      font-weight: bold;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .add-news-button {
      background: transparent;
      border: none;
    }
    .add-news-button img {
      width: 25px;
      height: 25px;
    }
    .add-news-button:hover {
      cursor: pointer;
    }
    .list-group {
      background-color: transparent;
      border: none;
    }
    .list-group-item {
      cursor: pointer;
      background-color: #ffffff;
      color: black;
      border: none;
    }
    .list-group-item:hover {
      background-color: #f1f1f1;
    }
    .subcategory {
      padding-left: 30px;
    }
    a {
      color: black;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    .toggle-icon {
      margin-right: 8px;
    }
    .toggle-icon::before {
      content: "+ ";
      color: #00ccff;
    }
    a:not(.collapsed) .toggle-icon::before {
      content: "- ";
    }
  </style>
</head>
<body>
  <div class="container shop-container mt-5">
    <div class="row">
      <!-- Category Sidebar -->
      <div class="col-md-3 category-list">
        <div class="card">
          <div class="card-header">
            Select Category
          </div>
          <ul class="list-group list-group-flush">
            <li class="list-group-item">
              <a data-bs-toggle="collapse" href="#costumeSub" role="button" aria-expanded="false" class="text-black d-flex align-items-center collapsed">
                <span class="toggle-icon"></span>
                <span>Costumes</span>
              </a>
              <div class="collapse" id="costumeSub">
                <ul class="list-group">
                  <li class="list-group-item subcategory">Head</li>
                  <li class="list-group-item subcategory">Body</li>
                  <li class="list-group-item subcategory">Back</li>
                  <li class="list-group-item subcategory">Weapon</li>
                  <li class="list-group-item subcategory">Enchantment Card</li>
                </ul>
              </div>
            </li>
            <li class="list-group-item">Special Offers</li>
            <li class="list-group-item">Crazy Deals</li>
            <li class="list-group-item">Hot Items</li>
            <li class="list-group-item">Ruby Coins</li>
            <li class="list-group-item">Daily Limit</li>
            <li class="list-group-item">
              <a data-bs-toggle="collapse" href="#eidolonSub" role="button" aria-expanded="false" class="text-black d-flex align-items-center collapsed">
                <span class="toggle-icon"></span>
                <span>Eidolons</span>
              </a>
              <div class="collapse" id="eidolonSub">
                <ul class="list-group">
                  <li class="list-group-item subcategory">Eidolons</li>
                  <li class="list-group-item subcategory">Eidolon Costumes</li>
                </ul>
              </div>
            </li>
            <li class="list-group-item">
              <a data-bs-toggle="collapse" href="#travelerSub" role="button" aria-expanded="false" class="text-black d-flex align-items-center collapsed">
                <span class="toggle-icon"></span>
                <span>Traveler Items</span>
              </a>
              <div class="collapse" id="travelerSub">
                <ul class="list-group">
                  <li class="list-group-item subcategory">Backpacks</li>
                </ul>
              </div>
            </li>
            <li class="list-group-item">
              <a data-bs-toggle="collapse" href="#gearSub" role="button" aria-expanded="false" class="text-black d-flex align-items-center collapsed">
                <span class="toggle-icon"></span>
                <span>Gear Improvement</span>
              </a>
              <div class="collapse" id="gearSub">
                <ul class="list-group">
                  <li class="list-group-item subcategory">Fortification</li>
                  <li class="list-group-item subcategory">Other</li>
                </ul>
              </div>
            </li>
            <li class="list-group-item">Fusion Scrolls</li>
            <li class="list-group-item">
              <a data-bs-toggle="collapse" href="#costumeSub2" role="button" aria-expanded="false" class="text-black d-flex align-items-center collapsed">
                <span class="toggle-icon"></span>
                <span>Costumes</span>
              </a>
              <div class="collapse" id="costumeSub2">
                <ul class="list-group">
                  <li class="list-group-item subcategory">Hat Costumes</li>
                  <li class="list-group-item subcategory">Body Costumes</li>
                  <li class="list-group-item subcategory">Back Costumes</li>
                  <li class="list-group-item subcategory">Weapon Costumes</li>
                </ul>
              </div>
            </li>
            <li class="list-group-item">Mounts</li>
          </ul>
        </div>
      </div>

      <!-- Product List -->
      <div class="col-md-9">
        <div class="card product-list">
          <div class="card-header">
            <span>Product List</span>
            <!-- Add Item Button in Header -->
            <button id="add-item" class="add-news-button" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Add News" data-bs-original-title="Add News">
              <img src="../img/plus.png" alt="Add Item" class="last-news-icon">
            </button>
          </div>
          <div class="row">
            <!-- Product Item 1 -->
            <div class="col-md-4">
              <div class="product-item">
                <div class="product-details">
                  <img src="https://via.placeholder.com/50" alt="Item 1">
                  <div class="product-info">
                    <p>Long Ponytail Lucky Pack</p>
                    <div class="price-wrapper">
                      <span class="special-price">39 <i class="fas fa-gem"></i></span>
                      <span class="price">19 <i class="fas fa-gem"></i></span>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-between w-100 mt-2">
                  <button class="btn btn-edit">Edit</button>
                  <button class="btn btn-custom">Delete</button>
                </div>
              </div>
            </div>

            <!-- Product Item 2 -->
            <div class="col-md-4">
              <div class="product-item">
                <div class="product-details">
                  <img src="https://via.placeholder.com/50" alt="Item 2">
                  <div class="product-info">
                    <p>Kingdom Knight Hairstyle</p>
                    <div class="price-wrapper">
                      <span class="special-price">39 <i class="fas fa-gem"></i></span>
                      <span class="price">19 <i class="fas fa-gem"></i></span>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-between w-100 mt-2">
                  <button class="btn btn-edit">Edit</button>
                  <button class="btn btn-custom">Delete</button>
                </div>
              </div>
            </div>

            <!-- Product Item 3 -->
            <div class="col-md-4">
              <div class="product-item">
                <div class="product-details">
                  <img src="https://via.placeholder.com/50" alt="Item 3">
                  <div class="product-info">
                    <p>Samurai Dog Ear Hair</p>
                    <div class="price-wrapper">
                      <span class="special-price">39 <i class="fas fa-gem"></i></span>
                      <span class="price">19 <i class="fas fa-gem"></i></span>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-between w-100 mt-2">
                  <button class="btn btn-edit">Edit</button>
                  <button class="btn btn-custom">Delete</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigation Arrows -->
          <div class="nav-arrows">
            <i class="fas fa-chevron-left"></i>
            <span>1/1</span>
            <i class="fas fa-chevron-right"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Font Awesome and Bootstrap JS -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>