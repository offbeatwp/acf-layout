<?php
namespace OffbeatWP\AcfLayout\Fields;

use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\AcfLayout\Repositories\AcfLayoutComponentRepository;

class ComponentsField extends AbstractField {
    public const FIELD_TYPE = 'flexible_content';

    public function init(): void
    {
        if (did_action('acf/init')) {
            $this->layouts();
            $this->attribute('button_label', __('Add component', 'offbeatwp'));
        }
    }

    public function layouts() {
        $acfLayoutComponentRepository = offbeat(AcfLayoutComponentRepository::class);

        $this->attribute('layouts', $acfLayoutComponentRepository->getLayouts(true));
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}