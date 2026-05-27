<?php

namespace Besnovatyj\Actors\entities\actors;

use Besnovatyj\Helpers\FilesystemHelper;
use DateTimeImmutable;
use DomainException;
use Besnovatyj\Meta\MetaBehavior;
use Besnovatyj\PessimisticLock\PessimisticLockBehavior;
use Besnovatyj\DomainEvents\AggregateRoot;
use Besnovatyj\Meta\Meta;
use Besnovatyj\DomainEvents\EventTrait;
use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\entities\actors\queries\ActorQuery;
use Besnovatyj\Actors\entities\Tag;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $taxonomy_id
 * @property integer $main_image_id
 * @property integer $status
 *
 * @property Meta $meta
 * @property Taxonomy $taxonomy
 * @property TagAssignment[] $tagAssignments
 * @property Tag[] $tags
 * @property Image[] $images
 * @property Image $mainImage
 *
 * @mixin PessimisticLockBehavior
 */
class Actor extends ActiveRecord implements AggregateRoot
{
    use EventTrait;

    public const int STATUS_DRAFT = 0;
    public const int STATUS_ACTIVE = 1;

    public Meta $meta;

    public static function create($name, $description, $taxonomyId, $status, Meta $meta): self
    {
        $actor = new static();
        $actor->name = $name;
        $actor->description = $description;
        $actor->taxonomy_id = $taxonomyId;
        $actor->status = $status;
        $actor->created_at = new DateTimeImmutable()->format('Y.m.d H:i:s');
        $actor->meta = $meta;
        return $actor;
    }

    public function edit($name, $description, $status, Meta $meta): void
    {
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->meta = $meta;
        $this->updated_at = new DateTimeImmutable()->format('Y.m.d H:i:s');
    }

    public function changeMainTaxonomy($taxonomyId): void
    {
        $this->taxonomy_id = $taxonomyId;
    }

    // <editor-fold desc="Statuses and flags">

    public function activate(): void
    {
        if ($this->isActive()) {
            throw new DomainException('Already enabled.');
        }
        $this->status = self::STATUS_ACTIVE;
    }

    public function draft(): void
    {
        if ($this->isDraft()) {
            throw new DomainException('Already disabled.');
        }
        $this->status = self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    // </editor-fold>

    public function getSeoTitle(): string
    {
        return $this->meta->title ?: $this->name;
    }

    // <editor-fold desc="Images">
    // Image manipulation methods handled by standalone Actions via yii2-cms-images

    public function setMainImage(?int $imageId): void
    {
        $this->main_image_id = $imageId;
    }

    // </editor-fold>

    // <editor-fold desc="Relations">

    public function getTaxonomy(): ActiveQuery
    {
        return $this->hasOne(Taxonomy::class, ['id' => 'taxonomy_id']);
    }

    public function getTagAssignments(): ActiveQuery
    {
        return $this->hasMany(TagAssignment::class, ['actor_id' => 'id']);
    }

    public function getTags(): ActiveQuery
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('tagAssignments');
    }

    public function getImages(): ActiveQuery
    {
        return $this->hasMany(Image::class, ['actor_id' => 'id'])->orderBy('sort');
    }

    public function getMainImage(): ActiveQuery
    {
        return $this->hasOne(Image::class, ['id' => 'main_image_id']);
    }

    // </editor-fold>

    // <editor-fold desc="Events">

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function beforeDelete(): bool
    {
        if (parent::beforeDelete()) {
            if ($this->images) {
                foreach ($this->images as $image) {
                    $image->delete();
                }
            }

            $origin = Yii::getAlias('@static/origin/Actors') . '/' . $this->id;
            $cache = Yii::getAlias('@static/cache/Actors') . '/' . $this->id;
            FilesystemHelper::deleteDirContents($origin, true);
            FilesystemHelper::deleteDirContents($cache, true);

            return true;
        }
        return false;
    }

    // </editor-fold>

    public static function tableName(): string
    {
        return '{{%actors_actors}}';
    }

    public function behaviors(): array
    {
        return [
            MetaBehavior::class,
            PessimisticLockBehavior::class,
            ...parent::behaviors(),
        ];
    }

    public static function find(): ActorQuery
    {
        return new ActorQuery(static::class);
    }
}
