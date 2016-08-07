<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateProjectTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_logged_in_user_can_create_a_project()
    {
        // Create client
        $client = App\Client::create();

        // Login first user
        \Auth::login(App\User::first());

        // Visit page
        $this->visit(route('project.create'));

        // Verify collect page loads
        $this->seePageIs(route('project.create'));

        // Fill in form an submit
        $this->type('Test Project', 'name')
            ->type('Test project description.', 'description')
            ->select($client->id, 'client_id')
            ->press('Create');

        // Verify redirected to correct page
        $this->seePageIs(route('project.index'));

        // Verify client added to database
        $this->seeInDatabase('projects', [
            'name' => 'Test Project',
            'description' => 'Test project description.',
            'client_id' => $client->id
        ]);
    }
}
