# Gigasavvy Magento Importer.

This package includes a `BatchFileReader` , `BatchCSVProcessor`, and a `BatchMagentoImporter`.  The purpose of all of those classes above is to

 1. Load files ( of possible magnitude, through chunk processing ) into memory using the `BatchFileReader` class.
 2. Process those CSV files into an array format ( multi-dimensional & fully combined ) using the `BatchCSVProcessor` class.
 3. Import that above combined array into Magento using the `BatchMagentoImporter` class.

## Usage

Define a certain chunk-size. ( Ex. 1024*1024 )
`define('CHUNK_SIZE',1024*1024);`

Generate the file resource
`$file = new BatchFileReader(__DIR__.'/file.csv',CHUNK_SIZE);`

Process the above resource into a combined, resource array.
`BatchCSVProcessor::processResourceToArray($file->getFileResource());`

## History

02/08/2016, 11:59am - Initial commit.  Includes all functionality except Magento importing.
