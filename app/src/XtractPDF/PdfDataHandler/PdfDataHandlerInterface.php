<?php

namespace XtractPDF\PdfDataHandler;

abstract class PdfDataHandlerInterface
{
    /**
     * Save the data PDF
     *
     * @param string   $identifier  A unique identifier for this document
     * @param resrouce $streamId    A stream that can be opened and read by fopen()
     */
    function save($identifier, $streamId);

    /**
     * Remove the data for a PDF
     *
     * @param string $identifier  An identifier
     */
    function del($identifer);

    /**
     * Stream the data for the PDF
     *
     * @param string $identifier  An identifier
     */
    function stream($identifier);
}

/* EOF: PdfDataHandlerInterface.php */