<?php

namespace XtractPDF\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use XtractPDF\Core\Model as BaseModel;

/**
 * Document Abstract
 * @ODM\EmbeddedDocument 
 */
class DocumentAbstract extends DocumentContent
{
    /* Everything the same as DocumentContent */
}

/* EOF: DocumentAbstract.php */