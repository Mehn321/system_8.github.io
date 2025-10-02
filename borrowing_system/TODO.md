# TODO: Fix PHP Warnings in Borrowing System

- [x] Add isset check for $\_POST['quantities_returned'] in transactions.php
- [x] Modify returnItems method in Transaction.php to check if $quantities_returned is array
- [x] Create PHP endpoint api/get_transaction_items.php to return JSON of transaction items
- [x] Add AJAX call in showReturnForm in script.js to load transaction items and create input fields
