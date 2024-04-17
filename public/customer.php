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
        <!-- <div id="loader" style="display: none; color: green">Fetching please wait...</div> -->
        <div id="loader" style="display: none;" class="alert alert-primary" role="alert">
            Fetching please wait ...
        </div>
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
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="col-md-4">
            <label for="deal_name" class="form-label">Choose Deal</label>
            <select class="form-select" id="deal_id" name="deal_name" required>
                <option selected>Choose...</option>
                <?php
                // Retrieve deals from database and populate dropdown
                $query = "SELECT deal_id, deal_name, retail_price FROM deals";
                $result = mysqli_query($connection, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['deal_name'] . "' data-price='" . $row['retail_price'] .  "' data-id='" . $row['deal_id'] . "'>" . $row['deal_name'] . "</option>";
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

        <div id="additional_info_form" style="margin-top: -4px;">

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
        var dealId = selectedOption.getAttribute('data-id');
        // document.getElementById('deal_price').value = dealPrice;

        document.getElementById('loader').style.display = 'block';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/mah-portal/process/get_deal_details.php?deal_id=' + dealId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                // Hide loader
                document.getElementById('loader').style.display = 'none';
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    // Populate form fields with retrieved data
                    populateForm(response);
                    document.getElementById('deal_price').value = dealPrice;
                } else {
                    console.error('Error:', xhr.status);
                }
            }
        };
        xhr.send();
    });

    function populateForm(data) {
        var form = document.getElementById('additional_info_form');

        // Clear existing dynamic input fields
        var dynamicFields = form.querySelectorAll('.dynamic-field');
        for (var i = 0; i < dynamicFields.length; i++) {
            dynamicFields[i].remove();
        }

        // Iterate through data properties and create input fields
        // for (var key in data) {
        //     if (data.hasOwnProperty(key)) {
        //         var value = data[key];
        //         if (typeof value === 'object') {
        //             value = JSON.stringify(value); // Convert object to string
        //         }
        //         var label = document.createElement('label');
        //         label.textContent = key;
        //         var input = document.createElement('input');
        //         input.type = 'text';
        //         input.name = 'deal_item_name[]';
        //         // input.name = key;
        //         input.value = data[key];
        //         input.classList.add('dynamic-field');
        //         label.classList.add('dynamic-field');
        //         input.classList.add('form-control');
        //         label.classList.add('form-label');
        //         form.appendChild(label);
        //         form.appendChild(input);
        //     }
        // }

        for (var i = 0; i < data.length; i++) {
            var dealItem = data[i];
            // var label = document.createElement('label');
            // label.textContent = dealItem.days;
            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'deal_item_name[]';
            input.value = dealItem.deal_item_name;
            input.value = dealItem.deal_item_name;
            input.classList.add('dynamic-field');
            // label.classList.add('dynamic-field');
            input.classList.add('form-control');
            input.classList.add('mt-4');
            // input.classList.add('col-6');
            // label.classList.add('form-label');
            // input.setAttribute('data-days', 'deal_item_days_' + dealItem.days);
            // form.appendChild(label);
            form.appendChild(input);

            var hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'deal_item_days_' + dealItem.days; // Set the name of the hidden input field
            hiddenInput.value = dealItem.days; // Set the value of the hidden input field
            form.appendChild(hiddenInput);
        }
    }
</script>


<?php
require_once('../public/footer.php');
?>