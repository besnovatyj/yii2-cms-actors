<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\forms\backend\actors;

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Model;

class TaxonomiesForm extends Model
{
    public int|null $main = null;

    public function __construct(?Actor $actors = null, $config = [])
    {
        if ($actors) {
            $this->main = $actors->taxonomy_id;
        }
        parent::__construct($config);
    }

//    public function taxonomiesList(): array
//    {
//        return ArrayHelper::map(Taxonomy::find()->orderBy('lft')->asArray()->all(), 'id', static function (array $taxonomy) {
//            return ($taxonomy['depth'] > 0 ? str_repeat('⚬ ', $taxonomy['depth']) . ' ' : '') . $taxonomy['name'];
//        });
//    }

    public function taxonomiesList(): array
    {
        $scope = new TreeQueryScope(Taxonomy::class);
        return $scope->dropdownTree();
    }

    public function rules(): array
    {
        return [
            ['main', 'required'],
            ['main', 'integer'],
        ];
    }
}
