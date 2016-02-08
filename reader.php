<?php
/**
 * Batch File Reader File
 * @package GSImporter
 */

require_once 'autoload.php';

/**
 * Class BatchFileReader
 * --
 * Reads & echos back large files through a chunk method of loading.
 * @package GSImporter
 */
class BatchFileReader
{
    /**
     * @var $file_handler
     * Variable handles the file ( wrapper )
     */
    private $file_handler;

    /**
     * @var $buffer
     * Variable file buffer
     */
    private $buffer;

    /**
     * @var $chunk_size
     * This variable handles the loading size of each individual portion of the file ( 756 / 1024 / 2048 )
     */
    private $chunk_size;

    /**
     * @var $final_chunk
     * This variable holds the buffer in memory
     */
    private $final_output;

    /**
     * BatchFileReader constructor.
     * @param $file_handler
     * @param $chunk_size
     */
    public function __construct($file_name,$chunk_size)
    {
        $this->file_handler = fopen($file_name,'rb');
        $this->chunk_size = $chunk_size;
    }

    /**
     * getFileResource
     * --
     * returns the current file resource ( file handler. )
     * @return mixed
     */
    public function getFileResource()
    {
        return $this->file_handler;
    }

    /**
     * getChunkSize
     * --
     * returns the current (set) chunk size.
     * @return mixed
     */
    public function getChunkSize()
    {
        return $this->chunk_size;
    }

    /**
     * getCurrentBuffer
     * --
     * returns the indexed buffer output. ( wherever the chunking process is at, this will return the current position and content within the file. )
     * @return mixed
     */
    public function getCurrentBuffer()
    {
        return $this->buffer;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->final_output;
    }

    /**
     * @return bool|int
     */
    public function readfile() {
        return $this->readfileChunked($this->file_handler);
    }

    /**
     * @param $filename
     * @param bool $retbytes
     * @return bool|int
     */
    private function readfileChunked($filename, $retbytes = TRUE)
    {
        // Reset the buffer, from any previous reads.
        $this->buffer = "";
        $cnt = 0;

        if ($this->file_handler === false) {
            // No file.
            return false;
        }

        while (!feof($this->file_handler)) {

            // get the current buffer ( the buffer is the viewport of the file we can see. )
            $this->buffer = fread($this->file_handler, $this->chunk_size);

            $this->final_output .= $this->buffer;

            // flush the current file stream
            ob_flush();
            flush();

            if ($retbytes) {
                $cnt += strlen($this->buffer);
            }
        }

        // Receive a status from the file ( boolean )
        $status = fclose($this->file_handler);

        if ($retbytes && $status) {
            return $cnt; // return # of bytes delivered like readfile() does.
        }

        return $status;
    }

}