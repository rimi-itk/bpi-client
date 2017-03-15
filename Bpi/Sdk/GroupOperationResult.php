<?php
namespace Bpi\Sdk;

/**
 * TODO please add a documentation for this class.
 */
class GroupOperationResult
{
    /**
     * @var \SimpleXMLElement
     */
    protected $element;

    /**
     * @var int
     */
    protected $code;

    /**
     * @var int
     */
    protected $skipped;

    /**
     * @var int
     */
    protected $success;

    /**
     * @var array
     */
    protected $successIds;

    public function __construct(\SimpleXMLElement $element)
    {
        $this->element = $element;

        if (isset($element)) {
            $this->code = (int)$element->code;
            $this->skipped = (int)$element->skipped;
            $this->success = (int)$element->success;
            if (isset($element->success_list)) {
                $this->successIds = [];
                foreach ($element->success_list->item as $item) {
                    $this->successIds[] = (string)$item;
                }
            }
        }
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getSkipped()
    {
        return $this->skipped;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function getSuccessIds()
    {
        return $this->successIds;
    }
}
