<?php

$file = "../templates/sales_template.csv";

if (file_exists($file)) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_template.csv"');
    readfile($file);
    exit;
} else {
    echo "Template file not found.";
}
?>