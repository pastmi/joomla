<?php
// Set up the data to be sent in the response.
$data = array('some data');

// Get the document object.
$document =& JFactory::getDocument();

// Set the MIME type for JSON output.
$document->setMimeEncoding('application/json');

// Change the suggested filename.
JResponse::setHeader('Content-Disposition','attachment;filename="'.$view->getName().'.json"');

// Output the JSON data.
echo json_encode($data);