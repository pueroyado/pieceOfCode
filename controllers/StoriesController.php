<?php

namespace app\controllers;

use app\components\posting\exception\AccessRestrictedException;
use app\components\posting\exception\NotFoundException;
use app\components\posting\StoriesService;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Пользовательские сторисы
 */
class StoriesController extends ApiController
{
    /**
     * @var StoriesService
     */
    private StoriesService $storiesService;

    /**
     * @param $id
     * @param $module
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct($id, $module, array $config = [])
    {
        $this->storiesService = Yii::$app->get(StoriesService::class);
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array<string, array<string>>
     */
    protected function getActionVerbs(): array
    {
        return [
            '*' => ['get'],
            'delete' => ['delete'],
            'viewed' => ['post']
        ];
    }

    /**
     * Получение списка пользователей
     * сторисы которых доступны для просмотра для авторизованного пользователя
     *
     * @throws \Exception
     */
    public function actionIndex(?int $limit = 20, ?int $offset = 0): array
    {
        return $this->storiesService
            ->getUserSubscriptionStories(
                $this->getAuthorizedUserId(),
                $limit,
                $offset
            );
    }

    /**
     * Удаление сторис
     * Создаёт задачу на удаление
     *
     * @param int $storiesId
     * @return string[]
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDelete(int $storiesId): array
    {
        $authUserId = $this->userManager->getStatement()
            ->getUserId();

        try {
            $storyDto = $this->storiesService->dataProvider
                ->getStoryById($storiesId);

            if ($authUserId !== $storyDto->getContentOwnerUserId()) {
                throw new AccessRestrictedException('Access denied');
            }

            $this->storiesService->remove($storyDto);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (AccessRestrictedException $e) {
            throw new ForbiddenHttpException($e->getMessage());
        }

        return ['status' => 'ok'];
    }

    /**
     * Отмечает сторисы пользователя просмотреными
     * @param int $userId
     * @return string[]
     */
    public function actionViewed(int $userId): array
    {
        $viewerUserId = $this->userManager->getStatement()
            ->getUserId();

        $this->storiesService
            ->viewed($userId, $viewerUserId);

        return ['status' => 'ok'];
    }

    /**
     * Получение контента сторис по запрашиваемому пользователю
     *
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function actionContent(int $userId): array
    {
        return $this->storiesService
            ->getUserStoriesContent(
                $userId,
                $this->getAuthorizedUserId()
            );
    }
}
