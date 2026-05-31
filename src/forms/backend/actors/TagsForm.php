<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\forms\backend\actors;

use Besnovatyj\Helpers\StringHelper;
use Besnovatyj\Actors\entities\actors\Actor;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class TagsForm extends Model
{
    public array $newTagsNames = [];

    public function __construct(?Actor $actor = null, $config = [])
    {
        if ($actor) {
            $this->newTagsNames = ArrayHelper::map($actor->tags, 'id', 'name');
        }
        parent::__construct($config);
    }

    public function beforeValidate(): bool
    {
        if (is_array($this->newTagsNames)) {
            $this->newTagsNames = array_filter(array_map(static function ($tagName) {
                return StringHelper::spaceReplace($tagName);
            }, array_values($this->newTagsNames)
            ));
        } else {
            $this->newTagsNames = [];
        }
        return parent::beforeValidate();
    }

    public function rules(): array
    {
        return [
            ['newTagsNames', 'each', 'rule' => ['string', 'length' => [0, 255]]],
        ];
    }
}
