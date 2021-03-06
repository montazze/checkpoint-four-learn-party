<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DashboardTest extends TestCase
{
    use LearnParty\Tests\PersistTestData;
    use DatabaseMigrations;

    /**
     * Assert that an authenticated user can see the create video page
     * and create it.
     *
     * @return void
     */
    public function testUserCanCreateVideo()
    {
        $this->createAndLoginUser();
        $categories = factory('LearnParty\Category', 4)->create();

        $this->visit('dashboard/videos/create')
             ->see('New Video Post')
             ->press('new-video')
             ->see('The url field is required.')
             ->type('A swanky youtube tutorial title', 'title')
             ->type('http://facebook.com', 'url')
             ->press('new-video')
             ->see('The url must be a valid youtube video Url')
             ->type('https://www.youtube.com/watch?v=pLs4Tex0U1U', 'url')
             ->type('A swanky new description of the video', 'description')
             ->select($categories[0]['id'], 'category_list')
             ->select($categories[1]['id'], 'category_list')
             ->press('new-video')
             ->see('A swanky youtube tutorial title');

        $this->seeinDatabase('videos', [
            'title' => 'A swanky youtube tutorial title',
            'url' => 'pLs4Tex0U1U'
        ]);
    }

    /**
     * Assert that a user can see the edit page of a video they own and update
     * it.
     *
     * @return [type] [description]
     */
    public function testUserCanEditVideo()
    {
        $this->createAndLoginUser();
        $categories = factory('LearnParty\Category', 3)->create();
        $video = factory('LearnParty\Video')->create(['user_id' => 1]);

        $this->visit('dashboard/videos/' . $video->id .'/edit')
             ->see($video->name)
             ->type('swanky new video description of awesome video', 'description')
             ->select($categories[0]['id'], 'category_list')
             ->select($categories[1]['id'], 'category_list')
             ->press('edit-video');

        $this->seeinDatabase('videos', [
            'title' => $video->title,
            'description' => 'swanky new video description of awesome video'
        ]);
    }

    /**
     * Assert that a user can delete a video they created.
     *
     * @return void
     */
    public function testUsercanDeleteVideo()
    {
        $user = $this->createAndLoginUser();
        $video = factory('LearnParty\Video')->create(['user_id' => 1]);
        $this->actingAs($user)
             ->call(
                 'DELETE',
                 'dashboard/videos/' . $video->id
             );

        $this->dontSeeInDatabase('videos', ['id' =>  1]);
    }

    /**
     * Test that a user can see the videos they uploaded
     *
     * @return void
     */
    public function testUserCanSeeUploadedVideos()
    {
        $user = $this->createAndLoginUser();
        $videos = factory('LearnParty\Video', 3)->create(['user_id' => 1]);

        $this->visit('dashboard/videos')
             ->see($videos[0]['title'])
             ->see($videos[1]['title'])
             ->see($videos[2]['title']);
    }

    /**
     * Assert that a user can see the videos that they have favorited
     *
     * @return void
     */
    public function testUserCanSeeFavoritedVideos()
    {
        $user = $this->createAndLoginUser();
        $videos = factory('LearnParty\Video', 3)->create();
        $favorite = factory('LearnParty\Favorite')->create(['user_id' => 1, 'video_id' => 1]);
        $favorite = factory('LearnParty\Favorite')->create(['user_id' => 1, 'video_id' => 2]);
        $favorite = factory('LearnParty\Favorite')->create(['user_id' => 1, 'video_id' => 3]);

        $this->visit('dashboard/videos/favorites')
             ->see($videos[0]['title'])
             ->see($videos[1]['title'])
             ->see($videos[2]['title']);
    }
}
