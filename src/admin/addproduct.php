<?php
session_start();

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include "sessions/sessiontimeout.php";
?>
<html lang="en">
    <head>
        <?php
        include "components/essential.inc.php";
        ?>

        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php
        include "components/nav.inc.php";
        ?>
        <main class="container mt-5">
            <div class="row-cols-3 g-3">
                <div class="col-lg-12 col-md-12 col-sm-12 col-12 edit_container">
                    <div id="edit_form">
                        <h4>Adding New Product</h4>
                        <form class='action-form' action='process/addproduct_process.php' method='post' enctype="multipart/form-data">
                            <div class='form-group'>
                                <label for='product_name'>Product Name</label>
                                <input class='form-control' type='text' name='product_name'>
                            </div> 
                            <div class='form-group'> 
                                <label for='product_cost'>Product Cost(SGD)</label>
                                <input class='form-control' type='text' name='product_cost' required> 
                            </div>
                            <div class='form-group'>
                                <label for='product_cost'>Product Category<br>Barebone Kits: 5<br>Keyboard: 4<br>keycaps: 3<br>Cables: 2<br>Switches: 1</label> 
                                <input class='form-control' type='number' name='category_id' required> 
                            </div>
                            <div class='form-group'>
                                <label for='product_sd'>Product Short Description</label>
                                <input class='form-control' type='text' name='product_sd' required> 
                            </div>
                            <div class='form-group'>
                                <label for='product_ld'>Product Long Description</label>
                                <textarea class='form-control' name='product_ld' rows='5' required></textarea>
                            </div> 
                            <div class='form-group'>
                                <label for='product_quantity'>Product Quantity</label>
                                <input class='form-control' type='number' name='product_quantity' required>
                            </div>
                            <div class='form-group'>
                                <label for='product_image'>Product Image</label>
                                <p class="text-danger" style="background-color: #FFFF00">Make sure the image name is same as product name<br> eg. Product name: Techware Veil 87; Image name: techwareveil87.jpg</P>
                                <input type="file" name="fileToUpload" required>
                            </div>
                            <div class='form-group'>
                                <label for='admin_pwd'>Enter Admin Password</label>
                                <input class='form-control password-text' type='password' name='admin_pwd' placeholder='Admin Password' required>
                            </div>
                            <input class='btn btn-success mt-3' type='submit' name='submit' value='Save'>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
    <link rel="stylesheet" href="css/edit.css">
</html>