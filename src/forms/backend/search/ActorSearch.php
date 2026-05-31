<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\forms\backend\search;

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\helpers\ActorHelper;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use Besnovatyj\Actors\entities\actors\Actor;
use yii\helpers\ArrayHelper;

class ActorSearch extends Model
{
    public $id;
    public $name;
    public $taxonomy_id;
    public $status;

    public function rules(): array
    {
        return [
            [['id', 'taxonomy_id', 'status'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Actor::find()->with('mainImage', 'taxonomy');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'taxonomy_id' => $this->taxonomy_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
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

    public function statusList(): array
    {
        return ActorHelper::statusList();
    }
}
