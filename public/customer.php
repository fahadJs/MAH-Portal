<?php
require_once('../public/header.php');
require_once('../db/db.php');
?>


<div class="container-fluid px-4">
    <h1 class="mt-4">Customer</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Customers</li>
    </ol>

    <form class="row g-3 mb-4" action="../process/customer_insert.php" method="POST">
        <div class="col-md-4">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="col-md-4">
            <label for="contact" class="form-label">Contact</label>
            <input type="text" class="form-control" id="contact" name="contact" required>
        </div>
        <div class="col-md-4">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="text" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="col-md-4">
            <label for="deal_id" class="form-label">Choose Deal</label>
            <select class="form-select" id="deal_id" name="deal_id" required>
                <option selected>Choose...</option>
                <?php
                // Retrieve deals from database and populate dropdown
                $query = "SELECT deal_id, deal_name, retail_price FROM deals";
                $result = mysqli_query($connection, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['deal_id'] . "' data-price='" . $row['retail_price'] . "'>" . $row['deal_name'] . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-4">
            <label for="deal_price" class="form-label">Price</label>
            <input type="number" class="form-control" id="deal_price" name="deal_price" required>
        </div>

        <div class="col-md-4">
            <label for="delivery_price" class="form-label">Delivery Price</label>
            <input type="number" class="form-control" id="delivery_price" name="delivery_price" required>
        </div>
        <div class="col-6">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" required>
        </div>
        <div class="col-6">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control" id="email" name="email" required>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('deal_id').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var dealPrice = selectedOption.getAttribute('data-price');
        document.getElementById('deal_price').value = dealPrice;
    });
</script>


<?php
require_once('../public/footer.php');
?>