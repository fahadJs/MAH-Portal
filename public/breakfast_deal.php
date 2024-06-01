<?php

// Start session
session_start();

require_once('../public/header.php');
require_once('../db/db.php');

// Check if user logged in
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

if (isset($_GET['cust_id'])) {
    $cust_id = $_GET['cust_id'];

    $customerQuery = "SELECT * FROM customers WHERE id = $cust_id";
    $custDetailsResult = mysqli_query($connection, $customerQuery);

    $custDetails = mysqli_fetch_assoc($custDetailsResult);
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add BreakFast Deal</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Customers BreakFast</li>
    </ol>

    <form class="row g-3 mb-4" action="../process/breakfast_deal_customer.php" method="POST">
        <ul class="mb-0">
            <li>Current deal: <span style="font-weight: bold;"><?php echo $custDetails['deal_name']?></span></li>
            <li>Deal price: <span style="font-weight: bold;"><?php echo $custDetails['deal_price']?></span></li>
            <li>Delivery price: <span style="font-weight: bold;"><?php echo $custDetails['delivery_price']?></span></li>
            <li>Customer type: <span style="font-weight: bold;"><?php echo $custDetails['type']?></span></li>
        </ul>
        <!-- <div id="loader" style="display: none; color: green">Fetching please wait...</div> -->
        <div id="loader" style="display: none;" class="alert alert-primary" role="alert">
            Fetching please wait ...
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text">Name</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <input type="text" class="form-control" id="name" name="name" value=<?php echo $custDetails['name']; ?> required readonly>
                <input type="text" class="form-control" id="cust_id" name="cust_id" value=<?php echo $cust_id; ?> hidden>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text">Contact</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <input type="text" class="form-control" id="contact" name="contact" value="<?php echo $custDetails['contact']; ?>" required readonly>
            </div>
            <!-- <label for="contact" class="form-label">Contact</label> -->
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text">Start Date</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>
            <!-- <label for="start_date" class="form-label">Start Date</label> -->
        </div>

        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text">Type</span>
                <select class="form-select" id="customer_type" name="customer_type" required>
                    <option>Choose...</option>
                    <option value="normal">Normal</option>
                    <option value="tester">Tester</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
        </div>

        <div class="col-md-4" id="deal_name_dropdown">
            <div class="input-group">
                <span class="input-group-text">Deal</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <select class="form-select" id="deal_id" name="deal_name" required>
                    <option>Choose...</option>
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
            <!-- <label for="deal_name" class="form-label">Choose Deal</label> -->

        </div>

        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text">Deal Price</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <input type="number" class="form-control" id="deal_price" name="deal_price" required>
            </div>
            <!-- <label for="deal_price" class="form-label">Price</label> -->
        </div>

        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text">Number of Persons</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <input type="number" class="form-control" id="number_of_persons" name="number_of_persons" min="1" required>
            </div>
            <!-- <label for="number_of_persons" class="form-label">Number of Persons</label> -->
        </div>

        <div class="col-6" id="e">
            <div class="input-group">
                <span class="input-group-text">Email</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <input type="text" class="form-control" id="email" name="email" value="<?php echo $custDetails['email']; ?>" required readonly>
            </div>
            <!-- <label for="email" class="form-label">Email</label> -->
        </div>
        <div class="col-md-6" id="d">
            <div class="input-group">
                <span class="input-group-text">Delivery Price</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <input type="number" class="form-control" id="delivery_price" name="delivery_price" required>
            </div>
            <!-- <label for="delivery_price" class="form-label">Delivery Price</label> -->
        </div>
        <div class="col-12">
            <div class="input-group">
                <span class="input-group-text">Address</span>
                <!-- <label for="name" class="form-label">Name</label> -->
                <input type="text" class="form-control" id="address" name="address" value="<?php echo $custDetails['address']; ?>" required readonly>
            </div>
            <!-- <label for="address" class="form-label">Address</label> -->
        </div>


        <div id="additional_info_form" style="margin-top: -4px;">

        </div>

        <button type="button" id="add_tester_field_btn" class="btn btn-secondary mt-3 mb-3">Add Dishes Days wise</button>
        <div id="tester_fields" class="row" style="display: none;">
            <!-- Tester fields will be dynamically added here -->
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('deal_name_dropdown').style.display = 'none';
    document.getElementById('add_tester_field_btn').style.display = 'none';
    document.getElementById('add_tester_field_btn').addEventListener('click', function() {
        addTesterField(); // Add a new tester field when button is clicked
    });

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
            // Create a new row to contain the input fields
            var row = document.createElement('div');
            row.classList.add('row');

            // First column for the deal item name
            var nameColumn = document.createElement('div');
            nameColumn.classList.add('col-6'); // Bootstrap grid column size
            var nameInput = document.createElement('input');
            nameInput.type = 'text';
            nameInput.name = 'deal_item_name[]';
            nameInput.value = dealItem.deal_item_name;
            nameInput.classList.add('dynamic-field');
            nameInput.classList.add('form-control');
            nameInput.classList.add('mt-4');
            nameColumn.appendChild(nameInput);
            row.appendChild(nameColumn);

            // Second column for the deal item weekdays
            var dayColumn = document.createElement('div');
            dayColumn.classList.add('col-6'); // Bootstrap grid column size
            var dayInput = document.createElement('input');
            dayInput.type = 'date';
            dayInput.name = 'deal_item_date[]';
            dayInput.value = dealItem.weekdays;
            dayInput.classList.add('dynamic-field');
            dayInput.classList.add('form-control');
            dayInput.classList.add('mt-4');
            dayInput.placeholder = 'Enter Day';
            // dayInput.readOnly = true;
            dayColumn.appendChild(dayInput);
            row.appendChild(dayColumn);

            var hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.classList.add('dynamic-field');
            hiddenInput.name = 'deal_item_days_' + dealItem.days; // Set the name of the hidden input field
            hiddenInput.value = dealItem.days; // Set the value of the hidden input field
            row.appendChild(hiddenInput);

            form.appendChild(row);
        }
    }

    document.getElementById('customer_type').addEventListener('change', function() {
        var selectedOption = this.value;
        if (selectedOption === 'normal') {
            document.getElementById('deal_name_dropdown').style.display = 'block';
            document.getElementById('tester_fields').style.display = 'none';
            document.getElementById('add_tester_field_btn').style.display = 'none';
            var email = document.getElementById('e');
            var date = document.getElementById('d');
            email.classList.remove(...email.classList);
            email.classList.add('col-4');
            date.classList.remove(...date.classList);
            date.classList.add('col-4');
            clearTesterFields();
        } else if (selectedOption === 'tester') {
            clearAll();
            var email = document.getElementById('e');
            var date = document.getElementById('d');
            email.classList.remove(...email.classList);
            email.classList.add('col-6');
            date.classList.remove(...date.classList);
            date.classList.add('col-6');
            document.getElementById('add_tester_field_btn').style.display = 'block';
            document.getElementById('deal_name_dropdown').style.display = 'none';
            clearTesterFields();
            document.getElementById('tester_fields').style.display = 'block';
            addTesterField();
        } else if (selectedOption === 'custom') {
            clearAll();
            var email = document.getElementById('e');
            var date = document.getElementById('d');
            email.classList.remove(...email.classList);
            email.classList.add('col-6');
            date.classList.remove(...date.classList);
            date.classList.add('col-6');
            document.getElementById('add_tester_field_btn').style.display = 'block';
            document.getElementById('deal_name_dropdown').style.display = 'none';
            clearTesterFields();
            document.getElementById('tester_fields').style.display = 'block';
            addTesterField();
        }
    });

    function clearAll() {
        var form = document.getElementById('additional_info_form');
        var dynamicFields = form.querySelectorAll('.dynamic-field');
        for (var i = 0; i < dynamicFields.length; i++) {
            dynamicFields[i].remove();
        }
    }

    function clearTesterFields() {
        var testerFieldsContainer = document.getElementById('tester_fields');
        testerFieldsContainer.innerHTML = '';
    }

    // function addTesterField() {
    //     var testerFieldsContainer = document.getElementById('tester_fields');
    //     var input = document.createElement('textarea');
    //     input.type = 'text';
    //     input.name = 'deal_item_name[]';
    //     input.classList.add('dynamic-field');
    //     input.classList.add('form-control');
    //     input.classList.add('mb-4');
    //     input.placeholder = 'Dishes';
    //     testerFieldsContainer.appendChild(input);

    //     var weekInput = document.createElement('textarea');
    //     weekInput.type = 'text';
    //     weekInput.name = 'deal_item_weekdays[]';
    //     weekInput.classList.add('dynamic-field');
    //     weekInput.classList.add('form-control');
    //     weekInput.classList.add('mb-4');
    //     weekInput.placeholder = 'Enter Day';
    //     testerFieldsContainer.appendChild(weekInput);

    //     // Add hidden field
    // var hiddenInput = document.createElement('input');
    // hiddenInput.type = 'hidden';
    // hiddenInput.classList.add('dynamic-field');
    // hiddenInput.name = 'deal_item_days_' + (document.querySelectorAll('#tester_fields textarea').length); // Set the name of the hidden input field
    // hiddenInput.value = (document.querySelectorAll('#tester_fields textarea').length); // Set the value of the hidden input field
    // testerFieldsContainer.appendChild(hiddenInput);
    // }


    function addTesterField() {
        var testerFieldsContainer = document.getElementById('tester_fields');

        // Create a div with the class "row" to contain the two textareas
        var rowDiv = document.createElement('div');
        rowDiv.classList.add('row');

        // First textarea (Dishes)
        var dishDiv = document.createElement('div');
        dishDiv.classList.add('col-6'); // Bootstrap grid column size
        var dishTextarea = document.createElement('textarea');
        dishTextarea.type = 'text';
        dishTextarea.name = 'deal_item_name[]';
        dishTextarea.classList.add('dynamic-field');
        dishTextarea.classList.add('form-control');
        dishTextarea.classList.add('mb-4');
        dishTextarea.placeholder = 'Dishes';
        dishDiv.appendChild(dishTextarea);
        rowDiv.appendChild(dishDiv); // Append to the row

        // Second textarea (Enter Day)
        var dayDiv = document.createElement('div');
        dayDiv.classList.add('col-6'); // Bootstrap grid column size
        var dayTextarea = document.createElement('input');
        dayTextarea.type = 'date';
        dayTextarea.name = 'deal_item_date[]';
        dayTextarea.classList.add('dynamic-field');
        dayTextarea.classList.add('form-control');
        dayTextarea.classList.add('mb-4');
        dayTextarea.placeholder = 'Enter Day';
        dayDiv.appendChild(dayTextarea);
        rowDiv.appendChild(dayDiv); // Append to the row

        // Add hidden field
        var hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.classList.add('dynamic-field');
        hiddenInput.name = 'deal_item_days_' + (document.querySelectorAll('#tester_fields textarea').length + 1); // Set the name of the hidden input field
        hiddenInput.value = (document.querySelectorAll('#tester_fields textarea').length + 1); // Set the value of the hidden input field
        // testerFieldsContainer.appendChild(hiddenInput);

        // Append the row and hidden input to the container
        testerFieldsContainer.appendChild(rowDiv);
        testerFieldsContainer.appendChild(hiddenInput);
    }

    // function calculateTotalPrice() {
    //     var dealPrice = parseFloat(document.getElementById('deal_price').value);
    //     var numberOfPersons = parseInt(document.getElementById('number_of_persons').value);

    //     if (!isNaN(dealPrice) && !isNaN(numberOfPersons)) {
    //         var totalPrice = dealPrice * numberOfPersons;
    //         document.getElementById('deal_price').value = totalPrice.toFixed(2); // Display total price with two decimal places
    //     }
    // }

    // // Event listener for input field change
    // document.getElementById('number_of_persons').addEventListener('change', calculateTotalPrice);
</script>


<?php
require_once('../public/footer.php');
?>