<?php

namespace Besnovatyj\Actors\forms\backend\actors;

use Besnovatyj\Forms\CompositeForm;
use Besnovatyj\Meta\MetaForm;
use Besnovatyj\Actors\entities\actors\Actor;

/**
 * @property MetaForm $meta
 * @property TaxonomiesForm $taxonomies
 * @property TagsForm $tags
 */
class ActorForm extends CompositeForm
{
    public string $name = '';
    public string $description = '';
    public int|null $status = null;

    public function __construct(?Actor $actor = null, $config = [])
    {
        if ($actor) {
            $this->name = $actor->name;
            $this->description = $actor->description;
            $this->status = $actor->status;
            $this->meta = new MetaForm($actor->meta);
            $this->taxonomies = new TaxonomiesForm($actor);
            $this->tags = new TagsForm($actor);
        } else {
            $this->meta = new MetaForm();
            $this->taxonomies = new TaxonomiesForm();
            $this->tags = new TagsForm();
        }
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['name', 'status'], 'required'],
            [['name'], 'string', 'max' => 255],
            ['description', 'string'],
            ['status', 'integer'],
            ['status', 'in', 'range' => [Actor::STATUS_DRAFT, Actor::STATUS_ACTIVE]],
        ];
    }

    protected function internalForms(): array
    {
        return ['meta', 'taxonomies', 'tags'];
    }

    public function statusList(): array
    {
        return [
            Actor::STATUS_DRAFT => 'DRAFT',
            Actor::STATUS_ACTIVE => 'ACTIVE',
        ];
    }
}
