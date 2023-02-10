<?php
namespace OffbeatWP\AcfLayout\Fields;

use OffbeatWP\Form\Fields\AbstractField;

class ComponentsField extends AbstractField {
    public const FIELD_TYPE = 'offbeat_components';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}