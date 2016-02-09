# Gigasavvy Magento Importer.

This package includes a `BatchFileReader` , `BatchCSVProcessor`, and a `BatchMagentoImporter`.  The purpose of all of those classes above is to

 1. Load files ( of possible magnitude, through chunk processing ) into memory using the `BatchFileReader` class.
 2. Process those CSV files into an array format ( multi-dimensional & fully combined ) using the `BatchCSVProcessor` class.
 3. Import that above combined array into Magento using the `BatchMagentoImporter` class.

## Usage

Define a certain chunk-size. ( This amount will set the batch-reading pace of the resource within the `BatchFileReader`. )
```php
define('CHUNK_SIZE',1024*1024);
```

Generate the file resource
```php
$file = new BatchFileReader(__DIR__.'/file.csv',CHUNK_SIZE);
```

Process the above resource into a combined, resource array.
```php
$import_order_array = BatchCSVProcessor::processResourceToArray($file->getFileResource());
```

Go ahead and create a new instance of the importer class.
```php
$importer = new BatchMagentoImporter($import_order_array,'admin@email.com',__DIR__.'/public_html/shop/app');
```

Make sure your Magento `app` directory is included correctly.  If so, compile the orders that are included within the `$import_order_array`. ( The compilation process builds Magento orders, and saves them with the status of the imported order. )

```php
if($importer->testMagentoConnection()) {
	$importer->compileOrders();
} else {
	return; // Return error|exception|false
}
```

The `above code` should be written within a blank PHP file.  To make sure you have access to the aforementioned classes, use the line below.
```php
require_once '/path/to/importer/autoload.php';
```

## History

02/08/2016, 11:59am - Initial commit.  Includes all functionality except Magento importing.

02/09/2016, 7:27am - Product addition commit.  At this point the `product-add-to-compilation` branch holds all requested functionality.

02/09/2016, 7:31am - Pull Request addition.  Just synchronized the `product-add-to-compilation` branch into the `master` branch.
