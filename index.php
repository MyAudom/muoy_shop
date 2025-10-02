<?php
include 'config.php';

// Fetch categories for navigation
$categories_query = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Muoy Shop</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="img/icon.ico" />
    <!-- Bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
    <style>
      /* ==== HORIZONTAL SCROLL FOR CATEGORIES ==== */

/* make collapse area flexible but allow children to shrink */
.collapse.navbar-collapse {
  min-width: 0;
}

/* the scrolling nav */
.navbar-nav.category-scroll {
  display: flex;
  flex-wrap: nowrap;
  overflow-x: auto;       
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  min-width: 0;
  margin-right: 10px;
}

/* nav items not shrinking to multiple lines */
.navbar-nav.category-scroll .nav-item {
  flex: 0 0 auto;
  white-space: nowrap;
}

/* scrollbar visuals (optional) */
.navbar-nav.category-scroll::-webkit-scrollbar {
  height: 6px;
}
.navbar-nav.category-scroll::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.18);
  border-radius: 3px;
}

    </style>
</head>
<body id="home" class="light-mode">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container px-4 px-lg-5 d-flex justify-content-between align-items-center">
            
            <!-- Logo -->
            <a class="navbar-brand nav-link" href="index.html">
            <img src="img/logo.jpg" alt="" style="height: 50px; border-radius: 50%;">
            </a>

            <!-- Toggle Button moved to right -->
            <button class="navbar-toggler ms-auto" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
                    aria-controls="navbarSupportedContent" aria-expanded="false" 
                    aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav category-scroll mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                <?php 
                $categories_query->data_seek(0);
                while($category = $categories_query->fetch_assoc()):
                ?>
                <li class="nav-item">
                    <a class="nav-link" href="#<?php echo strtolower(str_replace(['-', ' '], '', $category['name'])); ?>">
                    <?php echo $category['name']; ?>
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
            <a href="admin/index.php" class="btn btn-outline-dark">Admin</a>
            </div>
        </div>
    </nav>
    <!-- Header-->
    <header class=" py-5" style="background-color:rgb(255, 168, 37);">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Shoping in my webside</h1>
                <!-- items_loader -->
                <div class="items">
                    <div class="display-4 items_loader" style="color: #000;">
                        <p>Artwork Have </p>
                        <div class="words">
                            <span class="word">Necklace</span>
                            <span class="word">Bracelet</span>
                            <span class="word">Glasses</span>
                            <span class="word">Earrings</span>
                            <span class="word">Ring</span>
                            <span class="word">Bag</span>
                            <span class="word">Necklace</span>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </header>
    
    <?php
    // Reset categories query pointer
    $categories_query->data_seek(0);
    while($category = $categories_query->fetch_assoc()):
        $category_id = $category['id'];
        $category_name = $category['name'];
        $category_slug = strtolower(str_replace(['-', ' '], '', $category_name));
        $products = $conn->query("SELECT * FROM products WHERE category_id = $category_id");
        
        // Determine modal header class based on category
        $modal_header_class = 'bg-primary'; // Default
        if (stripos($category_name, 'women') !== false) {
            $modal_header_class = 'bg-danger';
        } // Add more conditions if needed for other categories
    ?>
        <!-- Section-<?php echo $category_slug; ?> -->
        <section id="<?php echo $category_slug; ?>">
            <div class="section-title">
                <span><?php echo $category_name; ?></span>
            </div>
            <div class="container px-4 px-lg-5 mt-5">
                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                    <?php while($product = $products->fetch_assoc()): 
                        $modal_id = 'productModal' . ucfirst($category_slug) . str_pad($product['id'], 4, '0', STR_PAD_LEFT);
                    ?>
                        <!-- Product card -->
                        <div class="col mb-5">
                            <div class="card h-100" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#<?php echo $modal_id; ?>">
                                <div class="badge bg-dark text-white position-absolute" style="top: 0.5rem; right: 0.5rem">New</div>
                                <img class="card-img-top" src="<?php echo $product['image']; ?>" />
                                <div class="card-body p-4">
                                    <div class="product-card">
                                        <h5 class="fw-bolder"><?php echo $product['name']; ?></h5>
                                        $<?php echo number_format($product['price'], 2); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        
        <?php 
        // Reset products query for modal generation
        $products = $conn->query("SELECT * FROM products WHERE category_id = $category_id");
        while($product = $products->fetch_assoc()): 
            $modal_id = 'productModal' . ucfirst($category_slug) . str_pad($product['id'], 4, '0', STR_PAD_LEFT);
        ?>
            <!-- Modal for product -->
            <div class="modal fade" id="<?php echo $modal_id; ?>" tabindex="-1" aria-labelledby="<?php echo $modal_id; ?>Label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header <?php echo $modal_header_class; ?> text-white">
                            <h5 class="modal-title" id="<?php echo $modal_id; ?>Label"><?php echo $product['name']; ?> - <?php echo $category_name; ?> Collection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="<?php echo $product['image']; ?>" class="img-fluid"/>
                                </div>
                                <div class="col-md-6">
                                    <h5><?php echo $product['name']; ?></h5>
                                    <p><?php echo $product['description']; ?></p>
                                    <h5>$<?php echo number_format($product['price'], 2); ?></h5>
                                    <div class="text-center">
                                        <div class="social-links">
                                            <a href="https://www.facebook.com/muoyshop" target="_blank">
                                                <div id="facebook" class="social-btn flex-center">
                                                    <svg viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg" style="fill: blue;">
                                                        <path d="M22.675 0h-21.35c-.733 0-1.325.592-1.325 1.325v21.351c0 .733.592 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.894-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.794.715-1.794 1.763v2.312h3.587l-.467 3.622h-3.12v9.293h6.116c.733 0 1.325-.591 1.325-1.324v-21.35c0-.733-.592-1.325-1.325-1.325z"></path>
                                                    </svg>
                                                    <span>Facebook</span>
                                                </div>
                                            </a>
                                            <a href="https://www.instagram.com/muoy_shop" target="_blank">
                                                <div id="instagram" class="social-btn flex-center">
                                                    <svg viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg" style="fill: #E1306C;">
                                                        <path d="M7.75 2h8.5A5.25 5.25 0 0 1 21.5 7.25v8.5A5.25 5.25 0 0 1 16.25 21h-8.5A5.25 5.25 0 0 1 2.5 15.75v-8.5A5.25 5.25 0 0 1 7.75 2zm6.645 2.604a1.146 1.146 0 1 0 0 2.292 1.146 1.146 0 0 0 0-2.292zM12 7.25a4.75 4.75 0 1 0 0 9.5 4.75 4.75 0 0 0 0-9.5zm0 1.5a3.25 3.25 0 1 1 0 6.5 3.25 3.25 0 0 1 0-6.5z"/>
                                                    </svg>
                                                    <span>Instagram</span>
                                                </div>
                                            </a>
                                            <a href="https://www.tiktok.com/@muoy_shop" target="_blank">
                                                <div id="tiktok" class="social-btn flex-center">
                                                    <svg viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg" style="fill: black;">
                                                        <path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.672a2.896 2.896 0 0 1-2.878 2.916 2.897 2.897 0 0 1-2.878-2.916 2.897 2.897 0 0 1 2.878-2.917c.154 0 .307.014.456.043V7.968a6.32 6.32 0 0 0-4.565 1.894 6.336 6.336 0 0 0-1.886 4.51c0 1.703.68 3.343 1.886 4.51a6.322 6.322 0 0 0 4.565 1.893 6.336 6.336 0 0 0 6.337-6.337V9.368a8.177 8.177 0 0 0 3.297.697V6.686z"></path>
                                                    </svg>
                                                    <span>TikTok</span>
                                                </div>
                                            </a>
                                            <a href="https://t.me/muoyshop" target="_blank">
                                                <div id="telegram" class="social-btn flex-center">
                                                    <svg viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg" style="fill: #0088cc;">
                                                        <path d="M2.273 10.533 18.09 4.151c.688-.27 1.344.167 1.116 1.008l-2.34 11.008c-.17.803-.686 1.008-1.39.653l-3.51-1.78-1.678 1.623c-.186.18-.34.33-.678.33-.338 0-.508-.167-.678-.5l-1.186-3.84-2.17-1.34c-.678-.42-.314-1.338.508-.923l5.424 3.34 2.34-7.678-12.576 5.482z"/>
                                                    </svg>
                                                    <span>Telegram</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endwhile; ?>

    <!-- Footer-->
    <div>
        <!-- Footer -->
        <footer class="text-center text-lg-start text-white" style="background-color: rgb(255, 168, 37)">
            <div class="container p-4 pb-0">
                <section>
                <div class="row">
                    <!-- Column 1 -->
                    <div class="col-3 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 font-weight-bold">Muoy Shop</h6>
                    <p>មានលក់ នាឡិកា វែនតា ប្រុស/ស្រី ជញ្ជៀន កាបូប និងផលិតផលស្អាតៗផ្សេងទៀត។</p>
                    </div>

                    <!-- Column 2 -->
                    <div class="col-3 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 font-weight-bold">Products</h6>
                    <p><a class="text-white">Necklace-Men</a></p>
                    <p><a class="text-white">Necklace-Women</a></p>
                    <p><a class="text-white">Bags</a></p>
                    <p><a class="text-white">Earrings</a></p>
                    <p><a class="text-white">Glasses</a></p>
                    </div>

                    <!-- Column 3 -->
                    <div class="col-3 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 font-weight-bold">Contact</h6>
                    <p><i class="fas fa-location mr-3"></i> ទីតាំង ផ្លូវ២៦ វត្តរាជបូព៌...</p>
                    <p><i class="fas fa-envelope mr-3"></i> sremuoyyin@gmail.com</p>
                    <p><i class="fas fa-phone mr-3"></i> +855 99 713 399</p>
                    </div>

                    <!-- Column 4 -->
                    <div class="col-3 mx-auto mt-3">
                    <h6 class="text-uppercase mb-4 font-weight-bold">Follow us</h6>
                    <a class="btn btn-primary btn-floating m-1" style="background-color: #3b5998" href="https://www.facebook.com/muoyshop" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-primary btn-floating m-1" style="background-color: #55acee" href="https://t.me/muoyshop" target="_blank"><i class="fab fa-telegram"></i></a>
                    <a class="btn btn-primary btn-floating m-1" style="background-color: #ac2b5a" href="https://www.instagram.com/muoy_shop" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a class="btn btn-primary btn-floating m-1" style="background-color: #000000" href="https://www.tiktok.com/@muoy_shop" target="_blank"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                </section>
            </div>
            <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2)"> © 2025 Copyright:
                <a class="text-white" href="index.php">Muoy_shop</a>
            </div>
        </footer>

        <!-- Footer -->
    </div>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/styles.js"></script>
     <script>
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar-nav.category-scroll');
    if (navbar && window.innerWidth >= 992) {
        let isDown = false;
        let startX;
        let scrollLeft;

        navbar.addEventListener('mousedown', (e) => {
            isDown = true;
            navbar.style.cursor = 'grabbing';
            startX = e.pageX - navbar.offsetLeft;
            scrollLeft = navbar.scrollLeft;
        });

        navbar.addEventListener('mouseleave', () => {
            isDown = false;
            navbar.style.cursor = 'grab';
        });

        navbar.addEventListener('mouseup', () => {
            isDown = false;
            navbar.style.cursor = 'grab';
        });

        navbar.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - navbar.offsetLeft;
            const walk = (x - startX) * 2;
            navbar.scrollLeft = scrollLeft - walk;
        });

        navbar.style.cursor = 'grab';
    }
});

</script>
</body>
</html>
<?php
$conn->close();
?>
